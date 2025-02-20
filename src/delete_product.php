<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Método inválido."]);
    exit();
}

// Captura o ID do produto da URL
if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID do produto não fornecido."]);
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id']; // ID do usuário autenticado

try {
    // Verifica se o produto existe antes de excluir
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    // Atualiza stock_logs para marcar como "Excluído"
    $logStmt = $conn->prepare("UPDATE stock_logs SET status = 'Excluído', description = 'Produto ID $id ({$product['name']}) foi excluído' WHERE product_id = :id");
    $logStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $logStmt->execute();

    // Registrar log de exclusão
    $action = "Usuário $user_id excluiu o produto ID $id ({$product['name']})";
    $logActionStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
    $logActionStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logActionStmt->bindParam(':action', $action);
    $logActionStmt->execute();

    // Excluir o produto do banco de dados
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    echo json_encode(["success" => true, "message" => "Produto excluído com sucesso!"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao excluir o produto: " . $e->getMessage()]);
}
