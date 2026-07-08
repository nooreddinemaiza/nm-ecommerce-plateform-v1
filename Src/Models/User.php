<?php

namespace Src\Models;

use Src\Database\Database;
use Src\Helpers\SessionManager;

/**
 * User Model
 */
class User
{
    private $db; // Instance de la classe Database
    private SessionManager $sessionManager;
    public function __construct(SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
        $this->db = new Database($this->sessionManager); // Injection de dépendance pour la classe Database
    }
    // Créer un nouvel utilisateur
    public function createUser($data)
    {
        $data['status'] = "inactive";
        $data['role'] = "manager";
        return $this->db->insert('users', $data);
    }
    // Récupérer un utilisateur par son ID
    public function getUserById($id)
    {
        $fields = 'id,username,fullname,email,phone,role,status,created_at,verified';
        $result = $this->db->select('users', $fields, 'id = ?', [$id]);
        return $result ? $result[0] : null;
    }
    public function checkAdmin()
    {
        $result = $this->db->selectA(
            table: "users",
            conditions: "role = 'admin'"
        );
        return $result ? true : false;
    }
    public function updateManagerStatus($id, $status)
    {
        if (!empty($status)) {
            return $this->db->update(
                table: 'users',
                data: ['status' => $status],
                conditions: 'id = :id',
                params: [":id" => $id],
            ) ? true : false;
        } else {
            return false;
        }
    }
    public function updateManagerRole($id, $status)
    {
        if (!empty($status)) {
            return $this->db->update(
                table: 'users',
                data: ['role' => $status],
                conditions: 'id = :id',
                params: [":id" => $id],
            ) ? true : false;
        } else {
            return false;
        }
    }
    // Récupérer un utilisateur par son email, username ou téléphone
    public function getUserByEmailUsernameOrPhone($identifier)
    {
        $result = $this->db->select(
            'users',
            '*',
            '(email = ? OR username = ? OR phone = ?)',
            [$identifier, $identifier, $identifier]
        );
        return $result ? $result[0] : null;
    }
    public function inactif($id)
    {
        $result = $this->db->select(
            'users',
            'status',
            '(id = ?)',
            [$id]
        );
        return $result ? $result[0] : null;
    }
    // Mettre à jour un utilisateur
    public function updateUser($data)
    {
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            return false;
        }
        $id = $data['id'];
        unset($data['id']);

        return $this->db->update('users', $data, 'id = :id', [':id' => $id]);
    }
    // Supprimer un utilisateur
    public function deleteUser($id)
    {
        return is_int($this->db->delete('users', 'id = ?', [$id])) ? true : false;
    }
    public function userExists($email, $username, $phone)
    {
        $result = $this->db->select('users', 'id', 'email = ? OR username = ? OR phone = ?', [$email, $username, $phone]);
        return !empty($result);
    }
    // Vérifier si un utilisateur existe par email
    public function userExistsByEmail($email)
    {
        $result = $this->db->select('users', 'id', 'email = ?', [$email]);
        return !empty($result);
    }
    // Vérifier si un utilisateur existe par email
    public function userExistsByUsername($username)
    {
        $result = $this->db->select('users', 'id', 'username = ?', [$username]);
        return !empty($result);
    }
    public function userExistsByPhone($phone)
    {
        $result = $this->db->select('users', 'id', 'phone = ?', [$phone]);
        return !empty($result);
    }
    // Récupérer tous les utilisateurs
    public function getAllUsers()
    {
        return $this->db->select('users', 'id,username,fullname,email,phone,role,status,created_at');
    }

    public function createPasswordResetToken($email, $token)
    {
        return $this->db->update(
            'users',
            [
                'reset_token' => $token,
                'reset_token_expiry' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ],
            'email = :email',
            [':email' => $email],
        );
    }
    public function setNewPassword($id, $password)
    {
        return $this->db->update('users', ['password' => $password], "id = $id", []);
    }
    // Récupérer un utilisateur par son jeton de réinitialisation
    public function getUserByResetToken($token)
    {
        $result = $this->db->selectA(
            table: 'users',
            columns: 'id',
            conditions: 'reset_token = ? AND reset_token_expiry > NOW()',
            params: [$token],
        );
        return $result ? $result[0] : null;
    }
    public function updateRL($identifier, $i)
    {
        return $this->db->update('users', ['reset_pswd_limit' => $i], "id = :id OR email = :id OR reset_token = :id", [':id' => $identifier]);
    }
    public function clearResetToken($userId)
    {
        return $this->db->update('users', ['reset_pswd_limit' => 3, 'reset_token' => '', 'reset_token_expiry' => date('0-0-0 0:0:0')], "id = $userId", []);
    }
    public function setAdminAccount($data)
    {
        return $this->db->insert('users', $data);
    }
}
