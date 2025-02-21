<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Nenhum dado recebido."]);
    exit();
}

if (!isset($data['productId'], $data['name'], $data['price'], $data['quantity'], $data['company'], $data['description'])) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

$id = $data['productId'];
$name = $data['name'];
$price = $data['price'];
$newQuantity = $data['quantity'];
$company = $data['company'];
$description = $data['description'];
$user_id = $_SESSION['user_id'];

try {
    // Buscar estado anterior do produto
    $stmt = $conn->prepare("SELECT quantity FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $oldQuantity = $stmt->fetchColumn();

    if ($oldQuantity === false) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    // Calcular diferença de quantidade
    $quantityChange = $newQuantity - $oldQuantity;

    // Atualizar produto
    $updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':quantity', $newQuantity, PDO::PARAM_INT);
    $updateStmt->bindParam(':company', $company);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        // Inserir no stock_logs caso a quantidade tenha mudado
        if ($quantityChange !== 0) {
            $logStmt = $conn->prepare("INSERT INTO stock_logs (product_id, user_id, change_value, current_quantity, description, status) 
                                       VALUES (:id, :user_id, :change_value, :quantity, :description, 'Editado')");
            $logStmt->bindParam(':id', $id);
            $logStmt->bindParam(':user_id', $user_id);
            $logStmt->bindParam(':change_value', $quantityChange);
            $logStmt->bindParam(':quantity', $newQuantity);
            $logStmt->bindParam(':description', $description);
            $logStmt->execute();
        }

        echo json_encode(["success" => true, "message" => "Produto atualizado com sucesso."]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao atualizar o banco de dados."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados: " . $e->getMessage()]);
}
