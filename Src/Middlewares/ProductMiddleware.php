<?php

namespace Src\Middlewares;

use Src\Controllers\CategorieController;
use Src\Controllers\PageController;
use Src\Controllers\ProductController;

class ProductMiddleware
{
    public static function checkProductExists($product)
    {
        $page = new PageController;
        $productData = (new ProductController)->getBySlug($product);
        if (!$productData) {
            $page->handleProduct404();
            exit;
        }
        if ($productData) {
            $data = [
                'mainProduct' => $productData[0],
                'relatedProducts' => [],
            ];
            $id = $data['mainProduct']['id'];
            if (!empty($data['mainProduct']['categories'])) {
                $categories = $data['mainProduct']['categories'];
                $categorie = null;
                if (is_array($categories)) {
                    $categorie = $categories[0];
                } else if (is_string($categories)) {
                    $categorie = $categories;
                }
                if ($categorie) {
                    $categorieController = new CategorieController;
                    $related = $categorieController->getRelatedProducts($categorie, $id);
                    if ($related) {
                        $data['relatedProducts'] = $related;
                    }
                }
            }
            $page->product($data);
            exit;
        }
    }

    public static function getPaginatedProducts()
    {
        $input = file_get_contents('php://input');
        // Vérification et assainissement des données POST
        $data = $input ? json_decode($input, true) : $_POST;
        $requiredFields = ['page', 'per_page', 'sortBy', 'order', 'search'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo json_encode(["success" => false, "message" => "Paramètre manquant : $field"]);
                exit;
            }
        }

        // Assainissement des valeurs
        $page = filter_var($data['page'], FILTER_VALIDATE_INT, ["options" => ["default" => 1, "min_range" => 1]]);
        $perPage = filter_var($data['per_page'], FILTER_VALIDATE_INT, ["options" => ["default" => 10, "min_range" => 1]]);
        $sortBy = in_array($data['sortBy'], ['created_at', 'price', 'title']) ? $data['sortBy'] : 'created_at';
        $order = strtoupper($data['order']) === 'ASC' ? 'ASC' : 'DESC';
        $search = htmlspecialchars(trim($data['search']));

        // Calcul de l'offset pour la pagination
        $offset = ($page - 1) * $perPage;

        // // Appel du contrôleur
        $productController = new ProductController();
        $result = $productController->paginatedProducts($page, $perPage, $sortBy, $order, $search, true);
        $is = false;
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'super_manager') {
            $is = true;
        }
        // Vérification des résultats et réponse JSON
        if ($result) {
            echo json_encode([
                "success" => true,
                "data" => $result,
                'is' => $is
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Aucun produit trouvé."
            ]);
        }
        exit;
    }
}
