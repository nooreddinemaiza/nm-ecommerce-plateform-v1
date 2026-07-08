<?php

namespace Src\Controllers;

use Src\Models\Content;
use Src\Helpers\AppLog;

class ContentController
{
    private $model;

    public function __construct()
    {
        $this->model = new Content();
    }
    public function setBanner()
    {
        $data = [];
        foreach ($_POST as $k => $v) {
            $data[$k] = trim(htmlspecialchars($v));
        }
        $str = '{"titre1":"' . $data['bannerTitleSmall'] . '","titre2":"' . $data['bannerTitleBig'] . '","description":"' . $data['bannerDescription'] . '","productid":"' . $data['productId'] . '"}';
        if ($this->model->setBanner($str)) {
            echo json_encode(['success' => true, 'message' => "Banniere modifiés avec succé!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Une erreur est survenu"]);
        }
    }
    public function setData()
    {
        $data = [];
        foreach ($_POST as $k => $v) {
            $data[$k] = ($k !== "map") ? trim(htmlspecialchars($v)) : $v;
        }
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($this->model->setData(['page' => 'contact', 'data' => $jsonData])) {
            echo json_encode(['success' => true, 'message' => "Modification avec succès !"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Une erreur est survenue"]);
        }
    }
    public function setMeta()
    {
        $data = [];
        foreach ($_POST as $k => $v) {
            $data[$k] = trim(htmlspecialchars($v));
        }
        if ($this->model->setMeta($data)) {
            echo json_encode(['success' => true, 'message' => "Meta modifiés avec succé!"]);
        } else {
            echo json_encode(['success' => false, 'message' => "Une erreur est survenu"]);
        }
    }
    public function getCustomSection()
    {
        if (empty($_POST['page'])) {
            echo json_encode([
                'success' => false,
                'message' => 'La page demandé est introuvable'
            ]);
            exit;
        }
        $page = $_POST['page'];
        $result = $this->model->getCustomSections($page);
        echo json_encode([
            'success' => $result ? true : false,
            'sections'    => json_decode($result[0]['data'], true)
        ]);
        exit;
    }
    //Sections start
    public function addCustomSection()
    {
        $data = $_POST;

        // Nettoyage et validation
        foreach ($data as $key => $value) {
            $data[$key] = trim(htmlentities($value));
        }

        if (!isset($data['page'], $data['title'], $data['details'], $data['link'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Données incomplètes.'
            ]);
            exit;
        }

        $result = $this->model->getCustomSections($data['page']);
        $existingSections = [];

        if ($result && isset($result[0]['data'])) {
            $raw = trim($result[0]['data']);
            if ($raw[0] !== '[' || substr($raw, -1) !== ']') {
                $raw = "[$raw]";
            }

            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $existingSections = $decoded;
            }
        }

        // Génération d’un nouvel ID unique
        $newId = 1;
        if (!empty($existingSections)) {
            $ids = array_column($existingSections, 'id');
            $newId = max($ids) + 1;
        }

        // Nouvelle section
        $newSection = [
            'id' => (string) $newId,
            'title' => $data['title'],
            'details' => $data['details'],
            'link' => $data['link']
        ];

        $existingSections[] = $newSection;
        $jsonData = json_encode($existingSections);

        // Sauvegarde
        if ($this->updateSection($data['page'], ['custom_sections' => $jsonData])) {
            echo json_encode([
                'success' => true,
                'message' => 'Section ajoutée avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la section.'
            ]);
        }
    }

    public function editCustomSection()
    {
        $data = $_POST;

        // Nettoyage des données
        foreach ($data as $key => $value) {
            $data[$key] = trim(htmlentities($value));
        }

        // Validation des champs requis
        $requiredFields = ['page', 'id', 'title', 'details', 'link'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Champ manquant : $field"
                ]);
                exit;
            }
        }

        // Récupération des données existantes
        $result = $this->model->getCustomSections($data['page']);
        if (!$result || !isset($result[0]['data'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune donnée trouvée pour cette page.'
            ]);
            exit;
        }

        $raw = trim($result[0]['data']);

        // Encadrer si besoin avec []
        if ($raw[0] !== '[' || substr($raw, -1) !== ']') {
            $raw = "[$raw]";
        }

        $sections = json_decode($raw, true);
        if (!is_array($sections)) {
            echo json_encode([
                'success' => false,
                'message' => 'Les données existantes sont invalides.'
            ]);
            exit;
        }

        $found = false;
        $updatedSections = [];

        foreach ($sections as $section) {
            if ($section['id'] == $data['id']) {
                // Mise à jour de la section ciblée
                $section['title'] = $data['title'];
                $section['details'] = $data['details'];
                $section['link'] = $data['link'];
                $found = true;
            }
            $updatedSections[] = $section;
        }

        if (!$found) {
            AppLog::warning("La section avec l'id {$data['id']} n'a pas pu être modifiée car elle est introuvable.");
            echo json_encode([
                'success' => false,
                'message' => 'Section introuvable.'
            ]);
            exit;
        }

        $json = json_encode($updatedSections);

        // Mise à jour en base de données
        if ($this->updateSection($data['page'], ['custom_sections' => $json])) {
            echo json_encode([
                'success' => true,
                'message' => 'Section modifiée avec succès !'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la section.'
            ]);
        }
    }
    public function deleteCustomSection()
    {
        $data = $_POST;

        // Nettoyage et validation
        $data['id'] = isset($data['id']) ? trim($data['id']) : null;
        $data['page'] = isset($data['page']) ? trim($data['page']) : null;

        if (!$data['id'] || !$data['page']) {
            echo json_encode([
                'success' => false,
                'message' => 'ID ou page manquant.'
            ]);
            exit;
        }

        // Récupération des sections existantes
        $result = $this->model->getCustomSections($data['page']);
        if (!$result || !isset($result[0]['data'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune donnée trouvée.'
            ]);
            exit;
        }

        $raw = trim($result[0]['data']);
        if ($raw[0] !== '[' || substr($raw, -1) !== ']') {
            $raw = "[$raw]";
        }

        $sections = json_decode($raw, true);
        if (!is_array($sections)) {
            echo json_encode([
                'success' => false,
                'message' => 'Les données de section sont invalides.'
            ]);
            exit;
        }

        // Supprimer la section par id
        $filtered = array_filter($sections, function ($s) use ($data) {
            return $s['id'] != $data['id'];
        });

        // Vérifie si suppression effective
        if (count($sections) === count($filtered)) {
            echo json_encode([
                'success' => false,
                'message' => 'Section introuvable.'
            ]);
            AppLog::warning("Tentative de suppression échouée : section ID {$data['id']} inexistante.");
            exit;
        }

        $json = json_encode(array_values($filtered)); // Réindexer

        // Mise à jour de la base
        if ($this->updateSection($data['page'], ['custom_sections' => $json])) {
            echo json_encode([
                'success' => true,
                'message' => 'Section supprimée avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour.'
            ]);
        }
    }

    public function updateSection($page, $data)
    {
        return $this->model->updateSection($page, $data);
    }
    //Sections End

    // Afficher tous les contenus
    public function index()
    {
        try {
            $content = $this->model->getAllContent();
            return $content;
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la récupération des contenus : " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la récupération des contenus.'], 500);
        }
    }
    public function getHome()
    {
        $content = $this->model->getHome();
        return $content;
    }
    public function getContact()
    {
        return  $this->model->getContact();
    }
    public function getShop()
    {
        return  $this->model->getShop();
    }
    public function getDevis()
    {
        return  "devis";
    }
    // Ajouter un contenu
    public function store($data)
    {
        try {
            $this->model->createContent($data);
            return $this->jsonResponse(['success' => true, 'message' => 'Contenu ajouté avec succès.']);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de l'ajout du contenu : " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'ajout du contenu.'], 500);
        }
    }

    // Mettre à jour un contenu
    public function update($id, $data)
    {
        try {
            $this->model->updateContent($id, $data);
            return $this->jsonResponse(['success' => true, 'message' => 'Contenu mis à jour avec succès.']);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la mise à jour du contenu : " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la mise à jour.'], 500);
        }
    }

    // Supprimer un contenu
    public function delete($id)
    {
        try {
            $this->model->deleteContent($id);
            return $this->jsonResponse(['success' => true, 'message' => 'Contenu supprimé avec succès.']);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la suppression du contenu : " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la suppression.'], 500);
        }
    }

    // Récupérer un contenu spécifique
    public function show($id)
    {
        try {
            $content = $this->model->getContentById($id);
            return $this->jsonResponse(['success' => true, 'data' => $content]);
        } catch (\Exception $e) {
            AppLog::error("Erreur lors de la récupération du contenu : " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la récupération du contenu.'], 500);
        }
    }

    // Fonction pour générer une réponse JSON
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        return json_encode($data);
    }
}
