<?php
require 'src/db_connection.php'; // Conexão com o banco de dados

// Captura dos parâmetros passados via GET
$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Consulta base
$query = "SELECT DATE(timestamp) AS date, SUM(change_value) AS total_change
          FROM stock_logs 
          WHERE 1=1";

// Filtragem baseada no nome do produto, se fornecido
if ($product_name) {
    $query .= " AND product_id IN (SELECT id FROM products WHERE name LIKE :product_name)";
}

// Filtragem por data de início, se fornecido
if ($start_date) {
    $query .= " AND timestamp >= :start_date";
}

// Filtragem por data de término, se fornecido
if ($end_date) {
    $query .= " AND timestamp <= :end_date";
}

// Agrupamento e ordenação por data
$query .= " GROUP BY DATE(timestamp) ORDER BY DATE(timestamp)";

// Preparando a consulta com PDO
$stmt = $conn->prepare($query);

// Vinculando parâmetros
if ($product_name) {
    $stmt->bindValue(':product_name', "%$product_name%", PDO::PARAM_STR);
}
if ($start_date) {
    $stmt->bindValue(':start_date', $start_date, PDO::PARAM_STR);
}
if ($end_date) {
    $stmt->bindValue(':end_date', $end_date, PDO::PARAM_STR);
}

// Executando a consulta
$stmt->execute();

// Processando os resultados
$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $data[] = [
        'date' => $row['date'],
        'total_change' => $row['total_change']
    ];
}

// Enviando os dados como JSON
echo json_encode($data);
