<?php
require 'src/db_connection.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID do produto não fornecido!"]);
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    echo json_encode(["success" => true, "data" => $product]);
} else {
    echo json_encode(["success" => false, "message" => "Produto não encontrado!"]);
}
