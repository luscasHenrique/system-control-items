<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $company = $_POST['company'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity']; // Capturar o campo quantidade

    // Gerar QR Code único como JSON
    $qrCodeData = json_encode([
        'name' => $name,
        'price' => $price,
        'company' => $company,
        'description' => $description,
        'quantity' => $quantity, // Incluir quantidade no QR Code
    ]);

    try {
        // Adicionar o campo quantidade ao SQL
        $sql = "INSERT INTO products (qrcode, name, price, company, description, quantity) 
                VALUES (:qrcode, :name, :price, :company, :description, :quantity)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':qrcode', $qrCodeData);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':company', $company);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT); // Associar quantidade

        if ($stmt->execute()) {
            header('Location: ../add_product.php?success=true');
            exit();
        } else {
            echo "Failed to add product.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
