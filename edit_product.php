<?php
require 'src/db_connection.php';
session_start(); // Garante que a sessão está ativa

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    die("ID do produto não fornecido!");
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id']; // ID do usuário logado

// Buscar o produto no banco de dados antes da edição
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produto não encontrado!");
}

// Atualizar o produto ao enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $company = $_POST['company'];
    $description = $_POST['description'];

    // Atualizar no banco de dados
    $updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $updateStmt->bindParam(':company', $company);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        // **Registrar log após a edição**
        $action = "Usuário $user_id editou o produto ID $id: ";
        $changes = [];

        // Verifica quais campos foram alterados
        if ($product['name'] !== $name) $changes[] = "Nome: '{$product['name']}' -> '$name'";
        if ($product['price'] != $price) $changes[] = "Preço: '{$product['price']}' -> '$price'";
        if ($product['quantity'] != $quantity) $changes[] = "Quantidade: '{$product['quantity']}' -> '$quantity'";
        if ($product['company'] !== $company) $changes[] = "Empresa: '{$product['company']}' -> '$company'";
        if ($product['description'] !== $description) $changes[] = "Descrição alterada";

        if (!empty($changes)) {
            $action .= implode(", ", $changes);
            $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
            $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $logStmt->bindParam(':action', $action);
            $logStmt->execute();
        }

        header("Location: index.php?success=Produto atualizado com sucesso!");
        exit();
    } else {
        echo "Erro ao atualizar o produto!";
    }
}
