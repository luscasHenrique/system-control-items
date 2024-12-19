<?php
require 'src/db_connection.php';

if (!isset($_GET['id'])) {
    die("ID do produto não fornecido!");
}

$id = $_GET['id'];

try {
    // Remover o produto do banco de dados
    $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: index.php?success=Produto excluído com sucesso!");
    exit();
} catch (PDOException $e) {
    die("Erro ao excluir o produto: " . $e->getMessage());
}
?>
