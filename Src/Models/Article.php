<?php

namespace Src\Models;

use Src\Database\Database;

class Article
{
    private $db;
    private $table = 'articles';

    public function __construct()
    {
        $this->db = new Database();
    }

    public function show($id) {}

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function find($id)
    {
        $result = $this->db->selectA(
            table: $this->table,
            columns: '*',
            conditions: "id = :id",
            params: [':id' => $id],
        );
        return $result ? $result[0] : [];
    }

    public function uniqueSlug($slug)
    {
        $result = $this->db->selectA(
            table: $this->table,
            columns: 'slug',
            conditions: "slug = :slug",
            params: [':slug' => $slug],
        );
        return $result ? true : false;
    }

    public function update($data)
    {
        $result = $this->db->update(
            table: $this->table,
            data: $data,
            conditions: "id = :id",
            params: [':id' => $data['id']],
        );
        return $result ? true : false;
    }

    public function delete($id)
    {
        $result = $this->db->delete(
            table: $this->table,
            conditions: "id = ?",
            params: ['?' => $id],
        );
        return $result ? true : false;
    }

    // # Version 1 : getPaginated
    public function getPaginated(int $page = 1, int $perPage = 10)
    {
        $offset = ($page - 1) * $perPage;
        return $this->db->selectA(
            table: '
                articles JOIN users
                ON articles.creator = users.id
                ',
            columns: '
                articles.title,
                articles.slug,
                articles.excerpt,
                articles.published_at,
                articles.image,
                users.fullname AS creator
            ',
            conditions: 'is_published = :is_published',
            params: [':is_published' => 1],
            orderBy: 'published_at DESC',
            limit: "$offset, $perPage",
        );
    }

    public function listForMan()
    {
        $columns = '
        articles.id,
        articles.title,
        articles.is_published,
        articles.visites,
        users.fullname AS creator
        ';
        $table = '
        articles JOIN users
        ON articles.creator = users.id
        ';
        $result = $this->db->selectA(
            table: $table,
            columns: $columns,
        );
        return $result ? $result : [];
    }
    public function getRecentArticles(int $limit = 5): array
    {
        return $this->db->selectA(
            table: $this->table,
            columns: '
                title,
                slug,
                published_at,
                image',
            conditions: 'is_published = :is_published',
            params: [':is_published' => 1],
            orderBy: 'published_at DESC',
            limit: $limit,
        );
    }
    public function updateVisites($slug, $visites)
    {
        return $this->db->update(
            table: $this->table,
            data: ["visites" => intval($visites) + 1],
            conditions: 'slug = :slug',
            params: [':slug' => $slug],
        );
    }
    public function getArticleBySlug(string $slug): array
    {
        $result = $this->db->selectA(
            table: '
                articles JOIN users
                ON articles.creator = users.id
            ',
            columns: '
                articles.title,
                articles.slug,
                articles.published_at,
                articles.image,
                articles.content,
                articles.meta,
                articles.visites,
                users.fullname AS creator',
            conditions: 'slug = :slug',
            params: [':slug' => $slug],
        );
        if ($result) {
            $result = $result[0];
            $this->updateVisites($slug, $result['visites']);
            return $result;
        }
        return [];
    }

    // # Version 2 : countAllPublished
    public function countAllPublished(): int
    {
        $results = $this->db->selectA(
            table: $this->table,
            columns: 'COUNT(*) as total',
            conditions: 'is_published = :is_published',
            params: [':is_published' => 1],
        );
        return (int)($results[0]['total'] ?? 0);
    }

    /**
     * Recherche des articles selon des critères
     * 
     * @param string $term Terme de recherche
     * @param array $filters Filtres supplémentaires (published, date_start, date_end, creator)
     * @param int $page Page courante
     * @param int $perPage Nombre d'articles par page
     * @return array Articles trouvés et métadonnées de pagination
     */
    public function search(string $term = '', array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        // Préparation du terme de recherche pour la langue française
        if (!empty($term)) {
            $searchTerms = $this->prepareSearchTerms($term);

            $searchConditions = [];
            foreach ($searchTerms as $index => $searchTerm) {
                $paramName = ":search{$index}";
                $params[$paramName] = "%{$searchTerm}%";
                $searchConditions[] = "articles.title LIKE {$paramName} OR 
                                     articles.content LIKE {$paramName} OR 
                                     articles.excerpt LIKE {$paramName}";
            }

            if (!empty($searchConditions)) {
                $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
            }
        }

        // Filtre par statut de publication
        if (isset($filters['published'])) {
            $conditions[] = "articles.is_published = :is_published";
            $params[':is_published'] = (int)$filters['published'];
        }

        // Filtre par date de début
        if (!empty($filters['date_start'])) {
            $conditions[] = "articles.published_at >= :date_start";
            $params[':date_start'] = $filters['date_start'] . ' 00:00:00';
        }

        // Filtre par date de fin
        if (!empty($filters['date_end'])) {
            $conditions[] = "articles.published_at <= :date_end";
            $params[':date_end'] = $filters['date_end'] . ' 23:59:59';
        }

        // Filtre par créateur
        if (!empty($filters['creator'])) {
            $conditions[] = "articles.creator = :creator";
            $params[':creator'] = $filters['creator'];
        }

        // Construction de la condition finale
        $conditionString = !empty($conditions) ? implode(' AND ', $conditions) : '';

        // Requête pour obtenir les articles
        $articles = $this->db->selectA(
            table: '
                articles JOIN users
                ON articles.creator = users.id
            ',
            columns: '
                articles.id,
                articles.title,
                articles.slug,
                articles.excerpt,
                articles.content,
                articles.published_at,
                articles.image,
                users.fullname AS creator
            ',
            conditions: $conditionString,
            params: $params,
            orderBy: 'articles.published_at DESC',
            limit: "$offset, $perPage"
        );

        // Requête pour compter le nombre total d'articles
        $countParams = $params;
        $totalResults = $this->db->selectA(
            table: '
                articles JOIN users
                ON articles.creator = users.id
            ',
            columns: 'COUNT(*) as total',
            conditions: $conditionString,
            params: $countParams
        );

        $total = isset($totalResults[0]['total']) ? (int)$totalResults[0]['total'] : 0;
        $totalPages = ceil($total / $perPage);

        return [
            'articles' => $articles ?: [],
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_results' => $total,
            'per_page' => $perPage
        ];
    }

    /**
     * Prépare les termes de recherche pour la langue française
     * 
     * @param string $term Terme de recherche brut
     * @return array Termes préparés pour la recherche
     */
    private function prepareSearchTerms(string $term): array
    {
        // Normalisation : suppression des accents, conversion en minuscules
        $normalized = $this->removeAccents(mb_strtolower(trim($term)));

        // Séparation des mots
        $words = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        $processedTerms = [];

        // Traitement pour chaque mot
        foreach ($words as $word) {
            // Ignorer les mots très courts (articles, prépositions)
            if (mb_strlen($word) <= 1) {
                continue;
            }

            // Ignorer les mots vides communs en français
            $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'du', 'au', 'aux', 'et', 'ou', 'de', 'a', 'en', 'ce', 'ces', 'cette'];
            if (in_array($word, $stopWords)) {
                continue;
            }

            // Ajouter le mot original
            $processedTerms[] = $word;

            // Ajouter des variantes pour certaines terminaisons françaises
            // (pour gérer singulier/pluriel, formes verbales)
            if (mb_strlen($word) > 3) {
                // Racine de base (enlève les terminaisons communes)
                $stem = preg_replace('/[esxyéèêëàâäôöùûüç]+$/i', '', $word);
                if (mb_strlen($stem) >= 3 && $stem !== $word) {
                    $processedTerms[] = $stem;
                }
            }
        }

        // Ajouter le terme complet initial
        if (!empty($normalized) && !in_array($normalized, $processedTerms)) {
            $processedTerms[] = $normalized;
        }

        // Ajouter le terme original avec accents
        if (!empty($term) && !in_array($term, $processedTerms)) {
            $processedTerms[] = $term;
        }

        return array_unique($processedTerms);
    }

    /**
     * Supprime les accents d'une chaîne
     * 
     * @param string $str Chaîne à traiter
     * @return string Chaîne sans accents
     */
    private function removeAccents(string $str): string
    {
        $search = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Œ', 'œ'];
        $replace = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 'ss', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'OE', 'oe'];

        return str_replace($search, $replace, $str);
    }
}
