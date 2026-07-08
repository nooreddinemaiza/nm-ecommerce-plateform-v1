<?php

namespace Src\Helpers;


class ShopHelper
{
    /**
     * Extrait les catégories uniques des produits
     * @param products : Liste des produit
     * @return categories : Liste des categories des produits
     */
    public static function extractCategories(array $products): array
    {
        $categories = [];
        foreach ($products as $product) {
            $productCategories = self::getProductCategories($product);
            foreach ($productCategories as $category) {
                // Remplacer les espaces par des underscores pour les classes CSS
                $categories[] = str_replace(' ', '_', trim($category));
            }
        }
        return array_values(array_unique(array_filter(array_map('trim', $categories))));
    }

    /**
     * Récupère les catégories d'un produit (peut être un tableau de catégories)
     */
    public static function getProductCategories(array $product): array
    {
        return is_array($product['categories'])
            ? $product['categories']
            : ($product['categories'] ? [$product['categories']] : []);
    }

    /**
     * Extrait et formate l'image principale d'un produit
     */
    public static function getMainImage(array $product): string
    {
        if (!empty($product['images'])) {
            $image = explode(',', $product['images']);
            return !empty($image[rand(0, count($image) - 1)]) ? $image[rand(0, count($image) - 1)] : "No_Image_Available.jpg";
        }
        return "No_Image_Available.jpg";
    }

    /**
     * Vérifie si le produit a un prix promotionnel
     */
    public static function hasDiscount(array $product): bool
    {
        return $product['old_price'] > $product['price'];
    }
}
