<?php
use Src\Services\Route;
use Src\Controllers\PageController;

// Définition des routes
Route::get('/contact', [PageController::class, 'contact']);
Route::get('/about', [PageController::class, 'about']);
Route::get('/home', function(){
    Route::redirect('/');
});
Route::get('/shop', [PageController::class, 'shop']);
Route::get('/devis', [PageController::class, 'devis']);
Route::get('/categories', [PageController::class, 'categories']);
Route::get('/actualites', [PageController::class, 'articles']);