<?php

namespace Src\Helpers;

use Src\Models\Section;

class SectionRenderer
{
    private $model;
    private $renderedSections = [];
    private $page;
    private $currentRenderIndex = 0;
    private $sectionsCache = [];

    public function __construct(?string $page = null)
    {
        $this->model = new Section();
        $this->page = $page;
    }

    public function setPage(string $page)
    {
        $this->page = $page;
        $this->resetSequentialRendering();
        $this->sectionsCache = [];
    }

    ####################################################################################
    /**
     * Récupère la section footer si elle existe
     */
    private function getFooterSection(string $pageName)
    {
        $sections = $this->getAllSections($pageName);
        if (empty($sections)) {
            return null;
        }

        // Si la page n'a qu'une seule section, elle est considérée comme footer
        if (count($sections) === 1) {
            return reset($sections);
        }

        // Sinon, uniquement la page "home" peut avoir un footer (dernière section)
        if ($pageName === 'home') {
            return end($sections);
        }

        return null;
    }

    /**
     * Récupère la section header (première section) d'une page
     */
    private function getHeaderSection(string $pageName)
    {
        $sections = $this->getAllSections($pageName);
        if (empty($sections)) {
            return null;
        }

        // Si la page n'a qu'une seule section, elle est considérée comme footer et non header
        if (count($sections) === 1) {
            return null;
        }

        // Retourne la première section comme header
        return reset($sections);
    }

    /**
     * Récupère toutes les sections d'une page
     */
    public function getAllSections(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return [];

        if (isset($this->sectionsCache[$pageName])) {
            return $this->sectionsCache[$pageName];
        }

        $result = $this->model->getCustomSections($pageName);
        if (!$result || empty($result) || !isset($result[0]['data'])) {
            return $this->sectionsCache[$pageName] = [];
        }

        $raw = trim($result[0]['data']);
        if (empty($raw)) return $this->sectionsCache[$pageName] = [];

        if ($raw[0] !== '[' || substr($raw, -1) !== ']') {
            $raw = "[$raw]";
        }

        $sections = json_decode($raw, true) ?: [];
        return $this->sectionsCache[$pageName] = $sections;
    }

    /**
     * Affiche une section spécifique par son ID
     */
    public function renderSection(string $sectionId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $sections = $this->getAllSections($pageName);
        $section = null;

        foreach ($sections as $s) {
            if ($s['id'] == $sectionId) {
                $section = $s;
                break;
            }
        }

        if (!$section) return $this->renderError("Section $sectionId non trouvée");

        // Vérification section footer
        $footerSection = $this->getFooterSection($pageName);
        if ($footerSection && $section['id'] === $footerSection['id']) {
            return $this->renderError("Section $sectionId ne peut être affichée que via footer()");
        }

        // Vérification section header
        $headerSection = $this->getHeaderSection($pageName);
        if ($headerSection && $section['id'] === $headerSection['id']) {
            return $this->renderError("Section $sectionId ne peut être affichée que via header()");
        }

        $this->renderedSections[] = $section['id'];
        return $this->renderSectionContent($section);
    }

    /**
     * Affiche les sections dans l'ordre, à l'exception du header et du footer
     */
    public function renderInOrder(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return '';

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        while ($this->currentRenderIndex < count($sections)) {
            $section = $sections[$this->currentRenderIndex];
            $this->currentRenderIndex++;

            // Skip header and footer sections
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            if (!in_array($section['id'], $this->renderedSections)) {
                $this->renderedSections[] = $section['id'];
                return $this->renderSectionContent($section);
            }
        }

        return '';
    }

    /**
     * Affiche la section footer (dernière section uniquement pour la page home)
     */
    public function footer(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $footerSection = $this->getFooterSection($pageName);
        if (!$footerSection) return '';

        if (in_array($footerSection['id'], $this->renderedSections)) return '';

        $this->renderedSections[] = $footerSection['id'];
        return $this->renderSectionContent($footerSection);
    }

    /**
     * Nouvelle fonction: Affiche la section header (première section)
     */
    public function header(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $headerSection = $this->getHeaderSection($pageName);
        if (!$headerSection) return '';

        if (in_array($headerSection['id'], $this->renderedSections)) return '';

        $this->renderedSections[] = $headerSection['id'];
        return $this->renderSectionContent($headerSection);
    }

    /**
     * Vérifie si la page a une seule section
     */
    public function hasSingleSection(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return false;

        return count($this->getAllSections($pageName)) === 1;
    }

    /**
     * Réinitialise le rendu séquentiel
     */
    public function resetSequentialRendering()
    {
        $this->currentRenderIndex = 0;
    }

    /**
     * Affiche toutes les sections non rendues, à l'exception du header et du footer
     */
    public function renderAllUnrenderedSections(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return '';

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $output = '';
        foreach ($sections as $section) {
            // Skip header and footer sections
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            if (!in_array($section['id'], $this->renderedSections)) {
                $output .= $this->renderSectionContent($section);
                $this->renderedSections[] = $section['id'];
            }
        }

        return $output;
    }

    /**
     * Affiche toutes les sections, à l'exception du header et du footer
     */
    public function renderAllSections(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $this->renderedSections = [];
        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return '';

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $output = '';
        foreach ($sections as $section) {
            // Skip header and footer sections
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            $output .= $this->renderSectionContent($section);
            $this->renderedSections[] = $section['id'];
        }

        return $output;
    }
    /**
     * Récupère la première section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return array|null La première section du modèle ou null si non trouvée
     */
    public function getFirstSectionByModel(string $modelId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return null;

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        foreach ($sections as $section) {
            // Ignorer header et footer
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            // Vérifier si c'est le modèle recherché
            if ($section['model'] == $modelId) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Récupère la dernière section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return array|null La dernière section du modèle ou null si non trouvée
     */
    public function getLastSectionByModel(string $modelId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return null;

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $lastSection = null;

        foreach ($sections as $section) {
            // Ignorer header et footer
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            // Vérifier si c'est le modèle recherché
            if ($section['model'] == $modelId) {
                $lastSection = $section;
            }
        }

        return $lastSection;
    }

    /**
     * Récupère toutes les sections d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return array Les sections du modèle spécifié
     */
    public function getSectionsByModel(string $modelId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return [];

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return [];

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $modelSections = [];

        foreach ($sections as $section) {
            // Ignorer header et footer
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            // Vérifier si c'est le modèle recherché
            if ($section['model'] == $modelId) {
                $modelSections[] = $section;
            }
        }

        return $modelSections;
    }

    /**
     * Récupère la Nième section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param int $index Position de la section à récupérer (commençant par 0)
     * @param string $page Nom de la page (optionnel)
     * @return array|null La section à la position spécifiée ou null si non trouvée
     */
    public function getNthSectionByModel(string $modelId, int $index, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $modelSections = $this->getSectionsByModel($modelId, $pageName);

        if (isset($modelSections[$index])) {
            return $modelSections[$index];
        }

        return null;
    }

    /**
     * Rend la première section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de la section ou message d'erreur
     */
    public function renderFirstSectionByModel(string $modelId, ?string $page = null)
    {
        $section = $this->getFirstSectionByModel($modelId, $page);

        if (!$section) {
            return $this->renderError("Aucune section du modèle $modelId trouvée");
        }

        if (!in_array($section['id'], $this->renderedSections)) {
            $this->renderedSections[] = $section['id'];
            return $this->renderSectionContent($section);
        }

        return '';
    }

    /**
     * Rend la dernière section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de la section ou message d'erreur
     */
    public function renderLastSectionByModel(string $modelId, ?string $page = null)
    {
        $section = $this->getLastSectionByModel($modelId, $page);

        if (!$section) {
            return $this->renderError("Aucune section du modèle $modelId trouvée");
        }

        if (!in_array($section['id'], $this->renderedSections)) {
            $this->renderedSections[] = $section['id'];
            return $this->renderSectionContent($section);
        }

        return '';
    }

    /**
     * Rend la Nième section d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param int $index Position de la section à rendre (commençant par 0)
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de la section ou message d'erreur
     */
    public function renderNthSectionByModel(string $modelId, int $index, ?string $page = null)
    {
        $section = $this->getNthSectionByModel($modelId, $index, $page);

        if (!$section) {
            return $this->renderError("Section #$index du modèle $modelId non trouvée");
        }

        if (!in_array($section['id'], $this->renderedSections)) {
            $this->renderedSections[] = $section['id'];
            return $this->renderSectionContent($section);
        }

        return '';
    }

    /**
     * Rend toutes les sections d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à rechercher
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de toutes les sections du modèle
     */
    public function renderAllSectionsByModel(string $modelId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return $this->renderError("Page non spécifiée");

        $modelSections = $this->getSectionsByModel($modelId, $pageName);
        if (empty($modelSections)) {
            return $this->renderError("Aucune section du modèle $modelId trouvée");
        }

        $output = '';
        foreach ($modelSections as $section) {
            if (!in_array($section['id'], $this->renderedSections)) {
                $output .= $this->renderSectionContent($section);
                $this->renderedSections[] = $section['id'];
            }
        }

        return $output;
    }

    /**
     * Compte le nombre de sections d'un modèle spécifique (excluant header/footer)
     * 
     * @param string $modelId ID du modèle à compter
     * @param string $page Nom de la page (optionnel)
     * @return int Nombre de sections du modèle spécifié
     */
    public function countSectionsByModel(string $modelId, ?string $page = null)
    {
        return count($this->getSectionsByModel($modelId, $page));
    }

    /**
     * Vérifie si un modèle spécifique est utilisé dans la page
     * 
     * @param string $modelId ID du modèle à vérifier
     * @param string $page Nom de la page (optionnel)
     * @return bool True si le modèle est utilisé, false sinon
     */
    public function hasModelInPage(string $modelId, ?string $page = null)
    {
        return $this->countSectionsByModel($modelId, $page) > 0;
    }

    /**
     * Récupère la section précédant une section spécifique (excluant header/footer)
     * 
     * @param string $sectionId ID de la section de référence
     * @param string $page Nom de la page (optionnel)
     * @return array|null La section précédente ou null si non trouvée
     */
    public function getPreviousSection(string $sectionId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return null;

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $previousSection = null;

        foreach ($sections as $section) {
            // Si c'est la section cherchée, retourner la précédente
            if ($section['id'] == $sectionId) {
                return $previousSection;
            }

            // Ne considérer que les sections normales (pas header/footer)
            if ($section['id'] !== $footerId && $section['id'] !== $headerId) {
                $previousSection = $section;
            }
        }

        return null;
    }

    /**
     * Récupère la section suivant une section spécifique (excluant header/footer)
     * 
     * @param string $sectionId ID de la section de référence
     * @param string $page Nom de la page (optionnel)
     * @return array|null La section suivante ou null si non trouvée
     */
    public function getNextSection(string $sectionId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return null;

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $foundCurrent = false;

        foreach ($sections as $section) {
            // Ignorer header et footer
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            // Si on a trouvé la section précédente, celle-ci est la suivante
            if ($foundCurrent) {
                return $section;
            }

            // Marquer qu'on a trouvé la section courante
            if ($section['id'] == $sectionId) {
                $foundCurrent = true;
            }
        }

        return null;
    }

    /**
     * Récupère toutes les sections normales (excluant header/footer)
     * 
     * @param string $page Nom de la page (optionnel)
     * @return array Liste des sections normales
     */
    public function getNormalSections(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return [];

        $sections = $this->getAllSections($pageName);
        if (empty($sections)) return [];

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        $normalSections = [];

        foreach ($sections as $section) {
            // Ignorer header et footer
            if ($section['id'] === $footerId || $section['id'] === $headerId) continue;

            $normalSections[] = $section;
        }

        return $normalSections;
    }

    /**
     * Récupère toutes les sections par modèle, organisées par type de modèle
     * 
     * @param string $page Nom de la page (optionnel)
     * @return array Sections organisées par modèle
     */
    public function getSectionsByModelType(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return [];

        $normalSections = $this->getNormalSections($pageName);
        if (empty($normalSections)) return [];

        $sectionsByModel = [];

        foreach ($normalSections as $section) {
            $modelId = $section['model'];
            if (!isset($sectionsByModel[$modelId])) {
                $sectionsByModel[$modelId] = [];
            }
            $sectionsByModel[$modelId][] = $section;
        }

        return $sectionsByModel;
    }

    /**
     * Vérifie si une section peut être rendue (si elle n'est pas header/footer)
     * 
     * @param string $sectionId ID de la section à vérifier
     * @param string $page Nom de la page (optionnel)
     * @return bool True si la section peut être rendue normalement
     */
    public function isNormalSection(string $sectionId, ?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return false;

        $footerSection = $this->getFooterSection($pageName);
        $footerId = $footerSection['id'] ?? null;

        $headerSection = $this->getHeaderSection($pageName);
        $headerId = $headerSection['id'] ?? null;

        return $sectionId !== $footerId && $sectionId !== $headerId;
    }
    /**
     * Récupère la première section normale d'une page (excluant header/footer)
     * 
     * @param string $page Nom de la page (optionnel)
     * @return array|null La première section normale ou null si non trouvée
     */
    public function getFirstNormalSection(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $normalSections = $this->getNormalSections($pageName);

        return !empty($normalSections) ? reset($normalSections) : null;
    }

    /**
     * Récupère la dernière section normale d'une page (excluant header/footer)
     * 
     * @param string $page Nom de la page (optionnel)
     * @return array|null La dernière section normale ou null si non trouvée
     */
    public function getLastNormalSection(?string $page = null)
    {
        $pageName = $page ?? $this->page;
        if (!$pageName) return null;

        $normalSections = $this->getNormalSections($pageName);

        return !empty($normalSections) ? end($normalSections) : null;
    }

    /**
     * Rend la première section normale d'une page (excluant header/footer)
     * 
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de la section ou message d'erreur
     */
    public function renderFirstNormalSection(?string $page = null)
    {
        $section = $this->getFirstNormalSection($page);

        if (!$section) {
            return $this->renderError("Aucune section normale trouvée");
        }

        if (!in_array($section['id'], $this->renderedSections)) {
            $this->renderedSections[] = $section['id'];
            return $this->renderSectionContent($section);
        }

        return '';
    }

    /**
     * Rend la dernière section normale d'une page (excluant header/footer)
     * 
     * @param string $page Nom de la page (optionnel)
     * @return string Contenu rendu de la section ou message d'erreur
     */
    public function renderLastNormalSection(?string $page = null)
    {
        $section = $this->getLastNormalSection($page);

        if (!$section) {
            return $this->renderError("Aucune section normale trouvée");
        }

        if (!in_array($section['id'], $this->renderedSections)) {
            $this->renderedSections[] = $section['id'];
            return $this->renderSectionContent($section);
        }

        return '';
    }
    ##################################################################################
    private function renderModel1(array $data)
    {
        if (!isset($data['lists']) || !is_array($data['lists'])) {
            return $this->renderError("Format des données invalide pour le modèle 1");
        }

        $listCount = count($data['lists']);
        $colClass = 'col-lg-' . (12 / min(4, max(1, $listCount)));

        $output = '<div class="row">';

        foreach ($data['lists'] as $list) {
            $output .= '<div class="' . $colClass . ' mb-4">';
            $output .= '<div class="bg-white rounded-3 shadow-sm h-100 p-3">';
            $output .= '<h5 class="text-dark text-uppercase fw-semibold border-bottom pb-2 mb-3">' . htmlspecialchars($list['title']) . '</h5>';
            $output .= '<ul class="list-unstyled">';

            foreach ($list['items'] as $item) {
                $isLink = $item['isLink'] === true || $item['isLink'] === "true" || $item['isLink'] === 1 || $item['isLink'] === "1";

                if ($isLink && isset($item['url']) && !empty($item['url'])) {
                    $output .= '<li class="mb-2">';
                    $output .= '<a href="' . htmlspecialchars($item['url']) . '" class="text-decoration-none link-dark d-block fw-medium">';
                    $output .= '<i class="fas fa-angle-right me-2 text-primary"></i>' . htmlspecialchars($item['text']);
                    $output .= '</a></li>';
                } else {
                    $output .= '<li class="mb-2 text-muted"><i class="fas fa-circle me-2 small"></i>' . htmlspecialchars($item['text']) . '</li>';
                }
            }

            $output .= '</ul></div></div>';
        }

        $output .= '</div>';

        return $output;
    }

    private function renderModel2(array $data)
    {
        if (!isset($data['smallTitle']) || !isset($data['largeTitle']) || !isset($data['description'])) {
            return $this->renderError("Format des données invalide pour le modèle 2");
        }

        $hasLink = !empty($data['linkText']) && !empty($data['linkUrl']);

        // Vérification plus stricte de l'existence de l'image
        $hasImage = isset($data['imageUrl']) && !empty($data['imageUrl']) && $data['imageUrl'] !== null;
        $imageUrl = $hasImage ? htmlspecialchars($data['imageUrl']) : '';

        // Ajuster dynamiquement la classe de colonne en fonction de la présence d'image
        $textColClass = $hasImage ? 'col-lg-8' : 'col-12';

        $output = '<div class="row align-items-center g-4 mb-5">';

        // Texte principal
        $output .= '<div class="' . $textColClass . '">';
        $output .= '<div class="section-heading mb-3">';
        $output .= '<h6 class="text-uppercase fw-semibold mb-2">' . htmlspecialchars($data['smallTitle']) . '</h6>';
        $output .= '<h2 class="fw-bold mb-3 text-black">' . htmlspecialchars($data['largeTitle']) . '</h2>';
        $output .= '</div>';
        // Description bien visible
        $output .= '<p class="text-black-50 fs-5 mb-4">' . nl2br(htmlspecialchars($data['description'])) . '</p>';
        // Lien (si présent)
        if ($hasLink) {
            $output .= '<div class="text-primary" style="text-align: left;">
                            <a href="' . htmlspecialchars($data['linkUrl']) . ' " class="btn btn-primary rounded-pill px-4 py-2">';
            $output .= htmlspecialchars($data['linkText']) . '</a></div>';
        }
        $output .= '</div>'; // fin texte

        // Image (si présente)
        if ($hasImage) {
            $output .= '<div class="col-lg-4 main-button">';
            $output .= '<div class="rounded-4 overflow-hidden shadow-sm" style="height: 270px;width: 270px;">';
            $output .= $hasLink ? ('<a href="' . htmlspecialchars($data['linkUrl']).'" style="position: initial;width: 100% !important;line-height: initial;background: none;color: black;text-align: left;font-size: initial;border-radius: initial;">') : '';
            $output .= '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($data['largeTitle']) . '" class="img-fluid w-100" style="object-fit: cover; height: 270px;">';
            $output .= $hasLink ? '</a>' : '';
            $output .= '</div></div>';
        }

        $output .= '</div>'; // fin row
        return $output;
    }
    private function renderModel3(array $data)
    {
        if (!isset($data['title']) || !isset($data['features']) || !is_array($data['features'])) {
            return $this->renderError("Format des données invalide pour le modèle 3");
        }

        $output = '<div class="col-12 mb-5">';
        $output .= '<div class="text-center">';
        $output .= '<h4 class="fw-bold">' . htmlspecialchars($data['title']) . '</h4>';
        $output .= '</div></div>';

        foreach ($data['features'] as $feature) {
            if (!isset($feature['title']) || !isset($feature['description']) || !isset($feature['icon'])) {
                continue;
            }

            $output .= '<div class="col-lg-3 col-md-6 mb-4 d-flex">';
            $output .= '<div class="card w-100 border-0 shadow-sm hover-shadow transition rounded text-center p-4">';

            // Icon
            if (!empty($feature["icon"])) {
                $output .= '<div class="text-primary mb-3" style="font-size: 2.5rem;">';
                $output .= '<i class="' . htmlspecialchars($feature["icon"]) . '"></i>';
                $output .= '</div>';
            }

            // Title
            $output .= '<h5 class="fw-semibold mb-2">' . htmlspecialchars($feature['title']) . '</h5>';

            // Description
            $output .= '<p class="text-muted mb-0 small" style="line-height:initial">' . htmlspecialchars($feature['description']) . '</p>';

            $output .= '</div></div>';
        }

        return $output;
    }
    private function renderModel4(array $data)
    {
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['images']) || !is_array($data['images'])) {
            return $this->renderError("Format des données invalide pour le modèle 4");
        }

        // En-tête de section avec animation au défilement
        $output = '<div class="col-12 mb-5 wow fadeInUp" data-wow-delay="0.2s">';
        $output .= '<div class="section-heading text-center position-relative">';
        $output .= '<span class="decorative-line"></span>';
        $output .= '<h2 class="fw-bold text-primary position-relative display-5">' . htmlspecialchars($data['title']) . '</h2>';
        $output .= '<p class="lead text-muted mx-auto" style="max-width: 700px;">' . htmlspecialchars($data['description']) . '</p>';
        $output .= '</div></div>';

        // Galerie d'images avec effet masonry
        $output .= '<div class="col-12"><div class="row g-4 gallery-container">';

        $delay = 0.1;
        foreach ($data['images'] as $image) {
            if (!isset($image['url']) || !isset($image['caption'])) {
                continue;
            }

            $delay += 0.1;
            $output .= '<div class="col-lg-4 col-md-6 wow fadeIn" data-wow-delay="' . $delay . 's">';
            $output .= '<div class="card border-0 gallery-card h-100 transform-hover">';

            // Image avec effet hover amélioré
            $output .= '<div class="img-container position-relative overflow-hidden">';
            $output .= '<img src="' . htmlspecialchars($image['url']) . '" class="card-img-top w-100 h-100" 
                    alt="' . htmlspecialchars($image['caption']) . '" 
                    style="aspect-ratio: 4/3; object-fit: cover;">';
            $output .= '<div class="img-overlay"></div>';
            $output .= '</div>';

            // Légende avec design amélioré
            if (!empty($image['caption'])) {
                $output .= '<div class="card-body text-center p-3">';
                $output .= '<p class="card-text fw-medium mb-0">' . htmlspecialchars($image['caption']) . '</p>';
                $output .= '</div>';
            }

            $output .= '</div></div>';
        }

        $output .= '</div></div>';

        // Ajout du CSS personnalisé pour les effets
        $output .= '<style>
        .decorative-line {
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--bs-primary), transparent);
            margin: 0 auto 15px;
        }
        .transform-hover {
            transition: all 0.4s ease;
        }
        .transform-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .img-container {
            overflow: hidden;
            border-radius: 8px 8px 0 0;
        }
        .img-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.15), transparent);
            opacity: 0;
            transition: 0.3s;
        }
        .transform-hover:hover .img-overlay {
            opacity: 1;
        }
        .card-img-top {
            transition: transform 0.8s ease;
        }
        .transform-hover:hover .card-img-top {
            transform: scale(1.05);
        }
        .gallery-card {
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        </style>';

        return $output;
    }
    private function renderError($message)
    {
        AppLog::warning($message);
    }

    public function isSectionRendered($sectionId)
    {
        return in_array($sectionId, $this->renderedSections);
    }

    public function resetRenderedSections()
    {
        $this->renderedSections = [];
        $this->resetSequentialRendering();
    }

    private function wrapSection($content)
    {
        return <<<HTML
        <div class="section trending">
            <div class="container" style="padding: 0 2%;border-radius: 10px;">
                <div class="row">
                    $content
                </div>
            </div>
        </div>
        HTML;
    }

    private function renderSectionContent(array $section)
    {
        $modelId = (int)$section['model'];
        $data = is_array($section['data']) ? $section['data'] : json_decode($section['data'], true);

        $content = '';
        switch ($modelId) {
            case 1:
                $content = $this->renderModel1($data);
                break;
            case 2:
                $content = $this->renderModel2($data);
                break;
            case 3:
                $content = $this->renderModel3($data);
                break;
            case 4:
                $content = $this->renderModel4($data);
                break;
            case 5:
                break;
            default:
                $content = $this->renderError("Modèle inconnu: $modelId");
        }

        return $this->wrapSection($content);
    }
}
