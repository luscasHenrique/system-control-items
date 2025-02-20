<?php
session_start();
require 'src/db_connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['productId'], $data['name'], $data['price'], $data['quantity'], $data['company'], $data['description'])) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit();
}

$id = $data['productId'];
$name = $data['name'];
$price = $data['price'];
$quantity = $data['quantity'];
$company = $data['company'];
$description = $data['description'];
$user_id = $_SESSION['user_id'];

// Buscar o estado anterior do produto
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);

// Atualizar no banco
$updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
$updateStmt->bindParam(':name', $name);
$updateStmt->bindParam(':price', $price);
$updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
$updateStmt->bindParam(':company', $company);
$updateStmt->bindParam(':description', $description);
$updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

if ($updateStmt->execute()) {
    // Registra no `stock_logs`
    $logStmt = $conn->prepare("INSERT INTO stock_logs (product_id, user_id, change_value, current_quantity) VALUES (:id, :user_id, 0, :quantity)");
    $logStmt->bindParam(':id', $id);
    $logStmt->bindParam(':user_id', $user_id);
    $logStmt->bindParam(':quantity', $quantity);
    $logStmt->execute();

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar."]);
}
