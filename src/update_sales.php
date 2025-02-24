<?php
session_start();
require 'db_connection.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

$userId = $_SESSION['user_id']; // Pegando o ID do usuário da sessão

// Recebe os dados do frontend (ID do produto, quantidade e status)
$data = json_decode(file_get_contents("php://input"), true);
$productId = $data['productId'];
$quantity = $data['quantity']; // Quantidade vendida ou estornada
$status = $data['status']; // 'Venda' ou 'Estorno'

// Verifica se os dados estão corretos
if (!isset($productId) || !isset($quantity) || !is_numeric($quantity) || $quantity <= 0 || !isset($status)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit();
}

try {
    // Inicia a transação
    $conn->beginTransaction();

    // Busca o produto no banco para verificar o preço
    $stmt = $conn->prepare("SELECT price FROM products WHERE id = :productId");
    $stmt->execute([':productId' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $price = $product['price'];
        $totalValue = $price * $quantity;

        // Se for 'Venda', o totalValue deve ser positivo
        if ($status == 'Venda') {
            $totalValue = abs($totalValue); // Garante que o valor da venda será positivo
        } else if ($status == 'Estorno') {
            // Se for 'Estorno', o totalValue pode ser negativo
            $totalValue = -abs($totalValue);
        }

        // Insere o log no banco de dados (vendendo ou estornando o produto)
        $stmt = $conn->prepare("INSERT INTO sales (product_id, user_id, quantity, total_value, status) 
                               VALUES (:productId, :userId, :quantity, :totalValue, :status)");

        // Insere o log da venda ou estorno
        $stmt->execute([
            ':productId' => $productId,
            ':userId' => $userId,
            ':quantity' => $quantity,
            ':totalValue' => $totalValue,
            ':status' => $status
        ]);

        // Atualiza a quantidade do produto no estoque
        $stmtUpdate = $conn->prepare("UPDATE products SET quantity = quantity + :quantity WHERE id = :productId");
        $stmtUpdate->execute([
            ':quantity' => ($status == 'Venda') ? -$quantity : $quantity, // Para venda diminui, para estorno adiciona
            ':productId' => $productId
        ]);

        // Commit da transação
        $conn->commit();

        // Resposta de sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Produto atualizado com sucesso para ' . $status . '!',
            'newQuantity' => $quantity
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado!']);
    }
} catch (Exception $e) {
    // Se houver erro, faz rollback
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar a venda ou estorno: ' . $e->getMessage()]);
}
