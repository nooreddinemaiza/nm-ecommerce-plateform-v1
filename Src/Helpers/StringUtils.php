<?php
namespace Src\Helpers;
class StringUtils {
    /**
     * Convertit la première lettre en majuscule
     */
    public static function capitalize(string $text): string {
        return ucfirst(mb_strtolower($text));
    }

    /**
     * Convertit tout le texte en majuscules
     */
    public static function toUpper(string $text): string {
        return mb_strtoupper($text);
    }

    /**
     * Convertit tout le texte en minuscules
     */
    public static function toLower(string $text): string {
        return mb_strtolower($text);
    }

    /**
     * Formate le texte en "camelCase"
     */
    public static function toCamelCase(string $text): string {
        $text = preg_replace('/[^a-zA-Z0-9]+/', ' ', $text);
        $words = array_map('ucfirst', explode(' ', mb_strtolower($text)));
        $words[0] = mb_strtolower($words[0]);
        return implode('', $words);
    }

    /**
     * Formate le texte en "snake_case"
     */
    public static function toSnakeCase(string $text): string {
        $text = preg_replace('/[^a-zA-Z0-9]+/', ' ', $text);
        return str_replace(' ', '_', mb_strtolower($text));
    }

    /**
     * Tronque le texte à une longueur donnée
     */
    public static function truncate(string $text, int $length, string $suffix = '...'): string {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Crée un extrait intelligent du texte
     * 
     * @param string $text Le texte source
     * @param int $length Longueur maximale de l'extrait (en caractères)
     * @param string $suffix Le suffixe à ajouter (par défaut '...')
     * @param bool $preserveWords Préserver les mots entiers (par défaut true)
     * @return string L'extrait formaté
     */
    public static function excerpt(string $text, int $length = 150, string $suffix = '...', bool $preserveWords = true): string {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $excerpt = mb_substr($text, 0, $length);
        if ($preserveWords) {
            $lastSpace = mb_strrpos($excerpt, ' ');
            if ($lastSpace !== false) {
                $excerpt = mb_substr($excerpt, 0, $lastSpace);
            }
        }
        $excerpt = rtrim($excerpt, '.,;:!?');
        return $excerpt . $suffix;
    }

    /**
     * Supprime les espaces en début et fin de chaîne et les espaces multiples
     */
    public static function clean(string $text): string {
        return preg_replace('/\s+/', ' ', trim($text));
    }

    /**
     * Entoure le texte avec des balises HTML
     */
    public static function wrap(string $text, string $tag): string {
        return "<{$tag}>" . htmlspecialchars($text) . "</{$tag}>";
    }

    /**
     * Génère un slug à partir du texte (URL-friendly)
     */
    public static function slug(string $text): string {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Inverse la chaîne de caractères
     */
    public static function reverse(string $text): string {
        return implode(array_reverse(mb_str_split($text)));
    }

    /**
     * Compte le nombre de mots
     */
    public static function wordCount(string $text): int {
        return str_word_count($text);
    }

    /**
     * Extrait les initiales d'une chaîne
     */
    public static function initials(string $text): string {
        $words = explode(' ', $text);
        return implode('', array_map(fn($word) => mb_substr($word, 0, 1), $words));
    }

    /**
     * Formate le texte en "Title Case"
     */
    public static function toTitleCase(string $text): string {
        return mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
    }

    /**
     * Vérifie si le texte contient un sous-texte
     */
    public static function contains(string $text, string $substring): bool {
        return mb_strpos($text, $substring) !== false;
    }

    /**
     * Vérifie si le texte commence par un préfixe donné
     */
    public static function startsWith(string $text, string $prefix): bool {
        return mb_substr($text, 0, mb_strlen($prefix)) === $prefix;
    }

    /**
     * Vérifie si le texte se termine par un suffixe donné
     */
    public static function endsWith(string $text, string $suffix): bool {
        return mb_substr($text, -mb_strlen($suffix)) === $suffix;
    }

    /**
     * Remplace les accents par leur version non accentuée
     */
    public static function replaceAccents($str) {
        // Tableau de remplacement des caractères accentués
        $accents = [
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
            'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
            'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
            'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
            'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u',
            'Ñ'=>'N', 'ñ'=>'n',
            'Ç'=>'C', 'ç'=>'c',
            'Ý'=>'Y', 'ý'=>'y', 'ÿ'=>'y',
            '&'=>'et'
        ];
        
        // Remplace les caractères accentués par leurs équivalents non accentués
        return strtr($str, $accents);
    }
    
    /**
     * Vérifie si le texte est un palindrome
     */
    public static function isPalindrome(string $text): bool {
        $text = mb_strtolower(preg_replace('/[^a-z0-9]/', '', $text));  // Normalisation
        $textArray = mb_str_split($text);  // Découpe la chaîne en tableau de caractères
        $reversedText = implode('', array_reverse($textArray));  // Inverse le tableau et recompose la chaîne
        return $text === $reversedText;  // Compare la chaîne originale et la chaîne inversée
    }

    /**
     * Supprime les caractères non alphanumériques, sauf l'espace
     */
    public static function sanitize(string $text): string {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    }
    /**
     * Ajoute un `#` au début de chaque mot de la chaîne.
     * Par exemple : "product launch sale" devient "#product #launch #sale"
     */
    public static function addHashtags(string $text): string {
        $words = explode(' ', $text);
        $tags = array_map(function($word) {
            // Ajouter un `#` au début de chaque mot, sauf s'il en a déjà un
            return (substr($word, 0, 1) === '#') ? $word : '#' . $word;
        }, $words);
        return implode(' ', $tags);
    }

    /**
     * Supprime les `#` du début de chaque mot dans la chaîne.
     * Par exemple : "#product #launch #sale" devient "product launch sale"
     */
    public static function removeHashtags(string $text): string {
        $words = explode(' ', $text);
        $tags = array_map(function($word) {
            // Supprimer le `#` s'il y en a un
            return (substr($word, 0, 1) === '#') ? substr($word, 1) : $word;
        }, $words);
        return implode(' ', $tags);
    }

    /**
     * Formate une chaîne de tags, en ajoutant un `#` si nécessaire.
     * Par exemple : "product launch" devient "#product #launch"
     */
    public static function formatTags(string $text): string {
        $words = explode(' ', $text);
        $tags = array_map(function($word) {
            // Ajouter un `#` uniquement si le mot ne commence pas déjà par un `#`
            return (substr($word, 0, 1) !== '#') ? '#' . $word : $word;
        }, $words);
        return implode(' ', $tags);
    }

    /**
     * Extrait tous les hashtags d'une chaîne.
     * Par exemple : "check out #product #launch #sale" retourne ["#product", "#launch", "#sale"]
     */
    public static function extractHashtags(string $text): array {
        preg_match_all('/#\w+/', $text, $matches);
        return $matches[0];  // Retourne les hashtags trouvés
    }
}