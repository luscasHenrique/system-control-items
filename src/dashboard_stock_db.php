<?php
session_start();
require 'src/db_connection.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'SuperAdmin', 'Seller'])) {
    header("Location: unauthorized.php");
    exit();
}

// Dados para os gráficos
// 1. Gráfico de Quantidade Total por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.quantity FROM products p WHERE p.company = 'Luna Editora'");
$stmt->execute();
$quantity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Gráfico de Valor Total de Estoque por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.price * p.quantity AS total_value FROM products p WHERE p.company = 'Luna Editora'");
$stmt->execute();
$stock_value_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Gráfico de Preço Médio por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.price FROM products p WHERE p.company = 'Luna Editora'");
$stmt->execute();
$price_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorna os dados para o gráfico
echo json_encode([
    'quantity_labels' => array_column($quantity_data, 'product_name'),
    'quantity_data' => array_column($quantity_data, 'quantity'),
    'stock_value_labels' => array_column($stock_value_data, 'product_name'),
    'stock_value_data' => array_column($stock_value_data, 'total_value'),
    'price_labels' => array_column($price_data, 'product_name'),
    'price_data' => array_column($price_data, 'price')
]);
