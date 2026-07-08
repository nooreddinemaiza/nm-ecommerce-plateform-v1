<?php

namespace Src\Database;

use PDO;
use PDOException;
use Src\Helpers\AppLog;
use Src\Helpers\Config;
use Src\Controllers\PageController;

class Database
{
    private $pdo;

    public function __construct()
    {
        try {
            $host = Config::get('DB_HOST');
            $dbname = Config::get('DB_NAME');
            $username = Config::get('DB_USER');
            $password = Config::get('DB_PASS');
            // Création du DSN (Data Source Name)
            $dsn = "mysql:host={$host};dbname={$dbname}";

            // Connexion PDO
            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->handleError("Connection failed: " . $e->getMessage());
        }
    }


    // Gère les erreurs de manière centralisée
    private function handleError($message)
    {
        AppLog::critical($message); // Enregistrement de l'erreur
        (new PageController)->handle500();
        die();
    }

    // Exécution des requêtes SELECT avec option GROUP BY
    public function select(
        $table,
        $columns = '*',
        $conditions = '',
        $params = [],
        $groupBy = '',
        $limit = '',
        $orderBy = '',
        $joins = '',
        $distinct = false,
        $having =  '',
        $debug = false
    ) {
        // Utiliser la nouvelle méthode select() avec les paramètres de selectA
        // en ajoutant les paramètres manquants avec leurs valeurs par défaut
        return $this->selectA(
            table: $table,
            columns: $columns,
            conditions: $conditions,
            params: $params,
            groupBy: $groupBy,
            having: $having,
            orderBy: $orderBy,
            limit: $limit,
            joins: $joins,
            distinct: $distinct,
            debug: $debug
        );
    }
    /**
     * Exécute une requête SELECT avec les paramètres spécifiés
     * 
     * @param string $table Nom de la table à sélectionner
     * @param string $columns Colonnes à sélectionner (par défaut '*')
     * @param string $conditions Conditions WHERE
     * @param array $params Paramètres pour les conditions WHERE
     * @param string $groupBy Groupe par colonne
     * @param string $having Clause HAVING
     * @param string $orderBy Clause ORDER BY
     * @param string $limit Clause LIMIT
     * @param string $joins Clause JOIN
     * @param bool $distinct Si true, les résultats distincts sont retournés
     * @param bool $debug Si true, la requête est affichée dans le navigateur
     * @return array|false Tableau des résultats ou false en cas d'erreur
     */

    public function selectA(
        string $table,
        string $columns = '*',
        string $conditions = '',
        array $params = [],
        string $groupBy = '',
        string $having = '',
        string $orderBy = '',
        string $limit = '',
        string $joins = '',
        bool $distinct = false,
        bool $debug = false
    ) {
        $query = "SELECT " . ($distinct ? "DISTINCT " : "") . "$columns FROM $table";

        if (!empty($joins)) $query .= " $joins";
        if (!empty($conditions)) $query .= " WHERE $conditions";
        if (!empty($groupBy)) $query .= " GROUP BY $groupBy";
        if (!empty($having)) $query .= " HAVING $having";
        if (!empty($orderBy)) $query .= " ORDER BY $orderBy";
        if (!empty($limit)) $query .= " LIMIT $limit";
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
            }
        }
        // 🔍 Mode debug
        if ($debug) {
            $this->debugQuery($query, $params);
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError("Erreur SQL : " . $e->getMessage());
            return false;
        }
    }

    // Vous devrez aussi ajouter cette méthode helper si elle n'existe pas déjà
    private function validateOrderBy($orderBy)
    {
        // Liste des mots-clés valides pour ORDER BY
        $validDirections = ['ASC', 'DESC', 'ASCENDING', 'DESCENDING'];

        // Diviser les différentes parties de ORDER BY
        $parts = explode(',', $orderBy);
        $validatedParts = [];

        foreach ($parts as $part) {
            $part = trim($part);
            // Séparer le nom de colonne et la direction
            $elements = explode(' ', $part);

            // Vérifier et nettoyer le nom de colonne
            $column = trim($elements[0]);
            if (preg_match('/^[a-zA-Z0-9_\.]+$/', $column)) {
                $validatedPart = "`" . str_replace('.', '`.`', $column) . "`";

                // Vérifier et ajouter la direction si elle existe
                if (isset($elements[1])) {
                    $direction = strtoupper(trim($elements[1]));
                    if (in_array($direction, $validDirections)) {
                        $validatedPart .= " $direction";
                    }
                }

                $validatedParts[] = $validatedPart;
            }
        }

        return empty($validatedParts) ? '' : implode(', ', $validatedParts);
    }

    // Exécution des requêtes INSERT
    public function insert($table, $data, $debug = false)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        if ($debug) {
            $this->debugQuery($query, $data);
        }
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($data);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de l'insertion : " . $e->getMessage());
        }
    }

    // Exécution des requêtes UPDATE
    public function update($table, $data, $conditions, $params = [], $debug = false)
    {
        if (empty($data)) {
            return false;
        }

        $setClause = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $query = "UPDATE $table SET $setClause WHERE $conditions";
        if ($debug) {
            $this->debugQuery($query, $params);
        }
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_merge(
                array_combine(array_map(fn($key) => ":$key", array_keys($data)), array_values($data)),
                $params
            ));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }

    // Exécution des requêtes DELETE
    public function delete($table, $conditions, $params = [], $debug = false)
    {
        if (empty($table) || empty(trim($conditions))) {
            return false;
        }
        $query = "DELETE FROM $table WHERE $conditions";
        if ($debug) {
            $this->debugQuery($query, $params);
        }
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(array_values($params));
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la suppression : " . $e->getMessage());
        }
    }

    /**
     * Version compatible avec les anciens appels qui utilise la nouvelle fonction deleteIn
     * 
     * @param string $table Nom de la table
     * @param string $column Nom de la colonne
     * @param array $values Tableau des valeurs pour la condition IN
     * @param bool $debug Afficher la requête pour débogage
     * @return int|false Nombre de lignes supprimées ou false en cas d'erreur
     */
    public function deleteIn(string $table, string $column, array $values, bool $debug = false)
    {
        // Appel direct à la nouvelle fonction deleteIn avec le format adapté
        return $this->deleteInA(
            table: $table,
            conditions: [
                ['column' => $column, 'values' => $values, 'operator' => 'IN'],
                'logic' => 'AND'
            ],
            debug: $debug
        );
    }

    /**
     * Supprime des enregistrements d'une table selon des conditions multiples
     * 
     * @param string $table Nom de la table
     * @param array $conditions Tableau de conditions
     *        Format: [
     *           ['column' => 'colonne1', 'values' => [1, 2, 3], 'operator' => 'IN'],
     *           ['column' => 'colonne2', 'values' => 4, 'operator' => '='],
     *           ['column' => 'colonne3', 'values' => [5, 6, 7], 'operator' => 'IN'],
     *           // Ou utiliser 'logic' => 'OR' pour regrouper des conditions
     *           'logic' => 'AND'  // Logique globale entre les conditions (AND par défaut)
     *        ]
     * @param bool $debug Afficher la requête pour débogage
     * @return int|false Nombre de lignes supprimées ou false en cas d'erreur
     */
    public function deleteInA(string $table, $conditions, bool $debug = false)
    {
        // Support de l'ancien format pour rétrocompatibilité
        if (is_string($conditions) && func_num_args() >= 3) {
            $column = $conditions;
            $values = func_get_arg(2);

            if (!is_array($values)) {
                return false;
            }

            // Convertir au nouveau format
            $conditions = [
                ['column' => $column, 'values' => $values, 'operator' => 'IN'],
                'logic' => 'AND'
            ];
        }

        if (empty($table) || empty($conditions)) {
            return false;
        }

        // Vérifier si le tableau de conditions est bien formé
        if (!$this->isValidConditions($conditions)) {
            return false;
        }

        $query = "DELETE FROM $table WHERE ";
        $params = [];

        // Construction de la requête
        $whereClause = $this->buildWhereClause($conditions, $params);
        $query .= $whereClause;

        if ($debug) {
            $this->debugQuery($query, $params);
        }

        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);  // Exécution de la requête
            return $stmt->rowCount();  // Retourner le nombre de lignes supprimées
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la suppression : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si le tableau de conditions est valide
     * 
     * @param array $conditions Tableau de conditions
     * @return bool true si valide, false sinon
     */
    private function isValidConditions(array $conditions): bool
    {
        $hasValidCondition = false;

        foreach ($conditions as $key => $condition) {
            if ($key === 'logic') {
                continue;
            }

            if (!is_array($condition)) {
                return false;
            }

            if (!isset($condition['column']) || !isset($condition['values'])) {
                return false;
            }

            if (!isset($condition['operator'])) {
                $condition['operator'] = is_array($condition['values']) ? 'IN' : '=';
            }

            $hasValidCondition = true;
        }

        return $hasValidCondition;
    }

    /**
     * Construit la clause WHERE de la requête
     * 
     * @param array $conditions Tableau de conditions
     * @param array &$params Tableau des paramètres de la requête (référence)
     * @return string Clause WHERE construite
     */
    private function buildWhereClause(array $conditions, array &$params): string
    {
        $whereConditions = [];
        $logic = isset($conditions['logic']) ? strtoupper($conditions['logic']) : 'AND';

        // Si la logique n'est ni AND ni OR, utiliser AND par défaut
        if ($logic !== 'AND' && $logic !== 'OR') {
            $logic = 'AND';
        }

        foreach ($conditions as $key => $condition) {
            if ($key === 'logic') {
                continue;
            }

            $column = $condition['column'];
            $values = $condition['values'];
            $operator = $condition['operator'] ?? (is_array($values) ? 'IN' : '=');

            if (is_array($values) && ($operator === 'IN' || $operator === 'NOT IN')) {
                if (empty($values)) {
                    // Si la liste est vide et opérateur IN, la condition ne sera jamais vraie
                    // Si opérateur NOT IN avec liste vide, toujours vrai, mais on ignore
                    if ($operator === 'IN') {
                        return '1 = 0'; // Condition toujours fausse
                    }
                    continue;
                }

                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $whereConditions[] = "$column $operator ($placeholders)";
                $params = array_merge($params, array_values($values));
            } else {
                // Pour les opérateurs simples (=, >, <, etc.)
                $whereConditions[] = "$column $operator ?";
                $params[] = $values;
            }
        }

        if (empty($whereConditions)) {
            return '1 = 1'; // Si aucune condition valide, retourner une condition toujours vraie
        }

        return implode(" $logic ", $whereConditions);
    }


    // Vérification de l'existence d'une table
    public function tableExists($tableName, $debug = false)
    {
        try {
            $result = $this->select("information_schema.tables", "*", "table_schema = DATABASE() AND table_name = ?", [$tableName]);
            return !empty($result);
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la vérification de la table : " . $e->getMessage());
        }
    }

    // Exécuter une requête directe (sans préparation)
    public function execQuery($query, $debug = false)
    {
        try {
            return $this->pdo->exec($query); // Utilisation de exec pour les requêtes ne retournant pas de données (INSERT, DELETE, etc.)
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de l'exécution de la requête : " . $e->getMessage());
        }
    }

    // Méthode pour récupérer une seule ligne
    public function fetch($query, $params = [], $debug = false)
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de l'exécution de la requête FETCH: " . $e->getMessage());
        }
    }

    // Création d'une table
    public function createTable($query, $debug = false)
    {
        try {
            $this->pdo->exec($query);
            return "Table créée avec succès.";
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la création de la table : " . $e->getMessage());
        }
    }

    // Fermer la connexion
    public function closeConnection()
    {
        $this->pdo = null;
    }

    // Retourner l'objet PDO
    public function getpdo()
    {
        return $this->pdo;
    }

    // Start a new transaction
    public function beginTransaction()
    {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors du démarrage de la transaction : " . $e->getMessage());
        }
    }

    // Commit the current transaction
    public function commitTransaction()
    {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de la validation de la transaction : " . $e->getMessage());
        }
    }

    // Rollback the current transaction
    public function rollbackTransaction()
    {
        try {
            return $this->pdo->rollBack();
        } catch (PDOException $e) {
            $this->handleError("Erreur lors de l'annulation de la transaction : " . $e->getMessage());
        }
    }
    private function debugQuery(string $query, array $params = [])
    {
        // Remplacer les paramètres dans la requête
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                // Si la valeur est une chaîne de caractères, on l'entoure de guillemets simples
                if (is_string($value)) {
                    $value = "'" . addslashes($value) . "'";
                }
                // Remplacer le premier "?" par la valeur correspondante
                $query = preg_replace('/\?/', $value, $query, 1);
            }
        }

        echo '<div class="container mt-4">';
        echo '<div class="card border-primary mb-3 shadow">';
        echo '<div class="card-header bg-primary text-white d-flex align-items-center">';
        echo '<i class="fas fa-tools me-2"></i>'; // Icône d'outils
        echo '<strong>Debug SQL Query</strong>';
        echo '</div>'; // Fin card-header
        echo '<div class="card-body">';

        // Affichage de la requête avec bouton de copie
        echo '<div class="mb-4 position-relative">';
        echo '<h5 class="text-primary mb-3"><i class="fas fa-search me-2"></i>Requête SQL</h5>';
        echo '<pre id="debugQueryText" class="bg-light p-3 border rounded text-dark" style="font-family: monospace; white-space: pre-wrap;">';
        echo htmlspecialchars($query);
        echo '</pre>';

        // Bouton de copie avec `this`
        echo '<button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2" onclick="copyQueryText(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Copier">';
        echo '<i class="fas fa-copy"></i>';
        echo '</button>';
        echo '</div>'; // Fin div position-relative

        // Affichage des paramètres s'il y en a
        if (!empty($params)) {
            echo '<div>';
            echo '<h5 class="text-primary mb-3"><i class="fas fa-map-pin me-2"></i>Paramètres</h5>';
            echo '<ul class="list-group">';
            foreach ($params as $key => $value) {
                echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                echo '<span class="badge bg-primary rounded-pill me-2">' . htmlspecialchars($key) . '</span>';
                echo '<span class="text-muted">➜</span>';
                echo '<span class="text-success">' . htmlspecialchars($value) . '</span>';
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>'; // Fin div
        } else {
            echo '<p class="text-muted"><i class="fas fa-info-circle me-2"></i>Aucun paramètre fourni.</p>';
        }

        echo '</div>'; // Fin card-body
        echo '</div>'; // Fin card
        echo '</div>'; // Fin container

        // Script JavaScript pour la copie
        echo <<<HTML
    <script>
        function copyQueryText(button) {
            const queryText = document.getElementById('debugQueryText').innerText;
            navigator.clipboard.writeText(queryText).then(() => {
                button.setAttribute('data-bs-original-title', 'Copié !');

                // Vérifier si un tooltip existe, sinon en créer un
                let tooltip = bootstrap.Tooltip.getInstance(button);
                if (!tooltip) {
                    tooltip = new bootstrap.Tooltip(button);
                }
                tooltip.show();

                // Rétablir le tooltip après 1.5s
                setTimeout(() => {
                    button.setAttribute('data-bs-original-title', 'Copier');
                    tooltip.hide();
                }, 1500);
            }).catch(err => console.error('Erreur lors de la copie:', err));
        }

        // Initialisation des tooltips Bootstrap
        document.addEventListener("DOMContentLoaded", function() {
            let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
        });
    </script>
    HTML;
    }
}
