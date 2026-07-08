<?php

namespace Src\Helpers;

use Src\Services\Route;
use Src\Controllers\PageController;
use Src\Models\User;

class SessionManager
{
    public function __construct()
    {
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.gc_maxlifetime', 3600);

        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['initialized'])) {
            session_regenerate_id(true);
            $_SESSION['initialized'] = true;
        }

        $ip_address = $this->getClientIp();
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $ip_address;
        }
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if ($_SESSION['ip_address'] !== $ip_address || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            $this->destroy();
            return false;
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 900) {
            $this->destroy();
            return false;
        }

        $_SESSION['last_activity'] = time();

        // Initialisation du suivi des visiteurs
        // $this->trackVisitor();
        // $this->trackPageVisit();
    }

    private function getClientIp(): string
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip_address;
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }

    public function regenerateSessionId()
    {
        session_regenerate_id(true);
    }

    public function isAuthenticated()
    {
        return isset($_SESSION['user_id']);
    }

    public function authenticate($user_id, $user_role = null, $full_name = null)
    {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_role'] = $user_role ?? 'user';
        $_SESSION['full_name'] = $full_name ?? 'Anonyme';
        Route::redirect('/dashboard');
    }
    public function inactif()
    {
        if (!$this->isAuthenticated()) {
            $this->logout();
            exit;
        }
        $result = (new User($this))->inactif($_SESSION['user_id']);
        if ($result && $result['status'] == "inactive") {
            $this->logout();
            (new PageController())->handleInactiveAccount();
            exit;
        }
    }
    public function logout()
    {
        $this->destroy();
    }

    public function getUserId()
    {
        return $this->get('user_id');
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }
    /**
     * Vérifie si une clé existe dans la session
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    public function getCsrfToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
    /**
     * Supprime une clé spécifique de la session
     */
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public function checkSessionExpiration(): bool
    {
        $expirationTime = $_SESSION['last_activity'] ?? 0;
        return (time() - $expirationTime) >= 900;
    }

    public function getExpirationTimestamp(): int
    {
        return $_SESSION['last_activity'] + 900;
    }
    /**
     * Bloquer certains User-Agents (bots)
     */
    public function isBlockedUserAgent(): bool
    {
        $blocked_agents = ['bot', 'crawl', 'spider', 'scraper'];
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        foreach ($blocked_agents as $bot) {
            if (strpos($user_agent, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Définir le token d'accès aux fichiers protégés
     */
    public function setFileAccessToken(string $token, int $expiry): void
    {
        $_SESSION['file_access_token'] = $token;
        $_SESSION['file_access_token_expiry'] = $expiry;
    }

    /**
     * Récupérer le token d'accès aux fichiers protégés
     */
    public function getFileAccessToken(): ?string
    {
        return $_SESSION['file_access_token'] ?? null;
    }

    /**
     * Récupérer la date d'expiration du token d'accès aux fichiers protégés
     */
    public function getFileAccessTokenExpiry(): ?int
    {
        return $_SESSION['file_access_token_expiry'] ?? null;
    }

    /**
     * Valider le token d'accès aux fichiers protégés
     */
    public function validateFileAccessToken(string $token): bool
    {
        return isset($_SESSION['file_access_token']) &&
            $_SESSION['file_access_token'] === $token &&
            time() <= ($_SESSION['file_access_token_expiry'] ?? 0);
    }


    /**
     * Afficher toutes les variables de session
     */
    public function debugSession()
    {
        echo "<pre>";
        print_r($_SESSION); // Retourne toutes les variables de session
        echo "</pre>";
    }

    /**
     * Suivi des visiteurs anonymes
     */
    private function trackVisitor()
    {
        if (!$this->isAuthenticated() && !isset($_SESSION['visitor_id'])) {
            $_SESSION['visitor_id'] = bin2hex(random_bytes(16)); // Génère un identifiant unique
            $_SESSION['visitor_start_time'] = time();
            $_SESSION['visitor_ip'] = $this->getClientIp();
            $_SESSION['visited_pages'] = []; // Initialise la liste des pages visitées
        }
    }

    /**
     * Enregistrer les pages visitées par le visiteur
     */
    private function trackPageVisit()
    {
        if (!$this->isAuthenticated()) {
            $current_page = $_SERVER['REQUEST_URI'] ?? 'unknown';

            // Extraire la catégorie et le titre du produit
            $parts = explode('/', trim($current_page, '/'));
            $category = isset($parts[0]) ? $parts[0] : 'unknown';
            $title = isset($parts[1]) ? $parts[1] : 'unknown';

            if (!isset($_SESSION['visited_pages'])) {
                $_SESSION['visited_pages'] = [];
            }

            $found = false;

            foreach ($_SESSION['visited_pages'] as &$entry) {
                if ($entry['page'] === $category) {
                    // Incrémenter le nombre de visites
                    $entry['number']++;

                    // Ajouter le titre s'il n'existe pas déjà
                    if (!in_array($title, $entry['titles'])) {
                        $entry['titles'][] = $title;
                    }

                    // Mettre à jour le timestamp
                    $entry['timestamp'] = time();

                    $found = true;
                    break;
                }
            }

            // Si la catégorie n'est pas encore enregistrée, on l'ajoute
            if (!$found) {
                $_SESSION['visited_pages'][] = [
                    'page' => $category,
                    'titles' => [$title],
                    'number' => 1,
                    'timestamp' => time()
                ];
            }
        }
    }

    /**
     * Vérifie si l'utilisateur est un visiteur (non connecté)
     */
    public function isVisitor(): bool
    {
        return !$this->isAuthenticated();
    }

    /**
     * Récupérer l'identifiant du visiteur
     */
    public function getVisitorId(): ?string
    {
        return $_SESSION['visitor_id'] ?? null;
    }

    /**
     * Récupérer la liste des pages visitées par le visiteur
     */
    public function getVisitedPages(): array
    {
        return $_SESSION['visited_pages'] ?? [];
    }
}
