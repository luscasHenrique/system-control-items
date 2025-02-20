<?php
session_start();
require 'src/db_connection.php';

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
        p.name AS product_name, 
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

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-center">
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
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="border border-gray-300 p-2"><?= $log['id']; ?></td>
                            <td class="border border-gray-300 p-2 font-bold"><?= $log['product_id']; ?></td>
                            <td class="border border-gray-300 p-2"><?= htmlspecialchars($log['product_name'] ?? 'Produto Removido'); ?></td>
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
                            <td class="border border-gray-300 p-2"><?= date("d/m/Y H:i", strtotime($log['timestamp'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>