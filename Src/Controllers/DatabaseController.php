<?php

namespace Src\Controllers;

use Src\Models\DashDB;


class DatabaseController
{
    private $model;
    private $tables = [
        'logins' => 'logins',
        'contacts' => 'contacts',
        'orders' => 'orders',
    ];
    public function __construct()
    {
        $this->model = new DashDB();
    }
    public function get($table)
    {
        if ($table === '*') {
            $data = [];
            foreach ($this->tables as $k => $v) {
                $data[$k] = $this->model->getTable($v);
            }
            return $data;
        }
        if (in_array($table, $this->tables)) {
            return $this->model->getTable($table);
        } else {
            return null;
        }
    }
    public function delete()
    {
        $data = $_POST;

        // Validation des données
        foreach ($data as $key => $value) {
            $data[$key] = trim(htmlspecialchars($value));
        }
        // Vérification si 'id' et 'table' sont valides
        if (!isset($data['id']) || !isset($data['table']) || !in_array($data['table'], $this->tables)) {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue, Veuillez actualiser la page et réessayer!"
            ]);
            exit;
        }

        // Vérification et nettoyage de l'ID
        $ids = array_filter(array_map('intval', explode(',', $data['id']))); // Nettoie les IDs

        if (empty($ids)) {
            echo json_encode([
                "success" => false,
                "message" => "Aucun ID valide fourni."
            ]);
            exit;
        }

        // Appel au modèle pour supprimer les enregistrements
        $result = $this->model->delete($data['table'], $ids);

        if ($result) {
            echo json_encode([
                "success" => true,
                "message" => "Suppression réussie!"
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue lors de la suppression."
            ]);
        }
        exit;
    }
    public function update()
    {
        $data = [];
        // Validation des données
        foreach ($_POST['data'] as $key => $value) {
            $data[$key] = trim(htmlspecialchars($value));
        }
        // Vérification si 'id' et 'table' sont valides
        if (!isset($data['id']) || !isset($data['table']) || !in_array($data['table'], $this->tables)) {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue, Veuillez actualiser la page et réessayer!"
            ]);
            exit;
        }
        $dbline = $this->model->getLine($data['table'], intval($data['id']));
        if (!$dbline) {
            echo json_encode([
                "success" => false,
                "message" => "Aucune données correspond à la requête!"
            ]);
            exit;
        }
        $line = [];
        foreach ($dbline[0] as $key => $value) {
            if ($this->checkKeys($data['table'], $key) && isset($data[$key]) && $data[$key] != $value) {
                $line[$key] = $data[$key];
            }
        }
        if (!$line) {
            echo json_encode([
                "success" => false,
                "message" => "Pas de changement détécté!"
            ]);
            exit;
        }
        $line['id'] = $data['id'];
        $line['table'] = $data['table'];
        $result = $this->model->update($line);
        if ($result) {
            echo json_encode([
                "success" => true,
            ]);
            exit;
        }
    }
    public function add()
    {
        $data = [];
        // Validation des données
        foreach ($_POST['data'] as $key => $value) {
            $data[$key] = trim(htmlspecialchars($value));
        }
        // Vérification si 'id' et 'table' sont valides
        if (!isset($data['table']) || !in_array($data['table'], $this->tables)) {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue, Veuillez actualiser la page et réessayer!"
            ]);
            exit;
        }
        $line = [];
        $line['table'] = $data['table'];
        unset($data['table']);
        foreach ($data as $key => $value) {
            if ($this->checkKeys($line['table'], $key)) {
                $line[$key] = $data[$key];
            }
        }
        if (!$line) {
            echo json_encode([
                "success" => false,
                "message" => "Pas de changement détécté!"
            ]);
            exit;
        }
        $result = $this->model->add($line);
        if ($result) {
            echo json_encode([
                "success" => true,
            ]);
            exit;
        }
    }
    private function checkKeys(string $table, $key)
    {
        $ctr = false;
        switch ($table) {
            case "contacts": {
                    $keys = ['id', 'email', 'name', 'subject', 'message', 'ip_address', 'user_agent', 'sent_at'];
                    if (in_array($key, $keys)) {
                        $ctr = true;
                    }
                }
                break;
                case "logins": {
                        $keys = ['id', 'user_id', 'ip_address', 'attempts', 'status', 'last_attempt', 'user_agent'];
                        if (in_array($key, $keys)) {
                            $ctr = true;
                        }
                    }
                    break;
                    case "logins": {
                            $keys = ["id","customer_name","customer_email","customer_phone","order_date","status","printed","customer_city","customer_city_zip","customer_address","total_amount"];
                            if (in_array($key, $keys)) {
                                $ctr = true;
                            }
                        }
                        break;
        }
        return $ctr;
    }
}

