<?php

namespace Src\Controllers;

use Exception;
use Src\Helpers\AppLog;
use Src\Services\Route;
use Src\Helpers\SessionManager;
use Src\Helpers\FileAndPathManager;

class ProtectedAssetController
{
    private $sessionManager;
    private $mimeTypes;

    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        $this->mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            // Ajoutez d'autres types MIME si nécessaire
        ];
    }

    public function serveFile($type, $file)
    {
        try {
            // Vérifier le token d'accès
            $this->checkAccessToken($type, $file);

            // Vérifier si le fichier existe
            $filePath = $this->getFilePath($type, $file);

            // Déterminer le type MIME
            $mimeType = $this->getMimeType($filePath);

            // Envoyer le bon en-tête de type MIME
            header("Content-Type: $mimeType");

            // Ajouter des en-têtes de cache
            $this->setCacheHeaders();
            // Servir le fichier
            readfile($filePath);
        } catch (Exception $e) {
            // Gérer les erreurs
            $this->handleError($e, $type, $file);
        }
    }

    private function checkAccessToken($type, $file)
    {
        $token = $_GET['token'] ?? '';
        if (!$this->sessionManager->validateFileAccessToken($token)) {
            throw new Exception("Token invalide ou expiré pour l'accès au fichier : $type/$file", 403);
        }
    }

    private function getFilePath($type, $file)
    {
        if (!FileAndPathManager::fileExists('protected_asset', "$type/$file")) {
            throw new Exception("Fichier protégé introuvable : $type/$file", 404);
        }
        return FileAndPathManager::getPath('protected_asset', "$type/$file");
    }

    private function getMimeType($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return $this->mimeTypes[$extension] ?? 'application/octet-stream';
    }

    private function setCacheHeaders()
    {
        header("Cache-Control: public, max-age=86400"); // Cache de 24 heures
        header("Pragma: cache");
    }

    private function handleError(Exception $e, $type, $file)
    {
        http_response_code($e->getCode());
        AppLog::error($e->getMessage());
        // Rediriger vers une page d'erreur ou afficher un message d'erreur approprié
        Route::redirect('/500/');
    }
}
