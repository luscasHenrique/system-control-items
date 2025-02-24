<?php
session_start();
require 'src/db_connection.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'SuperAdmin', 'Seller'])) {
    header("Location: unauthorized.php");
    exit();
}

// Dados para os gráficos
// 1. Gráfico de Vendas (Lucro) - Produto vs. Valor Atualizado
$stmt = $conn->prepare("
    SELECT 
        p.name AS product_name, 
        SUM(l.change_value) AS total_value
    FROM stock_logs l
    LEFT JOIN products p ON l.product_id = p.id
    WHERE p.company = 'Luna Editora'
    GROUP BY p.name
");
$stmt->execute();
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Gráfico de Quantidade Atual por Produto
$stmt = $conn->prepare("
    SELECT 
        p.name AS product_name, 
        SUM(l.current_quantity) AS quantity
    FROM stock_logs l
    LEFT JOIN products p ON l.product_id = p.id
    WHERE p.company = 'Luna Editora'
    GROUP BY p.name
");
$stmt->execute();
$quantity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Gráfico de Vendas por Empresa
$stmt = $conn->prepare("
    SELECT 
        p.company AS company_name, 
        SUM(l.change_value) AS total_value
    FROM stock_logs l
    LEFT JOIN products p ON l.product_id = p.id
    WHERE p.company = 'Luna Editora'
    GROUP BY p.company
");
$stmt->execute();
$company_sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Gráfico de Status das Ações (Editado vs Excluído)
$stmt = $conn->prepare("
    SELECT 
        l.status, 
        COUNT(l.id) AS status_count
    FROM stock_logs l
    GROUP BY l.status
");
$stmt->execute();
$status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5. Gráfico de Valor Total de Estoque por Produto
$stmt = $conn->prepare("
    SELECT 
        p.name AS product_name, 
        SUM(p.price * l.current_quantity) AS total_value
    FROM stock_logs l
    LEFT JOIN products p ON l.product_id = p.id
    WHERE p.company = 'Luna Editora'
    GROUP BY p.name
");
$stmt->execute();
$total_stock_value_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Registro de Estoque</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100 min-h-screen">
    <?php include 'menu.php'; ?>

    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 text-center">Dashboard - Registro de Estoque</h1>

        <!-- Gráfico de Vendas (Lucro) -->
        <div class="mb-8">
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Gráfico de Quantidade Atual por Produto -->
        <div class="mb-8">
            <canvas id="quantityChart"></canvas>
        </div>

        <!-- Gráfico de Vendas por Empresa -->
        <div class="mb-8">
            <canvas id="companySalesChart"></canvas>
        </div>

        <!-- Gráfico de Status das Ações -->
        <div class="mb-8">
            <canvas id="statusChart"></canvas>
        </div>

        <!-- Gráfico de Valor Total de Estoque por Produto -->
        <div class="mb-8">
            <canvas id="stockValueChart"></canvas>
        </div>
    </div>

    <script>
        // Gráfico de Vendas (Lucro)
        const salesChart = new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($sales_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Lucro/Venda (R$)',
                    data: <?php echo json_encode(array_column($sales_data, 'total_value')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Quantidade Atual por Produto
        const quantityChart = new Chart(document.getElementById('quantityChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($quantity_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Quantidade Atual',
                    data: <?php echo json_encode(array_column($quantity_data, 'quantity')); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Vendas por Empresa
        const companySalesChart = new Chart(document.getElementById('companySalesChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($company_sales_data, 'company_name')); ?>,
                datasets: [{
                    label: 'Vendas por Empresa',
                    data: <?php echo json_encode(array_column($company_sales_data, 'total_value')); ?>,
                    backgroundColor: ['#FF5733', '#33FF57', '#3357FF'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            }
        });

        // Gráfico de Status das Ações
        const statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($status_data, 'status')); ?>,
                datasets: [{
                    label: 'Status das Ações',
                    data: <?php echo json_encode(array_column($status_data, 'status_count')); ?>,
                    backgroundColor: ['#FF6347', '#32CD32'],
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            }
        });

        // Gráfico de Valor Total de Estoque por Produto
        const stockValueChart = new Chart(document.getElementById('stockValueChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($total_stock_value_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Valor Total de Estoque (R$)',
                    data: <?php echo json_encode(array_column($total_stock_value_data, 'total_value')); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>

</html>