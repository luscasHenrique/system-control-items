<?php
require 'db_connection.php'; // Conexão com o banco
session_start(); // Iniciar a sessão para capturar o usuário

// Verificar se o QR Code foi enviado
if (isset($_GET['qrCode']) && !empty(trim($_GET['qrCode']))) {
    $qrCode = trim($_GET['qrCode']);

    try {
        // Buscar informações do produto com base no QR Code
        $stmt = $conn->prepare("SELECT id, name, price, company, description, quantity FROM products WHERE TRIM(qrcode) = TRIM(:qrcode)");
        $stmt->bindParam(':qrcode', $qrCode);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            // **Inserção do log de consulta**
            if (isset($_SESSION['user_id'])) { // Garante que há um usuário logado
                $action = "Usuário " . $_SESSION['user_id'] . " consultou QR Code do produto ID " . $product['id'];
                $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
                $logStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $logStmt->bindParam(':action', $action);
                $logStmt->execute();
            }

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
