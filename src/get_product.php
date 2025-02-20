<?php
require 'db_connection.php';
header('Content-Type: application/json');
session_start();

// Ativar exibição de erros (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID do produto não fornecido!"]);
    exit();
}

$id = intval($_GET['id']); // Converte para número inteiro

try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(["success" => true, "data" => $product]);
    } else {
        echo json_encode(["success" => false, "message" => "Produto não encontrado!"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados", "error" => $e->getMessage()]);
}
exit();
