<?php

use Src\Services\Route;
use Src\Controllers\PageController;
use Src\Controllers\UserController;

// Définition des routes
Route::getGroup(function () {
    return [
        ['login', [PageController::class, 'login'], 'login'],
        ['logout', [UserController::class, 'logout'], 'logout'],
        ['forgot-password', [PageController::class, 'forgotPassword'], 'forgot-password'],
    ];
}, false, '', '/');

Route::postGroup(function () {
    return [
        ['login', [UserController::class, 'login'], 'login'],
        ['password-reset', [UserController::class, 'passwordReset'], ''],
        ['set-new-password', [UserController::class, 'setNewPassword'], '']
    ];
}, false, '', '/');

Route::post('/session-expired', [UserController::class, 'handleExpiration'], true);
