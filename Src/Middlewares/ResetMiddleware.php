<?php

namespace Src\Middlewares;

use Src\Helpers\SessionManager;
use Src\Controllers\UserController;

class ResetMiddleware
{
    private $sessionManager;
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
    public function checkResetToken()
    {
        if (!isset($_GET['token'])) {
            return false;
        }
        $token = htmlspecialchars(trim($_GET['token']));
        $user = new UserController($this->sessionManager);
        $user = $user->getUserByResetToken($token);
        if (!$user) {
            return false;
        }
        return true;
    }
}
