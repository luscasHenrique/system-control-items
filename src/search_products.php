<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($searchTerm)) {
    echo json_encode(["success" => false, "message" => "Nenhum termo de pesquisa fornecido."]);
    exit();
}

// Query que busca produtos em vários campos e evita produtos excluídos
$query = "
    SELECT p.id, p.name, p.price, p.quantity, p.company, p.description, u.username, 
           (p.price * p.quantity) AS total_value
    FROM products p
    JOIN users u ON p.user_id = u.id
    WHERE p.deleted_at IS NULL 
      AND (
          p.id LIKE :search 
          OR p.name LIKE :search 
          OR p.company LIKE :search 
          OR p.description LIKE :search 
          OR p.price LIKE :search
          OR p.quantity LIKE :search
      )
";

$stmt = $conn->prepare($query);
$searchWildcard = "%{$searchTerm}%";
$stmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
$stmt->execute();

// Retorna os resultados
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $products]);
