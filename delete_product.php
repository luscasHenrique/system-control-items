<?php
require 'src/db_connection.php';
session_start(); // Garante que a sessão está ativa

if (!isset($_GET['id'])) {
    die("ID do produto não fornecido!");
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id']; // Capturar o ID do usuário logado

try {
    // Buscar o nome do produto antes de excluir
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Produto não encontrado!");
    }

    $productName = $product['name'];

    // **Registrar log antes da exclusão**
    $action = "Usuário $user_id excluiu o produto ID $id ($productName)";
    $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
    $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logStmt->bindParam(':action', $action);
    $logStmt->execute();

    // Remover o produto do banco de dados
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    header("Location: index.php?success=Produto excluído com sucesso!");
    exit();
} catch (PDOException $e) {
    die("Erro ao excluir o produto: " . $e->getMessage());
}
