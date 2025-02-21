<?php
require 'src/db_connection.php';

$product_name = isset($_GET['product_name']) ? $_GET['product_name'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "SELECT DATE(timestamp) AS date, SUM(change_value) AS total_change
          FROM stock_logs 
          WHERE 1=1";

if ($product_name) {
    $query .= " AND product_id IN (SELECT id FROM products WHERE name LIKE '%$product_name%')";
}

if ($start_date) {
    $query .= " AND timestamp >= '$start_date'";
}

if ($end_date) {
    $query .= " AND timestamp <= '$end_date'";
}

$query .= " GROUP BY DATE(timestamp) ORDER BY DATE(timestamp)";

$result = $conn->query($query);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'date' => $row['date'],
        'total_change' => $row['total_change']
    ];
}

echo json_encode($data);
