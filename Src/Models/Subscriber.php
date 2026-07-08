<?php

namespace Src\Models;

use Src\Helpers\AppLog;
use Src\Helpers\Mailer;
use Src\Database\Database;

class Subscriber
{
    private $db;
    private $table = "subscribers";
    public function __construct()
    {
        $this->db = new Database();
    }

    public function subscribe($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function canSubscribe($data)
    {
        $fields = "
            COUNT(CASE WHEN email = :email THEN 1 ELSE NULL END) as already_subscribed,
            COUNT(*) as recent_subscriptions";

        $conditions = "ip_address = :ip_address 
                       AND user_agent LIKE :user_agent 
                       AND subscribed_at >= DATE_SUB(:subscribed_at, INTERVAL 1 HOUR)";

        $params = [
            ":email" => $data['email'],
            ":ip_address" => $data['ip_address'],
            ":user_agent" => '%' . $data['user_agent'] . '%',
            ":subscribed_at" => $data['subscribed_at']
        ];

        $result = $this->db->selectA(
            table: $this->table,
            columns: $fields,
            conditions: $conditions,
            params: $params,
        );

        return [
            "alreadySubscribed" => $result[0]['already_subscribed'] > 0,
            "allowedToSubscribe" => $result[0]['recent_subscriptions'] < 2
        ];
    }
    public function getSubscriptionStats()
    {
        $columns = "
            COUNT(*) as total_subs, 
            SUM(CASE WHEN DATE(subscribed_at) = CURDATE() THEN 1 ELSE 0 END) as today_subs,
            SUM(CASE WHEN MONTH(subscribed_at) = MONTH(CURDATE()) AND YEAR(subscribed_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as month_subs
        ";

        $result = $this->db->selectA('subscribers', $columns);
        return $result ? $result[0] : ['total_subs' => 0, 'today_subs' => 0, 'month_subs' => 0];
    }
    public function getSubscribers()
    {
        $result = $this->db->selectA(
            table: $this->table,
            columns: 'email'
        );
        if ($result) return $result;
        return [];
    }
    public function listSubscribers()
    {
        return $this->db->selectA(
            table: $this->table,
            orderBy: 'subscribed_at DESC'
        );
    }
    public function deleteSubscriber($needle,$e = 'id')
    {
        return $this->db->delete(
            $this->table,
            $e == 'id' ? 'id IN ( ? )' : 'email IN ( ? )',
            ["?" => $needle],
        );
    }
    public function getSubscriber($email)
    {
        $result = $this->db->selectA(
            table: $this->table,
            columns: "email",
            conditions: "email = :email",
            params: [':email' => $email],
        );
        if ($result) {
            return $result;
        }
        return false;
    }
}
