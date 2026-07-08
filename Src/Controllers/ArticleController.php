<?php

namespace Src\Controllers;

use Src\Helpers\AppLog;
use Src\Models\Article;
use Src\Helpers\ImageHandler;
use Src\Helpers\FileAndPathManager;
use Src\Services\Route;

class ArticleController
{
    protected $model;

    public function __construct()
    {
        $this->model = new Article();
    }

    public function listForMan()
    {
        $articles = $this->model->listForMan();
        echo json_encode(['success' => true, 'data' => $articles]);
    }

    public function getSingle()
    {
        $id = $_POST['id'] ?? null;
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => "ID d'article manquant."]);
            return;
        }
        $article = $this->model->find($id);
        if (!$article) {
            echo json_encode(['success' => false, 'message' => "Article introuvable."]);
            return;
        }
        echo json_encode(['success' => true, 'data' => $article]);
    }

    public function show($id)
    {
        $article = $this->model->find($id);
        if (!$article) {
            echo json_encode(['success' => false, 'message' => "Article introuvable."]);
            return;
        }
        echo json_encode(['success' => true, 'data' => $article]);
    }
    public function getRecent(int $limit = 5)
    {
        $articles = $this->model->getRecentArticles($limit);
        return $articles ?: [];
    }
    public function getArticleBySlug($slug)
    {
        $article = $this->model->getArticleBySlug($slug);
        if (!$article) {
            return null;
        }
        return $article;
    }
    /**
     * Recherche des articles selon différents critères
     * 
     * @return void Renvoie une réponse JSON
     */
    public function search()
    {
        // Recevoir et nettoyer le terme de recherche
        $searchTerm = isset($_GET['q']) ? trim(htmlspecialchars($_GET['q'])) : '';

        // Paramètres de pagination
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? (int)$_GET['per_page'] : 5;

        // Limiter le nombre d'articles par page pour éviter les surcharges
        $perPage = min($perPage, 50);

        // Effectuer la recherche via le modèle
        $searchResults = $this->model->search($searchTerm, [], $page, $perPage);

        // Formatage des résultats pour l'affichage
        if (!empty($searchResults['articles'])) {
            foreach ($searchResults['articles'] as &$article) {
                // Tronquer le contenu pour l'aperçu si le terme de recherche est présent
                if (!empty($searchTerm) && isset($article['content'])) {
                    $article['content_preview'] = $this->getContentPreview($article['content'], $searchTerm);
                    // Ne pas renvoyer le contenu complet pour optimiser
                    unset($article['content']);
                }

                // Formater la date
                if (!empty($article['published_at'])) {
                    $date = new \DateTime($article['published_at']);
                    $article['formatted_date'] = $date->format('d/m/Y H:i');
                }
            }
        }

        return ([
            'articles' => $searchResults['articles'] ?? [],
            'current_page' => $searchResults['current_page'],
            'total_pages' => $searchResults['total_pages'],
            'total_results' => $searchResults['total_results'],
            'per_page' => $searchResults['per_page'],
            'search_term' => $searchTerm,
        ]);
    }

    /**
     * Vérifie si une date est valide au format Y-m-d
     * 
     * @param string $date Date à vérifier
     * @return bool True si la date est valide
     */
    private function isValidDate(string $date): bool
    {
        $format = 'Y-m-d';
        $dateTime = \DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }

    /**
     * Extrait un aperçu du contenu autour du terme de recherche
     * 
     * @param string $content Contenu complet
     * @param string $searchTerm Terme de recherche
     * @param int $length Longueur maximale de l'aperçu
     * @return string Aperçu du contenu
     */
    private function getContentPreview(string $content, string $searchTerm, int $length = 200): string
    {
        // Nettoyer le contenu HTML
        $cleanContent = strip_tags($content);

        // Rechercher la position du terme (insensible à la casse)
        $pos = mb_stripos($cleanContent, $searchTerm);

        if ($pos !== false) {
            // Calculer les positions de début et de fin pour l'extrait
            $start = max(0, $pos - floor($length / 2));
            $extract = mb_substr($cleanContent, $start, $length);

            // Ajouter des points de suspension si nécessaire
            if ($start > 0) {
                $extract = '...' . $extract;
            }
            if ($start + $length < mb_strlen($cleanContent)) {
                $extract .= '...';
            }

            // Mettre en évidence le terme recherché
            return $extract;
        }

        // Si le terme n'est pas trouvé, retourner le début du contenu
        return mb_substr($cleanContent, 0, $length) . (mb_strlen($cleanContent) > $length ? '...' : '');
    }

    public function create()
    {
        $fileData = $_FILES;
        [$data, $errors] = $this->validateArticleData($_POST);

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        $meta = [
            'description' => $this->sanitize($_POST['article_meta_descp']) ?? '',
            'tags' => $this->sanitize($_POST['article_meta_tags']) ?? '',
        ];
        // $data['meta'] = '{"description":"' . $meta['description'] . ',"tags":"' . $meta['tags'] . '"}';
        $data['meta'] = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (empty($_POST['slug'])) {
            $errors['slug'] = "Le slug est requis.";
        } elseif (!preg_match('/^[a-z0-9\-]+$/i', $_POST['slug'])) {
            $errors['slug'] = "Le slug doit contenir uniquement des lettres, chiffres ou tirets.";
        } else {
            if ($this->model->uniqueSlug($_POST['slug'])) {
                $errors['slug'] = "Le slug existe déjà.";
            } else {
                $data['slug'] = $this->sanitize($_POST['slug']);
            }
        }
        // Image
        if (isset($fileData['image']) && $fileData['image']['error'] == 0) {
            try {
                $uploaded = $this->uploadImage($fileData['image'], $_POST['slug']);
                if ($uploaded) {
                    $data['image'] = basename($uploaded);
                } else {
                    $errors['image'] = "Erreur lors de l'upload de l'image.";
                }
            } catch (\Exception $e) {
                $errors['image'] = "Erreur critique lors de l'upload.";
                AppLog::warning("Upload image échoué : " . $e->getMessage());
            }
        }
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        $result = $this->model->create($data);
        header("Content-Type: application/json");
        if (!$result) {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la création de l'article."]);
            exit;
        }
        if ($data['is_published'] ?? false) {
            Route::addSitemapUrl(
                WEB_URL,
                '/actualites/' . $data['slug'],
                0.5,
                'monthly'
            );
        }

        echo json_encode(['success' => true, 'message' => "Article ajouté avec succès."]);
    }

    public function edit()
    {
        // Vérifier que la requête est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => "Méthode non autorisée."]);
            return;
        }

        // Vérifier l'ID de l'article
        $id = $_POST['article_id'] ?? null;
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => "ID d'article manquant ou invalide."]);
            return;
        }

        // Récupérer l'article existant
        $article = $this->model->find($id);
        if (!$article) {
            echo json_encode(['success' => false, 'message' => "Article introuvable."]);
            return;
        }

        // Validation des données
        [$data, $errors] = $this->validateArticleData($_POST);
        $wasPublished = (bool)$article['is_published'];
        $nowPublished = (bool)($data['is_published'] ?? $article['is_published']);

        // Vérification du slug séparément car il a besoin d'une validation supplémentaire
        if (empty($_POST['slug'])) {
            $errors['slug'] = "Le slug est requis.";
        } elseif (!preg_match('/^[a-z0-9\-]+$/i', $_POST['slug'])) {
            $errors['slug'] = "Le slug doit contenir uniquement des lettres, chiffres ou tirets.";
        } else {
            // Vérifier si le slug est unique (sauf s'il s'agit du même que l'article actuel)
            if ($_POST['slug'] !== $article['slug'] && $this->model->uniqueSlug($_POST['slug'])) {
                $errors['slug'] = "Ce slug existe déjà pour un autre article.";
            } else {
                $data['slug'] = $this->sanitize($_POST['slug']);
            }
        }

        // Si des erreurs existent, retourner une réponse d'erreur
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Identifier les données qui ont réellement changé
        $finalData = [];
        foreach ($data as $key => $value) {
            if (!isset($article[$key]) || $value != $article[$key]) {
                $finalData[$key] = $value;
            }
        }

        // Ne pas mettre à jour la date de publication automatiquement lors d'une édition
        if (isset($finalData['published_at'])) {
            // Si l'article n'était pas publié avant et qu'il l'est maintenant, alors seulement définir la date
            if (!$article['is_published'] && $data['is_published']) {
                $finalData['published_at'] = date("Y-m-d H:i:s");
            } else {
                unset($finalData['published_at']);
            }
        }

        // Traitement de l'image
        $keepCurrentImage = isset($_POST['keep_current_image']) && $_POST['keep_current_image'] === "on";

        if (!$keepCurrentImage && !empty($_FILES['image']['name'])) {
            // Upload de la nouvelle image
            $uploaded = $this->uploadImage($_FILES['image'], $data['slug']);
            if ($uploaded) {
                $finalData['image'] = basename($uploaded);

                // Supprimer l'ancienne image si elle existe
                if (!empty($article['image'])) {
                    $oldImagePath = FileAndPathManager::getDirectoryPath('article-image') . $article['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            } else {
                $errors['image'] = "Erreur lors de l'upload de l'image.";
                echo json_encode(['success' => false, 'errors' => $errors]);
                return;
            }
        } elseif ($data['slug'] !== $article['slug'] && !empty($article['image']) && $keepCurrentImage) {
            // Renommer l'image existante si le slug a changé mais on garde l'image
            $oldImagePath = FileAndPathManager::getDirectoryPath('article-image') . $article['image'];
            $newImagePath = FileAndPathManager::getDirectoryPath('article-image') . $data['slug'] . '.' . pathinfo($article['image'], PATHINFO_EXTENSION);

            if (file_exists($oldImagePath) && rename($oldImagePath, $newImagePath)) {
                $finalData['image'] = basename($newImagePath);
            }
        }

        // Vérifier s'il y a des modifications à appliquer
        if (empty($finalData)) {
            echo json_encode(['success' => false, 'message' => "Aucune modification apportée."]);
            return;
        }
        $finalData['id'] = $id;
        // Mettre à jour l'article
        $result = $this->model->update($finalData);
        if (!$result) {
            echo json_encode(['success' => false, 'message' => "Erreur lors de la mise à jour de l'article."]);
            return;
        }
        $oldSlug = $article['slug'];
        $newSlug = $data['slug'];

        $oldUrl = "/actualites/" . $oldSlug;
        $newUrl = "/actualites/" . $newSlug;

        // Cas 1 : L’article est désormais publié (et ne l'était pas avant)
        if (!$wasPublished && $nowPublished) {
            Route::addSitemapUrl(WEB_URL, $newUrl, 0.5, 'monthly');
        }

        // Cas 2 : L’article était publié et ne l’est plus
        elseif ($wasPublished && !$nowPublished) {
            Route::removeSitemapUrl($oldUrl);
        }

        // Cas 3 : Slug modifié et toujours publié
        elseif ($wasPublished && $nowPublished && $oldSlug !== $newSlug) {
            if (Route::removeSitemapUrl($oldUrl)) {
                Route::addSitemapUrl(WEB_URL, $newUrl, 0.5, 'monthly');
            } else {
                AppLog::error("Erreur lors du remplacement du lien sitemap : $oldUrl -> $newUrl");
            }
        }
        echo json_encode(['success' => true, 'message' => "Article mis à jour avec succès."]);
    }

    public function delete()
    {
        $id = $_POST['article_id'] ?? null;
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['success' => false, 'message' => "ID d'article manquant ou invalide."]);
            return;
        }
        // Récupérer l'article existant
        $article = $this->model->find($id);
        if (!$article) {
            echo json_encode(['success' => false, 'message' => "Article introuvable."]);
            return;
        }
        $link = "/actualites/" . $article['slug'];
        // Supprimer l'image associée à l'article
        if (!empty($article['image'])) {
            $imagePath = FileAndPathManager::getDirectoryPath('article-image') . $article['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        // Supprimer l'article de la base de données
        $deleted = $this->model->delete($id);
        if ($deleted) {
            Route::removeSitemapUrl($link);
            echo json_encode([
                'success' => true,
                'message' => "Article supprimé."
            ]);
        } else {

            echo json_encode([
                'success' => false,
                'message' => "Échec de la suppression."
            ]);
        }
    }

    private function validateArticleData(array $postData): array
    {
        $errors = [];
        $data = [];
        // Vérifier si l'utilisateur est connecté
        if (isset($_SESSION['user_id'])) {
            $data['creator'] = $_SESSION['user_id'];
        } else {
            Route::redirect('/logout');
            exit;
        }

        // Titre
        if (empty($postData['title'])) {
            $errors['title'] = "Le titre est requis.";
        } else {
            $data['title'] = $this->sanitize($postData['title']);
        }

        // Contenu
        if (empty($postData['content']) || trim(strip_tags($postData['content'])) === '') {
            $errors['content'] = "Le contenu ne peut pas être vide.";
        } else {
            $data['content'] = $postData['content'];
        }

        // Extrait
        $data['excerpt'] = isset($postData['excerpt']) ? $this->sanitize($postData['excerpt']) : '';
        if (str_word_count(strip_tags($data['excerpt'])) > 200) {
            $errors['excerpt'] = "L'extrait ne peut pas dépasser 200 mots.";
        }

        // Affiché
        $data['is_published'] = isset($postData['afficher']) ? true : false;
        $data['published_at'] = $data['is_published'] ? date("Y-m-d H:i:s") : null;

        return [$data, $errors];
    }

    private function sanitize($data)
    {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    private function uploadImage($image, $slug)
    {
        $uploadedImage = null;
        $uploadDir = FileAndPathManager::getDirectoryPath('article-image');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $slug;
        $filePath = $uploadDir . $fileName;

        $this->resizeImage($image['tmp_name'], $filePath);

        try {
            $imageHandler = new ImageHandler($filePath);
            $webpFilePath = $uploadDir . pathinfo($fileName, PATHINFO_FILENAME) . '.webp';

            if ($imageHandler->convertToWebP($webpFilePath, 80)) {
                $uploadedImage = $webpFilePath;
            } else {
                AppLog::warning("Conversion WebP échouée pour : " . $fileName);
                return false;
            }
        } catch (\Exception $e) {
            AppLog::warning("Erreur image : " . $image['name'] . " - " . $e->getMessage());
            return false;
        }

        return $uploadedImage;
    }

    private function resizeImage($sourcePath, $destinationPath, $maxWidth = 1024, $maxHeight = 720)
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
                AppLog::critical("Type d'image non supporté.");
                exit;
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

    public function getPaginated($page = 1, $limit = 10)
    {
        $articles = $this->model->getPaginated($page, $limit);
        $total = $this->model->countAllPublished();
        $totalPages = ceil($total / $limit);
        return [
            'articles' => $articles,
            'current_page' => $page,
            'total_pages' => $totalPages,
        ];
    }
}
