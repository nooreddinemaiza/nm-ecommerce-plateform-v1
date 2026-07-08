<?php

namespace Src\Helpers;

class FileAndPathManager
{
    private static string $baseDir;

    /**
     * Initialiser le répertoire de base.
     */
    public static function init(string $baseDir): void
    {
        if (isset(self::$baseDir)) {
            // Si déjà initialisé, ne pas réinitialiser
            return;
        }
        self::$baseDir = rtrim($baseDir, '/');
    }

    /**
     * Récupérer le chemin d'un fichier.
     */
    public static function getPath(string $type, string $name): string
    {
        $paths = [
            'template' => '/templates/',
            'page' => '/templates/pages/',
            'auth' => '/templates/auth/',
            'manager' => '/templates/manager/',
            'manager-part' => '/templates/manager/parts/',
            'part' => '/templates/parts/',
            'section' => '/templates/custom-sections/',
            'config' => '/config/',
            'log' => '/logs/',
            'route' => '/Src/routes/',
            'model' => '/Src/Models/',
            'controller' => '/Src/Controllers/',
            'asset' => '/public/assets/',
            'product-image' => '/public/assets/images/product-image/',
            'category-image' => '/public/assets/images/category-image/',
            'article-image' => '/public/assets/images/article-image/',
            'image' => '/public/assets/images/',
            'dashbord-part' => '/Src/parts/',
            'protected_asset' => '/Src/protected_assets/',
            'tcpdf' => '/Src/Libraries/TCPDF/',
            'file' => '/public/Files/',
            'src' => '/Src/',
            'public' => '/public/',
        ];

        if (!array_key_exists($type, $paths)) {
            $errorMessage = "Type de chemin non pris en charge : $type";
            AppLog::error($errorMessage); // Ajout du log d'erreur
            AppLog::warning($errorMessage);
            return "";
        }

        $filePath = self::$baseDir . $paths[$type] . ltrim($name, '/');

        // Validation de sécurité (éviter les attaques par chemin transversal)
        if (strpos(realpath($filePath), realpath(self::$baseDir)) !== 0) {
            $errorMessage = "Accès non autorisé au chemin : $filePath";
            AppLog::error($errorMessage); // Ajout du log d'erreur
            AppLog::warning($errorMessage);
            return "";
        }

        return $filePath;
    }

    /**
     * Récupérer le chemin d'un dossier en indiquant son parent et son nom.
     *
     * @param string $folderName Le nom du sous-dossier.
     * @return string Le chemin absolu du répertoire.
     */
    public static function getDirectoryPath(string $folderName): string
    {
        self::init(BASE_PATH);
        // Vérifie si le type parent existe dans la configuration des chemins
        $paths = [
            'template' => '/templates/',
            'config' => '/',
            'log' => '/logs/',
            'route' => '/Src/routes/',
            'model' => '/Src/Models/',
            'secure_keys' => '/Src/Services/',
            'controller' => '/Src/Controllers/',
            'asset' => '/public/assets/',
            'image' => '/public/assets/images/',
            'product-image' => '/public/assets/images/',
            'article-image' => '/public/assets/images/',
            'category-image' => '/public/assets/images/',
            'tcpdf' => '/Src/Libraries/',
        ];

        if (!array_key_exists($folderName, $paths)) {
            $errorMessage = "Type de répertoire parent non pris en charge : $folderName";
            AppLog::error($errorMessage);
            AppLog::warning($errorMessage);
            exit;
        }

        // Construit le chemin absolu
        $directoryPath = self::$baseDir . $paths[$folderName] . trim($folderName, '/') . '/';

        // Valide que le chemin est sécurisé
        if (strpos(realpath($directoryPath), realpath(self::$baseDir)) !== 0) {
            $errorMessage = "Accès non autorisé au chemin : $directoryPath";
            AppLog::error($errorMessage);
            AppLog::warning($errorMessage);
            exit;
        }

        return $directoryPath;
    }

    /**
     * Ajouter une ligne dans un fichier à une position donnée, si elle n'existe pas déjà.
     *
     * @param string $type      Le type de fichier (ex. 'log', 'config', etc.).
     * @param string $name      Le nom du fichier.
     * @param string $line      La ligne à ajouter.
     * @param string|int $position  La position d'ajout ('first', 'last' ou un index spécifique).
     *
     * @throws \Exception Si le fichier n'existe pas ou s'il y a une erreur d'écriture.
     */
    public static function addLineToFile(string $type, string $name, string $line, string|int $position = 'last')
    {
        $filePath = self::getPath($type, $name);

        // Vérifier si le fichier existe
        if (!file_exists($filePath)) {
            AppLog::warning("Fichier introuvable : $filePath");
            return false;
        }
        // Lire le contenu actuel du fichier
        $fileContent = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Vérifier si la ligne existe déjà
        if (in_array($line, $fileContent, true)) {
            AppLog::info("La ligne existe déjà dans le fichier : $filePath");
            return false;
        }

        // Ajouter une ligne vide avant et après la ligne
        $lineWithSpacing = ['', $line, ''];

        // Ajouter la ligne à la position demandée
        if ($position === 'first') {
            array_unshift($fileContent, ...$lineWithSpacing);
        } elseif ($position === 'last') {
            array_push($fileContent, ...$lineWithSpacing);
        } elseif (is_int($position) && $position >= 0 && $position <= count($fileContent)) {
            array_splice($fileContent, $position, 0, $lineWithSpacing);
        } else {
            AppLog::warning("Position invalide pour ajouter la ligne : $position");
            return false;
        }

        // Réécrire le contenu dans le fichier
        return file_put_contents($filePath, implode(PHP_EOL, $fileContent) . PHP_EOL);
    }
    /**
     * Supprime toutes les lignes d'un fichier contenant au moins un des morceaux de texte spécifiés.
     *
     * @param string $filePath     Le chemin vers le fichier à modifier.
     * @param array  $searchTerms  Un tableau de chaînes. Chaque élément est un morceau de texte à rechercher.
     *                              Si une ligne contient un de ces morceaux, elle sera supprimée.
     *
     * @return bool  Retourne true si l'opération de réécriture du fichier a réussi, false sinon
     *               (ex. : si le fichier n'existe pas ou en cas d'erreur d'écriture).
     */
    public static function removeLinesFromFile(string $filePath, array $searchTerms): bool
    {
        if (!file_exists($filePath)) {
            return false; // Fichier inexistant
        }

        $lines = file($filePath);
        $newContent = '';

        foreach ($lines as $line) {
            $shouldRemove = false;

            foreach ($searchTerms as $term) {
                if (stripos($line, $term) !== false) {
                    $shouldRemove = true;
                    break; // On peut sortir dès qu'un terme est trouvé
                }
            }

            if (!$shouldRemove) {
                $newContent .= $line;
            }
        }

        return file_put_contents($filePath, $newContent) !== false;
    }

    /**
     * Renomme un fichier en utilisant un hash MD5 basé sur le nom et l'horodatage.
     *
     * @param string $type Le type de fichier (ex: 'product-image', 'log', etc.).
     * @param string $oldName Le nom du fichier existant.
     * @return string Le nouveau nom de fichier après le renommage.
     * @throws \Exception Si une erreur survient lors du renommage.
     */
    public static function renameFileWithHash(string $type, string $oldName): string
    {
        try {
            // Récupérer le chemin absolu du fichier
            $filePath = self::getPath($type, $oldName);

            if (!file_exists($filePath)) {
                AppLog::warning("Fichier introuvable : $filePath");
                exit;
            }

            // Extraire l'extension du fichier
            $pathInfo = pathinfo($filePath);
            $extension = $pathInfo['extension'];

            // Générer un nouveau nom unique avec un hash
            $newName = md5($oldName . time()) . '.' . $extension;
            $newFilePath = $pathInfo['dirname'] . '/' . $newName;

            // Renommer le fichier
            if (!rename($filePath, $newFilePath)) {
                AppLog::warning("Erreur lors du renommage du fichier : $filePath");
                exit;
            }

            AppLog::info("Fichier renommé : $oldName -> $newName");

            return $newName;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors du renommage du fichier $oldName : " . $e->getMessage());
            return false;
        }
    }



    /**
     * Inclure un fichier avec des variables dynamiques.
     */
    public static function includeFile(string $type, string $name, array $data = []): void
    {
        try {
            $filePath = self::getPath($type, $name);

            if (is_readable($filePath)) {
                // Extraire les variables pour les rendre disponibles dans le fichier inclus
                extract($data);
                include $filePath;
            } else {
                $errorMessage = "Fichier non lisible : $filePath";
                AppLog::error($errorMessage); // Ajout du log d'erreur
                AppLog::warning($errorMessage);
                exit;
            }
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'inclusion du fichier $name : " . $e->getMessage());
            exit;
        }
    }

    /**
     * Vérifier si un fichier existe.
     */
    public static function fileExists($type, $name): bool
    {
        if ($type === null || $name === null) {
            return false;
        }
        try {
            $filePath = self::getPath($type, $name);
            return file_exists($filePath);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la vérification de l'existence du fichier $name : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Écrire dans un fichier (création si non existant).
     */
    public static function writeFile(string $type, string $name, string $content)
    {
        try {
            $filePath = self::getPath($type, $name);
            $dirPath = dirname($filePath);
            // Vérifier si le répertoire existe, sinon le créer
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true) && !is_dir($dirPath)) {
                    $errorMessage = "Impossible de créer le répertoire : $dirPath";
                    AppLog::error($errorMessage); // Ajout du log d'erreur
                    AppLog::warning($errorMessage);
                    return false;
                }
            }

            if (!is_writable($dirPath)) {
                $errorMessage = "Le répertoire n'est pas accessible en écriture : $dirPath";
                AppLog::error($errorMessage); // Ajout du log d'erreur
                AppLog::warning($errorMessage);
                return false;
            }

            file_put_contents($filePath, $content);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'écriture dans le fichier $name : " . $e->getMessage());
            return false;
        }
    }
    public static function writeFileA(string $type, string $name, string $content): bool
    {
        try {
            $filePath = self::getPath($type, $name);
            $dirPath = dirname($filePath);

            // Vérifier si le répertoire existe, sinon le créer
            if (!is_dir($dirPath)) {
                if (!mkdir($dirPath, 0755, true) && !is_dir($dirPath)) {
                    $errorMessage = "Impossible de créer le répertoire : $dirPath";
                    AppLog::error($errorMessage);
                    AppLog::warning($errorMessage);
                    return false;
                }
            }

            // Vérifier si le répertoire est accessible en écriture
            if (!is_writable($dirPath)) {
                $errorMessage = "Le répertoire n'est pas accessible en écriture : $dirPath";
                AppLog::error($errorMessage);
                AppLog::warning($errorMessage);
                return false;
            }

            // Écrire le contenu dans le fichier
            $result = file_put_contents($filePath, $content);

            if ($result === false) {
                AppLog::error("Échec de l'écriture dans le fichier : $filePath");
                return false;
            }

            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'écriture dans le fichier $name : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lire le contenu d'un fichier.
     */
    public static function readFile(string $type, string $name): string
    {
        try {
            $filePath = self::getPath($type, $name);

            if (!is_readable($filePath)) {
                $errorMessage = "Fichier non lisible : $filePath";
                AppLog::error($errorMessage); // Ajout du log d'erreur
                AppLog::warning($errorMessage);
                exit;
            }

            return file_get_contents($filePath);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la lecture du fichier $name : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un fichier.
     */
    public static function deleteFile(string $type, string $name): bool
    {
        try {
            $filePath = self::getPath($type, $name);

            if (!file_exists($filePath)) {
                $errorMessage = "Fichier introuvable : $filePath";
                AppLog::error($errorMessage);
                return false;
            }

            if (!unlink($filePath)) {
                $errorMessage = "Impossible de supprimer le fichier : $filePath";
                AppLog::error($errorMessage);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la suppression du fichier $name : " . $e->getMessage());
            return false;
        }
    }
    public static function lineContainsWord(string $filePath, string $word): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $file = fopen($filePath, 'r');
        if (!$file) {
            return false;
        }

        while (($line = fgets($file)) !== false) {
            if (stripos($line, $word) !== false) {
                fclose($file);
                return true;
            }
        }

        fclose($file);
        return false;
    }
}
