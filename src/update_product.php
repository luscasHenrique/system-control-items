<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

// Captura os dados da requisição JSON corretamente
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Nenhum dado recebido."]);
    exit();
}

// Verifica se todos os campos obrigatórios foram enviados
if (!isset($data['productId'], $data['name'], $data['price'], $data['quantity'], $data['company'], $data['description'])) {
    echo json_encode(["success" => false, "message" => "Dados incompletos."]);
    exit();
}

// Garante que o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

$id = $data['productId'];
$name = $data['name'];
$price = $data['price'];
$quantity = $data['quantity'];
$company = $data['company'];
$description = $data['description'];
$user_id = $_SESSION['user_id'];

try {
    // Verifica se o produto existe
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldProduct) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    // Atualiza o produto no banco de dados
    $updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $updateStmt->bindParam(':company', $company);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Produto atualizado com sucesso!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao atualizar o banco de dados."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados: " . $e->getMessage()]);
}
