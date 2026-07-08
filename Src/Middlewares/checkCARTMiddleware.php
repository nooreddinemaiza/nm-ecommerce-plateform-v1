<?php

namespace Src\Middlewares;

use Src\Services\Route;

class checkCARTMiddleware
{
    public static function checkCART()
    {
        if(empty($_SESSION['CART']['items'])){
            $_SESSION['panier'] = "vide";
            Route::redirect('/');
            exit;
        }
    }
}
