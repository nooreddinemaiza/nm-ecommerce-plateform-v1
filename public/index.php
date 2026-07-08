<?php


error_reporting(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


define("BASE_PATH", dirname(__DIR__));

use Helpers\Autoloader;
use Src\Controllers\PageController;
use Src\Helpers\FileAndPathManager;
use Src\Helpers\SessionManager;
use Src\Services\Route;

require_once(BASE_PATH . "/Src/Helpers/Autoloader.php");
require_once BASE_PATH . '/Src/vendor/autoload.php';
Autoloader::register();

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH . '/config');
$dotenv->safeLoad();

FileAndPathManager::init(BASE_PATH);
$sessionManager = new SessionManager;
FileAndPathManager::includeFile("config", "app.init.php", [
    "sessionManager" => $sessionManager,
    "dotenv" => $dotenv
]);
FileAndPathManager::includeFile("route", "web.php", ["sessionManager" => $sessionManager]);


FileAndPathManager::includeFile("route", "api.php", ["sessionManager" => $sessionManager]);
FileAndPathManager::includeFile("route", "auth.php", ["sessionManager" => $sessionManager]);
FileAndPathManager::includeFile("route", "admin.php", ["sessionManager" => $sessionManager]);
// Page pour les tests
Route::get('/test', function () {
    return (new PageController)->test();
});

Route::get('/', function () use ($sessionManager) {
    return (new PageController)->home($sessionManager);
});

Route::run($sessionManager);
