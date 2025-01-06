<?php
require 'db_connection.php';
session_start(); // Garante que a sessão está ativa

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $company = $_POST['company'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id']; // ID do usuário logado

    // Gerar QR Code único como JSON
    $qrCodeData = json_encode([
        'name' => $name,
        'price' => $price,
        'company' => $company,
        'description' => $description,
        'quantity' => $quantity,
    ]);

    try {
        // Inserir o produto com o ID do usuário
        $sql = "INSERT INTO products (qrcode, name, price, company, description, quantity, user_id) 
                VALUES (:qrcode, :name, :price, :company, :description, :quantity, :user_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':qrcode', $qrCodeData);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':company', $company);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            header('Location: ../add_product.php?success=true');
            exit();
        } else {
            echo "Erro ao adicionar o produto.";
        }
    } catch (PDOException $e) {
        echo "Erro no banco de dados: " . $e->getMessage();
    }
}
