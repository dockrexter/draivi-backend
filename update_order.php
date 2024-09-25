<?php
require_once 'DBConnection.php';
require 'vendor/autoload.php';


$pdo = DBConnection::getInstance();

$data = json_decode(file_get_contents('php://input'), true);
$number = $data['number'];
$action = $data['action'];

if ($action === 1) {
    // Adding 1 to orderamount
    $stmt = $pdo->prepare("UPDATE products SET orderamount = orderamount + 1 WHERE number = :number");
} else {
    // Clear orderamount
    $stmt = $pdo->prepare("UPDATE products SET orderamount = 0 WHERE number = :number");
}

$stmt->bindParam(':number', $number);
$stmt->execute();

// Fetching updated orderamount
$stmt = $pdo->prepare("SELECT orderamount FROM products WHERE number = :number");
$stmt->bindParam(':number', $number);
$stmt->execute();
$orderamount = $stmt->fetchColumn();

echo json_encode(['success' => true, 'orderamount' => $orderamount]);
