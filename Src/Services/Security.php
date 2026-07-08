<?php

namespace Src\Services;

use Exception;
use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Helpers\FileAndPathManager;

/**
 * Classe Security pour la gestion sécurisée des données sensibles
 * Fournit des méthodes pour le chiffrement/déchiffrement et la génération/gestion de clés
 */
class Security
{
    /**
     * Chiffre ou déchiffre une chaîne en utilisant AES-256-CBC avec HMAC SHA-256
     *
     * @param string $string La chaîne à traiter
     * @param string $action 'encrypt' pour chiffrer ou 'decrypt' pour déchiffrer
     * @param string|null $customKey Clé personnalisée (optionnelle)
     * @return string|false La chaîne traitée ou false en cas d'échec
     */
    public static function secureString($string, $action = 'encrypt', $customKey = null)
    {
        $method = 'AES-256-CBC';

        // Utiliser une clé personnalisée ou la clé par défaut de la configuration
        $key = $customKey ?: hash('sha256', Config::get("CRYPT_KEY"), true);

        if ($key === false) {
            AppLog::error("Erreur de clé de chiffrement: Clé invalide ou manquante");
            return false;
        }

        if ($action === 'encrypt') {
            try {
                $iv = random_bytes(16); // Générer un IV aléatoire de 16 octets
                $cipherText = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);

                if ($cipherText === false) {
                    AppLog::error("Erreur de chiffrement: " . openssl_error_string());
                    return false;
                }

                $hmac = hash_hmac('sha256', $cipherText, $key, true); // Générer un HMAC

                // Concaténer IV + HMAC + texte chiffré et encoder en Base64
                return base64_encode($iv . $hmac . $cipherText);
            } catch (Exception $e) {
                AppLog::error("Exception lors du chiffrement: " . $e->getMessage());
                return false;
            }
        } elseif ($action === 'decrypt') {
            try {
                $data = base64_decode($string);
                if ($data === false || strlen($data) < 48) {
                    AppLog::error("Erreur de déchiffrement: Données invalides");
                    return false;
                }

                $iv = substr($data, 0, 16);
                $hmac = substr($data, 16, 32);
                $cipherText = substr($data, 48);

                // Vérifier l'intégrité avec HMAC
                $calculatedHmac = hash_hmac('sha256', $cipherText, $key, true);
                if (!hash_equals($hmac, $calculatedHmac)) {
                    AppLog::error("Erreur de déchiffrement: Intégrité compromise");
                    return false;
                }

                // Déchiffrer les données
                $decrypted = openssl_decrypt($cipherText, $method, $key, OPENSSL_RAW_DATA, $iv);
                if ($decrypted === false) {
                    AppLog::error("Erreur de déchiffrement: " . openssl_error_string());
                    return false;
                }

                return $decrypted;
            } catch (Exception $e) {
                AppLog::error("Exception lors du déchiffrement: " . $e->getMessage());
                return false;
            }
        }

        AppLog::error("Action invalide: L'action doit être 'encrypt' ou 'decrypt'");
        return false;
    }
    /**
     * Vérifie si une chaîne est déjà chiffrée avec le tag ENC(...)
     *
     * @param string $value
     * @return bool
     */
    public static function isEncryptedFormat(string $value): bool
    {
        return str_starts_with($value, 'ENC(') && str_ends_with($value, ')');
    }

    /**
     * Emballe une chaîne chiffrée dans le format ENC(...)
     *
     * @param string $encryptedValue
     * @return string
     */
    public static function wrapEncrypted(string $encryptedValue): string
    {
        return "ENC(" . $encryptedValue . ")";
    }

    /**
     * Déballe une chaîne chiffrée du format ENC(...)
     *
     * @param string $wrapped
     * @return string
     */
    public static function unwrapEncrypted(string $wrapped): string
    {
        return substr($wrapped, 4, -1); // supprime "ENC(" et ")"
    }

    public static function secureEnvValue(string $value, string $cryptKey): string
    {
        if (self::isEncryptedFormat($value)) {
            return $value;
        }

        $encrypted = self::secureString($value, 'encrypt', $cryptKey);
        return self::wrapEncrypted($encrypted);
    }
    /**
     * Récupère et déchiffre une valeur de configuration sécurisée au format ENC(...)
     *
     * @param string $configKey Nom de la clé dans le fichier de configuration
     * @param string|null $customKey Clé de déchiffrement personnalisée (optionnelle)
     * @return string|null Valeur déchiffrée ou null en cas d’échec
     */
    public static function getDecryptedEnv(string $configKey, ?string $customKey = null): ?string
    {
        $value = trim(Config::get($configKey));
        $configPath = FileAndPathManager::getPath('config', 'secret.php');
        $keyFile = require_once($configPath);
        $key = $customKey ?: $keyFile;

        if (empty($value)) return null;

        if (self::isEncryptedFormat($value)) {
            $value = self::unwrapEncrypted($value);
            $decrypted = self::secureString($value, 'decrypt', $key);
            return $decrypted ?: null;
        }

        // Si la valeur n'est pas chiffrée, on retourne telle quelle
        return $value;
    }



    /**
     * Implémentation de HKDF (RFC 5869)
     * Utilisé pour dériver une clé à partir d'un matériel de clé et d'un sel
     * 
     * @param string $inputKey Matériel de clé initial
     * @param int $length Longueur de sortie désirée
     * @param string $salt Sel cryptographique
     * @param string $info Information contextuelle
     * @return string Clé dérivée
     */
    private static function hkdf($inputKey, $length, $salt = '', $info = '')
    {
        // Étape 1: Extraction
        $prk = hash_hmac('sha256', $inputKey, $salt, true);

        // Étape 2: Expansion
        $okm = '';
        $t = '';

        for ($i = 1; strlen($okm) < $length; $i++) {
            $t = hash_hmac('sha256', $t . $info . chr($i), $prk, true);
            $okm .= $t;
        }

        return substr($okm, 0, $length);
    }


    /**
     * Obtient une clé d'encryption pour protéger les fichiers de clés
     * 
     * @return string Clé d'encryption
     */
    private static function getKeyEncryptionKey()
    {
        // Essayer d'utiliser une clé définie dans la configuration
        $configKey = Config::get("KEY_ENCRYPTION_KEY");
        if (!empty($configKey)) {
            return hash('sha256', $configKey, true);
        }

        // Sinon, dériver une clé à partir des informations du serveur
        // Note: Cette approche n'est pas idéale pour la production mais offre un minimum de protection
        $serverInfo = php_uname();
        $hostInfo = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'cli';
        $pathInfo = __DIR__;

        return hash('sha256', $serverInfo . $hostInfo . $pathInfo . Config::get("CRYPT_KEY"), true);
    }




    /**
     * Méthode simplifiée pour chiffrer/déchiffrer des mots de passe ou données sensibles
     * sans dépendre du système de stockage de clés
     * 
     * @param string $data Données à chiffrer/déchiffrer
     * @param string $action 'encrypt' ou 'decrypt'
     * @param string|null $customKey Clé personnalisée (si null, utilise CRYPT_KEY de la config)
     * @return string|false Résultat ou false en cas d'échec
     */
    public static function simpleEncryptDecrypt($data, $action = 'encrypt', $customKey = null)
    {
        // Utiliser une clé personnalisée ou la clé par défaut de la configuration
        $key = $customKey ?: Config::get("CRYPT_KEY");
        if (empty($key)) {
            AppLog::error("Clé de chiffrement non définie dans la configuration");
            return false;
        }

        // Utiliser la fonction secureString directement, avec la clé hachée
        $hashedKey = hash('sha256', $key, true);
        return self::secureString($data, $action, $hashedKey);
    }


    /**
     * Génère un hash sécurisé pour un mot de passe
     * 
     * @param string $password Mot de passe à hasher
     * @return string|false Hash du mot de passe ou false en cas d'échec
     */
    public static function hashPassword($password)
    {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            if ($hash === false) {
                AppLog::error("Échec du hachage du mot de passe");
                return false;
            }

            return $hash;
        } catch (Exception $e) {
            AppLog::error("Exception lors du hachage du mot de passe: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie un mot de passe par rapport à son hash
     * 
     * @param string $password Mot de passe à vérifier
     * @param string $hash Hash à comparer
     * @return bool True si le mot de passe correspond, false sinon
     */
    public static function verifyPassword($password, $hash)
    {
        try {
            return password_verify($password, $hash);
        } catch (Exception $e) {
            AppLog::error("Exception lors de la vérification du mot de passe: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Génère une clé de cryptage forte pour CRYPT_KEY
     * 
     * @param int $length Longueur de la clé en octets (défaut: 32 octets = 256 bits)
     * @param bool $base64 Retourner la clé en base64 (défaut: true)
     * @param bool $printable Générer une clé avec uniquement des caractères imprimables (défaut: false)
     * @return string La clé de cryptage générée
     */
    public static function generateStrongCryptKey($length = 32, $base64 = true, $printable = false)
    {
        try {
            $rawKey = null;

            // Méthode 1: Utiliser sodium si disponible (préféré)
            if (function_exists('sodium_crypto_secretbox_keygen')) {
                if ($length === 32) {
                    $rawKey = sodium_crypto_secretbox_keygen();
                } else {
                    $rawKey = sodium_randombytes_buf($length);
                }
            }

            // Méthode 2: Utiliser random_bytes
            if ($rawKey === null) {
                $rawKey = random_bytes($length);
            }

            // Générer une clé avec uniquement des caractères imprimables (moins sécurisé mais parfois nécessaire)
            if ($printable) {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()-_=+[]{};:,.<>?';
                $charsLength = strlen($chars);
                $result = '';

                // Convertir les octets aléatoires en caractères imprimables
                for ($i = 0; $i < $length * 2; $i++) {
                    $randomByte = ord($rawKey[$i % $length]);
                    $result .= $chars[$randomByte % $charsLength];
                }

                return substr($result, 0, $length * 2); // Longueur doublée car un caractère imprimable = moins d'entropie
            }

            // Retourner la clé au format demandé
            if ($base64) {
                return base64_encode($rawKey);
            } else {
                return bin2hex($rawKey);
            }
        } catch (Exception $e) {
            throw new Exception("Impossible de générer une clé forte: " . $e->getMessage());
        }
    }
}
