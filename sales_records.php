<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'src/db_connection.php';

// Registrar log de acesso à página
$user_id = $_SESSION['user_id'];
$action = "Usuário $user_id acessou a página de Registro de Vendas.";
$logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
$logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$logStmt->bindParam(':action', $action);
$logStmt->execute();

// Configuração da paginação
$limit = 10;
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtrar apenas vendas e estornos
$query = "
    SELECT 
        s.id, 
        s.product_id, 
        p.name AS product_name, 
        p.company, 
        u.username AS user_name, 
        s.stock_after_action AS stock_quantity,  
        (s.stock_after_action * p.price) AS total_stock_value,  
        s.quantity AS sold_quantity, 
        (s.quantity * p.price) AS total_sales_value, 
        p.description, 
        s.status, 
        s.created_at
    FROM sales s
    JOIN products p ON s.product_id = p.id
    JOIN users u ON s.user_id = u.id
    WHERE s.status IN ('Venda', 'Estorno')
";

// Contar o número total de registros filtrados
$totalQuery = "SELECT COUNT(*) as total FROM sales s 
               JOIN products p ON s.product_id = p.id 
               WHERE s.status IN ('Venda', 'Estorno')";

$totalStmt = $conn->prepare($totalQuery);
$totalStmt->execute();
$totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit);

// Adicionar ordenação e paginação
$query .= " ORDER BY s.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Vendas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-container {
            overflow-x: auto;
            width: 100%;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
        }

        table {
            min-width: 1000px;
        }

        .status-venda {
            color: #065f46;
            font-weight: bold;
        }

        /* Verde */
        .status-estorno {
            color: #92400e;
            font-weight: bold;
        }

        /* Amarelo */
        .valor-venda {
            color: #065f46;
            font-weight: bold;
        }

        /* Verde */
        .valor-estorno {
            color: #92400e;
            font-weight: bold;
        }

        /* Amarelo */
    </style>
</head>

<body class="bg-gray-100">
    <?php include 'menu.php'; ?>

    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-center text-blue-700">Registros de Vendas</h1>

        <!-- Filtros e Exportação -->
        <div class="filters-container mb-4">
            <input type="text" id="searchInput" placeholder="Pesquisar..." class="p-2 border border-gray-300 rounded-lg text-center flex-grow">
            <select id="filterStatus" class="p-2 border border-gray-300 rounded-lg">
                <option value="">Filtrar por Status</option>
                <option value="Venda">Venda</option>
                <option value="Estorno">Estorno</option>
            </select>
            <input type="date" id="filterDate" class="p-2 border border-gray-300 rounded-lg">
            <button id="exportCSV" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                Exportar CSV
            </button>
        </div>

        <!-- Tabela Responsiva -->
        <div class="overflow-x-auto">
            <table id="salesTable" class="w-full border-collapse border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">ID Produto</th>
                        <th class="py-2 px-4 border-b min-w-[150px]">Produto</th>
                        <th class="py-2 px-4 border-b hidden md:table-cell">Empresa</th>
                        <th class="py-2 px-4 border-b hidden md:table-cell">Usuário</th>
                        <th class="py-2 px-4 border-b">Qtd Estoque</th>
                        <th class="py-2 px-4 border-b hidden md:table-cell">Valor Estoque</th>
                        <th class="py-2 px-4 border-b">Qtd Vendida</th>
                        <th class="py-2 px-4 border-b min-w-[150px]">Valor Venda</th>
                        <th class="py-2 px-4 border-b hidden lg:table-cell">Descrição</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <?php
                        $statusClass = $sale['status'] === 'Venda' ? 'text-green-700 font-bold' : 'text-yellow-700 font-bold';
                        $valorClass = $sale['status'] === 'Venda' ? 'text-green-700 font-bold' : 'text-yellow-700 font-bold';
                        ?>
                        <tr>
                            <td class="py-2 px-4 border-b"><?= $sale['id']; ?></td>
                            <td class="py-2 px-4 border-b"><?= $sale['product_id']; ?></td>
                            <td class="py-2 px-4 border-b min-w-[150px] truncate"><?= htmlspecialchars($sale['product_name']); ?></td>
                            <td class="py-2 px-4 border-b hidden md:table-cell"><?= htmlspecialchars($sale['company']); ?></td>
                            <td class="py-2 px-4 border-b hidden md:table-cell"><?= htmlspecialchars($sale['user_name']); ?></td>
                            <td class="py-2 px-4 border-b"><?= $sale['stock_quantity']; ?></td>
                            <td class="py-2 px-4 border-b hidden md:table-cell">R$ <?= number_format($sale['total_stock_value'], 2, ',', '.'); ?></td>
                            <td class="py-2 px-4 border-b"><?= $sale['sold_quantity']; ?></td>
                            <td class="py-2 px-4 border-b min-w-[150px] truncate"><span class="<?= $valorClass; ?>">R$ <?= number_format($sale['total_sales_value'], 2, ',', '.'); ?></span></td>
                            <td class="py-2 px-4 border-b hidden lg:table-cell"><?= htmlspecialchars($sale['description']); ?></td>
                            <td class="py-2 px-4 border-b"><span class="<?= $statusClass; ?>"><?= $sale['status']; ?></span></td>
                            <td class="py-2 px-4 border-b whitespace-nowrap"><?= date('d/m/Y H:i:s', strtotime($sale['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <!-- Paginação -->
        <div class="flex justify-center mt-8 space-x-4">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1; ?>" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Anterior</a>
            <?php endif; ?>
            <span>Página <?= $page; ?> de <?= $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1; ?>" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Próxima</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.getElementById('searchInput').addEventListener('input', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);
        document.getElementById('filterDate').addEventListener('change', filterTable);

        function filterTable() {
            let searchQuery = document.getElementById('searchInput').value.toLowerCase();
            let selectedStatus = document.getElementById('filterStatus').value;
            let selectedDate = document.getElementById('filterDate').value;

            let rows = document.querySelectorAll("#salesTable tbody tr");

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                let status = row.children[10].innerText;
                let date = row.children[11].innerText.split(" ")[0];

                let matchesSearch = text.includes(searchQuery);
                let matchesStatus = selectedStatus === "" || status === selectedStatus;
                let matchesDate = selectedDate === "" || date === selectedDate.split("-").reverse().join("/");

                row.style.display = matchesSearch && matchesStatus && matchesDate ? "" : "none";
            });
        }

        document.getElementById('exportCSV').addEventListener('click', function() {
            let csv = [];
            let rows = document.querySelectorAll("#salesTable tr");

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
            link.setAttribute("download", "registro_vendas.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>

</body>

</html>