<?php
session_start();
require 'db_connection.php';

// Define o cabeçalho para JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado!"]);
    exit();
}

// Verifica se o ID do produto foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID do produto não fornecido!"]);
    exit();
}

$id = intval($_GET['id']); // Converte para número inteiro
$user_id = $_SESSION['user_id']; // ID do usuário logado

try {
    // Buscar o nome do produto antes de excluir
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado!"]);
        exit();
    }

    $productName = $product['name'];

    // Registrar log antes da exclusão
    $action = "Usuário $user_id excluiu o produto ID $id ($productName)";
    $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
    $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logStmt->bindParam(':action', $action);
    $logStmt->execute();

    // Remover o produto do banco de dados
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    echo json_encode(["success" => true, "message" => "Produto excluído com sucesso!"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao excluir o produto: " . $e->getMessage()]);
}
