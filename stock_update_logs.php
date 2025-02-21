<?php
session_start();
require 'src/db_connection.php';

date_default_timezone_set('America/Sao_Paulo');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Buscar os registros de logs de alteração do estoque
$stmt = $conn->prepare("
    SELECT 
        l.id, 
        l.product_id, 
        COALESCE(p.name, 'Produto Removido') AS product_name, 
        p.company, 
        u.username AS user_name, 
        l.change_value, 
        l.current_quantity, 
        l.description, 
        l.status, 
        l.timestamp
    FROM stock_logs l
    LEFT JOIN products p ON l.product_id = p.id
    JOIN users u ON l.user_id = u.id
    ORDER BY l.timestamp DESC
");

$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Alterações no Estoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include 'menu.php'; ?>

    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 text-center">Registro de Alterações no Estoque</h1>

        <!-- Campo de Pesquisa e Botão de Exportação -->
        <div class="flex justify-between items-center mb-4">
            <input type="text" id="searchInput" placeholder="Pesquisar..." class="w-2/3 border border-gray-300 rounded-lg p-2 text-center">
            <button id="exportCSV" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                Exportar CSV
            </button>
        </div>

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table id="logTable" class="w-full border-collapse border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 p-2">ID</th>
                        <th class="border border-gray-300 p-2">ID Produto</th>
                        <th class="border border-gray-300 p-2">Nome</th>
                        <th class="border border-gray-300 p-2">Empresa</th>
                        <th class="border border-gray-300 p-2">Usuário</th>
                        <th class="border border-gray-300 p-2">Valor da Atualização</th>
                        <th class="border border-gray-300 p-2">Quantidade Atual</th>
                        <th class="border border-gray-300 p-2">Descrição</th>
                        <th class="border border-gray-300 p-2">Status</th>
                        <th class="border border-gray-300 p-2">Data e Hora</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="border border-gray-300 p-2"><?= $log['id']; ?></td>
                            <td class="border border-gray-300 p-2 font-bold"><?= $log['product_id']; ?></td>
                            <td class="border border-gray-300 p-2"><?= htmlspecialchars($log['product_name']); ?></td>
                            <td class="border border-gray-300 p-2"><?= htmlspecialchars($log['company'] ?? '-'); ?></td>
                            <td class="border border-gray-300 p-2"><?= htmlspecialchars($log['user_name']); ?></td>
                            <td class="border border-gray-300 p-2 font-bold <?= $log['change_value'] < 0 ? 'text-red-500' : 'text-green-500'; ?>">
                                <?= $log['change_value'] > 0 ? '+' : ''; ?><?= $log['change_value']; ?>
                            </td>
                            <td class="border border-gray-300 p-2"><?= $log['current_quantity']; ?></td>
                            <td class="border border-gray-300 p-2"><?= htmlspecialchars($log['description']); ?></td>
                            <td class="border border-gray-300 p-2 font-bold <?= $log['status'] == 'Excluído' ? 'text-red-500' : 'text-blue-500'; ?>">
                                <?= $log['status']; ?>
                            </td>
                            <td class="border border-gray-300 p-2"><?= date("d/m/Y H:i", strtotime($log['timestamp'] . ' UTC')) ?></td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Pesquisa em tempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            let query = this.value.toLowerCase();
            let rows = document.querySelectorAll("#logTableBody tr");

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        });

        // Exportar CSV
        document.getElementById('exportCSV').addEventListener('click', function() {
            let csv = [];
            let rows = document.querySelectorAll("table tr");

            for (let row of rows) {
                let cols = row.querySelectorAll("td, th");
                let data = [];
                cols.forEach(col => data.push(col.innerText));
                csv.push(data.join(","));
            }

            let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
            let encodedUri = encodeURI(csvContent);
            let link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "log_estoque.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>

</body>

</html>