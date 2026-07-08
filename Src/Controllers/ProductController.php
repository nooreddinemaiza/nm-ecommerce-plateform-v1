<?php

namespace Src\Controllers;

use Exception;
use Src\Helpers\AppLog;
use Src\Models\Product;
use Src\Helpers\UrlHelper;
use Src\Helpers\ShopHelper;
use Src\Helpers\ImageHandler;
use Src\Helpers\SessionManager;
use Src\Helpers\FileAndPathManager;
use Src\Helpers\Helper;
use Src\Services\Route;

class ProductController
{
    private $productModel;
    private const MAX_IMAGES = 4;
    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function deleteProduct(SessionManager $sessionManager)
    {
        $operationStatus = [];
        $imageDeleted = false;

        try {
            // Vérifier l'authentification
            if (!$sessionManager->isAuthenticated()) {
                $this->logError("Accès non autorisé.");
            }

            // Récupérer et valider l'ID du produit
            $productId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

            // Récupérer les données du produit
            $productData = $this->productModel->getProductById($productId);
            if (!$productData) {
                $operationStatus[] = [
                    'operation' => 'deleteProduct',
                    'status' => 'error',
                    'message' => "Produit introuvable!"
                ];
            } else {
                $productData = $this->combineDataByKey($productData, 'title')[0] ?? null;

                $link = UrlHelper::generateProductLink($productData['slug'], $productId);
                if (Route::urlExistsInSitemap(WEB_URL, $link)) {
                    Route::removeSitemapUrl($link);
                }
                // Supprimer les images associées
                if (!empty($productData['images'])) {
                    $imageIdList = [];

                    // Vérifier si les images sont sous forme de tableau ou de chaîne
                    if (is_array($productData['images'])) {
                        foreach ($productData['images'] as $image) {
                            $imageIdList[] = $image;
                        }
                    } elseif (is_string($productData['images'])) {
                        $imageIdList[] = $productData['images'];
                    }

                    // Suppression des fichiers images
                    foreach ($imageIdList as $image) {
                        if (FileAndPathManager::fileExists('product-image', $image)) {
                            if (!FileAndPathManager::deleteFile('product-image', $image)) {
                                $operationStatus[] = [
                                    'operation' => 'deleteImages',
                                    'status' => 'error',
                                    'message' => "Échec de la suppression de l'image : $image"
                                ];
                            } else {
                                $imageDeleted = true;
                            }
                        } else {
                            $imageDeleted = true; // Considérer comme supprimé si le fichier n'existe pas
                        }
                    }
                } else {
                    $imageDeleted = true;
                }

                // Suppression du produit après suppression des images
                if ($imageDeleted) {
                    if ($this->productModel->deleteProduct($productId)) {
                        $operationStatus[] = [
                            'operation' => 'deleteProduct',
                            'status' => 'success',
                            'message' => 'Produit supprimé avec succès.'
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $this->logError("Erreur lors de la suppression du produit : " . $e->getMessage());
            $operationStatus[] = [
                'operation' => 'deleteProduct',
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        // Retourner la réponse en JSON
        echo json_encode(["operationStatus" => $operationStatus]);
    }
    public function addProduct(SessionManager $sessionManager)
    {
        try {
            // Vérifier l'authentification
            if (!$sessionManager->isAuthenticated()) {
                $sessionManager->destroy();
                Route::redirect("/");
            }

            // Valider les données
            $data = $_POST;

            foreach ($data as $k => $v) {
                if (is_array($data[$k])) {
                    foreach ($data[$k] as $s => $t) {
                        $data[$k][$s] = trim(htmlspecialchars($t));
                    }
                } else {
                    $data[$k] = trim(htmlspecialchars($v));
                }
            }

            if (empty($data['title'])) {
                $errors['title'] = "Le titre est requis.";
            }

            if (empty($data['price'])) {
                $errors['price'] = "Le prix est requis.";
            }
            if (empty($data['slug'])) {
                $errors['slug'] = "Le slug est requis.";
            } elseif (!preg_match('/^[a-z0-9\-]+$/i', $data['slug'])) {
                $errors['slug'] = "Doit contenir uniquement des lettres, chiffres ou tirets.";
            } else {
                if ($this->productModel->uniqueSlug($data['slug'])) {
                    $errors['slug'] = "Existe déjà.";
                }
            }

            if (!empty($errors)) {
                $message = '';
                foreach ($errors as $key => $value) {
                    $message .= "$key : $value";
                }
                echo json_encode(['success' => false, 'errors' => $message]);
                exit;
            }
            // Ajouter le produit
            $productId = $this->productModel->createProduct($data, $sessionManager);

            if ($productId) {
                // Ajouter les catégories
                if (!empty($data['categories'])) {
                    $this->productModel->addProductCategories($productId, $data['categories']);
                }

                // Gérer l'upload des images
                if (!empty($_FILES['images'])) {
                    $postImages = $_FILES['images'];
                    $_FILES = [];
                    $postImages = $this->limitImages($postImages, self::MAX_IMAGES);
                    $this->validateImages($postImages);
                    $uploadedImages = $this->uploadImages($postImages, $data['slug']);
                    $this->productModel->addProductImages($productId, $uploadedImages);
                }
            }

            echo json_encode(["success" => true]);
        } catch (Exception $e) {
            AppLog::error("Erreur lors de l'ajout du produit : " . $e->getMessage());
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        }
    }
    public function updateProduct()
    {
        $operationStatus = [];
        $postData = [];
        $hasError = false;

        try {
            // Sécurisation des données POST
            foreach ($_POST as $key => $value) {
                $postData[$key] = is_array($value)
                    ? array_map('htmlspecialchars', array_map('trim', $value))
                    : htmlspecialchars(trim($value));
            }

            // Champs autorisés
            $allowedFields = ['id', 'title', 'price', 'stock', 'slug', 'reduction', 'appReduction', 'description', 'status', 'tags', 'meta_description', 'meta_tag', 'selectedCategories', 'deselectedCategories', 'imagesId', 'deletedImages', 'newImages'];
            foreach ($postData as $k => $v) {
                if (!in_array($k, $allowedFields)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Une erreur est survenue, veuillez réessayer plus tard.',
                        'operationStatus' => [
                            [
                                'operation' => 'validation',
                                'status' => 'error',
                                'message' => 'Champ non autorisé: ' . $k
                            ]
                        ]
                    ]);
                    exit;
                }
            }

            if (!empty($postData['tags'])) {
                $postData['tag'] = $postData['tags'];
                unset($postData['tags']);
            }
            // Vérification des champs obligatoires
            if (empty($postData['title']) || empty($postData['price']) || empty($postData['slug'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Titre, Prix et Slug sont obligatoires.',
                    'operationStatus' => [
                        [
                            'operation' => 'validation',
                            'status' => 'error',
                            'message' => 'Champs obligatoires manquants'
                        ]
                    ]
                ]);
                exit;
            }



            $productId = $postData['id'];
            $dbProduct = $this->getProductById($productId);
            if (!$dbProduct) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Produit introuvable.',
                    'operationStatus' => [
                        [
                            'operation' => 'fetch',
                            'status' => 'error',
                            'message' => 'Produit avec ID ' . $productId . ' non trouvé'
                        ]
                    ]
                ]);
                exit;
            }

            // Comparaison des données avec celles en base
            $differences = $this->checkData($postData, $dbProduct);
            if (!$differences && empty($_FILES['newImages']['name'][0]) && empty($postData['deletedImages'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucune modification détectée.',
                    'operationStatus' => [
                        [
                            'operation' => 'comparison',
                            'status' => 'warning',
                            'message' => 'Aucun changement à appliquer'
                        ]
                    ]
                ]);
                exit;
            }

            // Vérification du slug
            if (isset($differences['slug'])) {
                if (!preg_match('/^[a-z0-9\-]+$/i', $differences['slug'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Slug invalide. Uniquement lettres, chiffres ou tirets.',
                        'operationStatus' => [
                            [
                                'operation' => 'slug_validation',
                                'status' => 'error',
                                'message' => 'Format de slug invalide'
                            ]
                        ]
                    ]);
                    exit;
                } elseif ($this->productModel->uniqueSlug($differences['slug'], $productId)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Slug déjà utilisé.',
                        'operationStatus' => [
                            [
                                'operation' => 'slug_validation',
                                'status' => 'error',
                                'message' => 'Slug déjà existant'
                            ]
                        ]
                    ]);
                    exit;
                }

                $operationStatus[] = [
                    'operation' => 'slug_validation',
                    'status' => 'success',
                    'message' => 'Slug validé'
                ];
            }

            // Gestion des images
            if (isset($dbProduct['images']) && is_array($dbProduct['images'])) {
                $currentImageCount = count($dbProduct['images']);
            } elseif (!empty($dbProduct['images'])) {
                $currentImageCount = 1;
            } else {
                $currentImageCount = 0;
            }
            $deletedImageCount = 0;

            // Suppression des images
            if (!empty($postData['deletedImages'])) {
                $deletedImageCount = count($postData['deletedImages']);
                $deletionResult = $this->handleImageDeletions($postData['deletedImages']);
                // Vérification des résultats détaillés de la suppression
                if ($deletionResult['success']) {
                    $operationStatus[] = [
                        'operation' => 'images_deletion',
                        'status' => 'success',
                        'message' => count($deletionResult['deleted']) . ' image(s) supprimée(s) sur ' . $deletedImageCount . ' demandée(s)',
                        'details' => [
                            'count' => [
                                'requested' => $deletedImageCount,
                                'deleted' => count($deletionResult['deleted'])
                            ],
                            'deleted' => $deletionResult['deleted'],
                            'failed' => $deletionResult['failed']
                        ]
                    ];

                    // Si certaines images n'ont pas été supprimées, on l'indique
                    if (count($deletionResult['failed']) > 0) {
                        $hasError = true;
                        $operationStatus[] = [
                            'operation' => 'images_deletion_partial',
                            'status' => 'warning',
                            'message' => count($deletionResult['failed']) . ' image(s) n\'ont pas pu être supprimées',
                            'details' => [
                                'failed_images' => $deletionResult['failed']
                            ]
                        ];
                    }
                } else {
                    $hasError = true;
                    $operationStatus[] = [
                        'operation' => 'images_deletion',
                        'status' => 'error',
                        'message' => 'Échec de suppression des images',
                        'details' => [
                            'deleted' => $deletionResult['deleted'],
                            'failed' => $deletionResult['failed'],
                            'error' => $deletionResult['error']
                        ]
                    ];
                }
            }
            // Ajout des nouvelles images
            if (!empty($_FILES['newImages']['name'][0])) {
                $newImages = $_FILES['newImages'];
                $totalAfterUpload = $currentImageCount - $deletedImageCount + count($newImages['name']);
                if ($totalAfterUpload > self::MAX_IMAGES) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Nombre maximal d\'images dépassé.',
                        'operationStatus' => array_merge($operationStatus, [
                            [
                                'operation' => 'images_upload',
                                'status' => 'error',
                                'message' => 'Limite de ' . self::MAX_IMAGES . ' images dépassée',
                                'details' => [
                                    'current' => $currentImageCount,
                                    'deleted' => $deletedImageCount,
                                    'new' => count($newImages['name']),
                                    'total_after' => $totalAfterUpload,
                                    'max_allowed' => self::MAX_IMAGES
                                ]
                            ]
                        ])
                    ]);
                    exit;
                }

                $uploadResult = $this->uploadImages($newImages, $postData['slug']);
                if ($uploadResult) {
                    $uploadResult =  $this->productModel->addProductImages($productId, $uploadResult);
                    if ($uploadResult) {
                        $operationStatus[] = [
                            'operation' => 'images_upload',
                            'status' => 'success',
                            'message' => count($newImages['name']) . ' nouvelle(s) image(s) ajoutée(s)',
                            'details' => [
                                'count' => count($newImages['name'])
                            ]
                        ];
                    }
                } else {
                    $hasError = true;
                    $operationStatus[] = [
                        'operation' => 'images_upload',
                        'status' => 'error',
                        'message' => 'Échec de téléchargement des images'
                    ];
                }
            }

            // Mise à jour des catégories

            if (!empty($differences['selectedCategories']) || !empty($differences['deselectedCategories'])) {
                // Catégories envoyées par l'utilisateur
                $postSelected = !empty($differences['selectedCategories']) ? array_map('intval', explode(',', $differences['selectedCategories'])) : [];
                $postDeselected = !empty($differences['deselectedCategories']) ? array_map('intval', explode(',', $differences['deselectedCategories'])) : [];

                // Catégories déjà en base
                $dbCategories = array_map('intval', is_array($dbProduct['categorieID']) ? $dbProduct['categorieID'] : [$dbProduct['categorieID']]);

                // Ajouts : sélectionnés par l'utilisateur et non déjà en base
                $categoriesAdd = array_diff($postSelected, $dbCategories);

                // Suppressions : dans les catégories en base ET explicitement désélectionnés
                $categoriesRem = array_intersect($dbCategories, $postDeselected);

                [$addResult, $removeResult] = $this->handleCategories($productId, $categoriesAdd, $categoriesRem);

                if ($addResult !== false && $removeResult !== false) {
                    $operationStatus[] = [
                        'operation' => 'categories',
                        'status' => 'success',
                        'message' => 'Catégories mises à jour avec succès',
                        'details' => [
                            'added' => count($categoriesAdd),
                            'removed' => count($categoriesRem)
                        ]
                    ];
                } else {
                    $hasError = true;
                    $operationStatus[] = [
                        'operation' => 'categories',
                        'status' => 'error',
                        'message' => 'Erreur lors de la mise à jour des catégories',
                        'details' => [
                            'add_status' => $addResult,
                            'remove_status' => $removeResult
                        ]
                    ];
                }
            }
            unset($differences['selectedCategories'], $differences['deselectedCategories']);
            // Mise à jour du produit
            if (!empty($differences)) {
                $updResult = $this->productModel->updateProduct($productId, $differences);
                if ($updResult) {
                    if (
                        !empty($differences['slug'])
                        && ((!empty($differences['status'])
                            &&  $differences['status']) || $dbProduct['status'] == 'affiche')
                    ) {
                        $this->handleSiteMap($differences['slug'], $postData['slug']);
                    } elseif (!empty($differences['status'])) {
                        $this->handleSiteMap($dbProduct['slug']);
                    }
                    $operationStatus[] = [
                        'operation' => 'product_update',
                        'status' => 'success',
                        'message' => 'Informations du produit mises à jour',
                        'details' => [
                            'fields_updated' => array_keys($differences)
                        ]
                    ];
                } else {
                    $hasError = true;
                    $operationStatus[] = [
                        'operation' => 'product_update',
                        'status' => 'error',
                        'message' => 'Échec de mise à jour des informations du produit'
                    ];
                }
            }

            // Réponse finale
            if (!$hasError && !empty($operationStatus)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Produit mis à jour avec succès.',
                    'operationStatus' => $operationStatus
                ]);
            } else if ($hasError) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Des erreurs sont survenues pendant la mise à jour.',
                    'operationStatus' => $operationStatus
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Aucune opération effectuée.',
                    'operationStatus' => $operationStatus
                ]);
            }
        } catch (Exception $e) {
            $this->logError($e->getMessage());
            echo json_encode([
                "success" => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'operationStatus' => array_merge($operationStatus, [
                    [
                        'operation' => 'exception',
                        'status' => 'error',
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                ])
            ]);
        }
    }
    private function handleCategories($id, $add, $remove)
    {
        $addResult = $removeResult = true;

        if (!empty($add)) {
            $addResult = $this->productModel->updateCategories($id, $add, 'add');
        }

        if (!empty($remove)) {
            $removeResult = $this->productModel->updateCategories($id, $remove, 'remove');
        }

        return [$addResult, $removeResult];
    }
    // Afficher un produit par son ID
    public function viewProduct()
    {
        $productId = htmlspecialchars(trim($_POST["id"]));
        $productA = $this->productModel->getProductById((int)$productId);
        if (!$productA) {
            echo json_encode([
                'success' => false,
            ]);
            exit;
        }
        $product = $this->combineDataByKey($productA, 'title');

        $product[0]['link'] = UrlHelper::generateProductLink($product[0]['slug']);

        if (!empty($product[0]['images'])) {
            $images = $product[0]['images'];
            if (is_array($images)) {
                $dataImages = [];
                foreach ($images as $image) {
                    if (!FileAndPathManager::fileExists('product-image', $image)) {
                        $dataImages[] = "unfound.jpg";
                    } else {
                        $dataImages[] = $image;
                    }
                }
                $product[0]['images'] = $dataImages;
            } elseif (is_string($images)) {
                if (!FileAndPathManager::fileExists('product-image', $images)) {
                    $product[0]['images'] = "unfound.jpg";
                }
            } else {
                $product[0]['images'][0] = "No_Image_Available.jpg";
            }
        } else $product[0]['images'][0] = "No_Image_Available.jpg";

        // Décoder tous les champs avant l'envoi
        foreach ($product[0] as $key => $value) {
            if (is_string($value)) {
                $product[0][$key] = htmlspecialchars_decode($value, ENT_QUOTES);
            } elseif (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_string($subValue)) {
                        $product[0][$key][$subKey] = htmlspecialchars_decode($subValue, ENT_QUOTES);
                    }
                }
            }
        }

        echo !empty($product) ? json_encode($product) : json_encode(['status' => false]);
    }

    /**
     * Compare les données du formulaire avec les données de la base pour identifier les modifications
     * 
     * @param array $postData Les données soumises dans le formulaire
     * @param array $dbData Les données actuelles du produit dans la base de données
     * @return array Les données modifiées uniquement (pour la mise à jour)
     */
    private function checkData($postData, $dbData)
    {
        $differences = [];

        // Liste des champs à vérifier avec leur type
        $fieldsToCheck = [
            'title' => 'string',
            'price' => 'float',
            'stock' => 'int',
            'slug' => 'string',
            'reduction' => 'float',
            'apply_reduction_on' => 'float',
            'description' => 'string',
            'status' => 'string',
            'tags' => 'string',
            'meta_description' => 'string',
            'meta_tags' => 'string',
            'selectedCategories' => 'array',  // Cas spécial traité séparément
            'deselectedCategories' => 'array'  // Cas spécial traité séparément
        ];
        if (isset($postData['appReduction'])) {
            $postData['apply_reduction_on'] = $postData['appReduction'];
            unset($postData['appReduction']);
        }
        foreach ($fieldsToCheck as $field => $type) {
            // Vérifier si le champ existe dans les deux ensembles de données
            if (!isset($dbData[$field]) && !isset($postData[$field])) {
                continue; // Ignorer les champs absents des deux côtés
            }

            // Cas spécial pour les catégories
            if ($field === 'selectedCategories') {
                // Convertir les catégories DB et POST en tableaux triés d'entiers pour une comparaison correcte
                $dbCategories = isset($dbData['categorieID']) ? (
                    is_array($dbData['categorieID']) ?
                    array_map('intval', $dbData['categorieID']) :
                    [intval($dbData['categorieID'])]
                ) : [];

                $postCategories = isset($postData[$field]) && !empty($postData[$field]) ?
                    array_map('intval', explode(',', $postData[$field])) :
                    [];

                // Trier les tableaux pour une comparaison cohérente
                sort($dbCategories);
                sort($postCategories);

                // Comparer les tableaux triés
                if (json_encode($dbCategories) !== json_encode($postCategories) && !empty($postData[$field])) {
                    $differences[$field] = $postData[$field];
                }

                continue; // Passer au champ suivant
            }

            // Traiter les valeurs selon leur type pour une comparaison correcte
            $dbValue = isset($dbData[$field]) ? $dbData[$field] : null;
            $postValue = isset($postData[$field]) ? $postData[$field] : null;

            // Normalisation des valeurs en fonction du type
            switch ($type) {
                case 'int':
                    $dbValue = $dbValue !== null ? (int)$dbValue : 0;
                    $postValue = $postValue !== null ? (int)$postValue : 0;
                    break;

                case 'float':
                    // Normaliser pour éviter les problèmes de comparaison de nombres à virgule flottante
                    $dbValue = $dbValue !== null ? (float)number_format((float)$dbValue, 2, '.', '') : 0.0;
                    $postValue = $postValue !== null ? (float)number_format((float)$postValue, 2, '.', '') : 0.0;
                    break;

                case 'string':
                    $dbValue = $dbValue !== null ? trim((string)$dbValue) : '';
                    $postValue = $postValue !== null ? trim((string)$postValue) : '';
                    break;
            }

            // Comparaison stricte en tenant compte du type
            if ($dbValue !== $postValue) {
                $differences[$field] = $postValue;
            }
        }

        return $differences;
    }
    public function updateStock()
    {
        foreach ($_POST as $key => $value) {
            $$key = intval(trim(htmlspecialchars($value)));
        }
        if (!isset($id) || !isset($newstock)) {
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenu!'
            ]);
            exit;
        }
        $isproduct = $this->getProductById($id);
        if ($isproduct) {
            $result = $this->productModel->updateProduct($id, ['stock' => $newstock, 'stock_update' => date('y-m-d h:m:s')]);
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Stock modifié avec succé!'
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Probleme lors de la modification'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Les informations fournis ne corresspond à aucun produit!'
            ]);
        }
    }
    public function observeStock()
    {
        $result = $this->productModel->observeStock();
        if ($result) {
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            exit;
        }
        echo json_encode([
            'success' => false,
        ]);
        exit;
    }
    public function updateStatus()
    {
        foreach ($_POST as $key => $value) {
            $$key = intval(trim(htmlspecialchars($value)));
        }
        if (!isset($id) || !isset($status)) {
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenu!'
            ]);
            exit;
        }
        $isproduct = $this->getProductById($id);
        if ($isproduct) {
            $result = $this->productModel->updateProduct($id, ['status' => $status == 0 ? 'reduit' : 'affiche']);
            if ($result) {
                if ($status !== 0) {
                    $this->handleSiteMap($isproduct['slug']);
                } else {
                    $this->removeSiteMap($isproduct['slug']);
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Status modifié avec succé'
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Probleme lors de la modification'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Les informations fournis ne corresspond à aucun produit!'
            ]);
        }
    }

    private function handleSiteMap(String $newslug, ?string $oldSlug = null)
    {
        if (!empty($newslug) && !empty($oldSlug)) {
            $newlink = UrlHelper::generateProductLink($newslug);
            $oldLink = UrlHelper::generateProductLink($oldSlug);
            if (!Route::urlExistsInSitemap(WEB_URL, $oldLink)) {
                Route::addSitemapUrl(
                    WEB_URL,
                    $newlink,
                    0.7,
                    'monthly'
                );
            } else {
                if (Route::removeSitemapUrl($oldLink)) {
                    Route::addSitemapUrl(
                        WEB_URL,
                        $newlink,
                        0.7,
                        'monthly'
                    );
                } else AppLog::error("Erreur lors de la mise à jour du lien du produit dans le sitemap.");
            }
        } else {
            $newlink = UrlHelper::generateProductLink($newslug);
            Route::addSitemapUrl(
                WEB_URL,
                $newlink,
                0.7,
                'monthly'
            );
        }
    }
    private function removeSiteMap($slug)
    {
        $slug = UrlHelper::generateProductLink($slug);
        if (Route::urlExistsInSitemap(WEB_URL, $slug)) {
            return Route::removeSitemapUrl($slug);
        }
    }
    /**
     * Récupère les produits paginés
     *
     * @param int $page Le numéro de la page
     * @param int $perPage Le nombre de produits par page
     * @param string $sortBy Le champ de tri
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @return array
     */
    public function getPaginatedProducts($page = 1, $perPage = 10, $sortBy = 'created_at', $order = 'DESC', $search = "", $manager = false)
    {
        try {
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $perPage;

            // Récupérer les produits paginés avec filtre de recherche
            $products = $this->productModel->getPaginatedProducts($offset, $perPage, $sortBy, $order, $search, $manager);

            // Récupérer le nombre total de produits (avec ou sans recherche)
            $totalProducts = $this->productModel->getTotalProducts($search, $manager);
            $totalPages = ceil($totalProducts / $perPage);

            // Retourner les produits paginés et les informations de pagination
            return [
                'products' => Helper::combineDataByKey($products, "id"),
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => $totalProducts,
                    'total_pages' => $totalPages,
                ],
            ];
        } catch (Exception $e) {
            AppLog::error("Erreur lors de la récupération des produits paginés : " . $e->getMessage());
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => 0,
                    'total_pages' => 0,
                ],
            ];
        }
    }
    public function getProductsTitles()
    {
        return $this->productModel->getProductsTitles();
    }

    public function setTrending()
    {
        try {
            // Lecture des données JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!$data || !isset($data['add']) || !isset($data['remove'])) {
                echo json_encode([
                    "success" => false,
                    "error" => "invalid_data",
                    "message" => "Données invalides"
                ]);
                return;
            }

            // Assainir les IDs
            $addedCats = array_filter(array_map('intval', explode(',', $data['add'])));
            $removedCats = array_filter(array_map('intval', explode(',', $data['remove'])));

            // Convertir en chaîne pour requête SQL
            $addedCatsStr = !empty($addedCats) ? implode(',', $addedCats) : null;
            $removedCatsStr = !empty($removedCats) ? implode(',', $removedCats) : null;

            // Mise à jour dans la base de données
            $result = $this->productModel->setTrending($addedCatsStr, $removedCatsStr);

            echo json_encode([
                "success" => $result,
                "message" => $result ? "Mise à jour réussie" : "Échec de la mise à jour"
            ]);
        } catch (Exception $e) {
            AppLog::error("Erreur dans setTrending : " . $e->getMessage());
            echo json_encode([
                "success" => false,
                "error" => "update_error",
                "message" => "Erreur lors de la mise à jour"
            ]);
        }
    }
    public function mesProduits()
    {
        $products = $this->productModel->listPoruct();
        $product = $this->combineDataByKey($products, 'id');
        foreach ($product as $k => $tab) {
            $product[$k]['link'] = \Src\Helpers\UrlHelper::generateProductLink($product[$k]['slug'], $product[$k]['id']);
            if (is_array($tab['images'])) {
                foreach ($tab['images'] as $t  => $img) {
                    list($imageId, $imageName) = explode('|', $img);
                    if (!FileAndPathManager::fileExists('product-image', $imageName)) {
                        $product[$k]['images'][$t] = "$imageId|unfound.jpg";
                    }
                }
            } elseif (is_string($tab['images'])) {
                list($imageId, $imageName) = explode('|', $tab['images']);
                if (!FileAndPathManager::fileExists('product-image', $imageName)) {
                    $product[$k]['images'] = "$imageId|unfound.jpg";
                }
            } else {
                $product[$k]['images'] = "0|No_Image_Available.jpg";
            }
        }
        return !empty($product) ? json_encode($product) : [];
    }
    /**
     * Affiche un produit par son ID avec gestion complète des images
     */
    public function getEssentials()
    {
        $result = $this->productModel->getEssentials();
        if ($result) {
            foreach ($result as $key => $row) {
                $categoryIds = explode(',', $row['category_ids']);
                $categoryTitles = explode(',', $row['category_titles']);

                $categories = [];
                foreach ($categoryIds as $i => $id) {
                    $categories[] = [
                        'category_id' => $id,
                        'category_title' => $categoryTitles[$i] ?? ''
                    ];
                }

                unset($row['category_ids'], $row['category_titles']);
                $row['categories'] = $categories;
                $result[$key] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $this->combineDataByKey($result, 'product_id')
            ]);
            exit;
        }
        echo json_encode([
            'success' => false
        ]);
        exit;
    }
    public function getprotobyTitle($product)
    {
        $productId = htmlspecialchars($product);
        $productA = $this->productModel->getProductByTitle((int)$productId);
        $productA[0]['visited'] = $productA[0]['visited'] + 1;
        $this->productModel->updateProduct($productId, ['visited_times' => $productA[0]['visited']]);
        $products = $this->combineDataByKey($productA, 'id');
        if (!empty($products)) {
            $products = $this->processProductImages($products);
        }

        return !empty($products) ? $products : ['status' => false];
    }
    public function getBySlug($slug)
    {
        try {
            $slug = htmlspecialchars(trim($slug));
            $productData = $this->productModel->getBySlug($slug);

            if (empty($productData)) {
                (new PageController)->handleProduct404();
                exit;
            }

            // Combiner les données et traiter les images
            $products = $this->combineDataByKey($productData, 'id');
            if (!empty($products)) {

                // Mettre à jour le compteur de visites
                $visitedCount = isset($productData[0]['visited']) ? (int)$productData[0]['visited'] + 1 : 1;
                $this->productModel->updateBySlug($slug, ['visited_times' => $visitedCount]);
                $products[0]['images'] = explode(',', $products[0]['images']);
            }
            return !empty($products) ? $products : [];
        } catch (Exception $e) {
            (new PageController)->handleProduct404();
            exit;
        }
    }

    /**
     * Traite les images pour tous les produits
     */
    private function processProductImages(array $products): array
    {
        foreach ($products as $index => $product) {
            if (isset($product['images'])) {
                $imagesList = [];
                $imagesArray = explode(',', $product['images']);

                foreach ($imagesArray as $img) {
                    list($id, $path) = explode('|', $img);
                    $imagesList[] = [
                        'id' => $id,
                        'path' => $path
                    ];
                }

                $products[$index]['images'] = $imagesList;
            }
        }
        return $products;
    }

    /**
     * Obtient le chemin de l'image pour l'affichage
     */
    public function getProductImagePath(string $imagePath): string
    {
        if (empty($imagePath)) {
            return "No_Image_Available.jpg";
        }

        $parts = explode('|', $imagePath);
        if (count($parts) !== 2) {
            return "No_Image_Available.jpg";
        }

        list(, $imageName) = $parts;
        return FileAndPathManager::fileExists('product-image', $imageName)
            ? $imageName
            : "unfound.jpg";
    }

    // Rechercher des produits par texte
    public function searchProducts($searchText, $page = 1, $perPage = 5, $sortBy = 'created_at', $order = 'DESC')
    {

        try {
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $perPage;

            // Récupérer les produits paginés depuis le modèle
            $products = $this->productModel->searchProducts($searchText, $offset, $perPage, $sortBy, $order);

            // Récupérer le nombre total de produits pour calculer le nombre total de pages
            $totalProducts = $this->productModel->getTotalSearchProducts($searchText);
            $totalPages = ceil($totalProducts / $perPage);
            // Retourner les produits paginés et les informations de pagination
            return [
                'products' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => $totalProducts,
                    'total_pages' => $totalPages,
                ],
            ];
        } catch (Exception $e) {
            AppLog::error("Erreur lors de la récupération des produits paginés : " . $e->getMessage());
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => 0,
                    'total_pages' => 0,
                ],
            ];
        }
    }
    // Rechercher des produits par texte (maximum de 5 résultats)
    public function searchProductsLimited($searchText)
    {
        try {
            // Récupérer les produits en limitant à 5 résultats
            $data = [];
            $products = $this->productModel->searchProducts($searchText, 0, 5, 'created_at', 'DESC');
            foreach ($products as $product) {
                $link = UrlHelper::generateProductLink(
                    htmlspecialchars($product['slug']),
                    $product['id']
                );
                $product['link'] = $link;
                $product['images'] = ShopHelper::getMainImage($product);
                $product['categories'] = ShopHelper::getProductCategories($product);
                $data[] = $product;
            }
            // Retourner les produits sans pagination
            return [
                'products' => $data,
                'total_products' => count($data),
            ];
        } catch (Exception $e) {
            AppLog::error("Erreur lors de la récupération des produits : " . $e->getMessage());
            return [
                'products' => [],
                'total_products' => 0,
            ];
        }
    }

    // Afficher la liste des produits
    public function listProducts()
    {
        $products = $this->productModel->getProducts();
        $product = $this->combineDataByKey($products, 'id');
        foreach ($product as $k => $tab) {
            $product[$k]['link'] = \Src\Helpers\UrlHelper::generateProductLink($product[$k]['slug'], $product[$k]['id']);
            if (is_array($tab['images'])) {
                foreach ($tab['images'] as $t  => $img) {
                    list($imageId, $imageName) = explode('|', $img);
                    if (!FileAndPathManager::fileExists('product-image', $imageName)) {
                        $product[$k]['images'][$t] = "$imageId|unfound.jpg";
                    }
                }
            } elseif (is_string($tab['images'])) {
                list($imageId, $imageName) = explode('|', $tab['images']);
                if (!FileAndPathManager::fileExists('product-image', $imageName)) {
                    $product[$k]['images'] = "$imageId|unfound.jpg";
                }
            } else {
                $product[$k]['images'] = "0|No_Image_Available.jpg";
            }
        }
        return !empty($product) ? json_encode($product) : [];
    }
    public function getProductsList()
    {
        return $this->productModel->getProductsList();
    }
    public function getProductsListLimit($limit)
    {
        return $this->productModel->getLimitedDistinctProducts($limit);
    }

    public function is_product($product)
    {
        return $this->productModel->is_product($product);
    }
    private function validateProductData($data)
    {
        foreach ($data as $k => $v) {
            if (is_array($data[$k])) {
                foreach ($data[$k] as $s => $t) {
                    $data[$k][$s] = trim(htmlspecialchars($t));
                }
            } else {
                $data[$k] = trim(htmlspecialchars($v));
            }
        }
        if (!empty($data['title']) && !is_string($data['title'])) {
            return false;
        }
        if (empty($data['price']  || (float)$data['price'] < 0)) {
            return false;
        }
        return $data;
    }

    /**
     * Télécharge et traite plusieurs images en utilisant les indexes disponibles
     * 
     * @param array $images Tableau des images téléchargées ($_FILES['images'] par exemple)
     * @param string $slug Slug du produit pour nommer les fichiers
     * @return array Liste des chemins des images téléchargées
     */
    private function uploadImages($images, $slug)
    {
        $uploadedImages = [];
        $uploadDir = FileAndPathManager::getDirectoryPath('product-image');

        // Vérifier si les données d'upload sont valides
        if (empty($images) || !isset($images['tmp_name']) || !is_array($images['tmp_name'])) {
            return $uploadedImages;
        }

        // Créer le répertoire si nécessaire
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Trouver tous les indices disponibles avant de commencer le traitement
        $availableIndexes = [];
        for ($i = 0; $i < self::MAX_IMAGES; $i++) {
            $testFileName = "$slug-i$i";
            $webpTestFileName = "$slug-i$i.webp";

            // Vérifier si l'un des formats existe déjà
            if (
                !FileAndPathManager::fileExists('product-image', $testFileName) &&
                !FileAndPathManager::fileExists('product-image', $webpTestFileName)
            ) {
                $availableIndexes[] = $i;
            }
        }

        // Vérifier combien d'images on peut uploader
        $uploadCount = min(count($images['tmp_name']), count($availableIndexes));

        // Parcourir chaque image téléchargée
        for ($j = 0; $j < $uploadCount; $j++) {
            $tmpName = $images['tmp_name'][$j];

            // Vérifier si le fichier est bien un upload valide
            if (!is_uploaded_file($tmpName) || $images['error'][$j] !== UPLOAD_ERR_OK) {
                $this->logError("Erreur d'upload pour l'image " . ($images['name'][$j] ?? 'inconnue') .
                    " (code: " . ($images['error'][$j] ?? 'inconnu') . ")");
                continue;
            }

            // Prendre le premier indice disponible et le retirer de la liste
            $nextIndex = array_shift($availableIndexes);
            $fileName = "$slug-i$nextIndex";
            $filePath = $uploadDir . $fileName;

            // Redimensionner l'image avant de la traiter
            $this->resizeImage($tmpName, $filePath);
            // Créer une instance de ImageHandler pour manipuler l'image
            try {
                $imageHandler = new ImageHandler($filePath);

                // Définir le chemin du fichier converti en WebP
                $webpFileName = pathinfo($fileName, PATHINFO_FILENAME) . '.webp';
                $webpFilePath = $uploadDir . $webpFileName;

                // Convertir l'image en WebP
                if ($imageHandler->convertToWebP($webpFilePath, 80)) {
                    // Si la conversion réussit, ajouter le nom du fichier WebP à la liste
                    $uploadedImages[] = $webpFileName;

                    // Supprimer le fichier temporaire original après conversion réussie
                    if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) !== 'webp') {
                        unlink($filePath);
                    }
                } else {
                    $this->logError("Échec de la conversion en WebP pour : " . $fileName);
                    // Nettoyage en cas d'échec
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    // Remettre l'indice dans la liste des disponibles
                    array_unshift($availableIndexes, $nextIndex);
                }
            } catch (Exception $e) {
                $this->logError("Erreur lors de l'upload ou de la conversion de l'image : " .
                    $images['name'][$j] . " - " . $e->getMessage());
                // Nettoyage en cas d'exception
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                // Remettre l'indice dans la liste des disponibles
                array_unshift($availableIndexes, $nextIndex);
            }
        }

        return $uploadedImages;
    }
    private function combineDataByKey($data, $key)
    {
        $combinedData = [];
        foreach ($data as $item) {
            $uniqueKey = $item[$key];
            if (!isset($combinedData[$uniqueKey])) {
                $combinedData[$uniqueKey] = $item;
            } else {
                foreach ($item as $field => $value) {
                    if ($field !== $key) {
                        if ($combinedData[$uniqueKey][$field] !== $value) {
                            if (is_array($combinedData[$uniqueKey][$field])) {
                                if (!in_array($value, $combinedData[$uniqueKey][$field])) {
                                    $combinedData[$uniqueKey][$field][] = $value;
                                }
                            } else {
                                $combinedData[$uniqueKey][$field] = [$combinedData[$uniqueKey][$field], $value];
                            }
                        }
                    }
                }
            }
        }
        return array_values($combinedData);
    }

    private function resizeImage(string $sourcePath, string $destinationPath, int $maxWidth = 1024, int $maxHeight = 1024): bool
    {
        $imageInfo = getimagesize($sourcePath);
        if ($imageInfo === false) {
            $this->logError("Impossible de lire les dimensions de l'image.");
            return false;
        }

        [$srcWidth, $srcHeight, $srcType] = $imageInfo;
        $ratio = $srcWidth / $srcHeight;

        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth  = (int) round($maxHeight * $ratio);
            $newHeight = (int) $maxHeight;
        } else {
            $newWidth  = (int) $maxWidth;
            $newHeight = (int) round($maxWidth / $ratio);
        }

        // Chargement de l'image source selon le type
        $srcImage = match ($srcType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF  => imagecreatefromgif($sourcePath),
            default        => null,
        };

        if ($srcImage === null || $srcImage === false) {
            $this->logError("Type d'image non supporté.");
            return false;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Gestion de la transparence
        if ($srcType === IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparent);
        } elseif ($srcType === IMAGETYPE_GIF) {
            $transparentIndex = imagecolortransparent($srcImage);
            if ($transparentIndex !== -1) {
                $transparentColor = imagecolorsforindex($srcImage, $transparentIndex);
                $transparentIndex = imagecolorallocate(
                    $newImage,
                    $transparentColor['red'],
                    $transparentColor['green'],
                    $transparentColor['blue']
                );
                imagefill($newImage, 0, 0, $transparentIndex);
                imagecolortransparent($newImage, $transparentIndex);
            }
        }

        imagecopyresampled(
            $newImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $srcWidth,
            $srcHeight
        );

        $result = match ($srcType) {
            IMAGETYPE_JPEG => imagejpeg($newImage, $destinationPath, 90),
            IMAGETYPE_PNG  => imagepng($newImage, $destinationPath, 9),
            IMAGETYPE_GIF  => imagegif($newImage, $destinationPath),
            default        => false,
        };

        // Libération mémoire — plus obligatoire en PHP 8+ (GdImage est un objet
        // géré par le GC), mais reste utile pour libérer la mémoire immédiatement
        // sur de grosses images traitées en boucle.
        unset($srcImage, $newImage);

        return (bool) $result;
    }

    private function validateImages($images)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024;
        foreach ($images['tmp_name'] as $index => $tmpName) {
            if (!file_exists($tmpName)) {
                $this->logError("Le fichier temporaire n'existe pas pour '{$images['name'][$index]}'.");
            }
            $mimeType = mime_content_type($tmpName);
            if (!in_array($mimeType, $allowedTypes)) {
                $this->logError("Le fichier '{$images['name'][$index]}' n'est pas une image valide.");
            }
            if ($images['size'][$index] > $maxFileSize) {
                $this->logError("Le fichier '{$images['name'][$index]}' dépasse la taille maximale autorisée (5 Mo).");
            }
        }
        return $images;
    }


    private function logError($message)
    {
        AppLog::error($message);
    }
    private function limitImages($files, $maxImages = self::MAX_IMAGES)
    {
        $totalFiles = count($files['name']);

        // Si le nombre d'images est inférieur ou égal à $maxImages, on ne modifie rien
        if ($totalFiles <= $maxImages) {
            return $files;
        }

        // Sélection aléatoire de 4 fichiers parmi ceux uploadés
        $randomKeys = array_rand($files['name'], $maxImages);

        // Assurer que $randomKeys est un tableau même s'il y a exactement $maxImages fichiers
        if (!is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        // Créer un nouvel array contenant seulement les fichiers sélectionnés
        $limitedFiles = [
            'name'     => array_intersect_key($files['name'], $randomKeys),
            'type'     => array_intersect_key($files['type'], $randomKeys),
            'tmp_name' => array_intersect_key($files['tmp_name'], $randomKeys),
            'error'    => array_intersect_key($files['error'], $randomKeys),
            'size'     => array_intersect_key($files['size'], $randomKeys)
        ];

        return $limitedFiles;
    }
    public function getProductById($productId)
    {
        $product = $this->combineDataByKey($this->productModel->getProductById((int)$productId), 'title');
        return $product ? $product[0] : null;
    }
    public function checkDevisPr($productId)
    {
        $product = $this->combineDataByKey($this->productModel->checkDevisPr((int)$productId), 'title');
        return $product ? $product[0] : null;
    }

    /**
     * Gère la suppression des images et retourne des informations détaillées sur le résultat
     * 
     * @param array $images Tableau des identifiants d'images à supprimer
     * @return array Résultat détaillé de l'opération
     */
    private function handleImageDeletions($images)
    {
        $result = [
            'success' => false,
            'deleted' => [],
            'failed' => [],
            'error' => null
        ];

        // Vérification du paramètre
        if (empty($images) || !is_array($images)) {
            $result['error'] = 'Liste d\'images invalide';
            return $result;
        }

        // Suppression des fichiers physiques
        foreach ($images as $image) {
            if ($image == "unfound.jpg" || $image == "No_Image_Available.jpg") break;
            try {
                if (FileAndPathManager::fileExists('product-image', $image)) {
                    if (FileAndPathManager::deleteFile('product-image', $image)) {
                        $result['deleted'][] = $image;
                    } else {
                        $result['failed'][] = $image;
                    }
                } else {
                    $result['failed'][] = $image;
                }
            } catch (\Exception $e) {
                $result['failed'][] = $image;
                $result['error'] = $e->getMessage();
            }
        }

        // Si des images ont été supprimées physiquement, on les supprime en base
        if (!empty($result['deleted'])) {
            try {
                $deleted = $this->productModel->delProdImgRange($result['deleted']);
                if (!$deleted) {
                    // Les fichiers ont été supprimés mais pas les entrées en base
                    $result['error'] = 'Les fichiers ont été supprimés mais pas les entrées en base de données';
                    // On garde quand même les images dans "deleted" car les fichiers ont été supprimés
                }
            } catch (\Exception $e) {
                $result['error'] = 'Erreur lors de la suppression en base: ' . $e->getMessage();
            }
        }

        // L'opération est considérée comme réussie si au moins une image a été supprimée
        $result['success'] = !empty($result['deleted']);

        return $result;
    }

    public function paginatedProducts($page = 1, $perPage = 10, $sortBy = 'created_at', $order = 'DESC', $search = "", $manager = false)
    {
        try {
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $perPage;

            // Récupérer les produits paginés avec filtre de recherche
            $products = $this->productModel->paginatedProducts($offset, $perPage, $sortBy, $order, $search, $manager);

            // Récupérer le nombre total de produits (avec ou sans recherche)
            $totalProducts = $this->productModel->totalproducts($search, $manager);
            $totalPages = ceil($totalProducts / $perPage);

            // Retourner les produits paginés et les informations de pagination
            return [
                'products' => $products, // Pas besoin de Helper::combineDataByKey car pas de jointure
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total_items' => (int)$totalProducts,
                    'total_pages' => (int)$totalPages,
                ],
            ];
        } catch (Exception $e) {
            AppLog::error("Erreur lors de la récupération des produits paginés : " . $e->getMessage());
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total_items' => 0,
                    'total_pages' => 0,
                ],
            ];
        }
    }
}
