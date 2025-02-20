<?php
session_start();
require 'db_connection.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

// Captura os dados da requisição JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se os dados necessários foram enviados
if (!isset($data['productId'], $data['quantity'], $data['action'])) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit();
}

$productId = (int) $data['productId'];
$quantityChange = (int) $data['quantity'];
$action = $data['action'];
$userId = $_SESSION['user_id']; // Usuário logado

if ($quantityChange < 1) {
    echo json_encode(["success" => false, "message" => "A quantidade deve ser maior que zero."]);
    exit();
}

try {
    // Buscar a quantidade atual do produto
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = :productId");
    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    $currentQuantity = (int) $product['quantity'];

    // Definir a nova quantidade com base na ação
    if ($action === 'add') {
        $newQuantity = $currentQuantity + $quantityChange;
        $changeValue = $quantityChange; // Para log, valor positivo
    } elseif ($action === 'subtract') {
        if ($currentQuantity < $quantityChange) {
            echo json_encode(["success" => false, "message" => "Quantidade insuficiente no estoque."]);
            exit();
        }
        $newQuantity = $currentQuantity - $quantityChange;
        $changeValue = -$quantityChange; // Para log, valor negativo
    } else {
        echo json_encode(["success" => false, "message" => "Ação inválida."]);
        exit();
    }

    // Atualizar a quantidade no banco de dados
    $updateStmt = $conn->prepare("UPDATE products SET quantity = :newQuantity WHERE id = :productId");
    $updateStmt->bindParam(':newQuantity', $newQuantity, PDO::PARAM_INT);
    $updateStmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $updateStmt->execute();

    // Registrar no log da alteração
    $logStmt = $conn->prepare("
        INSERT INTO stock_logs (product_id, user_id, change_value, current_quantity) 
        VALUES (:productId, :userId, :changeValue, :newQuantity)
    ");
    $logStmt->bindParam(':productId', $productId, PDO::PARAM_INT);
    $logStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $logStmt->bindParam(':changeValue', $changeValue, PDO::PARAM_INT);
    $logStmt->bindParam(':newQuantity', $newQuantity, PDO::PARAM_INT);
    $logStmt->execute();

    // Retornar resposta de sucesso
    echo json_encode([
        "success" => true,
        "message" => "Estoque atualizado com sucesso!",
        "newQuantity" => $newQuantity
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados.", "error" => $e->getMessage()]);
}
