<?php

namespace Src\Controllers;

use Src\Helpers\Cart;
use Src\Models\Order;
use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Helpers\Mailer;
use Src\Helpers\Validator;


class OrderController
{
    private $orderModel;
    private $order_id_number;
    private $validator;
    private $email;
    public function __construct()
    {
        $this->email = Config::get("WEB_EMAIL");
        $this->orderModel = new Order();
        $this->validator = new Validator();
        $this->order_id_number = Config::get("ORDER_ID_NUMBER");
    }
    private function validateOrderData($data): array
    {
        $ndata = [];

        // Vérification que $data est bien un tableau
        if (!is_array($data) || empty($data)) {
            echo json_encode(["success" => false, "message" => "Données invalides"]);
            exit;
        }

        // Nettoyage des entrées utilisateur
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $s => $t) {
                    $data[$k][$s] = trim(htmlspecialchars($t));
                }
            } else {
                $data[$k] = trim(htmlspecialchars($v));
            }
        }

        // Vérification du CSRF token
        if (!isset($data['csrf_token'], $_SESSION['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }

        // Vérification de l'existence et validation des données requises
        if (
            empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) ||
            empty($data['phone']) || empty($data['address'])
        ) {
            echo json_encode(["success" => false, "message" => "Tous les champs sont obligatoires."]);
            exit;
        }

        // Validation des longueurs des champs
        if (strlen($data['firstName']) > 50 || strlen($data['lastName']) > 50) {
            echo json_encode(["success" => false, "message" => "Le prénom et le nom ne doivent pas dépasser 50 caractères."]);
            exit;
        }

        if (strlen($data['email']) > 100) {
            echo json_encode(["success" => false, "message" => "L'email ne doit pas dépasser 100 caractères."]);
            exit;
        }

        if (strlen($data['phone']) > 15) {
            echo json_encode(["success" => false, "message" => "Le numéro de téléphone ne doit pas dépasser 15 caractères."]);
            exit;
        }

        if (strlen($data['address']) > 255) {
            echo json_encode(["success" => false, "message" => "L'adresse ne doit pas dépasser 255 caractères."]);
            exit;
        }

        if (!$this->validator->validateEmail($data['email']) || !$this->validator->validatePhone($data['phone'])) {
            echo json_encode(["success" => false, "message" => "Email ou numéro de téléphone invalide."]);
            exit;
        }

        if (!empty($data['postal_code']) && (strlen($data['postal_code']) !== 5 || !preg_match('/^[0-9]{5}$/', $data['postal_code']))) {
            echo json_encode([
                'success' => false,
                'message' => "Le code postal doit être constitué de 5 chiffres.",
            ]);
            exit;
        }
        if (!empty($data['city']) && strlen($data['city']) > 50) {
            echo json_encode(["success" => false, "message" => "La ville ne doit pas dépasser 50 caractères."]);
            exit;
        }
        // Stockage des informations validées
        $ndata['customer_name'] = $data['firstName'] . ' ' . $data['lastName'];
        $ndata['customer_email'] = $data['email'];
        $ndata['customer_phone'] = $data['phone'];
        $ndata['customer_address'] = $data['address'];
        $ndata['customer_city_zip'] = $data['postalCode'];
        $ndata['customer_city'] = $data['city'];

        return $ndata;
    }

    /**
     * Créer une nouvelle commande
     * @return void
     */
    public function createOrder()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Données invalides"]);
                exit;
            }

            // Validation des données
            $data['customer_infos'] = $this->validateOrderData($data);

            // Récupération des articles du panier
            $data['items'] = \Src\Helpers\Cart::orderItems();
            if (empty($data['items'])) {
                echo json_encode(["success" => false, "message" => "Votre panier est vide."]);
                exit;
            }

            // Calcul du montant total
            $data['total_amount'] = array_sum(array_column($data['items'], 'price'));

            // Insertion de la commande en base de données
            $stmnt = $this->orderModel->create($data);

            if ($stmnt) {
                // Génération de l'ID de commande
                $order_id = $stmnt + $this->order_id_number;

                // Préparation des informations de la commande
                $order_infos = [
                    'client' => [
                        'id' => $order_id,
                        'nom_prenom' => $data['customer_infos']['customer_name'],
                        'address' => $data['customer_infos']['customer_address'],
                        'date' => (new \DateTime())->format("d/m/Y"),
                        'email' => $data['customer_infos']['customer_email'],
                        'phone' => $data['customer_infos']['customer_phone']
                    ],
                    'produits' => []
                ];

                foreach ($data['items'] as $value) {
                    $order_infos['produits'][] = [
                        'title' => $value['product_title'] ?? 'N/A',
                        'quantity' => $value['quantity'] ?? 0,
                        'unit_price' => $value['price'] ?? 0.00,
                        'appReduction' => isset($value['appReduction']) ? json_decode($value['appReduction'], true) : ['reduction' => 0, 'plus' => 0]
                    ];
                }

                // Envoi des emails
                $this->sendEmailToAdmin($order_infos);
                $this->sendSummaryToClient($order_infos);

                // Fin du panier
                Cart::endCart();

                echo json_encode(["success" => true, "message" => "Commande créée avec succès!", "order_id" => $order_id]);
            } else {
                echo json_encode(["success" => false, "message" => "Une erreur est survenue lors de la création de la commande."]);
            }
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'ajout de la commande : " . $e->getMessage());
            echo json_encode(["success" => false, "message" => "Erreur lors de l'ajout de la commande!"]);
        }
    }

    public function getOrderIdNumber()
    {
        return $this->order_id_number;
    }
    public function cancel(int $id)
    {
        $id -= $this->order_id_number;
        $status = 'cancelled';
        return $this->orderModel->updateStatus($id, $status);
    }
    public function updateStatus()
    {
        $data = $_POST;
        if (empty($data) || empty($data['order_id']) || empty($data['status'])) {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            exit;
        }
        $order_id = intval(htmlspecialchars($data['order_id'])) - $this->order_id_number;
        $status = htmlspecialchars($data['status']);

        $stmnt = $this->orderModel->updateStatus($order_id, $status);
        if ($stmnt) {
            echo json_encode(["success" => true, "message" => "Statut mis à jour avec succès!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Nous n'arrivons pas à changer le status!"]);
        }
    }
    /**
     * Récupérer toutes les commandes
     * @return array : Liste des commandes
     */
    public function listOrders()
    {
        return $this->orderModel->getAll();
    }

    /**
     * Récupérer toutes les commandes
     * @return array : Liste des commandes
     */
    public function getOrders()
    {
        $orders = $this->orderModel->getOrders();
        return $orders;
    }
    /**
     * Récupérer toutes les commandes paginées
     * @return void : Liste des commandes paginées
     */
    public function getPaginatedOrders(): void
    {
        $data = $_POST;
        foreach ($data as $key => $value) {
            $$key = trim(htmlspecialchars($value));
        }
        if (!isset($newOnly) || !isset($status) || !isset($search) || !isset($page) || !isset($per_page)) {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            exit;
        }
        $page = intval($page) < 1 ? 1 : intval($page);
        $per_page = intval($per_page) < 1 ? 5 : intval($per_page);
        $newOnly = intval($newOnly) < 1 ? 0 : intval($newOnly);
        if ($newOnly == 1) {
            $newOnly = true;
        } else {
            $newOnly = false;
        }
        $orders = $this->orderModel->getPaginatedOrders($page, $per_page, $search, $status, $newOnly);
        echo json_encode(["success" => true, "data" => $orders]);
    }
    /**
     * Récupérer une commande spécifique par son ID
     * @param int $id : ID de la commande
     * @param string $needle : Email ou téléphone du client
     * @return array|null : Données de la commande ou null si introuvable
     */
    public function find()
    {
        $data = $_POST;
        foreach ($data as $key => $value) {
            $$key = trim(htmlspecialchars($value));
        }
        if (!isset($reference) || !isset($needle)) {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            exit;
        }
        $order = $this->orderModel->find(intval($reference - $this->order_id_number), $needle);
        if (!empty($order)) {
            $order_infos = [
                'client' => [
                    'id' => $order[0]['order_id'],
                    'printed' => $order[0]['printed'],
                    'nom_prenom' => $order[0]['nom_prenom'],
                    'address' => $order[0]['address'],
                    'date' => (($order[0]['order_date'])),
                    'email' => $order[0]['email'],
                    'phone' => $order[0]['phone'],
                    'status' => $order[0]['status']
                ]
            ];
            $items = [];
            foreach ($order as $key => $value) {
                // Vérifie que les indices existent avant de les utiliser
                $items[$key]['title'] = isset($value['product_title']) ? $value['product_title'] : 'N/A';
                $items[$key]['quantity'] = isset($value['ordered_quantity']) ? $value['ordered_quantity'] : 0;
                $items[$key]['unit_price'] = isset($value['finalPrice']) ? $value['finalPrice'] : 0.00;
                $items[$key]['appReduction'] = isset($value['appReduction']) ? json_decode($value['appReduction'], true) : json_decode('{"reduction":0,"plus":0}', true);
            }
            $order_infos['produits'] = $items;
            return $order_infos;
        } else {
            return null;
        }
    }

    /**
     * Récupérer une commande spécifique par son ID
     * @param int $orderId : ID de la commande
     * @return array|null : Données de la commande ou null si introuvable
     */
    public function getOrderById(int $orderId)
    {
        $order = $this->orderModel->findById(intval($orderId - $this->order_id_number));
        $order_infos = [
            'client' => [
                'id' => $order[0]['order_id'],
                'printed' => $order[0]['printed'],
                'nom_prenom' => $order[0]['nom_prenom'],
                'address' => $order[0]['address'],
                'date' => (new \DateTime($order[0]['order_date']))->format("d/m/Y"),
                'email' => $order[0]['email'],
                'phone' => $order[0]['phone']
            ]
        ];
        $items = [];
        foreach ($order as $key => $value) {
            // Vérifie que les indices existent avant de les utiliser
            $items[$key]['title'] = isset($value['product_title']) ? $value['product_title'] : 'N/A';
            $items[$key]['quantity'] = isset($value['ordered_quantity']) ? $value['ordered_quantity'] : 0;
            $items[$key]['unit_price'] = isset($value['finalPrice']) ? $value['finalPrice'] : 0.00;
            $items[$key]['appReduction'] = isset($value['appReduction']) ? json_decode($value['appReduction'], true) : json_decode('{"reduction":0,"plus":0}', true);
        }
        $order_infos['produits'] = $items;
        return $order_infos;
    }
    public function getStats()
    {
        $stats = $this->orderModel->getStats();
        return $this->trierTableauParCompteur($stats, 'desc');
    }
    private function trierTableauParCompteur(array $tab, string $ordre = 'asc'): array
    {
        usort($tab, function ($a, $b) use ($ordre) {
            return ($ordre === 'asc') ? ($a['compteur'] <=> $b['compteur']) : ($b['compteur'] <=> $a['compteur']);
        });
        return $tab;
    }
    public function stats()
    {
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $$key = array_map(function ($item) {
                    return htmlentities(trim($item));
                }, $value);
            } else {
                $$key = htmlentities(trim($value));
            }
        }
        if (!isset($period) || !isset($start_date) || !isset($end_date)) {
            echo json_encode([
                'success' => false,
                'message' => 'Une erreur est survenu!'
            ]);
            exit;
        }
        if ($period === "custom") {
            if (empty($start_date) || empty($end_date)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'La durée personnalisé est invalide!'
                ]);
                exit;
            }
        }
        if (empty($status)) {
            $status = null;
        } elseif (is_array($status)) {
            $status = array_map(function ($s) {
                return '"' . addslashes($s) . '"';
            }, $status);
            $status = implode(',', $status);
        } else {
            $status = '"' . addslashes($status) . '"';
        }
        $res = $this->orderModel->getStatsFor($period, $start_date, $end_date, $status);
        if ($res) {
            $data = [
                'total' => 0,
                'orders' => $res,
            ];
            foreach ($res as $order) {
                $data['total'] += floatval($order['total']);
            }
            echo json_encode([
                'success' => true,
                'data'    =>  $data
            ]);
            exit;
        }
        echo json_encode([
            'success' => false,
            'message' => 'Aucun record pour ces valeurs!'
        ]);
        exit;
    }
    public function orderStat()
    {
        $orderStat =  $this->orderModel->orderStat();
        return [
            'total' => $orderStat['total'] ?? 0,
            'today' => $orderStat['today'] ?? 0,
            'week' => $orderStat['week'] ?? 0,
            'month' => $orderStat['month'] ?? 0,
        ];
    }
    public function statistics()
    {
        $res = $this->orderModel->statistics();
        if ($res) {
            echo json_encode([
                'success' => true,
                'data'    =>  $res
            ]);
            exit;
        }
        echo json_encode([
            'success' => false,
            'message' => 'Aucun record pour ces valeurs!'
        ]);
        exit;
    }
    /**
     * Supprimer une commande par son ID
     * @param int $orderId : ID de la commande
     * @return bool : Résultat de la suppression
     */
    public function delete()
    {
        $data = $_POST;
        if (empty($data) || empty($data['order_id'])) {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            exit;
        }
        $order_id = intval(htmlspecialchars($data['order_id']));
        $stmnt = $this->orderModel->delete($order_id);
        if ($stmnt) {
            echo json_encode(["success" => true, "message" => "Commande supprimée avec succès!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
        }
    }
    function sendEmailToAdmin($order_info)
    {
        $adminEmail = $this->email;
        $clientName = $order_info['client']['nom_prenom'];
        $subject = 'Nouvelle commande de ' . $clientName;

        // Style CSS inline pour l'email
        $css = '
        body { font-family: "Segoe UI", Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4A6FDC; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; }
        .header h2 { margin: 0; font-weight: 500; }
        .content { background-color: #fff; padding: 20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; margin-bottom: 15px; color: #2C3E50; border-bottom: 1px solid #eee; padding-bottom: 8px; }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 120px; font-weight: 500; color: #555; }
        .info-value { flex: 1; }
        .product-item { padding: 12px; margin-bottom: 10px; background-color: #f9f9f9; border-radius: 6px; }
        .product-title { font-weight: 500; margin-bottom: 5px; }
        .product-meta { font-size: 14px; color: #666; }
        .price { font-weight: 500; display: block; margin-top: 5px; }
        .price-original { text-decoration: line-through; color: #999; }
        .price-reduced { color: #e74c3c; }
        .alert { background-color: #FFF3CD; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px; color: #856404; }
        ';

        // Construire le corps de l'email en HTML
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Notification de Commande</title>
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🛍️ Nouvelle commande</h2>
                </div>
                <div class="content">
                    <!-- Informations du client -->
                    <div class="section">
                        <div class="section-title">👤 Informations du client</div>
                        <div class="info-row">
                            <div class="info-label">Nom</div>
                            <div class="info-value">' . htmlspecialchars($clientName) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email</div>
                            <div class="info-value">' . htmlspecialchars($order_info['client']['email']) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Téléphone</div>
                            <div class="info-value">' . htmlspecialchars($order_info['client']['phone']) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Adresse</div>
                            <div class="info-value">' . htmlspecialchars($order_info['client']['address']) . '</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Date</div>
                            <div class="info-value">' . htmlspecialchars($order_info['client']['date']) . '</div>
                        </div>
                    </div>
    
                    <!-- Détails de la commande -->
                    <div class="section">
                        <div class="section-title">📦 Détails de la commande</div>';

        foreach ($order_info['produits'] as $produit) {
            $title = htmlspecialchars($produit['title']);
            $quantity = htmlspecialchars($produit['quantity']);
            $unitPrice = htmlspecialchars($produit['unit_price']);
            $reduction = $produit['appReduction']['reduction'] ?? 0;
            $plus = $produit['appReduction']['plus'] ?? 0;

            $htmlBody .= '
                        <div class="product-item">
                            <div class="product-title">' . $title . '</div>
                            <div class="product-meta">
                                <div class="quantity">Quantité: ' . $quantity . '</div>';

            if ($reduction > 0 && $quantity > $plus) {
                $newPrice = $unitPrice - ($unitPrice * ($reduction / 100));
                $htmlBody .= '
                                <div class="price">
                                    <span class="price-original">Prix unitaire: ' . $unitPrice . ' DH</span><br>
                                    <span class="price-reduced">Prix réduit: ' . $newPrice . ' DH</span>
                                </div>';
            } else {
                $htmlBody .= '
                                <div class="price">Prix unitaire: ' . $unitPrice . ' DH</div>';
            }

            $htmlBody .= '
                            </div>
                        </div>';
        }

        $htmlBody .= '
                    </div>
    
                    <!-- Message de confirmation -->
                    <div class="alert">
                        ⚠️ Cette commande nécessite votre confirmation. Veuillez contacter le client pour finaliser la transaction.
                    </div>
                    <div class="alert">
                        <a href="' . WEB_URL . '/login">Se connecter</a>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Envoi de l'email
        $mailer = new Mailer();
        return $mailer->quickSend($adminEmail, $subject, $htmlBody, true);
    }
    function sendSummaryToClient(array $order_info): bool
    {
        // Vérification des données de base
        if (empty($order_info['client']) || !isset($order_info['produits'])) {
            AppLog::error("Les informations de commande sont incomplètes.");
        }

        // Récupération des informations du client
        $to = $order_info['client']['email'] ?? $this->email; // Email du client ou valeur par défaut
        $deliveryAddress = $order_info['client']['address'] ?? 'Non spécifiée'; // Adresse de livraison
        $subject = 'Récapitulatif de votre commande';

        // Initialisation des totaux
        $totalBeforeDiscount = 0;
        $totalAfterDiscount = 0;
        $totalReduction = 0;

        // Style CSS inline pour l'email
        $css = '
        body { font-family: "Segoe UI", Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #2ecc71; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .header h2 { margin: 0; font-weight: 500; }
        .content { background-color: #fff; padding: 20px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 18px; margin-bottom: 15px; color: #2C3E50; border-bottom: 1px solid #eee; padding-bottom: 8px; display: flex; align-items: center; }
        .section-title-icon { margin-right: 8px; }
        .product-item { padding: 12px; margin-bottom: 10px; background-color: #f9f9f9; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
        .product-info { flex: 1; }
        .product-title { font-weight: 500; margin-bottom: 5px; }
        .product-quantity { font-size: 14px; color: #666; }
        .product-price { text-align: right; min-width: 100px; }
        .price-original { text-decoration: line-through; color: #999; display: block; }
        .price-reduced { color: #e74c3c; font-weight: 500; }
        .normal-price { font-weight: 500; }
        .address-box { background-color: #f9f9f9; padding: 12px; border-radius: 6px; }
        .total-section { background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin-top: 20px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .total-divider { border-top: 1px solid #ddd; margin: 10px 0; }
        .grand-total { font-weight: 600; color: #2ecc71; font-size: 18px; }
        .alert { background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-top: 20px; color: #0c5460; border-radius: 4px; }
        ';

        // Construire le corps de l'email en HTML
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Récapitulatif de Commande</title>
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>✅ Récapitulatif de votre commande</h2>
                </div>
                <div class="content">
                    <div class="section-title">
                        <span>Référence de commande: <b>' . htmlspecialchars($order_info['client']['id'] ?? 'N/A') . '</b></span>
                    </div>  
                    <!-- Section Articles -->
                    <div class="section">
                        <div class="section-title">
                            <span class="section-title-icon">🛍️</span> Articles commandés
                        </div>';

        // Parcourir les produits
        foreach ($order_info['produits'] as $produit) {
            // Validation des données du produit
            if (empty($produit['title']) || !isset($produit['quantity']) || !isset($produit['unit_price'])) {
                AppLog::error("Les informations du produit sont incomplètes.");
            }

            $title = htmlspecialchars($produit['title']);
            $quantity = (int) $produit['quantity'];
            $unitPrice = (float) $produit['unit_price'];
            $reduction = (float) ($produit['appReduction']['reduction'] ?? 0);
            $plus = (int) ($produit['appReduction']['plus'] ?? 0);

            // Vérification des valeurs numériques
            if ($quantity <= 0 || $unitPrice < 0 || $reduction < 0 || $plus < 0) {
                AppLog::error("Les valeurs de quantité, prix ou réduction sont invalides.");
            }

            // Calcul du total avant réduction
            $totalLigneBeforeDiscount = $unitPrice * $quantity;
            $totalBeforeDiscount += $totalLigneBeforeDiscount;

            // Initialisation du total après réduction
            $totalLigneAfterDiscount = $totalLigneBeforeDiscount;

            // Vérifier si une réduction s'applique
            if ($reduction > 0 && $quantity >= $plus) {
                $reductionAmount = $totalLigneBeforeDiscount * ($reduction / 100);
                $totalReduction += $reductionAmount;
                $totalLigneAfterDiscount -= $reductionAmount;
            }

            $totalAfterDiscount += $totalLigneAfterDiscount;

            // Affichage dans le mail
            $htmlBody .= '
                            <div class="product-item">
                                <div class="product-info">
                                    <div class="product-title">' . $title . '</div>
                                    <div class="product-quantity">Quantité: ' . $quantity . '</div>
                                </div>
                                <div class="product-price">';

            if ($reduction > 0 && $quantity >= $plus) {
                $newPrice = $unitPrice * (1 - ($reduction / 100));
                $htmlBody .= '
                                    <span class="price-original">' . number_format($unitPrice, 2) . ' DH</span>
                                    <span class="price-reduced">' . number_format($newPrice, 2) . ' DH</span>';
            } else {
                $htmlBody .= '
                                    <span class="normal-price">' . number_format($unitPrice, 2) . ' DH</span>';
            }
            $htmlBody .= '
                                </div>
                            </div>';
        }

        $htmlBody .= '
                    </div>
    
                    <!-- Section Livraison -->
                    <div class="section">
                        <div class="section-title">
                            <span class="section-title-icon">🚚</span> Livraison
                        </div>
                        <div class="address-box">
                            <div class="product-title">Adresse de livraison</div>
                            <div>' . htmlspecialchars($deliveryAddress) . '</div>
                        </div>
                    </div>
    
                    <!-- Section Total -->
                    <div class="total-section">
                        <div class="total-row">
                            <span>Sous-total:</span>
                            <span>' . number_format($totalBeforeDiscount, 2) . ' DH</span>
                        </div>';

        if ($totalReduction > 0) {
            $htmlBody .= '
                        <div class="total-row" style="color: #e74c3c;">
                            <span>Remises:</span>
                            <span>-' . number_format($totalReduction, 2) . ' DH</span>
                        </div>';
        }

        $htmlBody .= '
                        <div class="total-divider"></div>
                        <div class="total-row">
                            <span>Total à payer:</span>
                            <span class="grand-total">' . number_format($totalAfterDiscount, 2) . ' DH</span>
                        </div>
                    </div>
    
                    <!-- Message de confirmation -->
                    <div class="alert">
                        <strong>📞 Prochaines étapes :</strong> Un membre de notre équipe vous contactera bientôt pour confirmer votre commande.
                    </div>
                </div>
            </div>
        </body>
        </html>';

        // Envoi de l'email
        $mailer = new Mailer();
        return $mailer->quickSend($to, $subject, $htmlBody, true);
    }
    /**
     * Envoi un email au client avec un style spécifique
     * @param string $to Email du client
     * @param string $subject Sujet de l'email
     * @param string $message Contenu HTML de l'email
     */
    public function sendStyledEmailToClient()
    {
        $data = $_POST;



        if (empty($data['recipient']) || empty($data['subject']) || empty($data['body'])) {
            echo json_encode(["success" => false, "message" => "Données manquantes!"]);
            return false;
        }

        $to = htmlspecialchars(trim($data['recipient']));
        $subject = htmlspecialchars(trim($data['subject']));
        $message = nl2br(htmlspecialchars(trim($data['body'])));

        // Style CSS inline pour l'email
        $css = '
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f9f9f9; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .email-header { background: #4CAF50; color: #fff; padding: 20px; text-align: center; font-size: 24px; }
        .email-body { padding: 20px; font-size: 16px; color: #555; }
        .email-footer { background: #f1f1f1; text-align: center; padding: 10px; font-size: 14px; color: #777; }
        a { color: #4CAF50; text-decoration: none; }
        ';

        // Construire le corps de l'email avec le style
        $htmlBody = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>' . htmlspecialchars($subject) . '</title>
            <style>' . $css . '</style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">Notification</div>
                <div class="email-body">' . $message . '</div>
                <div class="email-footer">Merci de nous avoir choisis. <br> <a href="' . WEB_URL . '">Visitez notre site</a></div>
            </div>
        </body>
        </html>';

        // Envoi de l'email
        $mailer = new Mailer();
        $result =  $mailer->quickSend($to, $subject, $htmlBody, true);
        if ($result) {
            echo json_encode(["success" => true, "message" => "E-mail envoyé avec succé!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de l'envoie!"]);
        }
        exit;
    }
}
