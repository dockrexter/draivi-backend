<?php
require_once 'DBConnection.php';
require 'vendor/autoload.php';

$db = DBConnection::getInstance();
$stmt = $db->query("SELECT number, name, bottlesize, price, priceGBP, orderamount FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($products);
