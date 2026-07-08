<?php

namespace Src\Helpers;

class UrlHelper
{
    public static function generateProductLink(string $title, ?int $id = null): string
    {
        // Remplacer les caractères spéciaux par leur version ASCII
        $slug = self::convertToAscii($title);

        // Convertir en minuscules
        $slug = strtolower($slug);

        // Remplacer les espaces par des tirets
        $slug = preg_replace('/[\s]+/', '-', $slug);

        // Supprimer les caractères non alphanumériques sauf _ et -
        $slug = preg_replace('/[^a-z0-9-_]/', '', $slug);

        // Supprimer les tirets multiples et trim
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return "/shop/{$slug}";
    }

    public static function decodeSlug(string $slug): string
    {
        // Remettre les underscores en espaces
        $slug = str_replace('_', ' ', $slug);

        // Remettre en majuscule la première lettre de chaque mot
        return ucwords(str_replace('-', ' ', $slug));
    }

    public static function matchTitleWithSlug(string $title, string $slug): bool
    {
        // Générer un slug à partir du titre
        $generatedSlug = self::generateProductLink($title, 0);
        $generatedSlug = basename(dirname($generatedSlug)); // Récupère juste le slug

        // Comparer les slugs
        return $generatedSlug === $slug;
    }

    private static function convertToAscii(string $string): string
    {
        // Normaliser les caractères spéciaux (accents)
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        return preg_replace('/[^a-zA-Z0-9-_ ]/', '', $normalized); // Garder seulement alphanumérique, - et _
    }

    public static function generateCategoryLink(string $title): string
    {
        // Normaliser le titre de la catégorie
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9-_]/', '-', $title)));

        // Supprimer les répétitions de tirets ou underscores
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = preg_replace('/_+/', '_', $slug);

        return "/categories/{$slug}/";
    }
    public static function generateUrl(array $params = []): string
    {
        $currentParams = $_GET;
        $newParams = array_merge($currentParams, $params);

        // Filtrer les paramètres vides
        $newParams = array_filter($newParams, function ($value) {
            return $value !== '' && $value !== null;
        });

        return '?' . http_build_query($newParams);
    }
}
