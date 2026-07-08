<?php

namespace Src\Models;

use Src\Helpers\AppLog;
use Src\Database\Database;
use Src\Helpers\SessionManager;
use Src\Helpers\FileAndPathManager;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // Créer un produit
    public function createProduct($data, SessionManager $sessionManager)
    {
        $productData = [
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? "",
            'meta_tag' => $data['meta_tag'] ?? "",
            'meta_description' => $data['meta_description'] ?? "",
            'tag' => $data['tags'] ?? "",
            'price' => intval($data['price']),
            'stock' => intval($data['stock']) ?? 0,
            'reduction' => intval($data['reduction']) ?? 0,
            'apply_reduction_on' => intval($data['appReduction']) ?? 10,
            'creator_id' => $sessionManager->getUserId(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->db->insert('products', $productData);
    }

    // Mettre à jour un produit 
    public function updateProduct($productId, $data)
    {
        $ndata = [];
        foreach ($data as $k => $v) {
            $ndata["$k"] = trim(htmlspecialchars($v));
        }
        if (!empty($data['tags'])) {
            $data['tag'] = $data['tags'];
            unset($data['tags']);
        }
        $ndata['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update(
            table: 'products',
            data: $ndata,
            conditions: 'id = :id',
            params: ['id' => $productId],
        );
    }
    // Mettre à jour un produit 
    public function updateBySlug($slug, $data)
    {
        $ndata = [];
        foreach ($data as $k => $v) {
            $ndata["$k"] = trim(htmlspecialchars($v));
        }
        $ndata['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update(
            table: 'products',
            data: $ndata,
            conditions: 'slug = :slug',
            params: ['slug' => $slug],
        );
    }

    public function deleteProduct($productId)
    {
        return $this->db->delete('products', 'id = ?', [$productId]);
    }

    public function listPoruct()
    {
        $table = "products";
        $joins = "  LEFT JOIN product_images ON product_images.product_id = products.id
                    LEFT JOIN product_categories ON product_categories.product_id = products.id
                    LEFT JOIN users ON products.creator_id = users.id
                    LEFT JOIN categories ON categories.id = product_categories.category_id";
        $fields = "
                    products.id as 'id',
                    products.title as 'title',
                    products.slug as 'slug',
                    products.description as 'description',
                    products.tag AS 'tag',
                    products.stock as 'stock',
                    products.price as 'price',
                    products.old_price as 'old_price',
                    products.reduction as 'reduction',
                    products.apply_reduction_on AS appReduction,
                    products.status as 'status',
                    concat(product_images.id,'|',product_images.image_path) AS 'images',
                    users.username as 'creator',
                    users.id as 'creatorId',
                    categories.id as 'categorieID',
                    categories.title as 'categories'
        ";
        return $this->db->selectA(table: $table, columns: $fields, joins: $joins);
    }
    public function getProductsTitles()
    {
        $table = "products";
        $fields = "
                    id,
                    title,
                    slug,
                    is_trend
        ";
        $result = $this->db->selectA(
            table: $table,
            columns: $fields,
            conditions: 'status = "affiche"',
        );
        return $result ? $result : [];
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
            $sql = "UPDATE products 
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
                return true;
            } else {
                // En cas d'échec, rollback
                $this->db->rollbackTransaction();
                return false;
            }
        } catch (\Exception $e) {
            // En cas d'erreur, rollback et logger
            $this->db->rollbackTransaction();
            AppLog::error("Erreur lors de la mise à jour des tendances : " . $e->getMessage());
            throw new \Exception("Erreur lors de la mise à jour des tendances : " . $e->getMessage());
        }
    }
    public function getStock()
    {
        $table = "products";
        $fields = "
            p.id,
            p.title,
            p.stock - COALESCE(SUM(oi.quantity), 0) AS remaining_stock
        ";
        $fields = "
                    id,
                    title,
                    stock
        ";
        return $this->db->selectA(table: $table, columns: $fields);
    }
    public function observeStock()
    {
        $table = "products p";
        $fields = "
                    p.id,
                    p.title,
                    p.stock_update,
                    IFNULL(p.stock - COALESCE(SUM(oi.quantity), 0), 0) AS instock,
                    COALESCE(SUM(oi.quantity), 0) AS outstock
        ";
        $joins = "
        LEFT JOIN 
            order_items oi ON p.id = oi.product_id
        LEFT JOIN 
            `orders` o ON oi.order_id = o.id AND o.status = 'delivered'";
        $orderBy = "p.title";
        $groupBy = "p.id";
        $result = $this->db->selectA(
            table: $table,
            columns: $fields,
            groupBy: $groupBy,
            orderBy: $orderBy,
            joins: $joins,
        );
        return $result ? $result : [];
    }
    public function checkDevisPr($id)
    {
        $table = "products";
        $fields = "
                    id,
                    title,
                    price
        ";
        return $this->db->selectA(
            table: $table,
            columns: $fields,
            conditions: 'id = ?',
            params: [$id],
        );
    }

    public function uniqueSlug($slug)
    {
        $result = $this->db->selectA(
            table: 'products',
            columns: 'slug',
            conditions: "slug = :slug",
            params: [':slug' => $slug],
        );
        return $result ? true : false;
    }

    // Récupérer un produit par son ID 
    public function getProductById($productId)
    {
        $tables = "products
            LEFT JOIN product_images ON product_images.product_id = products.id
            LEFT JOIN product_categories ON product_categories.product_id = products.id
            LEFT JOIN users ON products.creator_id = users.id
            LEFT JOIN categories ON categories.id = product_categories.category_id";
        $fields = "
                products.id as 'id',
                products.title as 'title',
                products.visited_times as 'visites',
                products.slug as 'slug',
                products.description as 'description',
                products.tag AS 'tag',
                products.meta_description AS meta_description,
                products.meta_tag AS meta_tag,
                products.stock as 'stock',
                products.price as 'price',
                products.old_price as 'old_price',
                products.reduction as 'reduction',
                products.apply_reduction_on AS appReduction,
                products.status as 'status',
                concat(product_images.image_path) AS 'images',
                users.username as 'creator',
                users.id as 'creatorId',
                categories.id as 'categorieID',
                categories.title as 'categories'";
        $s = 'products.id = ?';
        $t = [$productId];
        if ($_SESSION['user_role'] != "super_manager" && $_SESSION['user_role'] != "admin") {
            $s .= ' and users.id = ?';
            array_push($t, $_SESSION["user_id"]);
        }
        return $this->db->select(
            table: $tables,
            columns: $fields,
            conditions: $s,
            params: $t,
        );
    }
    /**
     * Get product by ID with 5 related products from same categories
     */
    public function getProductByTitle($productId)
    {
        $columns = "
           DISTINCT CAST(p.id AS CHAR) AS id,
           p.title AS title,
           p.slug AS slug,
           p.description AS description,
           p.tag AS tag,
           p.meta_description AS meta_description,
           p.meta_tag AS meta_tag,
           p.price AS price,
           p.old_price AS old_price,
           p.reduction AS reduction,
           p.apply_reduction_on AS appReduction,
           p.visited_times AS visited,
           COALESCE(GROUP_CONCAT(DISTINCT CONCAT(pi.id, '|', pi.image_path) SEPARATOR ','), '0|No_Image_Available.jpg') AS images,
           COALESCE(GROUP_CONCAT(DISTINCT c.title SEPARATOR ', '), 'Non catégorisé') AS categories,
           IF(p.id = ?, 1, 0) as is_main_product
       ";

        $tables = "
           products p
           LEFT JOIN product_images pi ON pi.product_id = p.id
           LEFT JOIN product_categories pc ON pc.product_id = p.id
           LEFT JOIN categories c ON c.id = pc.category_id
       ";

        $conditions = "
           p.status = 'affiche'
           AND (
               p.id = ?
               OR (
                   p.id != ? 
                   AND c.id IN (
                       SELECT category_id 
                       FROM product_categories 
                       WHERE product_id = ?
                   )
               )
           )
       ";

        $params = [$productId, $productId, $productId, $productId];
        $orderBy = "is_main_product DESC, RAND()";

        // Limit to main product + 5 related products
        $limit = "6";

        return $this->db->select(
            $tables,
            $columns,
            $conditions,
            $params,
            "p.id",
            $limit,
            $orderBy,
            "",
            false,
            "",
            false
        );
    }
    /**
     * Get product by ID with 5 related products from same categories
     */
    public function getBySlug($productId)
    {
        $columns = "
           p.id ,
           p.title ,
           p.slug ,
           p.description ,
           p.tag ,
           p.meta_description ,
           p.meta_tag ,
           p.price ,
           p.old_price ,
           p.reduction ,
           p.apply_reduction_on AS appReduction,
           p.visited_times AS visited,
           COALESCE(GROUP_CONCAT(DISTINCT pi.image_path SEPARATOR ','), '0|No_Image_Available.jpg') AS images,
           COALESCE(GROUP_CONCAT(DISTINCT c.title SEPARATOR ', '), 'Non catégorisé') AS categories
       ";

        $tables = "
           products p
           LEFT JOIN product_images pi ON pi.product_id = p.id
           LEFT JOIN product_categories pc ON pc.product_id = p.id
           LEFT JOIN categories c ON c.id = pc.category_id
       ";

        $conditions = "
           p.status = 'affiche'
           AND (
               p.slug = ?
           )
       ";
        $params = [$productId];

        // Limit to main product + 5 related products
        $limit = "6";

        $result = $this->db->select(
            table: $tables,
            columns: $columns,
            conditions: $conditions,
            params: $params,
            orderBy: "p.id",
            limit: $limit,
        );
        $controll = false;
        if (
            $result[0]['id'] &&
            $result[0]['title'] &&
            $result[0]['slug'] &&
            $result[0]['price']
        ) {
            $controll = true;
        }
        return $controll ? $result : [];
    }
    // Récupérer la liste des products avec pagination et tri
    public function getProducts()
    {
        $tables = "products
        LEFT JOIN product_images ON product_images.product_id = products.id
        LEFT JOIN product_categories ON product_categories.product_id = products.id
        LEFT JOIN users ON products.creator_id = users.id
        LEFT JOIN categories ON categories.id = product_categories.category_id";


        $fields = '
            products.id as "id",
            products.title as "title",
            products.slug as "slug",
            products.description as "description",
            products.tag AS "tag",
            products.stock as "stock",
            products.price as "price",
            products.old_price as "old_price",
            products.reduction as "reduction",
            products.apply_reduction_on AS appReduction,
            products.status as "status",
            products.created_at as "created_at",
            products.visited_times as "visited",
            concat(product_images.id,"|",product_images.image_path) AS "images",
            users.username as "creator",
            users.id as "creatorId",
            categories.id as "categorieID",
            categories.title as "categories"';

        if ($_SESSION['user_role'] != "super_manager" && $_SESSION['user_role'] != "admin") {
            $tables .= " WHERE users.id = $_SESSION[user_id]";
        }
        return $this->db->select($tables, $fields, '', [], 'products.created_at DESC');
    }

    //Recupere les produits pour le public
    public function getProductsList()
    {
        $tables = "products
        LEFT JOIN product_images ON product_images.product_id = products.id
        LEFT JOIN product_categories ON product_categories.product_id = products.id
        LEFT JOIN categories ON categories.id = product_categories.category_id";
        $fields = '
            products.id as "id",
            products.title as "title",
            products.slug as "slug",
            products.description as "description",
            products.tag AS "tag",
            products.is_trend AS "is_trend",
            products.price as "price",
            products.old_price as "old_price",
            products.reduction as "reduction",
            products.apply_reduction_on AS appReduction,
            product_images.image_path AS "images",
            categories.id as "categorieID",
            categories.title as "categories"';
        $conditions = "products.status = 'affiche'";
        return $this->db->select($tables, $fields, $conditions, [], 'products.created_at DESC');
    }
    //Recupere les produits pour le public avec limite
    public function getLimitedDistinctProducts(int $limit = 12)
    {
        $tables = "products
        inner join product_images ON product_images.product_id = products.id";
        $fields = '
            products.id as "id",
            products.title as "title",
            products.slug as "slug",
            products.description as "description",
            products.tag AS "tag",
            products.price as "price",
            products.old_price as "old_price",
            products.reduction as "reduction",
            products.apply_reduction_on AS appReduction,
            product_images.image_path AS "images"';
        $conditions = "products.status = 'affiche'";
        return $this->db->selectA(
            $tables,
            $fields,
            $conditions,
            [],
            'products.id',
            '',
            '',
            $limit,
            '',
            true,
        );
    }


    public function is_product($product)
    {
        return $this->db->selectA(
            table: 'products',
            conditions: 'slug = :slug AND status = "affiche"',
            params: [
                ":slug" => htmlentities($product),
            ],

        );
    }
    //product cats
    public function deleteProductCategories($productId)
    {

        return $this->db->delete('product_categories', 'product_id = :id', ['id' => $productId]);
    }
    //product images
    public function getProductImages($productId)
    {

        return $this->db->select('product_images', 'concat(product_images.id,"|",product_images.image_path) as "images"', 'product_id = :id', ['id' => $productId]);
    }


    // Ajouter des images à un produit
    public function addProductImages($productId, $images)
    {
        $paths = [];
        foreach ($images as $index => $tmpName) {
            $file = FileAndPathManager::getPath('product-image', basename($tmpName));
            if (file_exists($file)) {
                // Insérer le lien de l'image dans la base de données
                $imageName = basename($tmpName);
                $id = $this->db->insert('product_images', [
                    'product_id' => $productId,
                    'image_path' => $imageName,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                array_push($paths, $id . '|' . $imageName);
            }
        }
        if (count($paths)) return $paths;
    }
    /**
     * Récupère les produits paginés
     *
     * @param int $offset L'offset pour la pagination
     * @param int $perPage Le nombre de produits par page
     * @param string $sortBy Le champ de tri
     * @param string $order L'ordre de tri (ASC ou DESC)
     * @param string|null $search Terme de recherche
     * @param bool $manager Si true, affiche tous les produits, sinon seulement les produits actifs
     * @return array
     */
    public function getPaginatedProducts($offset, $perPage, $sortBy = 'created_at', $order = 'DESC', ?String $search = null, $manager = false)
    {
        // Sécurisation des paramètres de tri
        $allowedSortBy = ['created_at', 'price', 'title'];
        $allowedOrder = ['ASC', 'DESC'];
        $queryParams = [];
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'created_at';
        }

        if (!in_array($order, $allowedOrder)) {
            $order = 'DESC';
        }

        // Définition des jointures
        $joins = "
        LEFT JOIN product_images ON product_images.product_id = products.id
        LEFT JOIN product_categories ON product_categories.product_id = products.id
        LEFT JOIN categories ON categories.id = product_categories.category_id
        ";

        // Colonnes sélectionnées
        $fields = "
        products.id AS id,
        products.title AS title,
        products.slug AS slug,
        products.description AS description,
        products.tag AS tag,
        products.apply_reduction_on AS appReduction,
        products.price AS price,
        products.stock AS stock,
        products.status AS status,
        products.visited_times AS visited,
        products.old_price AS old_price,
        products.reduction AS reduction,
        GROUP_CONCAT(DISTINCT product_images.image_path ORDER BY product_images.id ASC) AS images,
        categories.id AS categorieID,
        categories.title AS categories
        ";

        // Gestion des conditions
        $conditionsArray = [];

        if (!$manager) {
            $conditionsArray[] = "products.status = 'affiche'";
        } else {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'super_manager') {
                $conditionsArray[] = "products.creator_id = $_SESSION[user_id]";
            }
        }
        if (!empty($search)) {
            $conditionsArray[] = "(products.title LIKE :search OR products.description LIKE :search OR products.tag LIKE :search)";
            $queryParams[':search'] = "%$search%";
        }

        // Construction finale du WHERE
        $conditions = !empty($conditionsArray) ? implode(" AND ", $conditionsArray) : "1";

        // GROUP BY pour éviter la duplication des produits
        $groupBy = "products.id, products.title, products.description, products.tag, products.apply_reduction_on, 
                products.price, products.status, products.visited_times, products.old_price, 
                products.reduction, categories.id, categories.title";

        // Sécurisation du ORDER BY
        $orderBy = "products.$sortBy $order";

        // Gestion du LIMIT et OFFSET
        $limit = "$perPage OFFSET $offset";

        // Exécution de la requête
        return $this->db->selectA(
            "products",  // Table principale
            $fields,     // Colonnes à récupérer
            $conditions, // Conditions WHERE
            $queryParams, // Paramètres de requête
            $groupBy,    // GROUP BY
            "",          // HAVING (non utilisé ici)
            $orderBy,    // ORDER BY
            $limit,      // LIMIT
            $joins,      // JOIN
            true,        // DISTINCT
            // true         // Debug
        );
    }



    public function searchProducts($searchText, $offset, $perPage, $sortBy = 'created_at', $order = 'DESC')
    {
        // Sécurisation des paramètres de tri
        $allowedSortBy = ['created_at', 'price', 'title'];
        $allowedOrder = ['ASC', 'DESC'];
        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'created_at';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'DESC';
        }

        // Nettoyage du texte de recherche
        $cleanSearchText = trim($searchText);

        // Construction des requêtes de similitude
        $searchCriteria = $this->buildSearchCriteria($cleanSearchText);

        // Définition des jointures
        $joins = "
            LEFT JOIN product_images ON product_images.product_id = products.id
            LEFT JOIN product_categories ON product_categories.product_id = products.id
            LEFT JOIN categories ON categories.id = product_categories.category_id
        ";

        // Colonnes sélectionnées
        $fields = "
            products.id AS id,
            products.title AS title,
            products.price AS price,
            products.old_price AS old_price,
            GROUP_CONCAT(DISTINCT product_images.image_path ORDER BY product_images.id ASC) AS images,
            categories.title AS categories
        ";

        // Ajout d'un score de pertinence
        $fields .= ", (" . $this->buildRelevanceFormula($searchCriteria) . ") AS relevance";

        // Construction des conditions de recherche
        $whereConditions = "products.status = 'affiche' AND (" . $searchCriteria['where'] . ")";
        $params = $searchCriteria['params'];

        // GROUP BY pour éviter la duplication des produits
        $groupBy = "products.id";

        // Changeons le tri par défaut pour les recherches
        $orderBy = "relevance DESC, products.$sortBy $order";
        $orderBy = $this->validateOrderBy($orderBy);

        // LIMIT avec OFFSET
        $limit = "$perPage OFFSET $offset";

        // Appel de la méthode select()
        return $this->db->selectA(
            "products",  // Table principale
            $fields,     // Colonnes à récupérer
            $whereConditions, // Conditions WHERE
            $params,     // Paramètres de requête
            $groupBy,    // GROUP BY
            "",          // HAVING (non utilisé ici)
            $orderBy,    // ORDER BY
            $limit,      // LIMIT
            $joins,      // JOIN
            true         // Distinct
        );
    }

    /**
     * Construit les critères de recherche avancée avec correspondances approximatives
     * @param string $searchText Texte de recherche saisi par l'utilisateur
     * @return array Tableau avec les conditions WHERE et les paramètres
     */
    private function buildSearchCriteria($searchText)
    {
        $conditions = [];
        $params = [];
        $index = 0;

        // Diviser la recherche en mots individuels
        $words = preg_split('/\s+/', $searchText);

        foreach ($words as $word) {
            if (strlen($word) < 2) continue; // Ignorer les mots trop courts

            // 1. Recherche exacte - priorité la plus élevée
            $paramName = "exact" . $index;
            $conditions[] = "products.title LIKE :$paramName OR products.description LIKE :$paramName OR products.tag LIKE :$paramName OR categories.tags LIKE :$paramName OR categories.title LIKE :$paramName";
            $params[$paramName] = "%$word%";
            $index++;

            // 2. Recherche sans caractères répétés - pour les erreurs de frappe avec répétition
            $noRepeats = preg_replace('/(.)\1+/', '$1', $word);
            if ($noRepeats !== $word) {
                $paramName = "norep" . $index;
                $conditions[] = "products.title LIKE :$paramName OR products.description LIKE :$paramName OR products.tag LIKE :$paramName OR categories.tags LIKE :$paramName OR categories.title LIKE :$paramName";
                $params[$paramName] = "%$noRepeats%";
                $index++;
            }

            // 3. Recherche avec distance d'édition réduite (simulé en SQL)
            // Génération de variantes avec une lettre en moins/en plus
            if (strlen($word) > 3) {
                // Variantes avec une lettre en moins
                for ($i = 0; $i < strlen($word); $i++) {
                    $variant = substr($word, 0, $i) . substr($word, $i + 1);
                    $paramName = "var" . $index;
                    $conditions[] = "products.title LIKE :$paramName OR products.tag LIKE :$paramName OR categories.tags LIKE :$paramName OR categories.title LIKE :$paramName";
                    $params[$paramName] = "%$variant%";
                    $index++;
                }

                // Variantes avec inversion de deux lettres adjacentes
                for ($i = 0; $i < strlen($word) - 1; $i++) {
                    $variant = substr($word, 0, $i) . $word[$i + 1] . $word[$i] . substr($word, $i + 2);
                    $paramName = "swap" . $index;
                    $conditions[] = "products.title LIKE :$paramName OR products.tag LIKE :$paramName OR categories.tags LIKE :$paramName OR categories.title LIKE :$paramName";
                    $params[$paramName] = "%$variant%";
                    $index++;
                }
            }

            // 4. Recherche par soundex (similitude phonétique)
            if (strlen($word) > 3) {
                $soundexValue = soundex($word);
                $paramName = "word" . $index;
                $soundexParam = "soundex" . $index;
                $conditions[] = "SOUNDEX(products.title) = :$soundexParam OR SOUNDEX(products.tag) = :$soundexParam";
                $params[$paramName] = $word;
                $params[$soundexParam] = $soundexValue;
                $index++;
            }
        }

        // Si la recherche originale contient plusieurs mots, ajouter une recherche sur la phrase complète
        if (count($words) > 1) {
            $paramName = "phrase";
            $conditions[] = "products.title LIKE :$paramName OR products.description LIKE :$paramName OR products.tag LIKE :$paramName OR categories.tags LIKE :$paramName OR categories.title LIKE :$paramName";
            $params[$paramName] = "%$searchText%";
        }

        return [
            'where' => implode(" OR ", $conditions),
            'params' => $params
        ];
    }

    /**
     * Construit la formule de calcul de pertinence pour le tri des résultats
     * @param array $searchCriteria Critères de recherche générés par buildSearchCriteria()
     * @return string Formule SQL pour le calcul de pertinence
     */
    private function buildRelevanceFormula($searchCriteria)
    {
        $formula = [];

        foreach ($searchCriteria['params'] as $param => $value) {
            if (strpos($param, 'exact') === 0) {
                // Score élevé pour les correspondances exactes
                $formula[] = "(products.title LIKE :$param) * 10";
                $formula[] = "(products.tag LIKE :$param) * 8";
                $formula[] = "(products.description LIKE :$param) * 5";
            } else if (strpos($param, 'norep') === 0 || strpos($param, 'var') === 0 || strpos($param, 'swap') === 0) {
                // Score moyen pour les variantes
                $formula[] = "(products.title LIKE :$param) * 3";
                $formula[] = "(products.tag LIKE :$param) * 2";
                $formula[] = "(products.description LIKE :$param) * 1";
            } else if (strpos($param, 'soundex') === 0) {
                // Score plus faible pour les correspondances phonétiques
                $formula[] = "(SOUNDEX(products.title) = :$param) * 2";
                $formula[] = "(SOUNDEX(products.tag) = :$param) * 1";
            } else if ($param === 'phrase') {
                // Score très élevé pour la phrase complète
                $formula[] = "(products.title LIKE :$param) * 15";
                $formula[] = "(products.tag LIKE :$param) * 12";
                $formula[] = "(products.description LIKE :$param) * 10";
            }
        }

        if (empty($formula)) {
            return "0"; // Aucun score si pas de critères
        }

        return implode(" + ", $formula);
    }

    public function getTotalSearchProducts($searchText)
    {
        // Nettoyage du texte de recherche
        $cleanSearchText = trim($searchText);

        // Construction des requêtes de similitude en utilisant la même logique que searchProducts
        $searchCriteria = $this->buildSearchCriteria($cleanSearchText);

        // Définition des jointures
        $joins = "
        LEFT JOIN product_categories ON product_categories.product_id = products.id
        LEFT JOIN categories ON categories.id = product_categories.category_id
    ";

        // Colonnes sélectionnées - simple comptage
        $fields = "COUNT(DISTINCT products.id) as total";

        // Construction des conditions de recherche en utilisant les mêmes critères
        $whereConditions = "products.status = 'affiche' AND (" . $searchCriteria['where'] . ")";
        $params = $searchCriteria['params'];

        // Appel de la méthode select() sans GROUP BY, ORDER BY, ou LIMIT
        $result = $this->db->selectA(
            "products",  // Table principale
            $fields,     // Colonnes à récupérer
            $whereConditions, // Conditions WHERE
            $params,     // Paramètres de requête
            "",          // GROUP BY (non utilisé pour le comptage)
            "",          // HAVING (non utilisé ici)
            "",          // ORDER BY (non nécessaire pour le comptage)
            "",          // LIMIT (non nécessaire pour le comptage)
            $joins,      // JOIN
            false        // Pas besoin de DISTINCT car déjà dans le COUNT
        );

        return isset($result[0]['total']) ? $result[0]['total'] : 0;
    }
    /**
     * Récupère le nombre total de produits
     *
     * @return int
     */
    public function getTotalProducts(?string $search = null, bool $manager = false)
    {
        $conditions = "";
        if ($manager) {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'super_manager') {
                $conditions = " AND creator_id = $_SESSION[user_id]";
            }
        }
        $result = $this->db->select('products', 'COUNT(*) as "total"', 'status = ? ' . $conditions, ['affiche']);
        return $result[0]['total'] ?? 0;
    }

    // Ajouter des catégories à un produit
    public function addProductCategories($productId, $categories)
    {
        $categories = explode(',', $categories);
        if (empty($categories)) {
            return false; // Aucune catégorie à ajouter
        }

        // Construction de la requête avec des placeholders pour chaque catégorie
        $values = [];
        foreach ($categories as $categoryId) {
            $values[] = "($productId, $categoryId)";
        }

        // Joindre les valeurs avec une virgule
        $valuesStr = implode(',', $values);
        $query = "DELETE FROM product_categories WHERE product_id = $productId;";
        // Requête d'insertion avec toutes les valeurs
        $query .= "INSERT INTO product_categories (product_id, category_id) VALUES $valuesStr";
        $this->db->execQuery($query);
    }
    /**
     * Met à jour les catégories d'un produit
     * 
     * @param int $id ID du produit
     * @param array $categories IDs des catégories
     * @param string $action Action à effectuer ('add' ou 'remove')
     * @return bool|int Résultat de l'opération
     */
    public function updateCategories($id, $categories, $action)
    {
        $result = false;

        switch ($action) {
            case 'remove':
                // Utilisation de la nouvelle version de deleteIn avec conditions multiples
                $result = $this->db->deleteInA(
                    table: 'product_categories',
                    conditions: [
                        ['column' => 'product_id', 'values' => $id, 'operator' => '='],
                        ['column' => 'category_id', 'values' => $categories, 'operator' => 'IN'],
                        'logic' => 'AND'
                    ],
                );
                break;

            case 'add':
                $values = [];
                foreach ($categories as $categoryId) {
                    $values[] = "($id, $categoryId)";
                }
                $valuesStr = implode(',', $values);

                $query = "INSERT INTO product_categories (product_id, category_id) VALUES $valuesStr";
                $result = $this->db->execQuery($query);
                break;

            default:
                break;
        }

        return $result;
    }
    private function validateOrderBy($orderBy)
    {
        // Liste des colonnes autorisées pour éviter les injections SQL
        $allowedColumns = ['products.created_at', 'products.price', 'products.title'];
        $allowedDirections = ['ASC', 'DESC'];

        // Séparer colonne et direction
        $parts = explode(' ', trim($orderBy));
        if (count($parts) !== 2) {
            return 'products.created_at DESC'; // Valeur par défaut si incorrect
        }

        list($column, $direction) = $parts;

        // Vérification des valeurs autorisées
        if (!in_array($column, $allowedColumns) || !in_array($direction, $allowedDirections)) {
            return 'products.created_at DESC'; // Valeur de secours
        }

        return "$column $direction"; // Retourne une valeur sécurisée
    }

    //product images
    public function deleteProductImages($idsTodelete)
    {
        $table = "product_images";
        $conditions = "product_id IN (?)";
        $param =  ['?' => $idsTodelete];
        return $this->db->delete(
            table: $table,
            conditions: $conditions,
            params: $param,
            debug: true
        );
        // return $this->db->delete('product_images', 'product_id = ?', ['?' => $productId]);
    }
    public function delProdImgRange($tab)
    {
        if (empty($tab)) {
            return false; // Rien à supprimer
        }
        // Construction dynamique des placeholders
        $placeholders = implode(',', array_fill(0, count($tab), '?'));
        // Appel à la méthode delete avec les bons paramètres
        return $this->db->delete('product_images', "image_path IN ($placeholders)", $tab);
    }
    // Mettre à jour les catégories d'un produit
    public function updateProductCategories($productId, $categories)
    {
        // Supprimer les anciennes catégories
        $this->db->delete('product_categories', 'product_id = :product_id', ['product_id' => $productId]);

        // Ajouter les nouvelles catégories
        $this->addProductCategories($productId, $categories);
    }
    // Model Product.php - Fonction modifiée pour la pagination simplifiée
    public function paginatedProducts($offset, $perPage, $sortBy = 'created_at', $order = 'DESC',?string $search = null, $manager = false)
    {
        // Sécurisation des paramètres de tri
        $allowedSortBy = ['created_at', 'price', 'title', 'status', 'visited_times'];
        $allowedOrder = ['ASC', 'DESC'];
        $queryParams = [];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'created_at';
        }

        if (!in_array($order, $allowedOrder)) {
            $order = 'DESC';
        }

        // Colonnes sélectionnées - simplifiées sans jointures
        $fields = "
    products.id,
    products.title,
    products.price,
    products.stock,
    products.status,
    products.visited_times AS visited
    ";

        // Gestion des conditions
        $conditionsArray = [];

        if (!$manager) {
            $conditionsArray[] = "products.status = 'affiche'";
        } else {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'super_manager') {
                $conditionsArray[] = "products.creator_id = $_SESSION[user_id]";
            }
        }

        if (!empty($search)) {
            $conditionsArray[] = "(products.title LIKE :search OR products.description LIKE :search OR products.tag LIKE :search)";
            $queryParams[':search'] = "%$search%";
        }

        // Construction finale du WHERE
        $conditions = !empty($conditionsArray) ? implode(" AND ", $conditionsArray) : "1";

        // Sécurisation du ORDER BY
        $orderBy = "products.$sortBy $order";

        // Gestion du LIMIT et OFFSET
        $limit = "$perPage OFFSET $offset";

        // Exécution de la requête - sans jointures
        return $this->db->selectA(
            "products",  // Table principale
            $fields,     // Colonnes à récupérer
            $conditions, // Conditions WHERE
            $queryParams, // Paramètres de requête
            "",          // GROUP BY - plus nécessaire
            "",          // HAVING (non utilisé ici)
            $orderBy,    // ORDER BY
            $limit,      // LIMIT
            "",          // JOIN - supprimé
            false,       // DISTINCT - plus nécessaire
            // true      // Debug
        );
    }
    // Fonction pour compter le nombre total de produits
    public function totalproducts(?string $search = null, $manager = false)
    {
        $queryParams = [];
        $conditionsArray = [];

        if (!$manager) {
            $conditionsArray[] = "products.status = 'affiche'";
        } else {
            if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'super_manager') {
                $conditionsArray[] = "products.creator_id = $_SESSION[user_id]";
            }
        }

        if (!empty($search)) {
            $conditionsArray[] = "(products.title LIKE :search OR products.description LIKE :search OR products.tag LIKE :search)";
            $queryParams[':search'] = "%$search%";
        }

        // Construction finale du WHERE
        $conditions = !empty($conditionsArray) ? implode(" AND ", $conditionsArray) : "1";

        $result = $this->db->selectA(
            "products",
            "COUNT(*) as total",
            $conditions,
            $queryParams
        );

        return $result[0]['total'] ?? 0;
    }


    public function getEssentials()
    {

        $tables = "
            products as p
        ";
        $fields = '
            p.id AS product_id,
            p.title AS product_title,
            p.description as product_description,
            p.price as product_price,
            p.status as product_status,
            GROUP_CONCAT(c.id) AS category_ids,
            GROUP_CONCAT(c.title) AS category_titles
        ';

        $joins = '
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
        ';

        $orderBy = "p.id";

        $groupBy = "p.id"; // à ajouter dans ta méthode si elle ne l’a pas encore

        $result = $this->db->selectA(
            table: $tables,
            columns: $fields,
            joins: $joins,
            orderBy: $orderBy,
            groupBy: $groupBy
        );
        return $result ? $result : [];
    }
}
