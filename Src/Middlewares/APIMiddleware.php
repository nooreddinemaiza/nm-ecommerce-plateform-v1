<?php

namespace Src\Middlewares;

use Src\Helpers\SessionManager;

class APIMiddleware
{
    private static $session;

    /**
     * Initialise la session et configure les paramètres de base
     */
    public static function init($sessionManager)
    {
            self::$session = $sessionManager;

        // Générer un nouveau token CSRF s'il n'existe pas
        if (!self::$session->has('csrf_token')) {
            self::$session->set('csrf_token', bin2hex(random_bytes(32)));
        }

        // Régénérer l'ID de session périodiquement pour plus de sécurité
        if (self::$session->has('token_regeneration_time')) {
            if (time() - self::$session->get('token_regeneration_time') > 1800) { // 30 minutes
                self::$session->regenerateSessionId();
                self::$session->set('token_regeneration_time', time());
            }
        } else {
            self::$session->set('token_regeneration_time', time());
        }
    }

    /**
     * Vérifie si la requête est une requête AJAX
     */
    private static function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Vérifie si le token CSRF est valide
     */
    private static function verifyCsrf()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

            if (!$token || $token !== self::$session->getCsrfToken()) {
                self::rejectRequest('CSRF token invalide.', 403);
            }
        }
    }

    /**
     * Limite le nombre de requêtes par IP
     */
    private static function rateLimit($maxRequests = 10, $interval = 60)
    {
        $ip = self::getClientIp();
        $time = time();

        $requestKey = 'rate_limit_' . md5($ip);

        if (!self::$session->has($requestKey)) {
            self::$session->set($requestKey, []);
        }

        $requests = self::$session->get($requestKey);
        $requests = array_filter($requests, fn($t) => $t > $time - $interval);
        $requests[] = $time;

        self::$session->set($requestKey, $requests);

        if (count($requests) > $maxRequests) {
            self::rejectRequest('Trop de requêtes, réessayez plus tard.', 429);
        }
    }

    /**
     * Vérifie le token reCAPTCHA
     */
    private static function checkRecaptcha($recaptchaToken)
    {
        $secretKey = getenv('RECAPTCHA_SECRET_KEY');
        if (empty($secretKey)) {
            error_log("ERREUR: Clé secrète reCAPTCHA non configurée");
            return false;
        }

        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaToken,
            'remoteip' => self::getClientIp()
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("ERREUR: Impossible de vérifier le reCAPTCHA");
            return false;
        }

        $responseData = json_decode($response);

        if (!$responseData->success || ($responseData->score ?? 1) < 0.5) {
            self::rejectRequest('Vérification de sécurité échouée.', 403);
            return false;
        }

        return true;
    }

    /**
     * Rejette la requête avec un message d'erreur
     */
    private static function rejectRequest($message, $statusCode = 400)
    {
        http_response_code($statusCode);

        if (self::isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo "<h1>Erreur</h1><p>$message</p>";
        }

        exit;
    }

    /**
     * Récupère l'adresse IP du client
     */
    private static function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return $ip;
    }

    /**
     * Vérifie si l'utilisateur est authentifié
     */
    public static function requireAuth()
    {
        self::init(self::$session);

        if (!self::$session->isAuthenticated()) {
            if (self::isAjaxRequest()) {
                self::rejectRequest('Authentification requise.', 401);
            } else {
                header('Location: /login');
                exit;
            }
        }
    }

    /**
     * Vérifie si l'utilisateur a le rôle requis
     */
    public static function requireRole($role)
    {
        self::init(self::$session);
        self::requireAuth();

        $userRole = self::$session->get('user_role');

        if ($userRole !== $role && $userRole !== 'admin') {
            self::rejectRequest('Accès non autorisé.', 403);
        }
    }

    /**
     * Applique les middlewares de sécurité pour les requêtes API
     */
    public static function apiSecurity($requireAuth = true, $validateRecaptcha = false)
    {
        self::init(self::$session );

        // Vérification AJAX pour les API
        if (!self::isAjaxRequest()) {
            self::rejectRequest('Méthode d\'accès non autorisée.', 405);
        }

        // Vérification CSRF
        self::verifyCsrf();

        // Limite de taux
        self::rateLimit();

        // Vérification reCAPTCHA si nécessaire
        if ($validateRecaptcha && isset($_POST['recaptcha_token'])) {
            self::checkRecaptcha($_POST['recaptcha_token']);
        }

        // Vérification d'authentification si nécessaire
        if ($requireAuth) {
            self::requireAuth();
        }

        // Vérification de l'expiration de session
        if (self::$session->checkSessionExpiration()) {
            self::$session->destroy();
            self::rejectRequest('Session expirée. Veuillez vous reconnecter.', 401);
        }

        // Bloquer les robots
        if (self::$session->isBlockedUserAgent()) {
            self::rejectRequest('Accès non autorisé.', 403);
        }
    }

    /**
     * Applique les middlewares de base
     */
    public static function apply()
    {
        self::init(self::$session );

        // Protection contre les attaques XSS
        header('X-XSS-Protection: 1; mode=block');

        // Protection contre le clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Protection contre le sniffing MIME
        header('X-Content-Type-Options: nosniff');

        // Politique de sécurité de contenu
        header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';");

        // Protection contre les robots
        if (self::$session->isBlockedUserAgent()) {
            self::rejectRequest('Accès non autorisé.', 403);
        }

        // Vérification de l'expiration de session pour les utilisateurs authentifiés
        if (self::$session->isAuthenticated() && self::$session->checkSessionExpiration()) {
            self::$session->destroy();
            header('Location: /login?expired=1');
            exit;
        }
    }

    /**
     * Renvoie le token CSRF courant
     */
    public static function getCsrfToken()
    {
        self::init(self::$session );
        return self::$session->getCsrfToken();
    }

    /**
     * Génère un champ de formulaire CSRF
     */
    public static function csrfField()
    {
        self::init(self::$session );
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::$session->getCsrfToken()) . '">';
    }
}
