<?php

namespace Src\Models;

use Src\Database\Database;
use Src\Helpers\AppLog;

class DashDB
{
    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }
    public function getTable($table)
    {
        return $this->db->selectA(
            table: $table,
            orderBy: 'id DESC'
        );
    }
    public function getLine($table, $id)
    {
        $result =  $this->db->selectA(
            table: $table,
            conditions: 'id = :id',
            params: ['id' => $id]
        );
        return $result ? $result : [];
    }
    public function delete($table, $id)
    {
        if (is_array($id)) {
            $ids = array_filter(array_map('intval', $id));
        } elseif (is_string($id) && !empty($id)) {
            $ids = array_filter(array_map(function ($value) {
                return (is_numeric($value) && (int)$value > 0) ? (int)$value : null;
            }, explode(',', $id)));
        } else {
            return false;
        }
        $ids = array_values($ids);
        if (empty($ids)) {
            return false;
        }
        return $this->db->deleteIn($table, 'id', $ids);
    }
    public function update($data)
    {
        $table = $data['table'];
        $id = intval($data['id']);
        unset($data['id'], $data['table']);
        return $this->db->update(
            table: $table,
            data: $data,
            conditions: 'id = :id',
            params: [':id' => $id]
        );
    }
    public function add($data)
    {
        $table = $data['table'];
        unset($data['table']);
        return $this->db->insert(
            table: $table,
            data: $data,
        );
    }
}
