<?php

namespace Src\Services;

use Src\Helpers\AppLog;
use Src\Helpers\SessionManager;
use Src\Controllers\PageController;
use Src\Helpers\FileAndPathManager;
use Exception;

class Route
{
    private static array $routes = [];
    private static array $protectedRoutes = [];
    private static array $globalMiddlewares = [];
    private static array $namedRoutes = [];
    private static array $disabledRoutes = [];
    private static array $redirectedRoutes = [];
    private static bool $globalRouteBlock = false;
    private static array $globalRedirectSettings = [
        'enabled' => false,
        'target' => '/',
        'status' => 302,
        'excluded' => []
    ];

    // Simplified method for adding routes
    private static function registerRoute(string $method, string $uri, $callback, bool $protected = false, ?string $name = null): void
    {
        $normalizedUri = self::normalizeUri($uri);
        self::$routes[$method][$normalizedUri] = $callback;

        if ($protected) {
            self::$protectedRoutes[$method][$normalizedUri] = true;
        }

        if ($name) {
            self::$namedRoutes[$name] = [$method, $normalizedUri];
        }
    }

    // HTTP method-specific route registrations using magic method
    public static function __callStatic(string $method, array $args)
    {
        $allowedMethods = ['get', 'post'];

        if (in_array($method, $allowedMethods)) {
            [$uri, $callback, $protected, $name] = array_pad($args, 4, null);
            self::registerRoute(strtoupper($method), $uri, $callback, $protected ?? false, $name);
            return;
        }

        AppLog::critical("Invalid HTTP method: {$method}");
        self::redirect();
    }

    /**
     * Enregistre un groupe de routes avec la même méthode HTTP et les mêmes paramètres
     * 
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param callable $routesDefinition Fonction qui définit les routes du groupe
     * @param bool $protected Si les routes sont protégées
     * @param string|null $namePrefix Préfixe pour les noms de routes
     * @param string $uriPrefix Préfixe d'URI pour toutes les routes du groupe
     * @return void
     */
    public static function group(string $method, callable $routesDefinition, bool $protected = false, ?string $namePrefix = null, string $uriPrefix = ''): void
    {
        $routes = $routesDefinition();

        if (!is_array($routes)) {
            AppLog::error("Group routes definition must return an array");
            return;
        }

        foreach ($routes as $route) {
            if (!is_array($route) || count($route) < 2) {
                AppLog::warning("Invalid route definition in group");
                continue;
            }

            [$uri, $callback] = $route;
            $name = $route[2] ?? null;

            // Appliquer les préfixes
            $fullUri = $uriPrefix . $uri;
            $fullName = $name ? ($namePrefix ? "{$namePrefix}.{$name}" : $name) : null;

            // Enregistrer la route
            self::registerRoute(strtoupper($method), $fullUri, $callback, $protected, $fullName);
        }
    }

    /**
     * Enregistre un groupe de routes GET
     * 
     * @param callable $routesDefinition Fonction qui définit les routes du groupe
     * @param bool $protected Si les routes sont protégées
     * @param string|null $namePrefix Préfixe pour les noms de routes
     * @param string $uriPrefix Préfixe d'URI pour toutes les routes du groupe
     * @return void
     */
    public static function getGroup(callable $routesDefinition, bool $protected = false, ?string $namePrefix = null, string $uriPrefix = ''): void
    {
        self::group('GET', $routesDefinition, $protected, $namePrefix, $uriPrefix);
    }

    /**
     * Enregistre un groupe de routes POST
     * 
     * @param callable $routesDefinition Fonction qui définit les routes du groupe
     * @param bool $protected Si les routes sont protégées
     * @param string|null $namePrefix Préfixe pour les noms de routes
     * @param string $uriPrefix Préfixe d'URI pour toutes les routes du groupe
     * @return void
     */
    public static function postGroup(callable $routesDefinition, bool $protected = false, ?string $namePrefix = null, string $uriPrefix = ''): void
    {
        self::group('POST', $routesDefinition, $protected, $namePrefix, $uriPrefix);
    }
    /**
     * Tableau pour stocker les middlewares par préfixe d'URI
     */
    private static array $prefixMiddlewares = [];

    /**
     * Tableau pour stocker les logiques à exécuter par préfixe d'URI
     */
    private static array $prefixLogics = [];

    /**
     * Ajoute un middleware à exécuter pour les routes correspondant à un préfixe
     * 
     * @param string $uriPrefix Préfixe URI pour lequel appliquer le middleware
     * @param callable $middleware Fonction middleware à exécuter
     * @return void
     */
    public static function addPrefixMiddleware(string $uriPrefix, callable $middleware): void
    {
        if (!isset(self::$prefixMiddlewares[$uriPrefix])) {
            self::$prefixMiddlewares[$uriPrefix] = [];
        }

        self::$prefixMiddlewares[$uriPrefix][] = $middleware;
    }

    /**
     * Ajoute une logique à exécuter pour les routes correspondant à un préfixe 
     * (par ex. pour initialiser des variables de session)
     * 
     * @param string $uriPrefix Préfixe URI pour lequel appliquer la logique
     * @param callable $logic Fonction à exécuter quand une route avec ce préfixe est appelée
     * @return void
     */
    public static function addPrefixLogic(string $uriPrefix, callable $logic): void
    {
        if (!isset(self::$prefixLogics[$uriPrefix])) {
            self::$prefixLogics[$uriPrefix] = [];
        }

        self::$prefixLogics[$uriPrefix][] = $logic;
    }


    // Fonction de redirection
    public static function redirect(string $url = '/'): void
    {
        header("Location: $url");
        exit;
    }
    // À ajouter dans la classe Route
    public static function setGlobalRedirect(string $target, int $status = 302, array $excluded = []): void
    {
        self::$globalRedirectSettings = [
            'enabled' => true,
            'target' => $target,
            'status' => $status,
            'excluded' => $excluded
        ];
    }

    public static function disableGlobalRedirect(): void
    {
        self::$globalRedirectSettings['enabled'] = false;
    }
    // Gère les erreurs
    private static function handleError(int $statusCode = 500): void
    {
        $pageController = new PageController();
        switch ($statusCode) {
            case 403:
                http_response_code(403);
                $pageController->handle403();
                break;

            case 404:
                http_response_code(404);
                $pageController->handle404();
                break;

            case 500:
            default:
                http_response_code(500);
                $pageController->handle500();
                break;
        }

        exit;
    }
    // Normalise l'URI en supprimant le slash final
    private static function normalizeUri(string $uri): string
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '';
        return rtrim($uri, '/');
    }
    // Enhanced route method with better error handling and parameter resolution
    public static function route(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new Exception("Route '{$name}' not defined.");
        }

        [$method, $uri] = self::$namedRoutes[$name];
        $resolvedUri = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)(?::[^}]+)?\}/',
            function ($matches) use (&$params) {
                $key = $matches[1];
                if (!isset($params[$key])) {
                    throw new Exception("Missing parameter: {$key}");
                }
                return $params[$key];
            },
            $uri
        );

        if (preg_match('/\{[^}]+\}/', $resolvedUri)) {
            throw new Exception("Not all route parameters were replaced.");
        }

        return $resolvedUri;
    }

    // More robust error handling for route not found
    private static function handleRouteNotFound(string $uri, string $method): void
    {
        AppLog::warning("Route not found: {$method} {$uri}");
        self::handleError(404);
    }

    // Improved middleware execution with error handling
    private static function executeMiddlewares(array $middlewares): bool
    {
        foreach ($middlewares as $middleware) {
            try {
                if ($middleware() === false) {
                    self::handleError(403);
                    return false;
                }
            } catch (Exception $e) {
                AppLog::error("Middleware error: " . $e->getMessage());
                self::handleError(500);
                return false;
            }
        }
        return true;
    }

    // More flexible route matching with type hints and optional parameters
    private static function matchRoute(string $route, string $uri): ?array
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)(?::(int|string|float))?\}/',
            function ($matches) {
                $type = $matches[2] ?? 'string';
                return match ($type) {
                    'int' => '(\d+)',
                    'float' => '(\d+\.\d+)',
                    default => '([^/]+)'
                };
            },
            $route
        );

        if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return null;
    }

    // Comprehensive route running method
    public static function run(SessionManager $sessionManager): void
    {
        if (php_sapi_name() === 'cli') {
            AppLog::warning("Script executed from command line.");
            return;
        }

        $uri = self::normalizeUri($_SERVER['REQUEST_URI']);
        $method = $_SERVER['REQUEST_METHOD'];

        // Global route checks
        if (!in_array($uri, self::$globalRedirectSettings['excluded'])) {
            if (self::$globalRouteBlock) {
                self::handleError(403);
            }

            if (self::$globalRedirectSettings['enabled']) {
                header("Location: " . self::$globalRedirectSettings['target'], true, self::$globalRedirectSettings['status']);
                exit;
            }
        }

        // Execute global middlewares
        if (!self::executeMiddlewares(self::$globalMiddlewares)) {
            return;
        }

        // Exécuter les middlewares basés sur les préfixes d'URI
        foreach (self::$prefixMiddlewares as $prefix => $middlewares) {
            if (strpos($uri, $prefix) === 0) {
                if (!self::executeMiddlewares($middlewares)) {
                    return;
                }
            }
        }

        // Exécuter les logiques basées sur les préfixes d'URI
        foreach (self::$prefixLogics as $prefix => $logics) {
            if (strpos($uri, $prefix) === 0) {
                foreach ($logics as $logic) {
                    try {
                        $logic($uri, $method, $sessionManager);
                    } catch (Exception $e) {
                        AppLog::error("Prefix logic error: " . $e->getMessage());
                    }
                }
            }
        }

        // Route matching
        $routes = self::$routes[$method] ?? [];
        foreach ($routes as $route => $callback) {
            $matches = self::matchRoute($route, $uri);
            if ($matches === null) {
                continue;
            }

            // Check for disabled routes
            if (isset(self::$disabledRoutes[$method][$route])) {
                self::handleError(410);
                return;
            }

            // Check for redirected routes
            if (isset(self::$redirectedRoutes[$method][$route])) {
                $target = self::resolveRedirectTarget(self::$redirectedRoutes[$method][$route], $matches);
                self::redirect($target);
                return;
            }

            // Vérification des routes protégées par un middleware
            if (isset(self::$protectedRoutes[$method][$route])) {
                \Src\Middlewares\AuthMiddleware::handle(true, $sessionManager->getUserId(), function () {
                    self::handleError(403);
                });
            }

            // Execute route callback
            if (is_array($callback)) {
                self::handleControllerCallback($callback, $matches, $sessionManager);
            } elseif (is_callable($callback)) {
                $callback(...$matches);
            } else {
                self::handleError(500);
            }

            return;
        }

        // No route found
        self::handleRouteNotFound($uri, $method);
    }
    /**
     * Tableau associatif pour stocker les routes par domaine
     */
    private static array $domainRoutes = [];

    /**
     * Enregistre une route pour un domaine spécifique
     * 
     * @param string $domain Nom de domaine (ex: "admin.example.com")
     * @param string $method Méthode HTTP
     * @param string $uri URI de la route
     * @param mixed $callback Fonction de rappel ou tableau [Controller, method]
     * @param bool $protected Si la route est protégée
     * @param string|null $name Nom optionnel de la route
     * @return void
     */
    public static function domain(string $domain, string $method, string $uri, $callback, bool $protected = false, ?string $name = null): void
    {
        $normalizedDomain = strtolower(trim($domain));
        $normalizedMethod = strtoupper($method);
        $normalizedUri = self::normalizeUri($uri);

        if (!isset(self::$domainRoutes[$normalizedDomain])) {
            self::$domainRoutes[$normalizedDomain] = [];
        }

        if (!isset(self::$domainRoutes[$normalizedDomain][$normalizedMethod])) {
            self::$domainRoutes[$normalizedDomain][$normalizedMethod] = [];
        }

        self::$domainRoutes[$normalizedDomain][$normalizedMethod][$normalizedUri] = $callback;

        if ($protected) {
            if (!isset(self::$protectedRoutes[$normalizedDomain])) {
                self::$protectedRoutes[$normalizedDomain] = [];
            }

            if (!isset(self::$protectedRoutes[$normalizedDomain][$normalizedMethod])) {
                self::$protectedRoutes[$normalizedDomain][$normalizedMethod] = [];
            }

            self::$protectedRoutes[$normalizedDomain][$normalizedMethod][$normalizedUri] = true;
        }

        if ($name) {
            // Format: domain::name
            self::$namedRoutes["{$normalizedDomain}::{$name}"] = [$normalizedDomain, $normalizedMethod, $normalizedUri];
        }
    }

    /**
     * Version exécutable pour les routes par domaine
     * À utiliser à la place de run() si vous avez des routes spécifiques par domaine
     */
    public static function runWithDomains(SessionManager $sessionManager): void
    {
        if (php_sapi_name() === 'cli') {
            AppLog::warning("Script executed from command line.");
            return;
        }

        $uri = self::normalizeUri($_SERVER['REQUEST_URI']);
        $method = $_SERVER['REQUEST_METHOD'];
        $host = strtolower($_SERVER['HTTP_HOST']);

        // Vérifier d'abord les routes par domaine
        if (isset(self::$domainRoutes[$host])) {
            $domainRoutes = self::$domainRoutes[$host][$method] ?? [];

            foreach ($domainRoutes as $route => $callback) {
                $matches = self::matchRoute($route, $uri);
                if ($matches !== null) {
                    // Vérifier si la route est protégée
                    if (isset(self::$protectedRoutes[$host][$method][$route])) {
                        \Src\Middlewares\AuthMiddleware::handle(true, $sessionManager->getUserId(), function () {
                            self::handleError(403);
                        });
                    }

                    // Exécuter le callback
                    if (is_array($callback)) {
                        self::handleControllerCallback($callback, $matches, $sessionManager);
                    } elseif (is_callable($callback)) {
                        $callback(...$matches);
                    } else {
                        self::handleError(500);
                    }

                    return;
                }
            }
        }

        // Si aucune route par domaine ne correspond, utiliser les routes normales
        self::run($sessionManager);
    }

    /* --------- MÉTHODES UTILITAIRES SUPPLÉMENTAIRES --------- */

    /**
     * Retourne toutes les routes enregistrées (utile pour le débogage)
     * 
     * @return array
     */
    public static function getAllRoutes(): array
    {
        $result = [
            'standard' => self::$routes,
            'protected' => self::$protectedRoutes,
            'named' => self::$namedRoutes,
            'disabled' => self::$disabledRoutes,
            'redirected' => self::$redirectedRoutes,
            'domain' => self::$domainRoutes ?? []
        ];

        return $result;
    }

    /**
     * Vérifie si une route existe
     * 
     * @param string $name Nom de la route
     * @return bool
     */
    public static function routeExists(string $name): bool
    {
        return isset(self::$namedRoutes[$name]);
    }

    /**
     * Enregistre un middleware qui sera appliqué uniquement aux routes du tableau spécifié
     * 
     * @param array $routes Tableau de routes auxquelles appliquer le middleware
     * @param callable $middleware Fonction middleware à exécuter
     * @return void
     */
    public static function applyMiddlewareToRoutes(array $routes, callable $middleware): void
    {
        foreach ($routes as $route) {
            // Support pour différents formats de route: string, [method, uri], ou [method, uri, name]
            if (is_string($route)) {
                // Route nommée
                if (isset(self::$namedRoutes[$route])) {
                    [$method, $uri] = self::$namedRoutes[$route];
                    if (!isset(self::$routeMiddlewares[$method][$uri])) {
                        self::$routeMiddlewares[$method][$uri] = [];
                    }
                    self::$routeMiddlewares[$method][$uri][] = $middleware;
                }
            } elseif (is_array($route) && count($route) >= 2) {
                // Format [method, uri]
                $method = strtoupper($route[0]);
                $uri = self::normalizeUri($route[1]);

                if (!isset(self::$routeMiddlewares[$method][$uri])) {
                    self::$routeMiddlewares[$method][$uri] = [];
                }
                self::$routeMiddlewares[$method][$uri][] = $middleware;
            }
        }
    }

    /**
     * Cache pour les routes compilées
     */
    private static ?array $compiledRoutes = null;

    /**
     * Compile toutes les routes pour une utilisation plus efficace
     * 
     * @return void
     */
    public static function compileRoutes(): void
    {
        if (self::$compiledRoutes !== null) {
            return; // Déjà compilé
        }

        self::$compiledRoutes = [];

        foreach (self::$routes as $method => $routes) {
            self::$compiledRoutes[$method] = [];

            foreach ($routes as $route => $callback) {
                // Convertir les paramètres de route en expressions régulières
                $compiledRoute = [
                    'pattern' => self::compileRoutePattern($route),
                    'callback' => $callback,
                    'protected' => isset(self::$protectedRoutes[$method][$route]),
                    'disabled' => isset(self::$disabledRoutes[$method][$route]),
                    'redirected' => self::$redirectedRoutes[$method][$route] ?? null
                ];

                self::$compiledRoutes[$method][] = $compiledRoute;
            }
        }
    }

    /**
     * Compile un pattern de route en expression régulière
     * 
     * @param string $route
     * @return string
     */
    private static function compileRoutePattern(string $route): string
    {
        $pattern = preg_replace_callback(
            '/\{([a-zA-Z0-9_]+)(?::(int|string|float|uuid|slug))?\}/',
            function ($matches) {
                $type = $matches[2] ?? 'string';
                return match ($type) {
                    'int' => '(\d+)',
                    'float' => '(\d+(?:\.\d+)?)',
                    'uuid' => '([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})',
                    'slug' => '([a-z0-9]+(?:-[a-z0-9]+)*)',
                    default => '([^/]+)'
                };
            },
            $route
        );

        return "#^{$pattern}$#";
    }

    /**
     * Tableau pour stocker les middlewares spécifiques à une route
     */
    private static array $routeMiddlewares = [];
    /**
     * Résout les paramètres dynamiques dans les redirections
     */
    private static function resolveRedirectTarget(string $target, array $matches): string
    {
        $params = array_slice($matches, 1);

        foreach ($params as $index => $value) {
            $target = str_replace('$' . ($index + 1), $value, $target);
        }

        return $target;
    }
    // Gère le callback du contrôleur
    private static function handleControllerCallback(array $callback, array $matches, SessionManager $sessionManager): void
    {
        [$controller, $action] = $callback;

        if (!class_exists($controller) || !method_exists($controller, $action)) {
            AppLog::error("Controller or action not found: {$controller}::{$action}");
            self::handleError(500);
        }

        $controllerInstance = new $controller($sessionManager);
        $controllerInstance->$action(...$matches);
    }
    /**
     * Génère un fichier robots.txt à partir des routes de l'application
     * 
     * @param string $baseUrl URL de base du site
     * @param array $disallowPaths Chemins à interdire aux robots
     * @param array $allowPaths Chemins à autoriser explicitement
     * @param string $sitemap Chemin vers le sitemap (optionnel)
     * @return bool Vrai si le fichier a été généré avec succès
     */
    public static function generateRobotsTxt(
        string $baseUrl,
        array $disallowPaths = [],
        array $allowPaths = [],
        ?string $sitemap = null
    ): bool {
        try {
            // Vérification de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            // Début du contenu du robots.txt
            $content = "User-agent: *\n";

            // Ajout des chemins à interdire aux robots
            foreach ($disallowPaths as $path) {
                $content .= "Disallow: " . self::normalizeUri($path) . "\n";
            }

            // Ajout des chemins à autoriser explicitement
            foreach ($allowPaths as $path) {
                $content .= "Allow: " . self::normalizeUri($path) . "\n";
            }

            // Interdiction automatique des routes avec méthodes non-GET
            foreach (['POST', 'PUT', 'DELETE'] as $method) {
                if (isset(self::$routes[$method])) {
                    foreach (array_keys(self::$routes[$method]) as $route) {
                        // Exclure les routes avec des paramètres dynamiques
                        if (!preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                            $content .= "Disallow: $route\n";
                        }
                    }
                }
            }

            // Interdiction automatique des routes protégées
            foreach (self::$protectedRoutes as $method => $routes) {
                foreach (array_keys($routes) as $route) {
                    // Exclure les routes avec des paramètres dynamiques
                    if (!preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                        $content .= "Disallow: $route\n";
                    }
                }
            }

            // Ajout du sitemap si fourni
            if ($sitemap) {
                $sitemapUrl = rtrim($baseUrl, '/') . '/' . ltrim($sitemap, '/');
                $content .= "\nSitemap: $sitemapUrl\n";
            }

            // Ajout de la directive Crawl-delay pour éviter la surcharge du serveur
            $content .= "Crawl-delay: 1\n";

            // Ajout de Host pour indiquer le domaine principal (important pour le SEO)
            $host = parse_url($baseUrl, PHP_URL_HOST);
            $content .= "Host: $host\n";

            // Écriture du fichier
            FileAndPathManager::writeFile('file', 'robots.txt', $content);
            AppLog::info("Fichier robots.txt généré avec succès");
            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la génération du fichier robots.txt: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Gestion avancée des routes (désactivation/redirection)
     * 
     * @param array $routes Liste des routes à gérer
     * @param string $action 'disable' ou 'redirect'
     * @param string|null $target Route cible pour les redirections
     * @param array $methods Méthodes HTTP concernées
     */
    public static function manageRoutes(
        array $routes,
        string $action,
        ?string $target = null,
        array $methods = ['GET', 'POST', 'PUT', 'DELETE']
    ): void {
        foreach ($methods as $method) {
            foreach ($routes as $route) {
                $normalizedRoute = self::normalizeUri($route);

                if ($action === 'disable') {
                    self::$disabledRoutes[$method][$normalizedRoute] = true;
                } elseif ($action === 'redirect' && $target !== null) {
                    self::$redirectedRoutes[$method][$normalizedRoute] = self::normalizeUri($target);
                }
            }
        }
    }

    /**
     * Génère un fichier robots.txt à partir des routes déjà enregistrées dans l'application
     * 
     * @param string $baseUrl URL de base du site
     * @param array $additionalDisallow Chemins supplémentaires à interdire aux robots
     * @param array $additionalAllow Chemins supplémentaires à autoriser explicitement
     * @param string $sitemap Chemin vers le sitemap (optionnel)
     * @return bool Vrai si le fichier a été généré avec succès
     */
    public static function generateRobotsTxtFromRoutes(
        string $baseUrl,
        array $additionalDisallow = [],
        array $additionalAllow = [],
        ?string $sitemap = null
    ): bool {
        try {
            // Vérification de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            // Récupération de toutes les routes GET publiques
            $publicRoutes = [];
            if (isset(self::$routes['GET'])) {
                foreach (self::$routes['GET'] as $route => $callback) {
                    // Vérifier si la route n'est pas protégée
                    if (!isset(self::$protectedRoutes['GET'][$route])) {
                        // Exclure les routes avec des paramètres dynamiques
                        if (!preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                            $publicRoutes[] = $route;
                        }
                    }
                }
            }

            // Récupération de toutes les routes protégées (tous types)
            $protectedRoutes = [];
            foreach (self::$protectedRoutes as $method => $routes) {
                foreach (array_keys($routes) as $route) {
                    // Exclure les routes avec des paramètres dynamiques
                    if (!preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                        $protectedRoutes[] = $route;
                    }
                }
            }

            // Récupération de toutes les routes non-GET
            $nonGetRoutes = [];
            foreach (['POST', 'PUT', 'DELETE'] as $method) {
                if (isset(self::$routes[$method])) {
                    foreach (array_keys(self::$routes[$method]) as $route) {
                        // Exclure les routes avec des paramètres dynamiques
                        if (!preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                            $nonGetRoutes[] = $route;
                        }
                    }
                }
            }

            // Fusion et dédoublonnage des routes à interdire
            $disallowPaths = array_unique(array_merge($protectedRoutes, $nonGetRoutes, $additionalDisallow));

            // Fusion et dédoublonnage des routes à autoriser
            $allowPaths = array_unique(array_merge($publicRoutes, $additionalAllow));

            // Suppression des doublons (si une route est à la fois dans allow et disallow, on la garde dans disallow)
            $allowPaths = array_diff($allowPaths, $disallowPaths);

            // Début du contenu du robots.txt
            $content = "User-agent: *\n";

            // Ajout des chemins à interdire aux robots
            foreach ($disallowPaths as $path) {
                $content .= "Disallow: " . self::normalizeUri($path) . "\n";
            }

            // Ajout des chemins à autoriser explicitement
            foreach ($allowPaths as $path) {
                $content .= "Allow: " . self::normalizeUri($path) . "\n";
            }

            // Ajout du sitemap si fourni
            if ($sitemap) {
                $sitemapUrl = rtrim($baseUrl, '/') . '/' . ltrim($sitemap, '/');
                $content .= "\nSitemap: $sitemapUrl\n";
            }

            // Ajout de la directive Crawl-delay pour éviter la surcharge du serveur
            $content .= "Crawl-delay: 1\n";

            // Ajout de Host pour indiquer le domaine principal (important pour le SEO)
            $host = parse_url($baseUrl, PHP_URL_HOST);
            $content .= "Host: $host\n";

            // Écriture du fichier
            FileAndPathManager::writeFile('file', 'robots.txt', $content);

            AppLog::info("Fichier robots.txt généré avec succès à partir des routes existantes");
            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la génération du fichier robots.txt: " . $e->getMessage());
            return false;
        }
    }
    public static function addSitemapUrl(
        string $baseUrl,
        string $path,
        ?float $priority = null,
        ?string $changefreq = null
    ): bool {
        try {
            // Vérification de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            $baseUrl = rtrim($baseUrl, '/');
            $path = self::normalizeUri($path);
            $fullUrl = $baseUrl . $path;

            $sitemapPath = FileAndPathManager::getPath('file', 'sitemap.xml');

            // Si le fichier n'existe pas, créer un sitemap de base sans duplication d'entête
            if (!file_exists($sitemapPath)) {
                $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
                $xmlContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
                $xmlContent .= self::createSitemapUrlEntry($fullUrl, $priority, $changefreq);
                $xmlContent .= '</urlset>';

                file_put_contents($sitemapPath, $xmlContent);
                AppLog::info("Nouveau fichier sitemap.xml créé avec l'URL: $fullUrl");
                return true;
            }

            // Charger le fichier existant
            $xmlContent = file_get_contents($sitemapPath);

            // Vérifier si c'est bien un fichier XML valide
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                AppLog::critical("Le fichier sitemap.xml existant n'est pas un XML valide");
            }

            // Convertir en chaîne pour manipulation
            $xmlString = $xml->asXML();

            // Vérifier si l'URL est déjà présente
            $pattern = '/<url>\s*<loc>' . preg_quote(htmlspecialchars($fullUrl), '/') . '<\/loc>.*?<\/url>\s*/s';
            if (preg_match($pattern, $xmlString)) {
                // Si l'URL existe, la mettre à jour en supprimant l'ancienne entrée
                $xmlString = preg_replace($pattern, '', $xmlString);
            }

            // Ajouter la nouvelle entrée avant la balise de fermeture </urlset>
            $newEntry = self::createSitemapUrlEntry($fullUrl, $priority, $changefreq);
            $xmlString = str_replace('</urlset>', $newEntry . '</urlset>', $xmlString);

            // Écrire le fichier mis à jour sans dupliquer l'en-tête
            file_put_contents($sitemapPath, $xmlString);
            $action = isset($pattern) ? "mise à jour" : "ajoutée";
            AppLog::info("URL $action dans sitemap.xml: $fullUrl");
            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'ajout d'une URL au sitemap.xml: " . $e->getMessage());
            return false;
        }
    }
    public static function urlExistsInSitemap(string $baseUrl, string $path): bool
    {
        try {
            // Validation de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            $baseUrl = rtrim($baseUrl, '/');
            $path = self::normalizeUri($path);
            $fullUrl = $baseUrl . $path;
            $encodedUrl = htmlspecialchars($fullUrl);

            $sitemapPath = FileAndPathManager::getPath('file', 'sitemap.xml');

            // Si le fichier n'existe pas
            if (!file_exists($sitemapPath)) {
                return false;
            }

            // Charger et parser le XML
            $xml = simplexml_load_file($sitemapPath);
            if (!$xml) {
                AppLog::critical("Fichier sitemap.xml invalide");
            }

            // Recherche de l'URL
            foreach ($xml->url as $urlEntry) {
                $storedUrl = (string)$urlEntry->loc;
                if ($storedUrl === $encodedUrl) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            AppLog::error("Erreur vérification URL dans sitemap: " . $e->getMessage());
            return false;
        }
    }
    public static function removeSitemapUrl(string $path): bool
    {
        $baseUrl = WEB_URL;
        try {
            // Vérification de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            $baseUrl = rtrim($baseUrl, '/');
            $path = self::normalizeUri($path);
            $fullUrl = $baseUrl . $path;

            $sitemapPath = FileAndPathManager::getPath('file', 'sitemap.xml');

            // Si le fichier n'existe pas, rien à supprimer
            if (!file_exists($sitemapPath)) {
                AppLog::info("Aucun fichier sitemap.xml trouvé, aucune suppression nécessaire.");
                return true;
            }

            // Charger le fichier existant
            $xmlContent = file_get_contents($sitemapPath);

            // Vérifier si c'est bien un fichier XML valide
            $xml = simplexml_load_string($xmlContent);
            if (!$xml) {
                AppLog::critical("Le fichier sitemap.xml existant n'est pas un XML valide");
            }

            // Convertir en chaîne pour manipulation
            $xmlString = $xml->asXML();

            // Créer le motif de recherche pour l'URL
            $pattern = '/<url>\s*<loc>' . preg_quote(htmlspecialchars($fullUrl), '/') . '<\/loc>.*?<\/url>\s*/s';

            // Supprimer toutes les occurrences de l'URL (normalement une seule)
            $newXmlString = preg_replace($pattern, '', $xmlString, -1, $count);

            if ($count > 0) {
                // Écrire le fichier mis à jour
                file_put_contents($sitemapPath, $newXmlString);
                AppLog::info("URL supprimée du sitemap.xml: $fullUrl");
            } else {
                AppLog::info("URL non trouvée dans sitemap.xml: $fullUrl");
            }

            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la suppression d'une URL du sitemap.xml: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Génère un fichier sitemap.xml à partir des routes enregistrées
     * 
     * @param string $baseUrl URL de base du site
     * @param array $additionalUrls URLs supplémentaires à inclure dans le sitemap
     * @param array $excludedUrls URLs à exclure du sitemap
     * @param array $urlsWithPriority URLs avec une priorité spécifique [url => priorité] (0.0 à 1.0)
     * @param array $urlsWithChangefreq URLs avec une fréquence de changement spécifique [url => changefreq]
     * @return bool Vrai si le fichier a été généré avec succès
     */
    public static function generateSitemapXml(
        string $baseUrl = WEB_URL,
        array $additionalUrls = [],
        array $excludedUrls = [],
        array $urlsWithPriority = [],
        array $urlsWithChangefreq = []
    ): bool {
        try {
            // Validation de l'URL de base
            if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
                AppLog::critical("URL de base invalide");
            }

            $baseUrl = rtrim($baseUrl, '/');
            $sitemapPath = FileAndPathManager::getPath('file', 'sitemap.xml');
            $existingUrlsData = [];

            // Charger le sitemap existant s'il existe
            if (file_exists($sitemapPath)) {
                $xmlContent = file_get_contents($sitemapPath);
                $xml = simplexml_load_string($xmlContent);

                if ($xml) {
                    foreach ($xml->url as $urlEntry) {
                        $loc = (string)$urlEntry->loc;
                        $path = self::normalizeUri(str_replace($baseUrl, '', $loc));

                        // Stocker les métadonnées existantes
                        $existingUrlsData[$path] = [
                            'priority' => isset($urlEntry->priority) ? (float)$urlEntry->priority : null,
                            'changefreq' => isset($urlEntry->changefreq) ? (string)$urlEntry->changefreq : null
                        ];
                    }
                }
            }

            // Récupération des routes GET publiques
            $publicRoutes = [];
            if (isset(self::$routes['GET'])) {
                foreach (self::$routes['GET'] as $route => $callback) {
                    if (!isset(self::$protectedRoutes['GET'][$route]) && !preg_match('/\{([a-zA-Z0-9_]+)\}/', $route)) {
                        $publicRoutes[] = $route;
                    }
                }
            }

            // Fusion des sources d'URLs
            $allUrls = array_unique(array_merge(
                $publicRoutes,
                $additionalUrls,
                array_keys($existingUrlsData) // Ajouter les URLs existantes
            ));

            // Exclusion des URLs
            $allUrls = array_diff($allUrls, $excludedUrls);
            sort($allUrls);

            // Construction du XML
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($allUrls as $url) {
                $normalizedUrl = self::normalizeUri($url);
                $fullUrl = $baseUrl . $normalizedUrl;

                // Fusion des métadonnées : existantes > paramètres > défaut
                $priority = $urlsWithPriority[$normalizedUrl]
                    ?? $existingUrlsData[$normalizedUrl]['priority']
                    ?? ($normalizedUrl === '' ? 1.0 : null);

                $changefreq = $urlsWithChangefreq[$normalizedUrl]
                    ?? $existingUrlsData[$normalizedUrl]['changefreq']
                    ?? null;

                // Génération de l'entrée
                $xml .= self::createSitemapUrlEntry($fullUrl, $priority, $changefreq);
            }

            $xml .= '</urlset>';

            // Écriture du fichier
            file_put_contents($sitemapPath, $xml);
            AppLog::info("Sitemap généré avec URLs dynamiques préservées");
            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur génération sitemap: " . $e->getMessage());
            return false;
        }
    }

    private static function createSitemapUrlEntry(string $url, ?float $priority, ?string $changefreq): string
    {
        $entry = '  <url>' . "\n";
        $entry .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
        $entry .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";

        if ($changefreq && in_array($changefreq, ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])) {
            $entry .= '    <changefreq>' . $changefreq . '</changefreq>' . "\n";
        }

        if ($priority !== null && $priority >= 0.0 && $priority <= 1.0) {
            $entry .= '    <priority>' . number_format($priority, 1) . '</priority>' . "\n";
        }

        $entry .= '  </url>' . "\n";
        return $entry;
    }
}
