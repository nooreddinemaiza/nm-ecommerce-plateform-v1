<?php

namespace Src\Database;

use PDOException;
use Src\Helpers\AppLog;
use Src\Helpers\Config;

class Migration
{
    private $db;
    private $tables = [];

    public function __construct()
    {
        $this->db = new Database();
        $this->tables = [
            'users',
            'logins',
            'products',
            'categories',
            'product_categories',
            'product_images',
            'orders',
            'order_items',
            'pages',
            'subscribers',
            'contacts',
            'articles',
        ];
    }

    // Exécuter toutes les migrations
    public function runMigrations()
    {
        try {
            // Récupérer les tables existantes
            $existing_tables = $this->db->select("information_schema.tables", "table_name", "table_schema = DATABASE()");

            // Tables à créer
            $tablesToCreate = $this->tables;

            // Si des tables existent déjà, on les compare avec celles à créer
            $tablesToCreate = array_diff($tablesToCreate, array_column($existing_tables, 'table_name'));

            if (!empty($tablesToCreate)) {
                AppLog::info("Démarrage de la migration");

                // Créer les tables manquantes
                foreach ($tablesToCreate as $table) {
                    $methodName = "create" . ucfirst($table) . "Table";

                    if (method_exists($this, $methodName)) {
                        $this->{$methodName}(); // Appelle la méthode correspondante pour créer la table
                        AppLog::info("La table '$table' a été créée avec succès.");
                    } else {
                        AppLog::error("La méthode '$methodName' n'existe pas pour la table '$table'.");
                    }
                }
                AppLog::info("Migration terminée avec succès !");
                return true;
            } else {
                AppLog::info("Toutes les tables sont déjà présentes, aucune migration nécessaire.");
                return true;
            }
        } catch (\PDOException $e) {
            // Afficher l'erreur dans le log sans bloquer l'exécution du programme
            AppLog::error("Erreur de migration : " . $e->getMessage());
            return false;
        }
    }

    // Supprimer toutes les tables (rollback)
    public function rollbackMigrations()
    {
        AppLog::warning("Rolling back db");
        $this->db->beginTransaction();
        try {
            foreach ($this->tables as $table) {
                $this->db->execQuery("DROP TABLE IF EXISTS $table");
            }
            $this->db->commitTransaction();
            AppLog::info("Toutes les tables ont été supprimées avec succès.");
        } catch (PDOException $e) {
            if ($this->db->beginTransaction()) {
                $this->db->rollbackTransaction();
                AppLog::error("Échec de la suppression des tables : " . $e->getMessage());
            }
        }
    }

    private function createUsersTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            fullname VARCHAR(100) NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            phone VARCHAR(255) NULL,
            password VARCHAR(255) NOT NULL,
            last_password VARCHAR(255),
            status ENUM('active', 'inactive') DEFAULT 'inactive',
            last_pass_mod TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            reset_token VARCHAR(255) NULL,
            reset_token_expiry DATETIME NULL,
            reset_pswd_limit tinyint(4) DEFAULT 3,
            role ENUM('super_manager', 'manager', 'admin') DEFAULT 'manager',
            verified enum('TRUE','FALSE') DEFAULT 'FALSE',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }

    private function createProductsTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            tag TEXT,
            meta_description text DEFAULT NULL,
            meta_tag text DEFAULT NULL,
            price DECIMAL(10, 2) NOT NULL,
            old_price DECIMAL(10, 2),
            stock INT DEFAULT 0,
            stock_update date,
            reduction INT,
            apply_reduction_on INT DEFAULT 5,
            visited_times INT DEFAULT 0,
            is_trend TINYINT(1) DEFAULT 0,
            creator_id INT,
            status ENUM('affiche','reduit') DEFAULT 'reduit',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";

        if ($this->db->execQuery($sql)) {
            // Vérifier si l'index FULLTEXT existe avant de l'ajouter
            $checkIndex = $this->db->execQuery("
                SHOW INDEX FROM products WHERE Key_name = 'title';
            ");

            if (!$checkIndex) {
                $this->db->execQuery("ALTER TABLE products ADD FULLTEXT(title, description, tag);");
            }
        }
    }

    private function createCategoriesTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            tags TEXT,
            image_path VARCHAR(255),
            reduction int,
            is_trend TINYINT(1) DEFAULT 0,
            visites INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }

    private function createProduct_categoriesTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS  product_categories (
            product_id INT,
            category_id INT,
            PRIMARY KEY (product_id, category_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;";
        $this->db->execQuery($sql);
    }

    private function createProduct_imagesTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }

    private function createOrdersTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(15),
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') 
                NOT NULL DEFAULT 'processing',
            printed TINYINT(1) DEFAULT 3,
            customer_city VARCHAR(100) NOT NULL DEFAULT '',
            customer_city_zip VARCHAR(10) NOT NULL DEFAULT '',
            customer_address varchar(150) DEFAULT '',
            total_amount DECIMAL(10, 2) NOT NULL
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }
    private function createLoginsTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS logins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL, -- NULL si l'utilisateur n'existe pas ou tentative sans compte valide
            ip_address VARCHAR(45) NOT NULL,
            attempts INT DEFAULT 1,
            status ENUM('success', 'failed', 'blocked') NOT NULL,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            user_agent TEXT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }
    private function createOrder_itemsTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            appReduction JSON DEFAULT '{\"reduction\":0,\"plus\":0}',
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        $this->db->execQuery($sql);
    }

    private function createPagesTable()
    {
        $sql = "
        CREATE TABLE IF NOT EXISTS pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_title VARCHAR(255) NOT NULL,           -- Titre de la page
        page_meta_description TEXT,                 -- Description pour le SEO
        meta_author TEXT,                           -- author pour le SEO
        page_meta_keywords VARCHAR(255),            -- Mots-clés pour le SEO
        page_data JSON,                             -- Champ JSON pour stocker des données variées (produits, catégories, offres, etc.)
        custom_sections TEXT DEFAULT '[]' ,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date de création
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Date de mise à jour
        )ENGINE=InnoDB;";
        $this->db->execQuery($sql);
    }
    private function createSubscribersTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS subscribers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NOT NULL
            );
        ";
        $this->db->execQuery($sql);
    }

    private function createContactsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS contacts (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";
        $this->db->execQuery($sql);
    }

    private function createArticlesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            creator varchar(255),
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT NOT NULL,
            excerpt TEXT,
            image VARCHAR(255),
            visites INT DEFAULT 0,
            meta TEXT DEFAULT NULL,
            is_published BOOLEAN DEFAULT FALSE,
            published_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
        ";
        $this->db->execQuery($sql);
    }

    private function checkPreconfig()
    {
        $result = $this->db->selectA(
            table: "pages",
            columns: "COUNT(*) AS pagesN"
        );
        return ($result[0]['pagesN']) ? true : false;
    }
    public function preConfig()
    {
        try {
            if (!$this->checkPreconfig()) {
                // Démarrer une transaction pour garantir l'intégrité des données
                $this->db->beginTransaction();
                $pagesData = [
                    [
                        'page_title' => 'home',
                        'page_meta_description' => 'Plateforme e-commerce administrable développée par NM.',
                        'page_meta_keywords' => 'NM,e-commerce,php,boutique,cms,tableau de bord',
                        'page_data' => json_encode([
                            'titre1' => 'Bienvenue sur ' . WEB_NAME,
                            'titre2' => 'UNE DÉMONSTRATION D\'UNE BOUTIQUE E-COMMERCE ADMINISTRABLE',
                            'description' => 'Ce projet démontre la conception d\'une plateforme e-commerce complète développée en PHP. Il comprend une boutique, un système de demande de devis ainsi qu\'un tableau de bord permettant d\'administrer entièrement le contenu sans modifier le code source.',
                            'productid' => '1'
                        ]),
                        'meta_author' => WEB_URL,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'custom_sections' => json_encode([
                            [
                                "id" => "18",
                                "model" => 2,
                                "page" => "home",
                                "data" => json_encode([
                                    "smallTitle" => "Projet réalisé par NM",
                                    "largeTitle" => "Une plateforme prête à être adaptée à votre activité",
                                    "description" => "Cette démonstration met en avant une architecture permettant de gérer les pages, les produits, les catégories, les paramètres du site et le référencement depuis une interface d'administration intuitive.",
                                    "imageUrl" => "/assets/images/logo.svg",
                                    "hasLink" => "true",
                                    "linkUrl" => "https://github.com/nooreddinemaiza.",
                                    "linkText" => "Voir le projet"
                                ])
                            ],
                            [
                                "id" => "22",
                                "model" => "5",
                                "page" => "home",
                                "data" => json_encode(["title" => "Modele vide"])
                            ],
                            [
                                "id" => "21",
                                "model" => "5",
                                "page" => "home",
                                "data" => json_encode(["title" => "Modele vide"])
                            ],
                            [
                                "id" => "20",
                                "model" => "5",
                                "page" => "home",
                                "data" => json_encode(["title" => "Modele vide"])
                            ],
                            [
                                "id" => "12",
                                "model" => 3,
                                "page" => "home",
                                "data" => json_encode([
                                    "title" => "Fonctionnalités principales",
                                    "features" => [
                                        [
                                            "icon" => "fas fa-layer-group",
                                            "title" => "Gestion dynamique du contenu",
                                            "description" => "Les pages et leur contenu sont administrables depuis le tableau de bord."
                                        ],
                                        [
                                            "icon" => "fas fa-box-open",
                                            "title" => "Catalogue de produits",
                                            "description" => "Gestion des produits, catégories et présentation de la boutique."
                                        ],
                                        [
                                            "icon" => "fas fa-file-signature",
                                            "title" => "Demandes de devis",
                                            "description" => "Les visiteurs peuvent envoyer des demandes de devis directement depuis le site."
                                        ],
                                        [
                                            "icon" => "fas fa-cogs",
                                            "title" => "Administration complète",
                                            "description" => "Configuration des métadonnées, contenus, paramètres et autres éléments sans modification du code."
                                        ]
                                    ]
                                ])
                            ]
                        ])

                    ],
                    [
                        'page_title' => 'contact',
                        'page_meta_description' => 'Contactez NM',
                        'page_meta_keywords' => 'contact,NM,portfolio',
                        'page_data' => json_encode([
                            'title' => 'Entrons en contact',
                            'introduction' => 'Cette plateforme est présentée comme une démonstration technique. Pour toute question, collaboration ou retour sur le projet, n\'hésitez pas à me contacter.',
                            'address' => Config::get("WEB_ADDRESS") ?? 'Morocco',
                            'phone' => Config::get("WEB_PHONE") ?? '+212 XXX XXX XXX',
                            'email' => Config::get("WEB_EMAIL") ?? "contact@" . WEB_URL,
                            'map' => ''
                        ]),
                        'meta_author'         => WEB_URL,
                        'created_at'          => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ],
                    [
                        'page_title'          => 'articles',
                        'page_meta_description' => 'Articles et actualités',
                        'page_meta_keywords' => 'blog,NM,actualités',
                        'page_data'           => NULL,
                        'meta_author'         => WEB_URL,
                        'created_at'          => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ],
                    [
                        'page_title'          => 'shop',
                        'page_meta_description' => 'Catalogue de démonstration',
                        'page_meta_keywords' => 'boutique,démo,NM,e-commerce',
                        'page_data'           => NULL,
                        'meta_author'         => WEB_URL,
                        'created_at'          => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ]
                ];

                foreach ($pagesData as $page) {
                    $this->db->insert('pages', $page);
                }

                // Valider la transaction
                $this->db->commitTransaction();
                return true;
            } else {
                AppLog::warning("Les preconfigurations sont déja inserées dans la base de données");
                return true;
            }
        } catch (\Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->getpdo()->rollBack();
            // Afficher l'erreur dans le log
            AppLog::critical("Erreur lors de la configuration initiale : " . $e->getMessage());
            return false;
        }
    }
}
