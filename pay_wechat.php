<?php

$merchant_id = isset($_GET['m']) ? intval($_GET['m']) : 1;
$consumer_mobile_id = isset($_GET['mb']) ? intval($_GET['mb']) : 1;
$orders = isset($_GET['order']) ? trim($_GET['order']) : '';
$address_id = isset($_GET['address']) ? intval($_GET['address']) : 1;
$city = isset($_GET['city']) ? htmlspecialchars(trim($_GET['city'])) : '北京';
$district = isset($_GET['district']) ? htmlspecialchars(trim($_GET['district'])) : '朝阳';
$garden = isset($_GET['garden']) ? htmlspecialchars(trim($_GET['garden'])) : '康营家园';
$room = isset($_GET['room']) ? htmlspecialchars(trim($_GET['room'])) : '';

$curr_time = time();

include('config/database.php');
$link = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($link->connect_errno) {
    printf("Connect failed: %s\n", $mysqli->connect_error);
    exit();
}

include('set_charset.php');

if (!$address_id)
{
    $sql = "INSERT INTO address (consumer_mobile_id, city, district, garden, room, status, created_at, updated_at) VALUES ('{$consumer_mobile_id}', '{$city}', '{$district}', '{$garden}', '{$room}', 1, '{$curr_time}', '{$curr_time}')";
    $link->query($sql);
    $address_id = $link->insert_id;
}

// 生成订单
if ($orders)
{
    $orders_arr = explode('|', $orders);
    foreach ($orders_arr as $row)
    {
        if (!$row)
        {
            continue;
        }
        list($goods_id, $count) = explode(':', $row);
        $sql = "SELECT price FROM goods WHERE goods_id = '{$goods_id}'";
        if ($result = $link->query($sql))
        {
            $goods = $result->fetch_array();
            $result->free();

            $price = $goods['price'];
            $amount = $count * $price;

            $sql = "INSERT INTO `order` (consumer_mobile_id, address_id, goods_id, quantity, amount, created_at) VALUES ('{$consumer_mobile_id}', '{$address_id}', '{$goods_id}', '{$count}', '{$amount}', '{$curr_time}')";
            $link->query($sql);
        }
    }
}

echo "开始支付, 敬请等待......";
