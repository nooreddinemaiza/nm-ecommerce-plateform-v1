<?php

namespace Src\Models;

use Src\Database\Database;

class Section
{
    private $db;
    private $table = "pages";

    public function __construct()
    {
        $this->db = new Database();
    }
    public function getCustomSections($page)
    {
        $table = $this->table;
        $fields = "custom_sections as data";
        $conditions = "page_title = ?";
        $params = [$page];
        $result = $this->db->selectA(
            table: $table,
            columns: $fields,
            conditions: $conditions,
            params: $params,
        );
        return $result ? $result : [];
    }
    public function updateSection(string $page, array $data)
    {
        return $this->db->update(
            table: $this->table,
            conditions: "page_title = :page_title",
            params: [":page_title" => $page],
            data: $data,
        );
    }
}
