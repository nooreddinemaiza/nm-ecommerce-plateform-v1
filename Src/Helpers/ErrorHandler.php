<?php

namespace Src\Helpers;

class ErrorHandler
{
    // Propriétés pour les messages d'erreur
    private $errors = [];
    //Exemple d'erreurs probables
    private $error_sample;
    // Méthode pour ajouter une erreur
    public function addError($type, $message = null)
    {
        $this->error_sample = require dirname(str_replace("\\", "/", dirname(__DIR__))) . '/config/errors_sample.php';
        if (array_key_exists($type, $this->error_sample)) {
            $this->errors[] = [
                'type' => $type,
                'message' => $this->error_sample[$type],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            $this->logError($type, '');
        } else {
            $this->errors[] = [
                'type' => $type,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            $this->logError($type, $message);
        }
    }
    // Méthode pour afficher les erreurs
    public function displayErrors()
    {
        if (empty($this->errors)) {
            return;
        }
        foreach ($this->errors as $error) {
            echo '<p><strong>' . htmlspecialchars($error['type']) . ':</strong> ' . htmlspecialchars($error['message'] ?? "'Null'") . ' (à ' . $error['timestamp'] . ')</p>';
        }
    }
    public function getSampleError($type)
    {
        require dirname(str_replace("\\", "/", dirname(__DIR__))) . '/config/errors_sample.php';
        $this->error_sample =  $tab;
        if (array_key_exists($type, $this->error_sample)) {
            return trim($this->error_sample[$type]);
        }
        return "Verifier les champs!";
    }
    // Méthode pour enregistrer l'erreur dans un fichier de log
    private function logError($type, $message)
    {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($type) . '] ' . $message . PHP_EOL;
        file_put_contents(
            dirname(str_replace("\\", "/", dirname(__DIR__))) . '/config/error.log',
            $logMessage,
            FILE_APPEND
        );
    }
    // Méthode pour gérer les erreurs de validation
    public function addValidationError($field, $message)
    {
        $this->addError('Validation Error', "Erreur sur le champ '$field': $message");
    }

    // Méthode pour gérer les erreurs de base de données
    public function addDatabaseError($message)
    {
        $this->addError('Database Error', $message);
    }

    // Méthode pour gérer les erreurs systèmes
    public function addSystemError($message)
    {
        $this->addError('System Error', $message);
    }

    // Méthode pour afficher les erreurs sous forme de JSON (utile pour les API)
    public function displayJsonErrors()
    {
        echo json_encode($this->errors);
    }
}
