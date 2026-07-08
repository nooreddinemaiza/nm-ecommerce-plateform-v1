<?php

namespace Src\Services;

use Src\Database\Database;
use Src\Helpers\AppLog;

class Authentication
{
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }
    public function loginAttempts($ip_address, $user_agent): int
    {
        $result = $this->db->selectA(
            table: 'logins',
            columns: 'COUNT(*) as attempts',
            conditions: 'ip_address = ? AND user_agent = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND status = "failed"',
            params: [$ip_address, $user_agent]
        );
        return intval($result[0]['attempts'] ?? 0);
    }
    public function getUser($ip_address, $user_agent)
    {
        $result = $this->db->selectA(
            table: 'logins',
            columns: '*',
            conditions: 'ip_address = ? AND user_agent = ? AND last_attempt > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND status = "failed"',
            params: [$ip_address, $user_agent]
        );
        return $result ? [
            'id' => $result[0]['id'] ?? null,
            'user_id' => $result[0]['user_id'] ?? null,
            'attempts' => $result[0]['attempts'] ?? 0,
            'status' => $result[0]['status'] ?? null,
            'last_attempt' => $result[0]['last_attempt'] ?? null,
        ] : [];
    }
    public function recordLoginAttempt($ip_address, $user_agent, $attempts, $status = 'failed', $id = null)
    {


        $data = $this->getUser($ip_address, $user_agent);
        if ($data) {
            $user = $data['user_id'];
            $id = $data['id'];
            $attempts = (int)$data['attempts'] + 1;
            $status = $status;
            return $this->setLoginStatus($ip_address, $user_agent, $attempts, $status, $id, $user);
        }
        return $this->db->insert(
            'logins',
            [
                'user_id' => $id,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'attempts' => $attempts,
                'status' => $status
            ]
        );
    }
    public function setLoginStatus($ip_address, $user_agent, $attempts, $status = 'failed', $id = null, $user = null)
    {
        $status = $attempts >= 5 ? 'blocked' : $status;
        
        return $this->db->update(
            'logins',
            [
                'user_id' => $user,
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'attempts' => $attempts,
                'status' => $status
            ],
            'id = :logId OR ( ip_address = :ip AND user_agent = :agent )',
            [
                ':logId' => $id,
                ':ip' => $ip_address,
                ':agent' => $user_agent
            ],
        );
    }
    public function resetLoginAttempts($ip_address, $user_agent)
    {
        return $this->db->delete(
            'logins',
            'ip_address = ? AND user_agent = ?',
            [$ip_address, $user_agent]
        );
    }
    public function is_blocked(): bool
    {
        $result = $this->db->selectA(
            table: 'logins',
            columns: 'ip_address,status',
            conditions: 'ip_address = ? AND user_agent = ?',
            params: [
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ],
            groupBy: "ip_address, user_agent",
            having: 'status = "blocked"',
        );
        if ($result) {
            AppLog::debug('User is blocked, IP: ' . $result[0]['ip_address']);
            return true;
        }
        return false;
    }
}
