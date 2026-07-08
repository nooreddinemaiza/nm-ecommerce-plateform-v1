<?php

namespace Src\Helpers;

class Cart
{
    /**
     * Initialise le panier dans la session
     */
    public static function beginCart(): void
    {
        if (!isset($_SESSION["CART"])) {
            $_SESSION["CART"] = ['items' => [], 'total' => 0];
        }
    }

    /**
     * Vide le panier
     */
    public static function endCart(): void
    {
        unset($_SESSION["CART"]);
        unset($_SESSION['products']);
    }

    /**
     * Stocke temporairement un produit dans la session avant son ajout au panier
     */
    public static function store(array $product): void
    {
        // Initialise la session des produits si elle n'existe pas
        if (!isset($_SESSION["products"])) {
            $_SESSION["products"] = [];
        }

        // Vérifie et nettoie l'ID du produit
        $id = $product["id"] ?? null;
        if ($id === null) {
            return;
        }

        // Nettoie et stocke le produit
        $_SESSION["products"][$id] = array_map([self::class, 'sanitizeInput'], $product);
    }
    public static function orderItems(): array
    {
        $items = [];
        foreach ($_SESSION["CART"]['items'] as $item) {
            $items[] = [
                'product_title' => $item['title'],
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                // 'price' => self::calculateFinalPrice($item)*$item['quantity'],
                'price' => ($item['price']),
                'appReduction' => '{"reduction":' . ((intval($item['reduction']) != 0) ? $item['reduction'] : 0)
                    . ',"plus":' . ((intval($item['reduction']) != 0) ? $item['appReduction'] : 0)
                    . '}',
            ];
        }
        return $items;
    }

    /**
     * Ajoute un produit au panier et met à jour le total
     * Gère la réponse JSON directement
     */
    public static function addToCart(): void
    {
        // Définit le type de contenu en JSON
        header('Content-Type: application/json');

        // Initialise le panier
        self::beginCart();

        // Récupère l'ID du produit
        $id = (int)self::sanitizeInput($_POST['id']);
        $quantity = max(1, (int)self::sanitizeInput($_POST['quantity'] ?? 1));

        // Vérifie l'existence du produit
        if ($id === null || !isset($_SESSION['products'][$id])) {
            self::sendJsonResponse(false, "Produit introuvable!");
        }

        // Gère l'ajout ou la mise à jour du produit dans le panier
        if (isset($_SESSION["CART"]['items'][$id])) {
            $_SESSION['CART']['items'][$id]['quantity'] += $quantity;
        } else {
            $_SESSION['CART']['items'][$id] = $_SESSION['products'][$id];
            $_SESSION['CART']['items'][$id]['quantity'] = $quantity;
        }

        // Calcule le prix final avec réduction
        $_SESSION['CART']['items'][$id]['final_price'] =
            self::calculateFinalPrice($_SESSION['CART']['items'][$id]) *
            $_SESSION['CART']['items'][$id]['quantity'];

        // Met à jour le total du panier
        self::updateTotal();

        // Envoi de la réponse JSON
        self::sendJsonResponse(
            true,
            '',
            array_values($_SESSION['CART']['items']),
            $_SESSION["CART"]['total']
        );
    }
    public static function isProductInCart($id): bool
    {
        return isset($_SESSION['CART']['items'][$id]);
    }
    /**
     * Modifie la quantité d'un produit dans le panier et met à jour le total
     */
    public static function modifyQuantity(): void
    {
        // Définit le type de contenu en JSON
        header('Content-Type: application/json');

        // Récupère et nettoie les paramètres
        $id = self::sanitizeInput($_POST['id'] ?? null);
        $newQuantity = (int)self::sanitizeInput($_POST['newQuantity'] ?? null);

        // Vérifie la validité de la quantité
        if (!isset($_SESSION["CART"]['items'][$id]) || $newQuantity < 1) {
            self::sendJsonResponse(false, "Quantité invalide ou produit introuvable!");
        }

        // Met à jour la quantité
        $_SESSION['CART']['items'][$id]['quantity'] = $newQuantity;

        // Recalcule le prix final avec la nouvelle quantité
        $_SESSION['CART']['items'][$id]['final_price'] =
            self::calculateFinalPrice($_SESSION['CART']['items'][$id]) * $newQuantity;

        // Met à jour le total du panier
        self::updateTotal();

        // Envoi de la réponse JSON
        self::sendJsonResponse(
            true,
            '',
            array_values($_SESSION['CART']['items']),
            $_SESSION["CART"]['total']
        );
    }

    /**
     * Supprime un produit du panier et met à jour le total
     */
    public static function deleteQuantity(): void
    {
        // Définit le type de contenu en JSON
        header('Content-Type: application/json');

        // Récupère et nettoie l'ID du produit
        $id = self::sanitizeInput($_POST['id'] ?? null);

        // Vérifie l'existence du produit dans le panier
        if (!isset($_SESSION["CART"]['items'][$id])) {
            self::sendJsonResponse(false, "Produit introuvable!");
        }

        // Supprime le produit du panier
        unset($_SESSION['CART']['items'][$id]);

        // Met à jour le total du panier
        self::updateTotal();

        // Envoi de la réponse JSON
        self::sendJsonResponse(
            true,
            '',
            array_values($_SESSION['CART']['items']),
            $_SESSION["CART"]['total']
        );
    }

    /**
     * Calcule le prix final d'un produit après application de la réduction (si applicable)
     */
    private static function calculateFinalPrice(array $item): float
    {
        // Prix de base
        $price = $item['price'] ?? 0;

        // Vérifie les conditions de réduction
        if (
            isset($item['reduction'], $item['appReduction']) &&
            $item['reduction'] > 0 &&
            $item['quantity'] >= $item['appReduction']
        ) {
            $price -= ($price * ($item['reduction'] / 100));
        }

        // Empêche un prix négatif
        return max(0, $price);
    }

    /**
     * Met à jour le total du panier
     */
    private static function updateTotal(): void
    {
        // Calcule le total en appliquant les prix finaux
        $total = 0;
        foreach ($_SESSION["CART"]['items'] as $item) {
            $total += self::calculateFinalPrice($item) * $item['quantity'];
        }

        // Met à jour le total dans la session
        $_SESSION["CART"]['total'] = $total;
    }

    /**
     * Récupère l'ID du produit à partir de l'URL de référence
     */
    private static function getProduct(): ?int
    {
        // Récupère l'URL de référence
        $url = $_SERVER['HTTP_REFERER'] ?? '';

        // Extrait l'ID en utilisant une expression régulière
        if (preg_match('/(\d+)$/', $url, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    /**
     * Nettoie les entrées utilisateur
     */
    private static function sanitizeInput($value): string
    {
        // Supprime les espaces et échappe les caractères HTML
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Envoi de réponse JSON standardisé
     */
    private static function sendJsonResponse(
        bool $success,
        string $message = '',
        array $list = [],
        float $total = 0
    ): void {
        // Prépare les données de réponse
        $response = [
            'success' => $success,
            'message' => $message,
            'list' => $list,
            'total' => $total
        ];

        // Ajoute un message d'erreur si nécessaire
        if (!empty($message)) {
            $response['message'] = $message;
        }

        // Ajoute la liste des articles si présente
        if (!empty($list)) {
            $response['list'] = $list;
        }

        // Ajoute le total si positif
        if ($total > 0) {
            $response['total'] = $total;
        }

        // Envoie la réponse JSON et termine le script
        echo json_encode($response);
        exit;
    }
}
