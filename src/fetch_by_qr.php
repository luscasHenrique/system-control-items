<?php
require 'db_connection.php'; // Conexão com o banco

// Verificar se o QR Code foi enviado
if (isset($_GET['qrCode']) && !empty(trim($_GET['qrCode']))) {
    $qrCode = trim($_GET['qrCode']);

    try {
        // Buscar informações do produto com base no QR Code
        // Tente buscar o produto
        $stmt = $conn->prepare("SELECT id, name, price, company, description, quantity FROM products WHERE TRIM(qrcode) = TRIM(:qrcode)");
        $stmt->bindParam(':qrcode', $qrCode);
        $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados.', 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'QR Code não enviado ou inválido.']);
}
