<?php

use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Services\Route;
use Src\Database\Migration;
use Src\Middlewares\APIMiddleware;
use Src\Controllers\PageController;
use Src\Controllers\UserController;
use Src\Helpers\FileAndPathManager;

define('WEB_NAME', Config::get("WEB_NAME"));
define('WEB_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
define('WEB_LOGO_SVG', FileAndPathManager::getPath("image", "logo.svg"));
define('WEB_LOGO_PNG', FileAndPathManager::getPath("image", "logo.png"));
define('WEB_LOGO_URL', '/assets/images/logo.svg');
define('WEB_LOGO_PNG_URL', '/assets/images/logo.png');

$data = $data ?? [];

$sessionManager = $data["sessionManager"];

// Initialisation du site
if (!Config::has('APP_SETUP') || Config::get('APP_SETUP') != 1) {
    try {
        $configDir = FileAndPathManager::getDirectoryPath("config");
        $data["dotenv"]->safeLoad();

        // Lancement des migrations et configuration
        $migration = new Migration();
        if ($migration->runMigrations()) {
            $userController = new UserController($sessionManager);

            if (
                $userController->setAdminAccount()
                && $migration->preConfig()
                && Route::generateRobotsTxtFromRoutes(
                    WEB_URL,
                    ['/login', '/logout', '/dashboard', '/search/*']
                )
                && Route::generateSitemapXML(
                    WEB_URL,
                    [],
                    [
                        '/login',
                        '/dashboard',
                        '/search/*',
                        '/sitemap.xml',
                        '/robots.txt'
                    ],
                    [
                        '/home' => '1.0',
                        '/shop' => '0.8',
                        '/categories' => '0.8',
                        '/about' => '0.5',
                        '/contact' => '0.5',
                    ],
                    [
                        '/home' => 'weekly',
                        '/shop' => 'weekly',
                        '/about' => 'monthly',
                        '/contact' => 'monthly',
                    ]
                )
            ) {
                FileAndPathManager::addLineToFile("config", ".env", "APP_SETUP=1");
                AppLog::info("Installation du site terminée avec succès.");
            }
        }
    } catch (Exception $e) {
        echo "Erreur: " . $e->getMessage();
        AppLog::critical("Erreur lors de la configuration du site : " . $e->getMessage());
    }
}

// Post-setup
APIMiddleware::init($sessionManager);
AppLog::cleanExpiredLogs();

if ($sessionManager->isAuthenticated()) {
    unset($_SESSION['attempt']);
}

if (Config::get("MAINTENANCE") === 'true') {
    Route::get('/maintenance', fn() => (new PageController)->maintenance());

    $notInMaintenance = [
        '/maintenance',
        '/login',
        '/dashboard',
        '/contact',
        '/contact/get-captcha',
        '/contact/regenerate-captcha',
        '/contact/check-captcha',
    ];
    Route::setGlobalRedirect('/maintenance', 302, $notInMaintenance);
}


if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    Route::disableGlobalRedirect();
}
