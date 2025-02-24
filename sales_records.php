<?php
include 'menu.php';
require 'src/db_connection.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Consulta para obter os registros de vendas
$stmt = $conn->prepare("SELECT s.id, p.id AS product_id, p.name AS product_name, u.name AS user_name, s.quantity, s.total_value, s.status, s.description, s.created_at
                        FROM sales s
                        JOIN products p ON s.product_id = p.id
                        JOIN users u ON s.user_id = u.id");
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Vendas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-6xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">Registros de Vendas</h1>

        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b text-left">ID</th>
                    <th class="py-2 px-4 border-b text-left">ID Produto</th>
                    <th class="py-2 px-4 border-b text-left">Produto</th>
                    <th class="py-2 px-4 border-b text-left">Empresa</th>
                    <th class="py-2 px-4 border-b text-left">Usuário</th>
                    <th class="py-2 px-4 border-b text-left">Quantidade Estoque</th>
                    <th class="py-2 px-4 border-b text-left">Valor Total Estoque</th>
                    <th class="py-2 px-4 border-b text-left">Quantidade Vendida</th>
                    <th class="py-2 px-4 border-b text-left">Valor Total Venda</th>
                    <th class="py-2 px-4 border-b text-left">Descrição</th>
                    <th class="py-2 px-4 border-b text-left">Status</th>
                    <th class="py-2 px-4 border-b text-left">Ações</th>
                    <th class="py-2 px-4 border-b text-left">Data</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td class="py-2 px-4 border-b"><?php echo $sale['id']; ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['product_id']; ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['product_name']; ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['user_name']; ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['quantity']; ?></td>
                        <td class="py-2 px-4 border-b">R$ <?php echo number_format($sale['total_value'], 2, ',', '.'); ?></td>
                        <td class="py-2 px-4 border-b">R$ <?php echo number_format($sale['total_value'], 2, ',', '.'); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['quantity']; ?></td>
                        <td class="py-2 px-4 border-b">R$ <?php echo number_format($sale['total_value'], 2, ',', '.'); ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['description']; ?></td>
                        <td class="py-2 px-4 border-b"><?php echo $sale['status']; ?></td>
                        <td class="py-2 px-4 border-b">
                            <a href="edit_sale.php?id=<?php echo $sale['id']; ?>" class="text-blue-500 hover:text-blue-700">Editar</a>
                        </td>
                        <td class="py-2 px-4 border-b"><?php echo date('d/m/Y H:i:s', strtotime($sale['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>