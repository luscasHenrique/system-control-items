<?php
session_start();
require '../src/db_connection.php';
header('Content-Type: application/json');

// Captura os parâmetros de busca
$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';

// Monta a query base
$query = "
    SELECT p.id, p.name, p.price, p.quantity, p.company, p.description, u.username, 
    (p.price * p.quantity) AS total_value
    FROM products p
    JOIN users u ON p.user_id = u.id
    WHERE p.id LIKE :search
    OR p.name LIKE :search
    OR p.price LIKE :search
    OR p.quantity LIKE :search
    OR p.company LIKE :search
    OR p.description LIKE :search
    OR u.username LIKE :search
    OR (p.price * p.quantity) LIKE :search
";

// Prepara a query
$stmt = $conn->prepare($query);
$searchWildcard = "%{$searchTerm}%";
$stmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
$stmt->execute();

// Retorna os resultados
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "data" => $products]);
