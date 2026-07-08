<?php

namespace Src\Controllers;

use Exception;
use Src\Helpers\AppLog;
use Src\Models\Section;

class SectionController
{
    private $model;

    public function __construct()
    {
        $this->model = new Section();
    }
    /**
     * Ajouter une nouvelle section
     *
     * @param int $modelId ID du modèle de section
     * @param string $page Nom de la page
     * @param array $data Données de la section
     * @return bool|int ID de la section créée ou false en cas d'erreur
     */
    public function add()
    {
        // Récupérer et décoder les données JSON
        $data = $_POST;
        if (!empty($data['model'] && !empty($data['page']))) {
            $modelId = $data['model'];
            $page = $data['page'];
            unset($data["page"], $data['model']);
            $data = $data['data'];
            $this->validateData($modelId, $data);
            // Préparer les données pour l'insertion
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
            $result = $this->model->getCustomSections($page);
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

            // Génération d'un nouvel ID unique
            $newId = 1;
            if (!empty($existingSections)) {
                $ids = array_column($existingSections, 'id');
                $newId = max($ids) + 1;
            }

            $sectionData = [
                'id' => (string) $newId,
                'model' => $modelId,
                'page' => $page,
                'data' => json_encode($data),
            ];

            // Logique pour insérer la nouvelle section au bon endroit
            $sectionCount = count($existingSections);

            if ($sectionCount <= 1) {
                // S'il n'y a pas de section ou une seule section, on ajoute simplement
                $existingSections[] = $sectionData;
            } else {
                // S'il y a déjà plus d'une section, on insère entre la première et la dernière
                // Créer un tableau temporaire pour réorganiser les sections
                $tempSections = [];

                // Ajouter la première section (header)
                $tempSections[] = $existingSections[0];

                // Ajouter la nouvelle section
                $tempSections[] = $sectionData;

                // Ajouter toutes les sections intermédiaires (si elles existent)
                for ($i = 1; $i < $sectionCount - 1; $i++) {
                    $tempSections[] = $existingSections[$i];
                }

                // Ajouter la dernière section (footer) si elle existe
                if ($sectionCount > 1) {
                    $tempSections[] = $existingSections[$sectionCount - 1];
                }

                $existingSections = $tempSections;
            }

            $jsonData = json_encode($existingSections);

            // Sauvegarde
            if ($this->updateSection($page, ['custom_sections' => $jsonData])) {
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
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Une erreur est survenue, actualiser la page et ressayer!"
            ]);
        }
    }
    /**
     * Afficher la liste des section d'une page
     */
    public function get()
    {
        if (!empty($_POST) && !empty($_POST['page'])) {
            $page = htmlspecialchars(trim($_POST['page']));
            $result = $this->model->getCustomSections($page);
            if ($result) {
                echo json_encode([
                    "success" => true,
                    "data" => json_decode($result[0]['data'], true)
                ]);
                exit;
            }
            echo json_encode([
                "success" => false,
            ]);
            exit;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Données introuvable pour la pages demandée!"
            ]);
            exit;
        }
    }
    /**
     * Afficher la liste des section d'une page
     */
    public function getSingle()
    {
        if (!empty($_POST) && !empty($_POST['page']) && !empty($_POST['id'])) {
            $page = htmlspecialchars(trim($_POST['page']));
            $id = htmlspecialchars(trim($_POST['id']));

            $result = $this->model->getCustomSections($page);
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

            // Rechercher la section par ID
            $foundSection = null;
            foreach ($sections as $section) {
                if ($section['id'] == $id) {
                    $foundSection = $section;
                    break;
                }
            }

            if (!$foundSection) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Section introuvable.'
                ]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $foundSection
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Données manquantes pour récupérer la section.'
            ]);
        }
    }
    public function updateSection($page, $data)
    {
        return $this->model->updateSection($page, $data);
    }

    /**
     * Mettre à jour une section existante
     */
    public function update()
    {
        if (!empty($_POST) && !empty($_POST['id']) && !empty($_POST['model']) && !empty($_POST['page']) && isset($_POST['data'])) {
            $sectionId = $_POST['id'];
            $modelId = (int)$_POST['model'];
            $page = $_POST['page'];
            $sectionData = is_array($_POST['data']) ? $_POST['data'] : json_decode($_POST['data'], true);

            // Valider les données selon le modèle
            $this->validateData($modelId, $sectionData);

            // Récupérer les sections existantes
            $result = $this->model->getCustomSections($page);

            $existingSections = [];
            if ($result && isset($result[0]['data'])) {
                $raw = trim($result[0]['data']);
                if (!empty($raw)) {
                    if ($raw[0] !== '[' || substr($raw, -1) !== ']') {
                        $raw = "[$raw]";
                    }
                    $existingSections = json_decode($raw, true) ?: [];
                }
            }

            // Rechercher et mettre à jour la section
            $updated = false;
            foreach ($existingSections as &$section) {
                if ($section['id'] == $sectionId) {
                    $section['model'] = $modelId;
                    $section['page'] = $page;
                    $section['data'] = json_encode($sectionData);  // Sérialiser les données pour stockage
                    $updated = true;
                    break;
                }
            }

            if (!$updated) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Section introuvable.'
                ]);
                exit;
            }

            // Sauvegarder les modifications
            $jsonData = json_encode($existingSections);
            if ($this->updateSection($page, ['custom_sections' => $jsonData])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Section mise à jour avec succès.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de la section.'
                ]);
            }
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Données manquantes pour la mise à jour de la section."
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
    /**
     * Valider les données selon le modèle
     * 
     * @param int $modelId ID du modèle
     * @param array $data Données à valider
     * @throws Exception Si les données sont invalides
     */
    private function validateData($modelId, $data)
    {
        switch ($modelId) {
            case 1: // Modèle de listes
                $this->validateModel1Data($data);
                break;
            case 2: // Modèle de contenu
                $this->validateModel2Data($data);
                break;
            case 3: // Modèle de features
                $this->validateModel3Data($data);
                break;
            case 4: // Modèle de galerie
                $this->validateModel4Data($data);
                break;
            case 5: // Modèle de galerie
                break;
            default:
                AppLog::warning("Modèle inconnu: $modelId");
                echo json_encode([
                    'success' => false,
                    'message' => "Modéle inconnu: $modelId"
                ]);
                exit;
        }
    }

    /**
     * Valider les données du modèle 1 (Listes)
     * 
     * @param array $data Données à valider
     */
    private function validateModel1Data($data)
    {
        if (!isset($data['lists']) || !is_array($data['lists'])) {
            echo json_encode([
                'success' => false,
                'message' => "Le format des données est invalide pour le modèle 1"
            ]);
            exit;
        }

        $listCount = count($data['lists']);
        if ($listCount < 2 || $listCount > 4) {
            echo json_encode([
                'success' => false,
                'message' => "Le nombre de listes doit être entre 2 et 4"
            ]);
            exit;
        }

        foreach ($data['lists'] as $list) {
            if (!isset($list['title']) || strlen($list['title']) > 50) {
                echo json_encode([
                    'success' => false,
                    'message' => "Le titre de la liste doit être défini et ne pas dépasser 50 caractères"
                ]);
                exit;
            }

            if (!isset($list['items']) || !is_array($list['items'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Les éléments de liste sont requis et doivent être un tableau"
                ]);
                exit;
            }

            $itemCount = count($list['items']);
            if ($itemCount < 2 || $itemCount > 5) {
                echo json_encode([
                    'success' => false,
                    'message' => "Le nombre d'éléments par liste doit être entre 2 et 5"
                ]);
                exit;
            }

            foreach ($list['items'] as $item) {
                if (!isset($item['text']) || strlen($item['text']) > 50) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Le texte de l'élément doit être défini et ne pas dépasser 50 caractères"
                    ]);
                    exit;
                }

                if (!isset($item['isLink'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => "Le type de l'élément (texte ou lien) doit être défini"
                    ]);
                    exit;
                }

                // Vérifie explicitement si c’est un lien (valeurs acceptées : true, "true", 1, "1")
                $isLink = $item['isLink'] === true || $item['isLink'] === "true" || $item['isLink'] === 1 || $item['isLink'] === "1";

                if ($isLink) {
                    if (!isset($item['url']) || empty($item['url'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => "L'URL est requise pour les éléments de type lien"
                        ]);
                        exit;
                    }
                }
            }
        }
    }


    /**
     * Valider les données du modèle 2 (Contenu)
     * 
     * @param array $data Données à valider
     * @return Message si les données sont invalides
     */
    private function validateModel2Data($data)
    {
        // Vérifier les titres et la description (obligatoires)
        if (!isset($data['smallTitle']) || empty($data['smallTitle'])) {
            echo json_encode([
                'success' => false,
                'message' => "Le petit titre est requis"
            ]);
            exit;
        }

        if (!isset($data['largeTitle']) || empty($data['largeTitle'])) {
            echo json_encode([
                'success' => false,
                'message' => "Le grand titre est requis"
            ]);
            exit;
        }

        if (!isset($data['description']) || empty($data['description'])) {
            echo json_encode([
                'success' => false,
                'message' => "La description est requise"
            ]);
            exit;
        }

        // Vérifier les longueurs maximales
        if (strlen($data['smallTitle']) > 50) {
            echo json_encode([
                'success' => false,
                'message' => "Le petit titre ne doit pas dépasser 50 caractères"
            ]);
            exit;
        }

        if (strlen($data['largeTitle']) > 100) {
            echo json_encode([
                'success' => false,
                'message' => "Le grand titre ne doit pas dépasser 100 caractères"
            ]);
            exit;
        }

        if (
            (!empty($data['linkUrl']) && empty($data['linkText'])) ||
            (!empty($data['linkText']) && empty($data['linkUrl'])) ||
            (isset($data['linkText']) && strlen($data['linkText']) > 50)
        ) {
            $message = "";

            if (!empty($data['linkUrl']) && empty($data['linkText'])) {
                $message = "Le texte du lien est requis";
            } elseif (!empty($data['linkText']) && empty($data['linkUrl'])) {
                $message = "Le URL est requis";
            } elseif (strlen($data['linkText']) > 50) {
                $message = "Le texte du lien ne doit pas dépasser 50 caractères";
            }

            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        }
    }
    /**
     * Valider les données du modèle 3 (Features avec icônes)
     * 
     * @param array $data Données à valider
     */
    private function validateModel3Data($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        if (!isset($data['title']) || empty($data['title'])) {
            echo json_encode([
                'success' => false,
                'message' => "Le titre principal est requis"
            ]);
            exit;
        }

        if (strlen($data['title']) > 100) {
            echo json_encode([
                'success' => false,
                'message' => "Le titre ne doit pas dépasser 100 caractères"
            ]);
            exit;
        }

        if (!isset($data['features']) || !is_array($data['features'])) {
            echo json_encode([
                'success' => false,
                'message' => "Les éléments sont requis et doivent être un tableau"
            ]);
            exit;
        }

        $featureCount = count($data['features']);
        if ($featureCount < 1 || $featureCount > 4) {
            echo json_encode([
                'success' => false,
                'message' => "Le nombre d'éléments doit être entre 1 et 4"
            ]);
            exit;
        }

        foreach ($data['features'] as $feature) {
            if (!isset($feature['title']) || empty($feature['title'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "Le titre de l'élément est requis"
                ]);
                exit;
            }

            if (strlen($feature['title']) > 50) {
                echo json_encode([
                    'success' => false,
                    'message' => "Le titre de l'élément ne doit pas dépasser 50 caractères"
                ]);
                exit;
            }

            if (!isset($feature['description']) || empty($feature['description'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "La description de l'élément est requise"
                ]);
                exit;
            }

            if (strlen($feature['description']) > 200) {
                echo json_encode([
                    'success' => false,
                    'message' => "La description de l'élément ne doit pas dépasser 200 caractères"
                ]);
                exit;
            }

            // L'icône est optionnelle, pas de validation obligatoire
        }
    }

    /**
     * Valider les données du modèle 4 (Galerie d'images)
     * 
     * @param array $data Données à valider
     */
    private function validateModel4Data($data)
    {
        if (!is_array($data)) {
            $data = json_decode($data, true);
        }
        if (!isset($data['title']) || empty($data['title'])) {
            echo json_encode([
                'success' => false,
                'message' => "Le titre de la galerie est requis"
            ]);
            exit;
        }

        if (strlen($data['title']) > 100) {
            echo json_encode([
                'success' => false,
                'message' => "Le titre ne doit pas dépasser 100 caractères"
            ]);
            exit;
        }

        if (!isset($data['description'])) {
            echo json_encode([
                'success' => false,
                'message' => "La description est requise"
            ]);
            exit;
        }

        if (strlen($data['description']) > 300) {
            echo json_encode([
                'success' => false,
                'message' => "La description ne doit pas dépasser 300 caractères"
            ]);
            exit;
        }

        if (!isset($data['images']) || !is_array($data['images'])) {
            echo json_encode([
                'success' => false,
                'message' => "Les images sont requises et doivent être un tableau"
            ]);
            exit;
        }

        $imageCount = count($data['images']);
        if ($imageCount < 1 || $imageCount > 6) {
            echo json_encode([
                'success' => false,
                'message' => "Le nombre d'images doit être entre 1 et 6"
            ]);
            exit;
        }

        foreach ($data['images'] as $image) {
            if (!isset($image['url']) || empty($image['url'])) {
                echo json_encode([
                    'success' => false,
                    'message' => "L'URL de l'image est requise"
                ]);
                exit;
            }

            // La légende est optionnelle, mais limitée si présente
            if (isset($image['caption']) && strlen($image['caption']) > 100) {
                echo json_encode([
                    'success' => false,
                    'message' => "La légende de l'image ne doit pas dépasser 100 caractères"
                ]);
                exit;
            }
        }
    }
    /**
     * Change l'ordre d'une section
     * 
     * @return void
     */
    public function reorderSection()
    {
        // Récupérer les données POST
        if (empty($_POST) || empty($_POST['page']) || empty($_POST['id']) || !isset($_POST['direction'])) {
            echo json_encode([
                "success" => false,
                "message" => "Données manquantes pour la réorganisation."
            ]);
            exit;
        }

        $page = htmlspecialchars(trim($_POST['page']));
        $sectionId = htmlspecialchars(trim($_POST['id']));
        $direction = htmlspecialchars(trim($_POST['direction'])); // 'up' ou 'down'

        // Récupérer les sections existantes
        $result = $this->model->getCustomSections($page);
        if (!$result || !isset($result[0]['data'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune section trouvée.'
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

        // Trouver l'index de la section à déplacer
        $currentIndex = -1;
        foreach ($sections as $index => $section) {
            if ($section['id'] == $sectionId) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === -1) {
            echo json_encode([
                'success' => false,
                'message' => 'Section introuvable.'
            ]);
            exit;
        }

        // Calculer le nouvel index en fonction de la direction
        $newIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        // Vérifier si le nouvel index est valide
        if ($newIndex < 0 || $newIndex >= count($sections)) {
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de déplacer la section dans cette direction.'
            ]);
            exit;
        }

        // Échanger les positions
        $temp = $sections[$currentIndex];
        $sections[$currentIndex] = $sections[$newIndex];
        $sections[$newIndex] = $temp;

        // Mettre à jour la base de données
        $jsonData = json_encode($sections);
        if ($this->updateSection($page, ['custom_sections' => $jsonData])) {
            echo json_encode([
                'success' => true,
                'message' => 'Ordre des sections mis à jour avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'ordre des sections.'
            ]);
        }
    }

    /**
     * Mettre à jour directement l'ordre complet des sections (drag and drop)
     */
    public function updateSectionsOrder()
    {
        // Récupérer et décoder les données JSON
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (empty($data) || empty($data['page']) || empty($data['sections'])) {
            echo json_encode([
                "success" => false,
                "message" => "Données manquantes pour la mise à jour de l'ordre."
            ]);
            exit;
        }

        $page = $data['page'];
        $newOrder = $data['sections']; // Tableau des IDs dans le nouvel ordre

        // Récupérer les sections existantes
        $result = $this->model->getCustomSections($page);
        if (!$result || !isset($result[0]['data'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Aucune section trouvée.'
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

        // Créer un tableau associatif pour un accès rapide par ID
        $sectionsById = [];
        foreach ($sections as $section) {
            $sectionsById[$section['id']] = $section;
        }

        // Créer le nouveau tableau de sections dans l'ordre spécifié
        $reorderedSections = [];
        foreach ($newOrder as $id) {
            if (isset($sectionsById[$id])) {
                $reorderedSections[] = $sectionsById[$id];
            }
        }

        // Vérifier que toutes les sections sont présentes
        if (count($reorderedSections) !== count($sections)) {
            echo json_encode([
                'success' => false,
                'message' => 'Certaines sections manquent dans le nouvel ordre.'
            ]);
            exit;
        }

        // Mettre à jour la base de données
        $jsonData = json_encode($reorderedSections);
        if ($this->updateSection($page, ['custom_sections' => $jsonData])) {
            echo json_encode([
                'success' => true,
                'message' => 'Ordre des sections mis à jour avec succès.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'ordre des sections.'
            ]);
        }
    }
}
