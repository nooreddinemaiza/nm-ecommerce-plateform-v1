<?php

namespace Src\Models;

use Src\Database\Database;

class Content
{
    private $db;
    private $table = "pages";

    public function __construct()
    {
        $this->db = new Database();
    }
    //set meta content
    public function setMeta($meta)
    {
        $data = [
            'page_meta_keywords' => $meta['keywords'] ?? "",
            'page_meta_description' => $meta['description'] ?? "",
            'meta_author' => $meta['author'] ?? ""
        ];
        return $this->db->update($this->table, $data, 'page_title = :id', [':id' => $meta['page'] ?? 1]);
    }
    //set Banner content
    public function setBanner($data)
    {
        return $this->db->update($this->table, ['page_data' => $data], 'id = :id', [':id' => $data['id'] ?? 1]);
    }

    //set Data
    public function setData($data)
    {
        return $this->db->update($this->table, ['page_data' => $data['data']], 'page_title = :id', [':id' => $data['page']]);
    }

    public function getHome()
    {

        return $this->db->select($this->table, "page_meta_description,page_meta_keywords,page_data,meta_author,custom_sections", "page_title = :title", ['title' => 'home']);
    }
    public function getShop()
    {

        return $this->db->select($this->table, "page_meta_description,page_meta_keywords,page_data,meta_author", "page_title = :title", ['title' => 'shop']);
    }
    public function getContact()
    {
        return $this->db->select($this->table, "page_meta_description,page_meta_keywords,page_data,meta_author", "page_title = :title", ['title' => 'contact']);
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
    // Récupérer tous les contenus
    public function getAllContent()
    {
        return $this->db->select($this->table);
    }
    // Récupérer un contenu par son ID
    public function getContentById($id)
    {
        return $this->db->select($this->table, '*', 'id = :id', ['id' => $id]);
    }

    // Ajouter un nouveau contenu
    public function createContent($data)
    {
        return $this->db->insert($this->table, $data);
    }

    // Mettre à jour un contenu
    public function updateContent($id, $data)
    {
        return $this->db->update($this->table, $data, 'id = :id', ['id' => $id]);
    }

    // Supprimer un contenu
    public function deleteContent($id)
    {
        return $this->db->delete($this->table, 'id = :id', ['id' => $id]);
    }
}
