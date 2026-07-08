# NM E-Commerce Platform v1

> A complete e-commerce platform developed in PHP following an MVC-inspired architecture, featuring a dynamic administration dashboard for managing products, pages, categories, quotes, subscribers, and more.

> **Author:** Noureddine MAIZA  
> **Repository:** https://github.com/nooreddinemaiza/nm-ecommerce-plateform-v1

---

# Features

- Product catalog
- Product search
- Categories management
- Quote request system
- Contact form
- Dynamic page management
- Administration dashboard
- Products management
- Categories management
- Moderators management
- Subscribers management
- Quotes management
- SMTP email support (PHPMailer)
- Password reset
- SEO metadata management
- Image upload
- CAPTCHA generation
- MVC-inspired architecture
- Object-Oriented PHP

---

# Technologies

- PHP 8.2+
- Apache2
- MariaDB / MySQL
- HTML5
- CSS3
- Bootstrap
- JavaScript
- jQuery
- Composer
- PHPMailer

---

# System Requirements

- Ubuntu 22.04 or newer
- Apache2
- PHP 8.2 or newer
- MariaDB or MySQL
- Composer
- Git

---

# 1. Update Ubuntu

```bash
sudo apt update
sudo apt upgrade -y
```

---

# 2. Install Apache

```bash
sudo apt install apache2 -y
```

Enable Apache

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Verify

```bash
sudo systemctl status apache2
```

---

# 3. Install PHP

```bash
sudo apt install -y \
php \
php-cli \
php-common \
php-mysql \
php-mbstring \
php-xml \
php-curl \
php-gd \
php-zip \
php-intl \
php-bcmath
```

Verify

```bash
php -v
```

---

# 4. Install Composer

```bash
sudo apt install composer -y
```

Verify

```bash
composer --version
```

---

# 5. Install Git

```bash
sudo apt install git -y
```

Verify

```bash
git --version
```

---

# 6. Install MariaDB

```bash
sudo apt install mariadb-server -y
```

Enable MariaDB

```bash
sudo systemctl enable mariadb
sudo systemctl start mariadb
```

Secure installation

```bash
sudo mysql_secure_installation
```

---

# 7. Create Database

Login

```bash
sudo mysql
```

Create database

```sql
CREATE DATABASE nm_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'nm_user'@'localhost' IDENTIFIED BY 'StrongPassword';

GRANT ALL PRIVILEGES
ON nm_ecommerce.*
TO 'nm_user'@'localhost';

FLUSH PRIVILEGES;

EXIT;
```

---

# 8. Clone the Project

Move to the web directory

```bash
cd /var/www
```

Clone

```bash
sudo git clone https://github.com/nooreddinemaiza/nm-ecommerce-plateform-v1.git
```

Go inside

```bash
cd nm-ecommerce-plateform-v1
```

---

# 9. Install Composer Dependencies

```bash
composer install
```

or

```bash
composer update
```

---

# 10. Configure Apache

Create a VirtualHost

```bash
sudo nano /etc/apache2/sites-available/nm-ecommerce.conf
```

Paste

```apache
<VirtualHost *:80>

    ServerName localhost

    DocumentRoot /var/www/nm-ecommerce-plateform-v1/Public

    <Directory /var/www/nm-ecommerce-plateform-v1/Public>

        AllowOverride All

        Require all granted

    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nm_error.log

    CustomLog ${APACHE_LOG_DIR}/nm_access.log combined

</VirtualHost>
```

Enable the site

```bash
sudo a2ensite nm-ecommerce.conf
```

Disable the default site

```bash
sudo a2dissite 000-default.conf
```

Enable rewrite

```bash
sudo a2enmod rewrite
```

Restart Apache

```bash
sudo systemctl restart apache2
```

---

# 11. Configure the Environment File

Copy

```bash
cp .env.example .env
```

Edit

```bash
nano .env
```

Configure your application according to your environment.

Example

```env
APP_ENV=development

APP_DEBUG=true

DB_HOST=localhost

DB_PORT=3306

DB_NAME=nm_ecommerce

DB_USERNAME=nm_user

DB_PASSWORD=StrongPassword
```

---

# 12. Import the Database

Import your SQL dump

```bash
mysql -u nm_user -p nm_ecommerce < database.sql
```

---

# 13. Linux Permissions

Give ownership to Apache

```bash
sudo chown -R www-data:www-data /var/www/nm-ecommerce-plateform-v1
```

Permissions

```bash
sudo find /var/www/nm-ecommerce-plateform-v1 -type d -exec chmod 755 {} \;

sudo find /var/www/nm-ecommerce-plateform-v1 -type f -exec chmod 644 {} \;
```

Writable directories

```bash
sudo chmod -R 775 Storage

sudo chmod -R 775 Public/uploads

sudo chown -R www-data:www-data Storage

sudo chown -R www-data:www-data Public/uploads
```

---

# 14. Restart Services

```bash
sudo systemctl restart apache2

sudo systemctl restart mariadb
```

---

# 15. Access the Application

Open your browser

```
http://localhost
```

or

```
http://SERVER_IP
```

---

# Project Structure

```
App/
Config/
Core/
Database/
Public/
Resources/
Routes/
Storage/
Vendor/
composer.json
.env
```

---

# Composer Packages

- graham-campbell/result-type
- phpoption/phpoption
- symfony/polyfill-ctype
- symfony/polyfill-mbstring
- symfony/polyfill-php80
- vlucas/phpdotenv
- phpmailer/phpmailer

---

# Development Notes

This project was originally developed in 2019 as a learning experience to understand how a complete e-commerce platform works. Although newer projects have significantly improved the architecture and stability, this repository reflects the foundation that led to the development of more advanced applications.

---

# License

This project is published for educational and portfolio purposes.

Commercial use requires the author's permission.

---

# Author

**Noureddine MAIZA**

GitHub:
https://github.com/nooreddinemaiza
