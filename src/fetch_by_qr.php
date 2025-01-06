<?php
require 'db_connection.php'; // Conexão com o banco

// Verificar se o QR Code foi enviado
if (isset($_GET['qrCode'])) {
    $qrCode = $_GET['qrCode'];

    try {
        // Buscar informações do produto com base no QR Code
        $stmt = $conn->prepare("SELECT id, name, price, company, description, quantity FROM products WHERE qrcode = :qrcode");
        $stmt->bindParam(':qrcode', $qrCode);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'QR Code não enviado.']);
}
