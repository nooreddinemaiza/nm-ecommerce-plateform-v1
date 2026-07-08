<?php

namespace Src\Middlewares;


class AuthMiddleware
{
    /**
     * Gère l'accès aux routes protégées.
     *
     * @param bool $isProtected Indique si la route est protégée.
     * @param int|null $userId L'ID de l'utilisateur connecté (null si non connecté).
     * @param callable|null $onUnauthorized Callback exécuté si l'accès est non autorisé.
     * @param callable|null $onAuthorized Callback exécuté si l'accès est autorisé.
     */
    public static function handle(
        bool $isProtected,
        ?int $userId,
        ?callable $onUnauthorized = null,
        ?callable $onAuthorized = null
    ): void {
        // Si la route n'est pas protégée, aucun contrôle n'est nécessaire
        if (!$isProtected) {
            if ($onAuthorized) {
                $onAuthorized();
            }
            return;
        }
        
        // Si l'utilisateur n'est pas authentifié, on bloque l'accès
        if (!$userId) {
            // AppLog::warning("Accès refusé à une route protégée. Utilisateur non authentifié.");
            if ($onUnauthorized) {
                $onUnauthorized(); // Exécuter un callback spécifique pour l'accès non autorisé
            } else {
                // Action par défaut si aucun callback n'est défini
                http_response_code(401);
                echo "Erreur : accès non autorisé.";
            }
            exit;
        } else {
            // L'utilisateur est authentifié, accès autorisé
            // AppLog::info("Accès autorisé à une route protégée pour l'utilisateur ID: $userId.");
            if ($onAuthorized) {
                $onAuthorized(); // Exécuter un callback spécifique pour l'accès autorisé
            }
        }
    }
}
