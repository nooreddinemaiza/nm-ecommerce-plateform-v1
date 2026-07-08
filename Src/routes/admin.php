<?php

use Src\Helpers\AppLog;
use Src\Services\Route;
use Src\Controllers\PageController;
use Src\Controllers\UserController;
use Src\Helpers\FileAndPathManager;
use Src\Controllers\OrderController;
use Src\Middlewares\ResetMiddleware;
use Src\Controllers\ArticleController;
use Src\Controllers\ContentController;
use Src\Controllers\ProductController;
use Src\Controllers\SectionController;
use Src\Controllers\VisitorController;
use Src\Middlewares\ProductMiddleware;
use Src\Controllers\DatabaseController;
use Src\Controllers\CategorieController;
use Src\Controllers\SubscriberController;
use Src\Controllers\ProtectedAssetController;

$sessionManager = $data['sessionManager'];
// Définition des routes

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'manager') {
        $sessionManager->inactif();
    }
    if ($_SESSION['user_role'] === 'admin') {


        Route::post('/add-user', [UserController::class, 'createUser'], true);
        Route::post('/manager/update/status', [UserController::class, 'updateManagerStatus'], true);
        Route::post('/manager/update/role', [UserController::class, 'updateManagerRole'], true);
        Route::post('/manager/update/password', [UserController::class, 'updateManagerPassword'], true);
        Route::post('/manager/get-infos', [UserController::class, 'getInfos'], true);

        Route::post('/user-delete', [UserController::class, 'deleteUser'], true);

        Route::post('/categories/trending-set', [CategorieController::class, 'setTrending'], true);
        Route::post('/products/trending-set', [ProductController::class, 'setTrending'], true);


        Route::post('/trending-product-get', function () {
            $result = (new ProductController)->getProductsTitles();
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'data' => $result
                ]);
                exit;
            }
            echo json_encode([
                'success' => false,
                'message' => 'Pas de produits!'
            ]);
        }, true);

        Route::post('/sections/add', [SectionController::class, 'add'], true);
        Route::post('/sections/update', [SectionController::class, 'update'], true);
        Route::post('/sections/update-order', [SectionController::class, 'updateSectionsOrder'], true);
        Route::post('/sections/reorder', [SectionController::class, 'reorderSection'], true);
        Route::post('/sections/get', [SectionController::class, 'get'], true);
        Route::post('/sections/get-single', [SectionController::class, 'getSingle'], true);

        Route::post('/banner-modify', [ContentController::class, 'setBanner'], true);
        Route::post('/meta-modify', [ContentController::class, 'setMeta'], true);
        Route::post('/data-modify', [ContentController::class, 'setData'], true);

        Route::get('/dashboard/documentation', function () {
            return (new PageController)->documentation();
        }, true);
        Route::post('/database/delete', function () {
            return (new DatabaseController)->delete();
        }, true);
        Route::post('/database/update', function () {
            return (new DatabaseController)->update();
        }, true);
        Route::post('/database/add', function () {
            return (new DatabaseController)->add();
        }, true);
        Route::get('/dashboard/database', function () use ($sessionManager) {
            if (!$sessionManager->getUserId() && $_SESSION['user_role'] === 'admin') {
                $sessionManager->destroy();
                Route::redirect('/login');
                exit;
            }
            return (new PageController)->database($sessionManager);
        }, true);

        Route::post('/log/del', function () {
            $result = FileAndPathManager::writeFileA('log', 'app.log', "");
            if ($result) {
                echo json_encode([
                    "success" => true
                ]);
                exit;
            }
            echo json_encode([
                "success" => false
            ]);
        }, true);

        Route::get('/log', function () {
            if (!isset($_GET['level']) || !isset($_GET['search'])) {
                echo "Rien à afficher";
                exit;
            }
            echo AppLog::exportToHtml(
                level: $_GET['level'] != 'ALL' ? $_GET['level'] : '',
                dateFormat: $_GET['date']  != 'ALL' ? $_GET['level'] : '',
                searchText: $_GET['search']
            );
        }, true);
    }

    if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'super_manager') {

        Route::post('/products/stock/get', [ProductController::class, 'getStock'], true);
        Route::post('/products/stock/observe', [ProductController::class, 'observeStock'], true);
        Route::post('/products/stock/update', [ProductController::class, 'updateStock'], true);
        Route::post('/products/status/update', [ProductController::class, 'updateStatus'], true);
        Route::post('/get-category-products', [ProductController::class, 'getEssentials'], true);
        Route::post('/add-products-to', [CategorieController::class, 'addToCategory'], true);

        Route::post('/categories/create', [CategorieController::class, 'create'], true);
        Route::post('/categories-delete', [CategorieController::class, 'delete'], true);
        Route::post('/categories-list', [CategorieController::class, 'catsForMan'], true);
        Route::post('/categories-edit', [CategorieController::class, 'edit'], true);
        Route::post('/get-category', [CategorieController::class, 'getInfos'], true);

        Route::post("/orders/paginate", [OrderController::class, 'getPaginatedOrders'], true);
        Route::post("/orders/stats", [OrderController::class, 'stats'], true);
        Route::post("/ventes/stats", [OrderController::class, 'stats'], true);
        Route::post("/orders/statistics", [OrderController::class, 'statistics'], true);
        Route::post("/orders/delete", [OrderController::class, 'delete'], true);
        Route::post("/orders/update-status", [OrderController::class, 'updateStatus'], true);
        Route::post("/orders/message-client", [OrderController::class, 'sendStyledEmailToClient'], true);

        Route::post('/visitor/feedback', [UserController::class, 'sendFeedback']);
        Route::post('/visitor/feedbacks/list', [VisitorController::class, 'getPaginatedMessages'], true);
        Route::post('/visitor/feedbacks/delete', [VisitorController::class, 'deleteMessage'], true);

        Route::post('/subscribers/notify', [SubscriberController::class, 'notifySubscribers'], true);
        Route::post('/subscribers/list', [SubscriberController::class, 'listSubscribers'], true);
        Route::post('/subscribers/delete', [SubscriberController::class, 'delete'], true);
        Route::post('/subscribers/delete-list', [SubscriberController::class, 'delete'], true);

        Route::post('/manager/articles/add', [ArticleController::class, 'create'], true);
        Route::post('/manager/articles/update', [ArticleController::class, 'edit'], true);
        Route::post('/manager/articles/delete', [ArticleController::class, 'delete'], true);
        Route::post('/manager/articles/list', [ArticleController::class, 'listForMan'], true);
        Route::post('/manager/articles/getSingle/', [ArticleController::class, 'getSingle'], true);
    }

    Route::post('/dashboard/categories-mod', [CategorieController::class, 'getEssentials'], true);

    Route::post('/update-products', [ProductController::class, 'updateProduct'], true);
    Route::post('/dashboard/view-product', [ProductController::class, 'viewProduct'], true);

    Route::post('/manager/profile/get', [UserController::class, 'getManagerProfle'], true);
    Route::post("/manager/profile/update", [UserController::class, 'updateUserProfile'], true);

    Route::post('/dashboard/products/paginate', function () {
        ProductMiddleware::getPaginatedProducts();
    });

    Route::post('/add-product', function () use ($sessionManager) {
        return (new ProductController)->addProduct($sessionManager);
    }, true);
    Route::post('/delete-product', function () use ($sessionManager) {
        return (new ProductController)->deleteProduct($sessionManager);
    }, true);
    Route::get('/protected_assets/{type}/{file}', function ($type, $file) use ($sessionManager) {
        $controller = new ProtectedAssetController($sessionManager);
        return $controller->serveFile($type, $file);
    });
    Route::get('/dashboard', function () use ($sessionManager) {
        return (new PageController)->dashboard($sessionManager);
    }, true);
}

Route::get('/reset-password', function () use ($sessionManager) {
    $resetMiddleware = new ResetMiddleware($sessionManager);
    if ($resetMiddleware->checkResetToken()) {
        return (new PageController)->resetPasswordPage();
    }
    return Route::redirect('/403');
});
