<?php

namespace Src\Controllers;

use Exception;
use GrahamCampbell\ResultType\Success;
use Src\Helpers\AppLog;
use Src\Services\Route;
use Src\Models\Categorie;
use Src\Helpers\ImageHandler;
use Src\Helpers\FileAndPathManager;
use Src\Helpers\UrlHelper;

class CategorieController
{
    private $categorieModel;

    public function __construct()
    {
        $this->categorieModel = new Categorie();
    }
    public function getTrendingCategories()
    {
        return $this->categorieModel->getTrendingCategories();
    }
    // Affiche la liste des catégories
    public function index()
    {
        $categories = $this->categorieModel->getAllCategories();
        // Envoyer une réponse JSON
        return !empty($categories) ? json_encode($categories) : "Aucune categorie trouvé.";
    }
    public function catsForMan()
    {
        $categories = $this->categorieModel->getAllCategories();
        // Envoyer une réponse JSON
        echo !empty($categories) ? json_encode(
            [
                "success" => true,
                "data" => $categories
            ]
        ) : json_encode(
            [
                "success" => false,
            ]
        );
    }
    public function getEssentials()
    {
        $categories = $this->categorieModel->getEssentials();
        // Envoyer une réponse JSON
        echo !empty($categories) ? json_encode(
            [
                "success" => true,
                "data" => $categories
            ]
        ) : json_encode(
            [
                "success" => false,
            ]
        );
    }
    public function edit()
    {
        $data = [];
        foreach ($_POST as $key => $value) {
            $data[$key] = htmlentities(trim($value));
        }
        $dbcat = $this->categorieModel->getCategorieByID($data['id']);
        if ($dbcat) {
            $newCategorie = [];
            if ($dbcat['title'] != $data['title']) {
                $newCategorie["title"] = $data['title'];
            }
            if ($dbcat['description'] != $data['description']) {
                $newCategorie["description"] = $data['description'];
            }
            if ($dbcat['tags'] != $data['tags']) {
                $newCategorie["tags"] = $data['tags'];
            }
            if ($dbcat['reduction'] != $data['reduction']) {
                $newCategorie["reduction"] = intval($data['reduction'] ?? "0");
            }
            if (count($newCategorie)) {
                $result = $this->categorieModel->edit(intval($data['id']), $newCategorie);
                if ($result) {
                    if (!empty($newCategorie['title'])) {
                        $oldLink = \Src\Helpers\UrlHelper::generateCategoryLink($dbcat['title']);
                        $newlink = \Src\Helpers\UrlHelper::generateCategoryLink($newCategorie['title']);
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
                    }
                    echo json_encode(["success" => true, "message" => "Categorie modifié avec succé!"]);
                    exit;
                } else {
                    echo json_encode(["success" => false, "message" => "Erreur lors de la modification!"]);
                    exit;
                }
            } else {
                echo json_encode(["success" => false, "message" => "Pas de changement récuperé!"]);
                exit;
            }
        } else {
            echo json_encode(["success" => false, "message" => "Aucune categorie correspond au donnée entrées!"]);
            exit;
        }
    }
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [];
            foreach ($_POST as $k => $v) {
                $data[$k] = htmlspecialchars($v);
            }

            // Vérification du titre
            if (empty($data['title'])) {
                echo json_encode(["success" => false, "error" => "title_empty"]);
                return;
            }
            $data['title'] = \Src\Helpers\StringUtils::replaceAccents($data['title']);
            // Vérification si la catégorie existe déjà
            if (count($cat = $this->categorieModel->getCategorieByTitle($data['title']))) {
                echo json_encode(["success" => false, "error" => "cat_found"]);
                return;
            }

            // Gestion de l'image avec la fonction uploadImages
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                try {
                    // Appel de la fonction d'upload d'image
                    $uploadedImages = $this->uploadImage($_FILES['image'], $data['title']);
                    // `null` ici car on n'a pas encore d'ID de produit
                    $data['image_path'] = basename($uploadedImages); // Assumer que l'image est le premier fichier téléchargé
                } catch (Exception $e) {
                    echo json_encode(["success" => false, "error" => "image_upload_failed", "message" => $e->getMessage()]);
                    return;
                }
            }

            // Créer la catégorie
            $result = $this->categorieModel->createCategory($data);
            if ($result) {
                $catLink = \Src\Helpers\UrlHelper::generateCategoryLink($data['title']);
                Route::addSitemapUrl(
                    WEB_URL,
                    $catLink,
                    0.7,
                    'monthly'
                );
                echo json_encode(["success" => true, "error" => "cat_succed", "id" => $result]);
                AppLog::info("Catégorie " . $data['title'] . " ajoutée avec succès.");
                return;
            } else {
                echo json_encode(["success" => false, "error" => "add_error"]);
                return;
            }
        }
    }
    private function uploadImage($image, $categorieTitle)
    {
        $uploadedImage = null;
        $uploadDir = FileAndPathManager::getDirectoryPath('category-image'); // Change ce path si nécessaire
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if ($image['error'] === 0) {
            $fileName = $categorieTitle;
            $filePath = $uploadDir . $fileName;

            // Redimensionner l'image avant de la sauvegarder
            $this->resizeImage($image['tmp_name'], $filePath);

            try {
                // Créer une instance de ImageHandler pour gérer l'image
                $imageHandler = new ImageHandler($filePath);

                // Définir le chemin de destination pour l'image WebP
                $webpFilePath = $uploadDir . pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

                // Convertir l'image en WebP
                if ($imageHandler->convertToWebP($webpFilePath, 80)) {
                    // Si la conversion réussit, définir le chemin du fichier WebP
                    $uploadedImage = $webpFilePath;
                } else {
                    throw new Exception("Échec de la conversion en WebP pour : " . $fileName);
                }
            } catch (Exception $e) {
                throw new Exception("Erreur lors de l'upload ou de la conversion de l'image : " . $image['name'] . " - " . $e->getMessage());
            }
        } else {
            throw new Exception("Erreur lors du téléchargement de l'image : code d'erreur " . $image['error']);
        }

        return $uploadedImage;
    }
    public function getInfos()
    {
        $id = $_POST['id'];
        if (empty($id) || !is_numeric($id) || $id == 0) {
            echo json_encode([
                'success' => false,
            ]);
            exit;
        }
        $result = $this->categorieModel->getInfos($id);
        if ($result) {

            $data = [];
            $data['id'] = $result[0]['id'];
            $data['title'] = $result[0]['title'];
            $data['description'] = $result[0]['description'];
            $data['tags'] = $result[0]['tags'];
            $data['reduction'] = $result[0]['reduction'];
            $data['is_trend'] = $result[0]['is_trend'];
            $data['visites'] = $result[0]['visites'];
            $data['created_at'] = $result[0]['created_at'];
            $data['product_count'] = $result[0]['product_count'];
            $data['image'] = '/assets/images/category-image/';
            $data['link'] = UrlHelper::generateCategoryLink($result[0]['title']);
            if (empty($result[0]['image_path'])) {
                $data['image'] .= 'No_Image_Available.jpg';
            } else if (FileAndPathManager::fileExists('category-image', $result[0]['image_path'])) {
                $data['image'] .= $result[0]['image_path'];
            } else {
                $data['image'] .= 'unfound.jpg';
            }

            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            exit;
        }
        echo json_encode([
            'success' => false,
        ]);
        exit;
    }
    private function resizeImage($sourcePath, $destinationPath, $maxWidth = 800, $maxHeight = 800)
    {
        list($srcWidth, $srcHeight, $srcType) = getimagesize($sourcePath);
        $ratio = $srcWidth / $srcHeight;
        if ($maxWidth / $maxHeight > $ratio) {
            $newWidth = $maxHeight * $ratio;
            $newHeight = $maxHeight;
        } else {
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
        }
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        switch ($srcType) {
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($sourcePath);
                break;
            default:
                throw new Exception("Type d'image non supporté.");
        }
        imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        switch ($srcType) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $destinationPath, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $destinationPath, 9);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $destinationPath);
                break;
        }
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
            $result = $this->categorieModel->setTrending($addedCatsStr, $removedCatsStr);

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
    public function delete()
    {
        $id = htmlentities($_POST['categoryIdToDelete']);
        if (!is_numeric($id)) {
            echo "ID invalide.";
            return;
        }
        try {
            if ($this->deleteCategoryImage($id)) {
                $categorie = $this->categorieModel->getCategorieByID($id);
                $link = \Src\Helpers\UrlHelper::generateCategoryLink($categorie['title']);
                $result = $this->categorieModel->deleteCategory($id);
                if ($result) {
                    if (Route::urlExistsInSitemap(WEB_URL, $link)) {
                        Route::removeSitemapUrl($link);
                    }
                    AppLog::info("Catégorie '$id' supprimée avec succès.");
                    echo json_encode(["success" => true, "message" => "Catégorie supprimée avec succès."]);
                }
            } else {
                AppLog::error("Erreur lors de la suppression de la catégorie '$id'.");
                echo json_encode(["success" => false, "message" => "Erreur lors de la suppression de la catégorie."]);
            }
        } catch (Exception $e) {
            echo json_encode(["status" => "erro", "message" => $e->getMessage()]);
        }
    }
    private function deleteCategoryImage($id)
    {
        $result = $this->getCategoryImage($id)[0]["image_path"];
        if (($result)) {
            if (!FileAndPathManager::fileExists('category-image', $result)) {
                return false;
            } else {
                FileAndPathManager::deleteFile('category-image', $result);
                return true;
            }
        }
        return true;
    }
    private function getCategoryImage($catId)
    {
        return $this->categorieModel->getCategoryImage($catId);
    }
    public function getCategoryPaginatedProducts(string $categoryTitle, int $page = 1, int $perPage = 10, string $sortBy = 'created_at', string $order = 'DESC'): array
    {
        try {
            // Calculer l'offset pour la pagination
            $offset = ($page - 1) * $perPage;

            // Récupérer les produits paginés de la catégorie spécifique
            $products = $this->categorieModel->getProductsByCategory($categoryTitle, $offset, $perPage, $sortBy, $order);
            // Récupérer le nombre total de produits dans cette catégorie
            $totalProducts = count($this->categorieModel->getTotalProductsByCategory($categoryTitle));
            $totalPages = ceil($totalProducts / $perPage);
            $category = [
                'id' => $products[0]['category_id'] ?? '',
                'title' => $products[0]['category_title'] ?? '',
                'description' => $products[0]['category_description'] ?? '',
                'image' => $products[0]['category_image'] ?? '',
                'tags' => !empty($products[0]['category_tags']) ?? "",
            ];
            // Nettoyer les données des produits (supprimer les informations de catégorie redondantes)
            foreach ($products as &$product) {
                unset(
                    $product['category_id'],
                    $product['category_title'],
                    $product['category_description'],
                    $product['category_tags'],
                    $product['category_image']
                );
            }

            // Retourner les produits paginés et les informations de pagination
            return [
                'category' => $category,
                'products' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_products' => $totalProducts,
                    'total_pages' => $totalPages,
                ],
            ];
        } catch (Exception $e) {
            AppLog::error("Erreur lors de la récupération des produits paginés pour la catégorie '$categoryTitle' : " . $e->getMessage());
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
    public function isCategory($title)
    {
        return $this->categorieModel->isCategory($title);
    }
    public function updateVisites($data)
    {
        if (empty($data['id']) || !isset($data['visites'])) {
            return false;
        }
        return $this->categorieModel->updateVisites($data['id'], $data['visites']);
    }
    public function addToCategory()
    {
        header('Content-Type: application/json');

        // Vérification de la catégorie
        if (empty($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
            echo json_encode([
                'success' => false,
                'message' => "La catégorie sélectionnée n'existe pas !"
            ]);
            exit;
        }

        $category = intval($_POST['category_id']);
        $added = [];
        $skipped = [];
        $removed = [];

        // Traitement des produits à ajouter
        if (!empty($_POST['selected_products']) && is_array($_POST['selected_products'])) {
            foreach ($_POST['selected_products'] as $item) {
                $productId = intval(htmlentities(trim($item['product_id'])));
                if (!$this->categorieModel->alreadyIn($category, $productId)) {
                    $added[] = $productId;
                } else {
                    $skipped[] = $productId;
                }
            }

            if (!empty($added)) {
                $this->categorieModel->updateCategories($category, $added, 'add');
            }
        }

        // Traitement des produits à retirer
        if (!empty($_POST['deselected_products']) && is_array($_POST['deselected_products'])) {
            foreach ($_POST['deselected_products'] as $item) {
                $productId = intval(htmlentities(trim($item['product_id'])));
                $removed[] = $productId;
            }

            if (!empty($removed)) {
                $this->categorieModel->updateCategories($category, $removed, 'remove');
            }
        }

        // Construction de la réponse complète
        $success = (!empty($added) || !empty($removed));

        echo json_encode([
            'success' => $success,
            'message' => $success
                ? "Mise à jour des catégories effectuée avec succès."
                : "Aucune modification apportée.",
            'operations' => [
                'add' => [
                    'success' => !empty($added),
                    'added_products' => $added,
                    'skipped_products' => $skipped
                ],
                'remove' => [
                    'success' => !empty($removed),
                    'removed_products' => $removed
                ]
            ]
        ]);
        exit;
    }
    public function getRelatedProducts($category, $product)
    {
        $result = $this->categorieModel->getRelatedProducts($category, $product);
        foreach ($result as $k => $product) {
            $result[$k]['images'] = explode(',', $result[$k]['images']);
        }
        return $result ? $result : [];
    }
}
