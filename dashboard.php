<?php include 'menu.php'; ?>
<?php require 'src/db_connection.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Relatórios</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="container mx-auto px-4 py-8">
        <!-- Título -->
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Dashboard de Relatórios</h1>

        <!-- Filtros -->
        <div class="flex justify-center gap-6 mb-8">
            <input type="text" id="productName" class="border-2 p-2 rounded-md" placeholder="Produto (filtro)">
            <input type="date" id="startDate" class="border-2 p-2 rounded-md">
            <input type="date" id="endDate" class="border-2 p-2 rounded-md">
            <button id="applyFilters" class="bg-blue-500 text-white px-4 py-2 rounded-md">Aplicar Filtros</button>
        </div>

        <!-- Gráfico de Estoque -->
        <div class="mb-8">
            <canvas id="stockChart"></canvas>
        </div>

    </div>

    <script>
        const ctx = document.getElementById('stockChart').getContext('2d');
        let stockChart;

        // Função para buscar os dados e renderizar o gráfico
        function fetchChartData() {
            const productName = document.getElementById('productName').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            // Requisição para o backend para pegar os dados
            fetch(`/path/to/your/api.php?product_name=${productName}&start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    const dates = data.map(item => item.date);
                    const changes = data.map(item => item.total_change);

                    // Se o gráfico já existir, destruímos o anterior
                    if (stockChart) {
                        stockChart.destroy();
                    }

                    // Criar o gráfico
                    stockChart = new Chart(ctx, {
                        type: 'line', // ou 'bar' para gráfico de barras
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Variação de Estoque',
                                data: changes,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    type: 'category',
                                    title: {
                                        display: true,
                                        text: 'Data'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Valor'
                                    },
                                    min: 0,
                                    max: 1000
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error("Erro ao buscar dados:", error));
        }

        // Aplicar os filtros
        document.getElementById('applyFilters').addEventListener('click', (e) => {
            e.preventDefault();
            fetchChartData();
        });

        // Carregar os dados assim que a página for carregada
        window.onload = fetchChartData;
    </script>

</body>

</html>