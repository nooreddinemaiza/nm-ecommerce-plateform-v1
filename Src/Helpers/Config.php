<?php

namespace Src\Helpers;

class Config
{
    /**
     * Récupère une valeur depuis les variables d'environnement
     * 
     * @param string $key La clé de la variable d'environnement
     * @param mixed $default Valeur par défaut si la clé n'existe pas
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    /**
     * Vérifie si une clé existe dans les variables d'environnement
     * 
     * @param string $key La clé de la variable
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset($_ENV[$key]);
    }

    /**
     * Récupère toutes les variables d'environnement sous forme de tableau
     * 
     * @return array
     */
    public static function getAll(): array
    {
        return $_ENV;
    }

    /**
     * Définir une nouvelle variable dans l'environnement (utile pour des tests ou autres)
     * 
     * @param string $key La clé de la variable
     * @param string $value La valeur à définir
     */
    public static function set(string $key, string $value)
    {
        $_ENV[$key] = $value;
    }
}
