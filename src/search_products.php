<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

// Captura os parâmetros de busca
$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

// Monta a query base garantindo que os produtos com deleted_at não apareçam
$query = "
    SELECT p.id, p.name, p.price, p.quantity, p.company, p.description, u.username, 
    (p.price * p.quantity) AS total_value
    FROM products p
    JOIN users u ON p.user_id = u.id
    WHERE (p.id LIKE :search_id 
        OR p.name LIKE :search_name 
        OR p.company LIKE :search_company)
    AND p.deleted_at IS NULL
";

// Prepara a query
$stmt = $conn->prepare($query);
$searchWildcard = "%{$searchTerm}%";
$stmt->bindParam(':search_id', $searchWildcard, PDO::PARAM_STR);
$stmt->bindParam(':search_name', $searchWildcard, PDO::PARAM_STR);
$stmt->bindParam(':search_company', $searchWildcard, PDO::PARAM_STR);
$stmt->execute();

// Retorna os resultados
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $products]);
