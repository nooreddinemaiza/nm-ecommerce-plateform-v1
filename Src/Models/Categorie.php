<?php

namespace Src\Models;

use Src\Database\Database;
use Src\Helpers\AppLog;

class Categorie
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getTrendingCategories()
    {
        return $this->db->selectA(
            table: "categories",
            columns: "*",
            conditions: "is_trend = 1",
            orderBy: "created_at DESC",
        );
    }

    // Récupérer toutes les catégories
    public function getAllCategories()
    {
        return $this->db->selectA(
            table: "categories",
            columns: "*",
            orderBy: "created_at DESC"
        );
    }
    public function getEssentials()
    {
        return $this->db->selectA(
            table: "categories",
            columns: "id,
                      title",
            orderBy: "created_at DESC"
        );
    }
    public function edit($id, $data)
    {
        return $this->db->update(
            'categories',
            $data,
            'id = :id',
            [':id' => $id],
        );
    }
    // Récupérer toutes les catégories
    public function getCategorieByTitle($title)
    {
        $result = $this->db->select(
            'categories',
            '*',
            '(title = ?)',
            [$title]
        );
        return $result ? $result : [];
    }
    public function getCategorieByID($id)
    {
        $result = $this->db->select(
            'categories',
            '*',
            '(id = ?)',
            [$id]
        );
        return $result ? $result[0] : [];
    }
    // Ajouter une nouvelle catégorie
    public function createCategory($data)
    {
        return $this->db->insert("categories", $data);
    }

    // Supprimer une catégorie
    public function deleteCategory($id)
    {
        return $this->db->delete("categories", "id = ?", [$id]);
    }
    public function setTrending($added, $removed)
    {
        try {
            // Commencer une transaction
            $this->db->beginTransaction();

            // Initialiser les conditions SQL
            $conditions = [];
            $whereIds = [];

            // Traiter les IDs à ajouter
            $addedIds = array_filter(array_map('intval', explode(',', trim($added, ','))));
            if (!empty($addedIds)) {
                $conditions[] = "WHEN id IN (" . implode(',', $addedIds) . ") THEN 1";
                $whereIds = array_merge($whereIds, $addedIds);
            }

            // Traiter les IDs à retirer
            $removedIds = array_filter(array_map('intval', explode(',', trim($removed, ','))));
            if (!empty($removedIds)) {
                $conditions[] = "WHEN id IN (" . implode(',', $removedIds) . ") THEN 0";
                $whereIds = array_merge($whereIds, $removedIds);
            }

            // Si aucune modification n'est nécessaire, valider la transaction et retourner
            if (empty($conditions)) {
                $this->db->commitTransaction();
                return true;
            }

            // Construire la requête SQL
            $sql = "UPDATE categories 
                    SET is_trend = CASE 
                        " . implode("\n                    ", $conditions) . "
                        ELSE is_trend
                    END
                    WHERE id IN (" . implode(",", $whereIds) . ")";

            // Exécuter la requête
            $result = $this->db->execQuery($sql);

            // Vérifier le résultat
            if ($result) {
                $this->db->commitTransaction();
                AppLog::info("Mise à jour des tendances réussie. Ajoutés: $added, Retirés: $removed");
                return true;
            } else {
                // En cas d'échec, rollback
                $this->db->rollbackTransaction();
                AppLog::error("Échec de la mise à jour des tendances. Ajoutés: $added, Retirés: $removed");
                return false;
            }
        } catch (\Exception $e) {
            // En cas d'erreur, rollback et logger
            $this->db->rollbackTransaction();
            AppLog::error("Erreur lors de la mise à jour des tendances : " . $e->getMessage());
            throw new \Exception("Erreur lors de la mise à jour des tendances : " . $e->getMessage());
        }
    }

    public function getCategoryImage($catId)
    {
        return $this->db->select("categories", 'image_path', 'id = :id', ['id' => $catId]);
    }
    public function getInfos($id)
    {
        $result = $this->db->selectA(
            table: 'categories c',
            joins: 'LEFT JOIN 
                    product_categories pc ON c.id = pc.category_id',
            columns: 'c.id AS id,
                      c.title AS title,
                      c.description,  
                      c.tags, 
                      c.image_path,   
                      c.reduction,    
                      c.is_trend, 
                      c.visites,  
                      c.created_at,
                      COUNT(pc.product_id) AS product_count',
            conditions: 'c.id = :category_id',
            params: [':category_id' => $id],
            groupBy: 'c.id, c.title;'
        );
        return $result ? $result : [];
    }
    public function getPaginatedProductsByCategory(int $categoryId, int $page, int $perPage, string $sortBy, string $order): array
    {
        $offset = ($page - 1) * $perPage;

        // Requête pour récupérer les produits de la catégorie
        $sql = "SELECT p.* 
            FROM products p
            INNER JOIN product_categories pc ON p.id = pc.product_id
            WHERE pc.category_id = :category_id
            ORDER BY p.$sortBy $order
            LIMIT :perPage OFFSET :offset";

        $params = [
            'category_id' => $categoryId,
            'perPage' => $perPage,
            'offset' => $offset,
        ];

        $products = $this->db->execQuery($sql, $params);

        // Requête pour compter le nombre total de produits dans la catégorie
        $countSql = "SELECT COUNT(*) as total 
                 FROM products p
                 INNER JOIN product_categories pc ON p.id = pc.product_id
                 WHERE pc.category_id = :category_id";
        $totalProducts = $this->db->execQuery($countSql, ['category_id' => $categoryId])[0]['total'];

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalProducts / $perPage),
                'total_products' => $totalProducts,
            ],
        ];
    }
    public function getProductsByCategory($categoryTitle, $offset, $perPage, $sortBy = 'created_at', $order = 'DESC')
    {
        $offset =  intval($offset);
        // Sécurisation des paramètres de tri
        $allowedSortBy = ['created_at', 'price', 'title']; // Ajoute d'autres champs si nécessaire
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'created_at';
        }

        if (!in_array($order, $allowedOrder)) {
            $order = 'DESC';
        }

        $tables = "
            products
            LEFT JOIN product_images ON product_images.product_id = products.id
            LEFT JOIN product_categories ON product_categories.product_id = products.id
            LEFT JOIN categories ON categories.id = product_categories.category_id
        ";

        $fields = '
            products.id as "id",
            products.title as "title",
            products.slug as "slug",
            products.description as "description",
            products.tag AS "tag",
            products.price as "price",
            products.old_price as "old_price",
            products.reduction as "reduction",
            COALESCE(GROUP_CONCAT(DISTINCT product_images.image_path ORDER BY product_images.id ASC), "No_Image_Available.jpg") AS "images",
            categories.id as "category_id",
            categories.title as "category_title",
            categories.description as "category_description",
            categories.image_path as "category_image",
            categories.tags as "category_tags"
        ';

        $conditions = "products.status = 'affiche'";
        $params = [];

        if (!empty($categoryTitle)) {
            $conditions .= " AND categories.title = ?";
            $params[] = $categoryTitle;
        }
        // Sécurisation de ORDER BY

        // LIMIT avec OFFSET
        $limit = "$perPage OFFSET $offset";
        $groupBy = "products.id";
        // Appel de la méthode select()
        return $this->db->selectA(
            $tables,  // Table principale
            $fields,     // Colonnes à récupérer
            $conditions, // Conditions WHERE
            $params,          // Paramètres de requête
            $groupBy,    // GROUP BY
            "",          // HAVING (non utilisé ici)
            "",    // ORDER BY
            $limit,      // LIMIT
            '',      // JOIN
            true,         // Distinct
            false         // Debug
        );
    }

    public function isCategory($category)
    {
        $result = $this->db->select("categories", "id ,visites", "title = ?", [$category]);
        if ($result) {
            return $result[0];
        }
        return [];
    }
    public function updateVisites($id, $visites)
    {
        return $this->db->update(
            'categories',
            ['visites' => intval($visites) + 1],
            'id = :id',
            [':id' => $id],
        );
    }
    public function getTotalProductsByCategory($categoryTitle)
    {
        $tables = "
            products
            LEFT JOIN product_images ON product_images.product_id = products.id
            LEFT JOIN product_categories ON product_categories.product_id = products.id
            LEFT JOIN categories ON categories.id = product_categories.category_id
        ";

        $fields = '
            COUNT(products.id) as "total"
        ';

        $conditions = "products.status = 'affiche'";
        $params = [];

        if ($categoryTitle) {
            $conditions .= " AND categories.title = ?";
            $params[] = $categoryTitle;
        }

        $groupBy = "products.id";

        return $this->db->select($tables, $fields, $conditions, $params, $groupBy, '', '', '', '', '');
    }
    public function addToCategory($query)
    {
        $query = "INSERT INTO product_categories (category_id, product_id) VALUES $query";
        $result = $this->db->execQuery($query);
        return $result;
    }
    public function getRelatedProducts($categoryTitle, $productId)
    {
        $tables = "
        products
        LEFT JOIN product_images ON product_images.product_id = products.id
        LEFT JOIN product_categories ON product_categories.product_id = products.id
        LEFT JOIN categories ON categories.id = product_categories.category_id
    ";

        $fields = "
        DISTINCT products.id,
        products.title,
        products.slug,
        products.price,
        products.old_price,
        products.reduction,
        COALESCE(GROUP_CONCAT(DISTINCT product_images.image_path SEPARATOR ','), 'No_Image_Available.jpg') AS images
    ";

        $conditions = "products.status = 'affiche'";
        $params = [];

        if ($categoryTitle) {
            $conditions .= " AND categories.title = ?";
            $params[] = $categoryTitle;
        }

        if ($productId) {
            $conditions .= " AND products.id != ?";
            $params[] = $productId;
        }

        $groupBy = "products.id";
        $orderBy = "products.id desc";
        $limit = "4";

        return $this->db->select(
            $tables,
            $fields,
            $conditions,
            $params,
            $groupBy,
            $limit,
            $orderBy,
            '',
            false,
            '',
            false
        );
    }

    /**
     * Met à jour les catégories d'un produit
     * 
     * @param int $id ID du produit
     * @param array $categories IDs des catégories
     * @param string $action Action à effectuer ('add' ou 'remove')
     * @return bool|int Résultat de l'opération
     */
    public function updateCategories($id, $products, $action)
    {
        $result = false;

        switch ($action) {
            case 'remove':
                // Utilisation de la nouvelle version de deleteIn avec conditions multiples
                $result = $this->db->deleteInA(
                    table: 'product_categories',
                    conditions: [
                        ['column' => 'category_id', 'values' => $id, 'operator' => '='],
                        ['column' => 'product_id', 'values' => $products, 'operator' => 'IN'],
                        'logic' => 'AND'
                    ],
                );
                break;

            case 'add':
                $values = [];
                foreach ($products as $productId) {
                    $values[] = "($id, $productId)";
                }
                $valuesStr = implode(',', $values);

                $query = "INSERT INTO product_categories (category_id, product_id) VALUES $valuesStr";
                $result = $this->db->execQuery($query);
                break;

            default:
                break;
        }

        return $result;
    }
    public function alreadyIn($categoryId, $productId)
    {
        $table = 'product_categories';
        $conditions = 'category_id = :category_id AND product_id = :product_id';
        $params = [
            ":category_id" => $categoryId,
            ":product_id" => $productId,
        ];

        $result = $this->db->selectA(
            table: $table,
            conditions: $conditions,
            params: $params,
        );

        return $result ? true :  false;
    }
}
