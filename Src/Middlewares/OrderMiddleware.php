<?php

namespace Src\Middlewares;
use Src\Controllers\OrderController;

class OrderMiddleware
{
    public static function checkOrderExists($order_id)
    {
        
        if(!is_numeric($order_id)){
            return false;
        }
        $order = (new OrderController)->getOrderById($order_id);
        if (!$order) {
            return false;
        }
        return $order;
    }
}
