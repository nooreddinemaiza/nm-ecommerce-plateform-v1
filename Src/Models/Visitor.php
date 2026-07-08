<?php

namespace Src\Models;

use Src\Database\Database;

class Visitor
{
    private $db;
    private $table = "contacts";
    public function __construct()
    {
        $this->db = new Database();
    }

    public function sendMessage($data)
    {
        $table = $this->table;
        unset($data['csrf_token']);
        $status = $this->db->insert($table, $data);
        return $status;
    }
    public function allowedToSendMessage($ip_address, $user_agent, $sent_at)
    {
        $table = $this->table;
        $fields = "COUNT(*) as count";
        $conditions = "ip_address = ? AND user_agent LIKE ? AND sent_at >= DATE_SUB( ?, INTERVAL 1 HOUR)";
        $params = [$ip_address, '%' . $user_agent . '%', $sent_at];
        $messages = $this->db->selectA($table, $fields, $conditions, $params);
        return $messages[0]['count'] <= 5 ? true : false;
    }
    public function getMessages()
    {
        $table = $this->table;
        $fields = "id, name, email, subject, message, sent_at";
        $messages = $this->db->selectA($table, $fields);
        return $messages;
    }
    public function latestMessages()
    {
        $table = $this->table;
        $fields = "name, subject, message, sent_at";
        $messages = $this->db->selectA(table:$table, columns:$fields, orderBy:"sent_at DESC", limit:"1");
        return $messages;
    }
    public function getPaginatedMessages(int $page = 1, int $perPage = 5, string $search = '', bool $newOnly = false)
    {
        // Calcul de l'offset pour la pagination
        $offset = ($page - 1) * $perPage;

        // Conditions de recherche et de filtrage
        $whereConditions = [];
        $params = [];
        $perPage = 5;
        // Recherche par email, sujet ou message
        if (!empty($search)) {
            $whereConditions[] = "(email LIKE :search OR subject LIKE :search OR message LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Filtrer les messages des dernières 24h
        if ($newOnly) {
            $orderBy = "sent_at DESC";
        }else{
            $orderBy = "sent_at ASC";
        }

        // Construction de la clause WHERE
        $whereClause = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';

        // Récupération du nombre total de messages
        $totalMessages = $this->db->selectA(
            table: $this->table,
            columns: "COUNT(id) AS total",
            conditions: $whereClause,
            params: $params
        );
        $totalMessages = $totalMessages[0]['total'] ?? 0;
        $totalPages = ceil($totalMessages / $perPage);

        // Récupération des messages
        $messages = $this->db->selectA(
            table: $this->table,
            columns: "id, name, email, subject, message, sent_at",
            conditions: $whereClause,
            params: $params,
            orderBy: $orderBy,
            limit: "$perPage OFFSET $offset",
        );

        return [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_messages' => $totalMessages,
            'messages' => $messages
        ];
    }
    public function deleteMessage($id)
    {
        $table = $this->table;
        $status = $this->db->delete($table, 'id = ?', [$id]);
        return $status;
    }
}
