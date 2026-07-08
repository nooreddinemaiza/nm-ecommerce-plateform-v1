<?php

namespace Src\Controllers;

use Src\Helpers\AppLog;
use Src\Services\Route;
use Src\Helpers\CSRFProtection;
use Src\Helpers\SessionManager;
use Src\Helpers\FileAndPathManager;
use Src\Helpers\Helper;
use Src\Middlewares\OrderConsulterMiddleware;

class PageController
{
    private array $metaData;
    public function __construct()
    {
        $this->initializeMetaData();
    }
    private function initializeMetaData(): void
    {
        $this->metaData = [
            'title' => 'Informatique Marrakech', // Titre par défaut
            // Meta classiques
            'name' => [
                'description' => 'Bienvenue sur ' . WEB_NAME . ', votre boutique en ligne de confiance.',
                'keywords' => 'boutique, achat en ligne, ' . WEB_NAME,
                'author' => WEB_NAME,
                'viewport' => 'width=device-width, initial-scale=1.0',
                'charset' => 'UTF-8',
                'robots' => 'index, follow', // SEO friendly
                'canonical' => WEB_URL, // URL principale pour éviter le duplicate content
            ],

            // Twitter Cards
            'twitter' => [
                'twitter:card' => 'summary_large_image',
                'twitter:title' => WEB_NAME,
                'twitter:description' => 'Explorez notre boutique en ligne avec une large gamme de produits.',
                'twitter:image' => WEB_URL . WEB_LOGO_PNG_URL,
            ],

            // Open Graph (Facebook, WhatsApp, etc.)
            'properties' => [
                'og:title' => WEB_NAME,
                'og:description' => 'Découvrez les meilleurs produits chez ' . WEB_NAME . '.',
                'og:image' => WEB_URL . WEB_LOGO_PNG_URL,
                'og:url' => WEB_URL,
                'og:type' => 'website',
                'og:site_name' => WEB_NAME,
            ],

            // Liens rel pour SEO et performances
            'rel' => [
                'icon' => WEB_URL . '/assets/images/favicon.ico',
                // 'apple-touch-icon' => WEB_URL . '/images/apple-touch-icon.png',
                // 'manifest' => WEB_URL . '/manifest.json',
                'preconnect' => 'https://fonts.googleapis.com',
                'dns-prefetch' => 'https://fonts.gstatic.com',
            ],
        ];
    }

    protected function setMetaData(
        string $title,
        string $description = '',
        string $keywords = '',
        string $author = '',
        string $image = '',
        string $url = '',
        string $prevPageUrl = '',
        string $nextPageUrl = ''
    ): void {
        $this->metaData['title'] = $title;
        // Mise à jour des meta classiques
        $this->metaData['name'] = array_merge($this->metaData['name'], [
            'description' => $description ?: $this->metaData['name']['description'],
            'keywords' => $keywords ?: $this->metaData['name']['keywords'],
            'author' => $author ?: $this->metaData['name']['author'],
            'canonical' => $url ?: $this->metaData['name']['canonical'],
        ]);

        // Mise à jour des Twitter Cards
        $this->metaData['twitter'] = array_merge($this->metaData['twitter'], [
            'twitter:title' => $title,
            'twitter:description' => $description ?: $this->metaData['twitter']['twitter:description'],
            'twitter:image' => $image ?: $this->metaData['twitter']['twitter:image'],
        ]);

        // Mise à jour des meta Open Graph
        $this->metaData['properties'] = array_merge($this->metaData['properties'], [
            'og:title' => $title,
            'og:description' => $description ?: $this->metaData['properties']['og:description'],
            'og:image' => $image ?: $this->metaData['properties']['og:image'],
            'og:url' => $url ?: $this->metaData['properties']['og:url'],
        ]);

        // Gestion des liens rel pour le SEO
        if ($prevPageUrl) {
            $this->metaData['rel']['prev'] = $prevPageUrl;
        }
        if ($nextPageUrl) {
            $this->metaData['rel']['next'] = $nextPageUrl;
        }
    }

    private function renderView(
        string $type,
        string $template,
        int $httpCode = 200,
        bool $includeHeader = true,
        bool $includeFooter = true,
        array $variables = [],
    ): void {
        try {
            http_response_code($httpCode);
            // Extraction des variables pour les rendre disponibles dans la vue
            extract($variables);
            $metaTags = $this->metaData ?? [];
            $css = $variables['css'] ?? '';
            $js =  $variables['js'] ?? '';
            FileAndPathManager::includeFile('part', 'head.php', compact('metaTags', 'css', 'js'));
            if ($includeHeader) {
                FileAndPathManager::includeFile('part', 'header.php');
            }
            if ($template == 'login' || $template == 'dashboard' || $template == 'database') {
                $data = count($variables) ? $variables : ['csrf_token' => CSRFProtection::generateToken()];
                // Initialisation
                if ($template == 'login') {
                    FileAndPathManager::includeFile('auth', "login.php", $data);
                } else {
                    if ($template == 'dashboard') {
                        FileAndPathManager::includeFile('manager', "dashboard.php", $data);
                    }
                    if ($template == 'database') {
                        FileAndPathManager::includeFile('manager', "database.php", $data);
                    }
                }
            } else {
                FileAndPathManager::includeFile($type, "$template.php", $variables);
            }

            if ($includeFooter) {
                $contentController = new ContentController();
                $data =  [
                    'pageContent' => $contentController->getContact(),
                ];
                FileAndPathManager::includeFile('part', 'footer.php', $data);
            }
            FileAndPathManager::includeFile('part', 'foot.php', compact('js'));
        } catch (\Exception $e) {
            AppLog::error("Erreur lors du rendu de la vue '$template' : " . $e->getMessage());
            $this->handle500();
        }
    }

    public function login($variables = []): void
    {
        if (isset($_SESSION['user_id'])) {
            Route::redirect('/dashboard');
        }
        $this->setMetaData('Connexion - ' . WEB_NAME, 'Connectez-vous à votre compte.', 'connexion, utilisateur');
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['properties'] = [];
        $this->metaData['twiter'] = [];
        $variables['error'] = $variables['error'] ?? '';
        $variables['csrf_token'] = $variables['csrf_token'] ?? CSRFProtection::generateToken();
        $variables['blocked'] = $variables['blocked'] ?? false;
        $variables['attempts'] = $variables['attempts'] ?? $_SESSION['attempts'] ?? 0;
        // Rendu de la vue avec les données
        $this->renderView('auth', 'login', 200, false, false, $variables);
    }
    public function dashboard(SessionManager $sessionManager): void
    {
        // Générer un token unique
        $token = bin2hex(random_bytes(16));
        // Définir le token et son expiration (5 minutes)
        $sessionManager->setFileAccessToken($token, time() + 300);
        $variables = [
            'css' => '<link rel="stylesheet" href="/protected_assets/css/dashboard.css?token=' . $token . '">',
            'js' => ''
        ];

        $variables['css'] .= '
        <script src="/assets/js/tinymce/tinymce.min.js"></script>';
        $variables['css'] .= '
        <script src="/assets/js/tinymce/langs/fr.js"></script>
        ';
        $variables['css'] .= '
        <script src="/assets/js/owned/toaster.js"></script>
        ';
        $variables['js'] .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
        ';
        $variables['js'] .= '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        ';
        $variables['css'] .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" /> 
        ';
        $variables['js'] .= '<script src="/protected_assets/js/dashboard-functions.js?token=' . $token . '"></script>
        ';
        $variables['js'] .= '<script src="/protected_assets/js/dashboard-manager.js?token=' . $token . '"></script>
        ';
        if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'):
            $variables['js'] .= '<script src="/protected_assets/js/dashboard-super.js?token=' . $token . '"></script>
            ';
            if ($_SESSION['user_role'] === 'admin'):
                $variables['js'] .= '<script src="/protected_assets/js/dashboard-admin.js?token=' . $token . '"></script>
                ';
            endif;
        endif;
        // Définir les métadonnées de la page de tableau de bord
        $this->metaData['name'] = [];
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['title'] = 'Tableau de board - ' . WEB_NAME;
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];

        if ($_SESSION['user_role'] === 'super_manager' || $_SESSION['user_role'] === 'admin'):
            $variables['users'] = (new UserController($sessionManager))->getAllUsers();
            $variables['pages'] = (new ContentController())->index() ?? [];
            $variables['feedback'] = (new VisitorController($sessionManager))->latestMessages() ?? [];
        endif;
        if ($_SESSION['user_role'] === 'manager'):
            $variables['users'] = (new UserController($sessionManager))->getUser($_SESSION['user_id']);
        endif;
        $orderController = new OrderController();
        $variables['categories'] = (new CategorieController($sessionManager))->index() ?? [];
        $variables['products'] = (new ProductController())->listProducts() ?? [];
        $variables['productsChart'] = $orderController->getStats() ?? [];
        $variables['ordersStats'] = $orderController->orderStat() ?? [];
        $variables['subscribers'] = (new SubscriberController($sessionManager))->getSubscriptionStats() ?? [];
        $variables['csrf_token'] = CSRFProtection::generateToken();
        // Rendre la vue du tableau de bord
        $this->renderView('admin', 'dashboard', 200, false, false, $variables);
    }
    public function database(SessionManager $sessionManager)
    {
        $dbController = new DatabaseController;
        // Générer un token unique
        $token = bin2hex(random_bytes(16));
        // Définir le token et son expiration (5 minutes)
        $sessionManager->setFileAccessToken($token, time() + 300);
        $data = [];
        $data['tables'] = $dbController->get('*');
        $this->metaData['name'] = [];
        $data['css'] = '<link rel="stylesheet" href="/protected_assets/css/db.css?token=' . $token . '"/>
        ';
        $data['js'] = '<script src="/protected_assets/js/db.js?token=' . $token . '"></script>
        ';
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['title'] = 'Tableau de board - ' . WEB_NAME;
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];
        $this->renderView('admin', 'database', 200, false, false, $data);
    }
    public function documentation()
    {
        $data = [];
        $this->setMetaData(
            'Documentation du site - ' . WEB_NAME,
            'Documentation du site',
            'Documentation du site',
            WEB_NAME
        );
        $data['css'] = '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">';
        $this->renderView('page', 'documentation', 200, false, false, $data);
    }
    public function handleInactiveAccount(): void
    {
        $this->setMetaData(
            'Inactive - ' . WEB_NAME,
            'Compte desactivé!',
            'account, desactivated, disabled'
        );
        $this->renderView('page', 'inactive', 200, false, false);
    }
    public function home(): void
    {
        $productController = new ProductController();
        $contentController = new ContentController();
        $data =  [
            'productList' => $productController->getProductsList(),
            'pageContent' => $contentController->getHome(),
            'categorieList' => (new CategorieController)->getTrendingCategories()
        ];
        $this->setMetaData(
            'Accueil - ' . WEB_NAME,
            $data["pageContent"][0]['page_meta_description'],
            $data["pageContent"][0]['page_meta_keywords'],
            $data["pageContent"][0]['meta_author']
        );
        CSRFProtection::generateToken();
        $this->renderView('page', 'home', 200, true, true, $data);
    }
    public function shop(): void
    {
        // Récupérer les paramètres de pagination de l'URL
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage =  12;
        $sortBy = 'created_at';
        $order = 'DESC';

        $productController = new ProductController();
        $contentController = (new ContentController())->getShop();

        $data = [
            'productList' => $productController->getPaginatedProducts($page, $perPage, $sortBy, $order),
            'categorieList' => (new CategorieController)->index()
        ];

        $this->setMetaData(
            'Boutique - ' . WEB_NAME,
            $contentController[0]['page_meta_description'],
            $contentController[0]['page_meta_keywords'],
            $contentController[0]['meta_author']
        );

        CSRFProtection::generateToken();
        $this->renderView('page', 'shop', 200, true, true, $data);
    }
    public function categories()
    {
        $categorieController = new CategorieController();
        $productController = new ProductController();
        $data = [
            'categoriesList' => json_decode($categorieController->index(), true),
            'productsList' => Helper::combineDataByKey(json_decode($productController->mesProduits(), true), 'id')
        ];
        $this->setMetaData(
            'Categories - ' . WEB_NAME,
            'Categories - ' . WEB_NAME,
            'Categories - ' . WEB_NAME,
            WEB_NAME
        );
        CSRFProtection::generateToken();
        $this->renderView('page', 'categories', 200, true, true, $data);
    }
    public function product($product, $debug = false): void
    {
        $mainProduct = $product['mainProduct'];
        $relatedProducts = $product['relatedProducts'];
        $id = $mainProduct['id'];
        if ($debug) {
            echo "<pre>-------------- Information du produit ------------------------
            <h3>Produit principal</h3>";
            print_r($mainProduct);
            echo "<br>---------------------------------------------------------------------
            <h3>Produits similaires</h3>";
            print_r($relatedProducts);
            echo "<br>---------------------------------------------------------------------<br></pre>";
            exit;
        }
        $mainProduct['link'] = WEB_URL . \Src\Helpers\UrlHelper::generateProductLink(
            $mainProduct['slug'],
            $id
        );
        $image = $mainProduct['images'][0];
        $data = [
            'relatedProducts' => $relatedProducts,
            'mainProduct' => $mainProduct,
            'csrf_token' => CSRFProtection::generateToken()
        ];
        $data['css'] = '<script type="application/ld+json">
                    {
                    "@context": "https://schema.org/",
                    "@type": "Product",
                    "name": "' . htmlspecialchars($mainProduct['title']) . '",
                    "image": [
                        ' . implode(',', array_map(fn($image) => '"' . WEB_URL . '/assets/images/product-image/' . htmlspecialchars_decode($image) . '"', $mainProduct['images'])) . '
                    ],
                    "description": "' . htmlspecialchars_decode($mainProduct['description']) . '",
                    "sku": "' . htmlspecialchars_decode($mainProduct['id']) . '",
                    "brand": {
                        "@type": "Brand",
                        "name": "Nom de ta marque"
                    },
                    "category": "' . (is_array($mainProduct['categories']) ? implode(', ', array_map('htmlspecialchars_decode', $mainProduct['categories'])) : htmlspecialchars_decode($mainProduct['categories'])) . '",
                    "offers": {
                        "@type": "Offer",
                        "url":' . WEB_URL .  \Src\Helpers\UrlHelper::generateProductLink(
            $mainProduct['slug'],
            $mainProduct['id']
        ) . '",
                        "priceCurrency": "DH",
                        "price": "' . $mainProduct['price'] . '",
                        ' . (($mainProduct['old_price'] ?? 0) > ($mainProduct['price'] ?? 0) ? '"priceValidUntil": "2025-12-31",' : '') . '
                        "itemCondition": "https://schema.org/NewCondition",
                        "availability": "' . (($mainProduct['stock'] ?? 0) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock') . '",
                        "seller": {
                        "@type": "Organization",
                        "name": "Nom de ton site"
                        }
                    }' . (!empty($mainProduct['reduction']) ? ',
                    "discount": {
                        "@type": "Discount",
                        "discountCurrency": "DH",
                        "discountPercent": "' . $mainProduct['reduction'] . '"
                    }' : '') . ',
                    "tag": [
                        ' . implode(',', array_map(fn($tag) => '"' . htmlspecialchars_decode(trim($tag)) . '"', is_array($mainProduct['tag']) ? $mainProduct['tag'] : explode(',', $mainProduct['tag']))) . '
                    ]
                    }
                    </script>';

        //Les produits suivant et precedant
        if (count($relatedProducts) > 1) {
            $prevNext = $this->getPrevNext($relatedProducts, $id);

            $this->metaData['rel']['prev'] =  WEB_URL . \Src\Helpers\UrlHelper::generateProductLink(
                $prevNext['prev']['slug'],
                $prevNext['prev']['id']
            );
            $this->metaData['rel']['next'] =  WEB_URL . \Src\Helpers\UrlHelper::generateProductLink(
                $prevNext['next']['slug'],
                $prevNext['next']['id']
            );
        }
        // Mise à jour des métadonnées
        $categorie = explode(',', $mainProduct['categories'])[0];
        $this->setMetaData(
            $mainProduct['title'] . ' - ' . $categorie . ' | ' . WEB_NAME,
            substr($mainProduct['meta_description'] ?? $mainProduct['description'] ?? "Description", 0, 160),
            $mainProduct['meta_tag'] ?? $mainProduct['tag'] ?? "Tags",
            WEB_NAME,
            $image,
            WEB_URL . \Src\Helpers\UrlHelper::generateProductLink($mainProduct['slug'], $id),
            $this->metaData['rel']['prev'] ?? '',
            $this->metaData['rel']['next'] ?? ''
        );
        $this->renderView('page', 'product', 200, true, true, $data);
    }
    public function category($title, $debug = false): void
    {
        // Récupérer les paramètres de pagination de l'URL
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage =  12;
        $sortBy = 'created_at';
        $order = 'DESC';
        $categorie = [];
        $categorieController = new categorieController();
        $data = $categorieController->getCategoryPaginatedProducts($title, $page, $perPage, $sortBy, $order);
        $categorie = $data['category'];
        $data = [
            "category" => $categorie,
            "productList" => $data['products'],
            "pagination" => $data['pagination']
        ];
        if ($debug) {
            echo "<pre>-------------- Liste des produits ------------------------<br>";
            print_r($data);
            echo "<br>---------------------------------------------------------------------<br></pre>";
        }
        $this->setMetaData(
            'Categorie - ' . $categorie['title'] . ' - ' . WEB_NAME,
            $categorie['description'] ?? "Produit",
            $categorie['tags'] ?? "Tags",
            WEB_NAME
        );
        CSRFProtection::generateToken();
        $this->renderView('page', 'category', 200, true, true, $data);
    }
    public function checkout(): void
    {
        $this->setMetaData(
            'Finalisation de l\'achât - '  . WEB_NAME,
            $mainProduct['description'] ?? "Finalisation de l'achat",
            $mainProduct['tag'] ?? "Tags",
            WEB_NAME
        );
        $data['js'] = '<script src="/assets/js/owned/checkout.js" crossorigin="anonymous"></script>';

        $data['csrf_token'] = CSRFProtection::generateToken();
        $data['css'] = '<link rel="stylesheet" href="/assets/css/owned/checkout.css">';
        $data['website'] = WEB_NAME;
        $data['items'] = $_SESSION['CART']['items'];
        $data['total'] = $_SESSION['CART']['total'];
        $this->renderView('page', 'checkout', 200, false, false, $data);
    }
    public function devis(): void
    {
        $this->setMetaData(
            'Demande de devis - '  . WEB_NAME,
            "Demande de devis",
            "",
            WEB_NAME
        );
        $productController = new ProductController();
        $data['products'] = $productController->getProductsTitles();
        $data['csrf_token'] = CSRFProtection::generateToken();
        $data['css'] = '<link rel="stylesheet" href="/assets/css/owned/devis.css">';
        $data['js'] = '<script src="/assets/js/owned/devis.js"></script>';
        $data['website'] = WEB_NAME;
        $data['items'] = $_SESSION['CART']['items'] ?? [];
        $data['total'] = $_SESSION['CART']['total'] ?? [];
        $this->renderView('page', 'devis', 200, true, true, $data);
    }

    public function showOrder($id, $order): void
    {
        // Définir les métadonnées de la page de tableau de bord
        $this->metaData['name'] = [];
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];
        $data['website'] = WEB_NAME;
        $data['order_infos'] = $order;
        $data['order_id'] = $id;
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('page', 'orderShow', 200, false, true, $data);
    }
    public function orderConsulter($data = []): void
    {
        // Définir les métadonnées de la page de tableau de bord
        $this->metaData['name'] = [];
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];
        $data['notFound'] = isset($_GET['error']) ? true : false;
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('page', 'orderConsulter', 200, false, true, $data);
    }
    public function orderConsulterCaptcha()
    {
        $data['csrf_token'] = CSRFProtection::generateToken();
        $data["captcha"] = OrderConsulterMiddleware::orderCaptchaSet();
        $this->renderView('page', 'orderConsulter', 200, false, true, $data);
    }
    public function search($search): void
    {
        // Récupérer les paramètres de pagination de l'URL
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        if (!is_int($page)) {
            $page = 1;
        }
        $perPage =  12;
        $sortBy = 'created_at';
        $order = 'DESC';
        $productController = new ProductController();
        $data = [];
        $data['search'] = "";
        $data['searchProductList'] = [];
        if (!empty($search)) {
            $data['search'] = $search;
            $data['searchProductList'] = $productController->searchProducts($search, $page, $perPage, $sortBy, $order);
        }
        $data = array_merge($data, [
            'productList' => $productController->getProductsListLimit(12)
        ]);
        $this->setMetaData(
            'Recherche - ' . WEB_NAME,
            'Recherche - ' . WEB_NAME,
            'Recherche - ' . WEB_NAME . ' ' . $search,
            WEB_NAME
        );
        $this->renderView('page', 'search', 200, true, true, $data);
    }

    public function resetPasswordPage(): void
    {
        if (!isset($_GET['token'])) {
            Route::redirect('/');
            exit;
        }
        $data['csrf_token'] = CSRFProtection::generateToken();
        $data['token'] = isset($_GET['token']) ? $_GET['token'] : null;
        // Définir les métadonnées de la page de tableau de bord
        $this->metaData['name'] = [];
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];
        $this->renderView('auth', 'reset-password', 200, false, false, $data);
    }
    public function forgotPassword(): void
    {

        // Définir les métadonnées de la page de tableau de bord
        $this->metaData['name'] = [];
        $this->metaData['name']['robots'] = 'index,unfollow';
        $this->metaData['properties'] = [];
        $this->metaData['twitter'] = [];
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('auth', 'forgot-password', 200, false, false, $data);
    }

    public function articles(): void
    {
        $data = [];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        if (!is_int($page)) {
            $page = 1;
        }
        $articleController = new ArticleController();
        $articles = $articleController->search();
        $data['recents'] = $articleController->getRecent(5);
        $data['articles'] = $articles['articles'] ?? [];
        $data['totalPages'] = $articles['total_pages'];
        $data['currentPage'] = $articles['current_page'];
        $data['search'] = $_GET['q'] ?? null;
        $data['js'] = '<script src="/assets/js/owned/articles.js"></script>';

        $this->renderView('page', 'articles', 200, true, true, $data);
    }
    public function article($data): void
    {
        $this->setMetaData(
            'Article - ' . WEB_NAME,
            $data["article"]['meta']['description'],
            $data["article"]['meta']['tags'],
            WEB_NAME,
            WEB_URL . htmlspecialchars_decode($data["article"]['image'])
        );
        $data['js'] = '<script src="/assets/js/owned/articles.js"></script>';
        $this->renderView('page', 'article', 200, true, true, $data);
    }
    public function test(): void
    {
        // $data['articles'] = (new ArticleController())->;
        $data['css'] = '<link rel="stylesheet" href="/assets/ckeditor/ckeditor5.css">';
        $data['js'] = '<script src="/assets/js/owned/ckeditor5-config.js"></script>';
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('page', 'test', 200, false, false, $data);
    }
    public function contact(): void
    {
        $contentController = new ContentController();
        $data =  [
            'pageContent' => $contentController->getContact(),
        ];

        $this->setMetaData(
            'Contact - ' . WEB_NAME,
            $data["pageContent"][0]['page_meta_description'],
            $data["pageContent"][0]['page_meta_keywords'],
            $data["pageContent"][0]['meta_author']
        );
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('page', 'contact', 200, true, true, $data);
    }
    public function about(): void
    {
        $contentController = new ContentController();
        $data =  [
            'pageContent' => $contentController->getContact(),
        ];

        $this->setMetaData(
            'A propos - ' . WEB_NAME,
            '',
            '',
            WEB_NAME,
            ''
        );
        $data['csrf_token'] = CSRFProtection::generateToken();
        $this->renderView('page', 'about', 200, true, true, $data);
    }
    public function maintenance(): void
    {
        $data = [];
        $this->setMetaData(
            'Maintenance - ' . WEB_NAME,
            'Maintenance en cours',
            'maintenance, site en maintenance',
            WEB_NAME
        );
        $data['js'] = '<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>';
        $this->renderView('page', 'maintenance', 503, false, true, $data);
    }
    public function handle404(): void
    {
        try {
            http_response_code(404);
            $this->setMetaData(
                'Page non trouvée',
                'La page que vous recherchez est introuvable.',
                '404, erreur, page non trouvée'
            );
            $this->renderView('page', '404', 404, false, false);
        } catch (\Exception $e) {
            AppLog::critical("Page introuvable: " . $e->getMessage());
        }
    }
    public function handleProduct404(): void
    {
        $productController = new ProductController();
        $data =  [
            'productList' => $productController->getProductsListLimit(5),
        ];

        try {
            http_response_code(404);
            $this->setMetaData(
                'Page non trouvée',
                'Désolé, le produit que vous recherchez n\'est plus disponible dans notre catalogue. Découvrez d\'autres produits dans la catégorie Consultez nos produits similaires ou utilisez notre barre de recherche pour trouver ce que vous cherchez.',
                'produit non trouvé,article indisponible,shopping en ligne,achat en ligne,e-commerce'
            );
            $this->renderView('page', 'product_404', 404, false, false, $data);
        } catch (\Exception $e) {
            AppLog::critical("Page introuvable: " . $e->getMessage());
        }
    }
    public function handle500(): void
    {
        try {
            http_response_code(500);
            $this->setMetaData(
                'Erreur interne',
                'Une erreur interne du serveur est survenue.',
                '500, erreur, serveur'
            );
            $this->renderView('page', '500', 500, false, false);
        } catch (\Exception $e) {
            AppLog::critical("Erreur critique lors de la gestion de la page 500 : " . $e->getMessage());
        }
    }
    public function handle403(): void
    {
        try {
            http_response_code(403);
            $this->setMetaData(
                'Accès interdit',
                'Vous n’avez pas l’autorisation d’accéder à cette page.',
                '403, accès interdit, erreur'
            );
            $this->renderView('page', '403', 403, false, false);
        } catch (\Exception $e) {
            AppLog::critical("Erreur critique lors de la gestion de la page 403 : " . $e->getMessage());
        }
    }
    private function getPrevNext(array $array, int $currentId): array
    {
        $keys = array_keys($array); // Récupère les clés du tableau
        $index = array_search($currentId, $keys); // Trouve la position actuelle

        $prev = $keys[$index - 1] ?? array_rand($array); // Précédent ou aléatoire si non existant
        $next = $keys[$index + 1] ?? array_rand($array); // Suivant ou aléatoire si non existant
        return [
            'prev' => $array[$prev] ?? null,
            'next' => $array[$next] ?? null
        ];
    }
}
