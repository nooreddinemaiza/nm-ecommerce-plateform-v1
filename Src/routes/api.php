<?php

use Src\Helpers\Cart;
use Src\Services\Route;
use Src\Helpers\UrlHelper;
use Src\Controllers\PageController;
use Src\Helpers\FileAndPathManager;
use Src\Controllers\OrderController;
use Src\Controllers\ProductController;
use Src\Controllers\VisitorController;
use Src\Middlewares\ArticleMiddleware;
use Src\Middlewares\ProductMiddleware;
use Src\Middlewares\CategoryMiddleware;
use Src\Controllers\CategorieController;
use Src\Middlewares\checkCARTMiddleware;
use Src\Controllers\SubscriberController;
use Src\Middlewares\OrderConsulterMiddleware;

$sessionManager = $data["sessionManager"];

//Routes For Orders :
Route::post("/cart/add-product", [Cart::class, 'addToCart']);
Route::post("/cart/modify-product", [Cart::class, 'modifyQuantity']);
Route::post("/cart/delete-product", [Cart::class, 'deleteQuantity']);
Route::post("/checkout-confirmed", [OrderController::class, 'createOrder']);

//afficher une commande pour le client
Route::get('/consulter-commande', function () {
    if (isset($_SESSION["not_robot"]["order"]) && $_SESSION["not_robot"]["order"]) {
        $pageController = new PageController();
        $pageController->orderConsulter();
    } else {
        Route::redirect('/consulter-commande/captcha');
    }
});
Route::get('/consulter-commande/captcha', function () {
    $pageController = new PageController();
    $pageController->orderConsulterCaptcha();
});
Route::post('/consulter-commande/captcha-check', function () {
    OrderConsulterMiddleware::checkCaptcha();
});
Route::post('/order/find', function () {
    $order = OrderConsulterMiddleware::checkOrderExists($_POST['reference'], $_POST['needle']);
    if ($order) {
        unset($_SESSION["not_robot"]["order"]);
        $pageController = new PageController();
        $pageController->showOrder($_POST['reference'], $order);
    } else {
        Route::redirect('/consulter-commande?error=1');
    }
});

Route::post('/order/cancel', function () {
    if (!empty($_POST['order_id'])) {
        $orderController = new OrderController();
        $id = intval(htmlspecialchars($_POST['order_id']));
        if (is_array($orderController->getOrderById($id))) {
            if ($orderController->cancel($id)) {
                echo json_encode(["success" => true, "message" => "Commande annulée avec succès!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Référence de commande non trouvée!"]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Une erreur est survenue!"]);
        exit;
    }
});

Route::post('/search/item', function () {
    $productController = new ProductController();
    $data = $productController->searchProductsLimited($_POST['search']);
    echo json_encode($data);
});

Route::getGroup(
    routesDefinition: function () {
        return [
            ['500', [PageController::class, 'handle500']],
            ['404', [PageController::class, 'handle404']],
            ['403', [PageController::class, 'handle403']],
            ['product-not-found', [PageController::class, 'handleProduct404']],

            ["actualites/{slug}", function ($slug) {
                ArticleMiddleware::checkArticleExists($slug);
            }],
            ["shop/{slug}", function ($slug) {

                ProductMiddleware::checkProductExists($slug);
            }],
            ["categories/{category}", function ($category) {
                $category = UrlHelper::decodeSlug($category);
                $categoryController = (new CategorieController);
                $result = $categoryController->isCategory($category);
                if ($result) {
                    $categoryController->updateVisites($result);
                    (new PageController)->category($category);
                }
            }],
            ["search/{search}", function ($search) {
                $$pageController = new PageController();
                $pageController->search(urldecode($search));
            }],
            ["robots.txt", function () {
                header('Content-Type: text/plain');
                echo (FileAndPathManager::readFile('file', 'robots.txt'));
            }],
            ["sitemap.xml", function () {
                header('Content-Type: text/xml');
                echo (FileAndPathManager::readFile('file', 'sitemap.xml'));
            }],
            ["checkout", function () {
                checkCARTMiddleware::checkCART();
                (new PageController)->checkout();
            }],
        ];
    },
    uriPrefix: '/'
);
Route::postGroup(
    routesDefinition: function () use ($sessionManager) {
        $visitor = new VisitorController($sessionManager);
        return [
            ["get-captcha", function () use ($visitor) {
                $visitor->captchaSet();
            }],
            ["regenerate-captcha", function () use ($visitor) {
                $visitor->captchaRenew();
            }],
            ["check-captcha", function () use ($visitor) {
                $visitor->sendMessage();
            }],
        ];
    },
    namePrefix: 'contact',
    uriPrefix: '/contact/'
);

Route::postGroup(
    routesDefinition: function () use ($sessionManager) {
        $visitor = new SubscriberController($sessionManager);
        return [
            ["get-captcha", function () use ($visitor) {
                $visitor->captchaSet();
            }],
            ["regenerate-captcha", function () use ($visitor) {
                $visitor->captchaRenew();
            }],
            ["check-captcha", function () use ($visitor) {
                $visitor->setDevis();
            }],
        ];
    },
    uriPrefix: '/devis/'
);

Route::post("/subscribe", function ()  use ($sessionManager) {
    (new SubscriberController($sessionManager))->subscribe();
});

