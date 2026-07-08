<?php

namespace Src\Middlewares;

use Src\Controllers\CategorieController;
use Src\Services\Route;

class CategoryMiddleware
{
    public static function checkCategoryExists($categorie)
    {
        // Vérification de l'existence des produits ayant la categorie
        $isCategorie = (new CategorieController)->isCategory($categorie);
        if (!$isCategorie) {
            Route::redirect('/404');
        }
        return true;
    }
}
