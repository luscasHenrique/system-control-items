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
$quantity = $data['quantity'];
$company = $data['company'];
$description = $data['description'];
$user_id = $_SESSION['user_id'];

try {
    // Buscar estado anterior do produto
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldProduct) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    // Atualizar o produto no banco de dados
    $updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $updateStmt->bindParam(':company', $company);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        // Criar descrição do que foi alterado
        $changes = [];
        if ($oldProduct['name'] !== $name) $changes[] = "Nome: '{$oldProduct['name']}' → '$name'";
        if ($oldProduct['price'] != $price) $changes[] = "Preço: '{$oldProduct['price']}' → '$price'";
        if ($oldProduct['quantity'] != $quantity) $changes[] = "Quantidade: '{$oldProduct['quantity']}' → '$quantity'";
        if ($oldProduct['company'] !== $company) $changes[] = "Empresa: '{$oldProduct['company']}' → '$company'";
        if ($oldProduct['description'] !== $description) $changes[] = "Descrição alterada";

        $changeDescription = implode(", ", $changes);

        // Atualizar o `stock_logs` com as alterações
        $logStmt = $conn->prepare("INSERT INTO stock_logs (product_id, user_id, change_value, current_quantity, description, status) 
                                   VALUES (:id, :user_id, 0, :quantity, :description, 'Editado')");
        $logStmt->bindParam(':id', $id);
        $logStmt->bindParam(':user_id', $user_id);
        $logStmt->bindParam(':quantity', $quantity);
        $logStmt->bindParam(':description', $changeDescription);
        $logStmt->execute();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao atualizar o banco de dados."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro no banco de dados: " . $e->getMessage()]);
}
