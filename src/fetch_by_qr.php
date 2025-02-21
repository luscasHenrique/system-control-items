<?php
require '../src/db_connection.php';
header('Content-Type: application/json');

if (!isset($_GET['qrCode'])) {
    echo json_encode(["success" => false, "message" => "Código QR não fornecido."]);
    exit();
}

$qrCode = $_GET['qrCode'];

try {
    // Buscar o produto pelo QR Code, mesmo que tenha sido excluído
    $stmt = $conn->prepare("SELECT * FROM products WHERE qrcode = :qrCode");
    $stmt->bindParam(':qrCode', $qrCode, PDO::PARAM_STR);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Produto não encontrado."]);
        exit();
    }

    // Verifica se o produto está excluído
    $isDeleted = !is_null($product['deleted_at']);

    echo json_encode([
        "success" => true,
        "product" => [
            "id" => $product['id'],
            "name" => $product['name'],
            "price" => $product['price'],
            "quantity" => $product['quantity'],
            "company" => $product['company'],
            "description" => $product['description'],
            "deleted" => $isDeleted // Adiciona flag para indicar se o produto está excluído
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro ao buscar o produto: " . $e->getMessage()]);
}
