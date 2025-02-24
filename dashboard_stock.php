<?php
session_start();
require 'src/db_connection.php';

// Verifica se o usuário está logado e tem permissão
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'SuperAdmin', 'Seller'])) {
    header("Location: unauthorized.php");
    exit();
}

// Inicializa filtros
$product_filter = '';
$date_filter = '';

// Verifica se o filtro de produtos foi aplicado
if (isset($_POST['products']) && !empty($_POST['products'])) {
    $products = implode(',', $_POST['products']);
    $product_filter = "AND p.id IN ($products)";
}

// Verifica se o filtro de período foi aplicado
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $date_filter = "AND l.timestamp BETWEEN '$start_date' AND '$end_date'";
}

// Caso o filtro de "Limpar Filtro" seja acionado, resetamos os filtros
if (isset($_POST['clear_filter'])) {
    $product_filter = '';
    $date_filter = '';
}

// Dados para os gráficos
// 1. Gráfico de Quantidade Total por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.quantity 
                        FROM products p 
                        WHERE 1=1 $product_filter");
$stmt->execute();
$quantity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Gráfico de Valor Total de Estoque por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.price * p.quantity AS total_value 
                        FROM products p 
                        WHERE 1=1 $product_filter");
$stmt->execute();
$stock_value_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Gráfico de Preço Médio por Produto
$stmt = $conn->prepare("SELECT p.name AS product_name, p.price 
                        FROM products p 
                        WHERE 1=1 $product_filter");
$stmt->execute();
$price_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Estoque</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .chart-container {}

        .charts-wrapper {}

        @media (max-width: 768px) {
            .charts-wrapper {
                grid-template-columns: 1fr;
            }
        }

        .checkbox-container {
            max-height: 80px;
            overflow-y: auto;
            padding-right: 10px;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include 'menu.php'; ?>

    <!-- Filtros -->
    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md mb-8">
        <form method="POST">
            <div class="flex justify-between items-center mb-4">
                <!-- Filtro de Produtos -->
                <div class="w-1/3">
                    <label for="products" class="block text-sm font-medium text-gray-700">Selecione os Produtos</label>
                    <div class="checkbox-container">
                        <?php
                        // Pega todos os produtos da empresa
                        $product_stmt = $conn->prepare("SELECT id, name FROM products");
                        $product_stmt->execute();
                        $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($products as $product) {
                            echo "<div><input type='checkbox' name='products[]' value='" . $product['id'] . "' class='mr-2'>" . $product['name'] . "</div>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Filtro de Período -->
                <div class="max-w-full flex justify-center items-end gap-2">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Início</label>
                        <input type="date" id="start_date" name="start_date" class="p-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Término</label>
                        <input type="date" id="end_date" name="end_date" class="p-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <button type="submit" class="bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-700">Filtrar</button>
                        <button type="submit" name="clear_filter" class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-700">Limpar Filtro</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 text-center">Dashboard de Estoque</h1>

        <!-- Gráficos -->
        <div class="charts-wrapper grid grid-cols-1 justify-items-center  gap-4">
            <!-- Gráfico de Quantidade Total por Produto -->
            <div class="chart-container w-full">
                <canvas id="quantityChart"></canvas>
            </div>

            <!-- Gráfico de Valor Total de Estoque por Produto -->
            <div class="chart-container  w-full">
                <canvas id="stockValueChart"></canvas>
            </div>

            <!-- Gráfico de Preço Médio por Produto -->
            <div class="chart-container  w-full">
                <canvas id="priceChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Gráfico de Quantidade Total por Produto
        const quantityChart = new Chart(document.getElementById('quantityChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($quantity_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Quantidade em Estoque',
                    data: <?php echo json_encode(array_column($quantity_data, 'quantity')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Valor Total de Estoque por Produto
        const stockValueChart = new Chart(document.getElementById('stockValueChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($stock_value_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Valor Total de Estoque (R$)',
                    data: <?php echo json_encode(array_column($stock_value_data, 'total_value')); ?>,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de Preço Médio por Produto
        const priceChart = new Chart(document.getElementById('priceChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($price_data, 'product_name')); ?>,
                datasets: [{
                    label: 'Preço Médio (R$)',
                    data: <?php echo json_encode(array_column($price_data, 'price')); ?>,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
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