# NM E-Commerce Platform v1

NM E-Commerce Platform v1 est une plateforme e-commerce développée en PHP orienté objet suivant une architecture MVC (non stricte). Elle permet à une entreprise de présenter ses produits et services en ligne tout en administrant entièrement son contenu via un tableau de bord.

> ⚠️ Ce projet a été initialement développé en 2019 dans le cadre d'un projet personnel afin d'acquérir une première expérience complète dans le développement d'une plateforme e-commerce. Il est aujourd'hui publié à des fins de démonstration et de portfolio.

> **Author:** Noureddine MAIZA  
> **Repository:** https://github.com/nooreddinemaiza/nm-ecommerce-plateform-v1

---

# Fonctionnalités

- Catalogue de produits
- Catégories de produits
- Recherche
- Demande de devis
- Formulaire de contact
- Tableau de bord d'administration
- Gestion des produits
- Gestion des catégories
- Gestion des pages
- Gestion des modérateurs
- Gestion des abonnés
- Gestion des devis
- Upload d'images
- Optimisation SEO
- Envoi d'emails via SMTP
- Réinitialisation de mot de passe
- CAPTCHA
- Gestion des métadonnées
- Et bien plus...

---

# Technologies

- PHP 8.2
- MySQL / MariaDB
- Apache2
- HTML5
- CSS3
- Bootstrap
- JavaScript
- jQuery
- Composer
- PHPMailer

---

# Installation

Les étapes suivantes ont été réalisées sous **Ubuntu**.

## 1. Mettre le système à jour

```bash
sudo apt update
sudo apt upgrade -y
```

---

## 2. Installer Apache

```bash
sudo apt install apache2 -y
```

Activer Apache

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Vérifier son état

```bash
sudo systemctl status apache2
```

---

## 3. Installer PHP

```bash
sudo apt install php php-cli php-common libapache2-mod-php -y
```

Installer les extensions nécessaires

```bash
sudo apt install \
php-mysql \
php-mbstring \
php-xml \
php-curl \
php-zip \
php-gd \
php-intl \
php-bcmath \
php-imagick \
php-soap \
php-opcache \
-y
```

Vérifier

```bash
php -v
```

---

## 4. Installer MariaDB

```bash
sudo apt install mariadb-server mariadb-client -y
```

Activer MariaDB

```bash
sudo systemctl enable mariadb
sudo systemctl start mariadb
```

Sécuriser MariaDB

```bash
sudo mysql_secure_installation
```

---

## 5. Installer Git

```bash
sudo apt install git -y
```

---

## 6. Installer Composer

```bash
sudo apt install composer -y
```

Vérifier

```bash
composer --version
```

---

# Cloner le projet

Se placer dans le dossier web

```bash
cd /var/www
```

Cloner le dépôt

```bash
sudo git clone https://github.com/nooreddinemaiza/nm-ecommerce-plateform-v1.git
```

Entrer dans le projet

```bash
cd nm-ecommerce-plateform-v1
```

---

# Installer les dépendances Composer

```bash
composer install
```

---

# Configuration du fichier .env

Le projet utilise un fichier **.env** pour stocker sa configuration.

Copiez le fichier modèle :

```bash
cp env.copy .env
```

Ouvrez ensuite le fichier :

```bash
nano .env
```

Renseignez les valeurs correspondant à votre environnement.

```ini
# === BASE DE DONNÉES ===

# Adresse du serveur MySQL
DB_HOST=""

# Nom de la base de données
DB_NAME=""

# Nom d'utilisateur
DB_USER=""

# Mot de passe de la base de données
# Cette ligne sera effacée automatiquement pour votre sécurité
DB_PASS=""



# === MODE MAINTENANCE ===

MAINTENANCE=false



# === INFOS PUBLIQUES DU SITE ===

WEB_NAME=""
WEB_SLOGAN=""
WEB_ADDRESS=""
WEB_PHONE=""
WEB_EMAIL=""



# === PARAMÈTRES EMAIL ===

# Mot de passe ou mot de passe d'application
# Cette ligne sera effacée automatiquement pour votre sécurité
WEB_EMAIL_PASSWORD=""

WEB_EMAIL_HOST=""
WEB_EMAIL_PORT=
WEB_EMAIL_ENCRYPTION=""



# === ADMIN PRINCIPAL ===

WEB_ADMIN_USERNAME=""
WEB_ADMIN_EMAIL=""



# === CAPTCHA ===

CAPTCHA_LENGTH=6
CAPTCHA_MAX_ATTEMPTS=3



# === COMMANDES ===

ORDER_ID_NUMBER=13102025
WEB_CANCEL_ORDER_LIMIT="1 Jour"



# === DEVIS ===

DEVIS_WITH_SUBSCRIBE=false
DEVIS_SEND_COPY_TO_CLIENT=true
```

---

# Créer la base de données

Connexion à MariaDB

```bash
sudo mysql
```

Créer la base

```sql
CREATE DATABASE ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Créer un utilisateur

```sql
CREATE USER 'ecommerce'@'localhost' IDENTIFIED BY 'VotreMotDePasse';
```

Attribuer les droits

```sql
GRANT ALL PRIVILEGES ON ecommerce.* TO 'ecommerce'@'localhost';

FLUSH PRIVILEGES;

EXIT;
```

---

# Importer la base de données

Importer le fichier SQL fourni avec le projet

```bash
mysql -u ecommerce -p ecommerce < database.sql
```

---

# Configuration d'Apache

Créer un VirtualHost

```bash
sudo nano /etc/apache2/sites-available/nm-ecommerce.conf
```

```apache
<VirtualHost *:80>

    ServerName localhost

    DocumentRoot /var/www/nm-ecommerce-plateform-v1/Public

    <Directory /var/www/nm-ecommerce-plateform-v1/Public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nm-error.log
    CustomLog ${APACHE_LOG_DIR}/nm-access.log combined

</VirtualHost>
```

Activer le site

```bash
sudo a2ensite nm-ecommerce.conf
```

Activer mod_rewrite

```bash
sudo a2enmod rewrite
```

Redémarrer Apache

```bash
sudo systemctl restart apache2
```

---

# Permissions Linux

Attribuer le propriétaire du projet

```bash
sudo chown -R www-data:www-data /var/www/nm-ecommerce-plateform-v1
```

Donner les permissions recommandées

```bash
sudo find /var/www/nm-ecommerce-plateform-v1 -type d -exec chmod 755 {} \;
```

```bash
sudo find /var/www/nm-ecommerce-plateform-v1 -type f -exec chmod 644 {} \;
```

Si le projet utilise un dossier de stockage ou d'upload, lui donner les droits d'écriture :

```bash
sudo chmod -R 775 Storage
```

ou

```bash
sudo chmod -R 775 Public/uploads
```

(selon votre configuration)

---

# Accéder au projet

Depuis le navigateur :

```
http://localhost
```

ou

```
http://adresse-ip-du-serveur
```

---

# Dépendances Composer

Le projet utilise notamment :

- PHPMailer
- vlucas/phpdotenv
- graham-campbell/result-type
- phpoption/phpoption
- symfony/polyfill-ctype
- symfony/polyfill-mbstring
- symfony/polyfill-php80

---

# Licence

Ce projet est publié à des fins de démonstration et de portfolio.

Vous êtes libre de l'étudier, de l'adapter et de vous en inspirer conformément à la licence du dépôt.

---

Développé par **Noureddine MAIZA**.
