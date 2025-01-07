<?php
require 'src/db_connection.php';

// Configurar o cabeçalho do arquivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=product_list.csv');

// Criar um ponteiro para escrever o arquivo CSV
$output = fopen('php://output', 'w');

// Escrever o cabeçalho da tabela no CSV
fputcsv($output, ['ID', 'Name', 'Price', 'Quantity', 'Company', 'Description', 'User', 'Total Value']);

try {
    // Buscar todos os registros da tabela
    $stmt = $conn->prepare("
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.quantity, 
            p.company, 
            p.description, 
            u.username,
            (p.price * p.quantity) AS total_value
        FROM 
            products p 
        JOIN 
            users u 
        ON 
            p.user_id = u.id
    ");
    $stmt->execute();

    // Escrever os registros no arquivo CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            number_format($row['price'], 2, '.', ''), // Preço com ponto decimal
            $row['quantity'],
            $row['company'],
            $row['description'],
            $row['username'],
            number_format($row['total_value'], 2, '.', '') // Valor total com ponto decimal
        ]);
    }
} catch (PDOException $e) {
    die("Erro ao exportar os dados: " . $e->getMessage());
}

// Fechar o ponteiro do arquivo CSV
fclose($output);
exit();
