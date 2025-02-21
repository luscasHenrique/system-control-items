<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Método inválido."]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID do produto não fornecido."]);
    exit();
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id']; // ID do usuário autenticado

try {
    // Verifica se o produto existe e ainda não foi excluído
    $stmt = $conn->prepare("SELECT name, quantity FROM products WHERE id = :id AND deleted_at IS NULL");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado ou já excluído."]);
        exit();
    }

    // Marcar produto como excluído (soft delete)
    $deleteStmt = $conn->prepare("UPDATE products SET deleted_at = NOW() WHERE id = :id");
    $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();

    // Adicionar registro no `stock_logs`
    $logStmt = $conn->prepare("
        INSERT INTO stock_logs (product_id, user_id, change_value, current_quantity, description, status, timestamp) 
        VALUES (:id, :user_id, 0, :quantity, 'Produto excluído', 'Excluído', NOW())
    ");
    $logStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logStmt->bindParam(':quantity', $product['quantity'], PDO::PARAM_INT);
    $logStmt->execute();

    echo json_encode(["success" => true, "message" => "Produto marcado como excluído com sucesso!"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao excluir o produto: " . $e->getMessage()]);
}
