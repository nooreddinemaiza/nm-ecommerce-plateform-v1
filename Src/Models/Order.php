<?php

namespace Src\Models;

use Src\Database\Database;

class Order
{
    private $db;
    private $table;
    public function __construct()
    {
        $this->table = 'orders';
        $this->db = new Database();
    }

    /**
     * Créer une commande avec ses articles
     * @param array $data : Données de la commande
     * @return int|null : ID de la commande créée ou null en cas d'erreur
     */
    public function create(array $data)
    {
        try {
            $this->db->beginTransaction();

            // Insérer la commande
            $orderData = [
                'customer_name'    => htmlspecialchars($data['customer_infos']['customer_name']),
                'customer_email'   => htmlspecialchars($data['customer_infos']['customer_email']),
                'customer_phone'   => htmlspecialchars($data['customer_infos']['customer_phone']),
                'customer_address' => htmlspecialchars($data['customer_infos']['customer_address']),
                'customer_city_zip' => htmlspecialchars($data['customer_infos']['customer_city_zip']),
                'customer_city'    => htmlspecialchars($data['customer_infos']['customer_city']),
                'order_date'       => date("Y-m-d H:i:s"),
                'total_amount'     => $data['total_amount'], // Ajouter le montant total
            ];

            // Insérer la commande dans la table des commandes
            $orderId = $this->db->insert($this->table, $orderData);
            if (!$orderId) {
                throw new \Exception("Échec de la création de la commande.");
            }
            // Insérer les articles de la commande
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $orderItemsData = [
                        'order_id'      => $orderId,
                        'product_id'    => $item['product_id'],
                        'quantity'      => $item['quantity'],
                        'price'         => $item['price'], // Ajouter le prix unitaire
                        'appReduction'  => $item['appReduction']
                    ];

                    // Insérer chaque article dans la table order_items
                    $inserted = $this->db->insert('order_items', $orderItemsData);
                    if (!$inserted) {
                        throw new \Exception("Échec de l'insertion d'un article de la commande.");
                    }
                }
            } else {
                throw new \Exception("Aucun article trouvé dans la commande.");
            }

            // Valider la transaction
            $this->db->commitTransaction();

            // Retourner l'ID de la commande créée
            return $orderId;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler la transaction
            $this->db->rollbackTransaction();
            error_log("Erreur lors de la création de la commande : " . $e->getMessage());
            return null;
        }
    }
    /**
     * Récupérer toutes les commandes avec leurs articles
     * @return array : Liste des commandes
     */
    public function getAll()
    {
        return $this->db->selectA($this->table, '*');
    }
    public function getOrders()
    {
        $orders = $this->db->selectA(
            table: "orders o",
            columns: "
                o.id AS order_id,
                o.customer_name AS nom_prenom,
                o.customer_email AS email,
                o.customer_phone AS phone,
                o.customer_address AS address,
                o.order_date AS order_date,
                o.status AS status,
                oi.product_id AS product_id,
                p.title AS product_title,
                oi.price AS finalPrice,
                oi.quantity AS ordered_quantity,
                oi.appReduction AS appReduction
            ",
            joins: "
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                LEFT JOIN products p ON oi.product_id = p.id
            ",
            orderBy: "o.id ASC"
        );

        if (!$orders) {
            return [];
        }

        $formattedOrders = [];

        foreach ($orders as $order) {
            $orderId = $order['order_id'];

            // Si la commande n'existe pas encore dans le tableau, on l'initialise
            if (!isset($formattedOrders[$orderId])) {
                $formattedOrders[$orderId] = [
                    'client' => [
                        'id' => $order['order_id'],
                        'status' => $order['status'],
                        'nom_prenom' => $order['nom_prenom'],
                        'address' => $order['address'],
                        'date' => (new \DateTime($order['order_date']))->format("d/m/Y"),
                        'email' => $order['email'],
                        'phone' => $order['phone']
                    ],
                    'produits' => []
                ];
            }

            // Vérifier si le produit est défini avant de l'ajouter
            if (!empty($order['product_title'])) {
                $formattedOrders[$orderId]['produits'][] = [
                    'product_id' => $order['product_id'],
                    'title' => $order['product_title'],
                    'quantity' => $order['ordered_quantity'],
                    'unit_price' => $order['finalPrice'],
                    'appReduction' => json_decode($order['appReduction'], true) ?? ['reduction' => 0, 'plus' => 0]
                ];
            }
        }

        // Retourner un tableau indexé propre
        return array_values($formattedOrders);
    }

    public function getPaginatedOrders(int $page = 1, int $perPage = 10, string $search = '', string $status = '', bool $newOnly = false)
    {
        // Calculer l'offset pour la pagination
        $offset = ($page - 1) * $perPage;

        // Préparer les conditions de recherche et de filtrage
        $whereConditions = [];
        $params = [];

        // Filtrer par statut si spécifié
        if (!empty($status)) {
            $whereConditions[] = "o.status = :status";
            $params[':status'] = $status;
        }

        // Recherche par nom client, email, ID de commande ou produit
        if (!empty($search)) {
            $whereConditions[] = "(o.customer_name LIKE :search OR o.customer_email LIKE :search OR o.customer_phone LIKE :search OR o.id LIKE :search OR p.title LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Filtrer les nouveautés (commandes des dernières 24h)
        if ($newOnly) {
            $orderBy = "DESC";
        } else {
            $orderBy = "ASC";
        }

        // Construire la clause WHERE finale
        $whereClause = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';

        // Récupérer le nombre total de commandes selon les filtres
        $totalOrders = $this->db->selectA(
            table: "orders o",
            columns: "COUNT(DISTINCT o.id) AS total",
            conditions: $whereClause,
            params: $params,
            joins: "LEFT JOIN order_items oi ON o.id = oi.order_id LEFT JOIN products p ON oi.product_id = p.id"
        );
        $totalOrders = $totalOrders[0]['total'] ?? 0;
        $totalPages = ceil($totalOrders / $perPage);

        // Récupérer les commandes avec leurs produits
        $orders = $this->db->selectA(
            table: "orders o",
            columns: "
                o.id AS order_id,
                o.printed AS printed,
                o.customer_name AS nom_prenom,
                o.customer_email AS email,
                o.customer_phone AS phone,
                o.customer_address AS address,
                o.order_date AS order_date,
                o.status AS status,
                oi.product_id AS product_id,
                p.title AS product_title,
                oi.price AS finalPrice,
                oi.quantity AS ordered_quantity,
                oi.appReduction AS appReduction
            ",
            joins: "
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
            ",
            conditions: $whereClause,
            params: $params,
            orderBy: "o.order_date $orderBy, o.id DESC", // Commandes les plus récentes en premier
            limit: "$perPage OFFSET $offset",
        );

        if (!$orders) {
            return [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_orders' => $totalOrders,
                'orders' => []
            ];
        }

        $formattedOrders = [];
        $now = new \DateTime();

        foreach ($orders as $order) {
            $orderId = $order['order_id'];

            // Vérifier si c'est une nouvelle commande (moins de 24h)
            $orderDate = new \DateTime($order['order_date']);
            $interval = $now->diff($orderDate);
            $isNew = $interval->days < 1;

            // Initialiser la commande si elle n'existe pas encore dans le tableau
            if (!isset($formattedOrders[$orderId])) {
                $formattedOrders[$orderId] = [
                    'client' => [
                        'id' => $order['order_id'],
                        'printed' => $order['printed'],
                        'order_id' => $order['order_id'], // Ajout pour compatibilité avec le code JS
                        'status' => $order['status'],
                        'nom_prenom' => $order['nom_prenom'],
                        'address' => $order['address'],
                        'date' => $orderDate->format("d/m/Y H:i"),
                        'email' => $order['email'],
                        'phone' => $order['phone'],
                        'is_new' => $isNew // Ajout du marqueur de nouveauté
                    ],
                    'produits' => []
                ];
            }

            // Vérifier que le produit est valide avant de l'ajouter
            if (!empty($order['product_title'])) {
                $formattedOrders[$orderId]['produits'][] = [
                    'product_id' => $order['product_id'],
                    'title' => $order['product_title'],
                    'quantity' => $order['ordered_quantity'],
                    'unit_price' => $order['finalPrice'],
                    'appReduction' => json_decode($order['appReduction'], true) ?? ['reduction' => 0, 'plus' => 0]
                ];

                // Pour chaque commande, stocker aussi un tableau simple des titres des produits
                if (!isset($formattedOrders[$orderId]['product_list'])) {
                    $formattedOrders[$orderId]['product_list'] = [];
                }
                $formattedOrders[$orderId]['product_list'][] = $order['product_title'] . " (x" . $order['ordered_quantity'] . ")";
            }
        }

        // Pour chaque commande, convertir la liste des produits en chaîne de caractères
        foreach ($formattedOrders as $orderId => $orderData) {
            if (isset($orderData['product_list'])) {
                $formattedOrders[$orderId]['product_list_str'] = implode(", ", $orderData['product_list']);
                unset($formattedOrders[$orderId]['product_list']); // Supprimer le tableau temporaire
            } else {
                $formattedOrders[$orderId]['product_list_str'] = "Aucun produit";
            }
        }

        // Retourner un tableau paginé
        return [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_orders' => $totalOrders,
            'orders' => array_values($formattedOrders)
        ];
    }

    /**
     * Supprimer une commande et ses articles associés
     * @param int $id : ID de la commande
     * @return bool : Résultat de la suppression
     */
    public function delete(int $id)
    {
        try {
            $this->db->beginTransaction();
            // Supprimer la commande
            $result = $this->db->delete('orders', 'id = ?', [$id]);

            $this->db->commitTransaction();
            return $result > 0;
        } catch (\Exception $e) {
            $this->db->rollbackTransaction();
            return false;
        }
    }

    public function updateStatus(int $id, string $status)
    {
        $status = strtolower($status);
        return $this->db->update($this->table, ['status' => $status], 'id = :id', [':id' => $id]);
    }

    /**
     * Trouver une commande par son ID avec ses articles
     * @param int $id : ID de la commande
     * @return array|null : Données de la commande ou null si introuvable
     */
    public function findById(int $id)
    {
        $fields = "
            o.id AS order_id,
            o.printed AS printed,
            oi.price AS finalPrice, 
            p.title AS product_title, 
            oi.quantity AS ordered_quantity, 
            o.customer_address AS address, 
            o.order_date AS order_date,
            oi.appReduction AS appReduction,
            o.customer_name AS nom_prenom,
            o.customer_email AS email,
            o.customer_phone AS phone
        ";

        $tables = "orders o";
        $joins = "
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            LEFT JOIN products p ON oi.product_id = p.id
        ";
        $conditions = "o.id = :id"; // Ne pas inclure "WHERE", `selectA` l'ajoute
        $params = [':id' => $id];

        return $this->db->selectA(
            table: $tables,
            columns: $fields,
            conditions: $conditions,
            params: $params,
            joins: $joins
        );
    }
    public function find(int $id, string $needle)
    {
        $fields = "
            o.id AS order_id,
            o.printed AS printed,
            oi.price AS finalPrice, 
            p.title AS product_title, 
            oi.quantity AS ordered_quantity, 
            o.customer_address AS address, 
            o.order_date AS order_date,
            oi.appReduction AS appReduction,
            o.customer_name AS nom_prenom,
            o.customer_email AS email,
            o.customer_phone AS phone,
            o.status AS status
        ";

        $tables = "orders o";
        $joins = "
            LEFT JOIN order_items oi ON o.id = oi.order_id 
            LEFT JOIN products p ON oi.product_id = p.id
        ";
        $conditions = "o.id = :id AND o.status != 'cancelled' AND (o.customer_email = :email OR o.customer_phone = :phone)";
        $params = [':id' => $id, ':email' => $needle, ':phone' => $needle];

        return $this->db->selectA(
            table: $tables,
            columns: $fields,
            conditions: $conditions,
            params: $params,
            joins: $joins
        );
    }
    public function getStats()
    {
        return $this->db->selectA(
            table: "products",
            columns: "COUNT(products.id) as 'compteur', products.title",
            joins: "INNER JOIN order_items on product_id = products.id",
            groupBy: "products.id"
        );
    }
    public function getStatsFor($period, $start, $end, ?String $status = null)
    {
        $columns = "id,
                    total_amount as 'total',
                    order_date as 'date',
                    customer_name as 'name',
                    status
                    ";
        $conditions = "DATE(order_date) = CURDATE()";
        $params = [];
        switch ($period) {
            case 'week':
                $conditions = "YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'month':
                $conditions = "YEAR(order_date) = YEAR(CURDATE()) 
                               AND MONTH(order_date) = MONTH(CURDATE())";
                break;
            case 'year':
                $conditions = "YEAR(order_date) = YEAR(CURDATE())";
                break;

            case 'custom':
                $conditions = "order_date BETWEEN :start AND :end";
                $params = [
                    ':start' => "$start",
                    ':end' => "$end"
                ];
                break;
            default:
                $conditions = "DATE(order_date) = CURDATE()";
                break;
        }
        if (!empty($status)) {
            $conditions .= ' AND status IN (' . $status . ')';
        }
        $result = $this->db->selectA(
            table: "orders",
            columns: $columns,
            conditions: $conditions,
            params: $params,
            // debug:true
        );
        return $result ? $result : [];
    }
    public function statistics()
    {
        $columns = "id,
                    total_amount AS 'total',
                    DATE(order_date) AS 'date',
                    customer_name AS 'name',
                    status
                    ";
        $result = $this->db->selectA(
            table: "orders",
            columns: $columns,
            orderBy: "order_date DESC"
        );
        return $result ? $result : [];
    }
    public function orderStat()
    {
        $columns = "
            COUNT(*) AS total,
            SUM(CASE WHEN DATE(order_date) = CURDATE() THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS week,
            SUM(CASE WHEN YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS month
        ";

        $result = $this->db->selectA("orders", $columns);

        return !empty($result) ? $result[0] : [
            "total" => 0,
            "today" => 0,
            "week" => 0,
            "month" => 0
        ];
    }
}
