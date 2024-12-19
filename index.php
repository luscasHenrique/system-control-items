<?php include 'menu.php'; ?>
<?php require 'src/db_connection.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-blue-700 text-center">Product List</h1>

        <!-- Campo de Pesquisa -->
        <input
            type="text"
            id="searchInput"
            placeholder="Search for products..."
            class="w-full border border-gray-300 rounded-lg p-2 mb-4 text-center">

        <!-- Contêiner da Tabela com Overflow -->
        <div class="overflow-x-auto">
            <!-- Tabela de Produtos -->
            <table class="w-full border-collapse border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 p-2">ID</th>
                        <th class="border border-gray-300 p-2">Name</th>
                        <th class="border border-gray-300 p-2">Price</th>
                        <th class="border border-gray-300 p-2">Quantity</th>
                        <th class="border border-gray-300 p-2">Company</th>
                        <th class="border border-gray-300 p-2">Description</th>
                        <th class="border border-gray-300 p-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    <?php
                    // Configurações de paginação
                    $limit = 10; // Registros por página
                    $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Contar total de registros
                    $totalStmt = $conn->query("SELECT COUNT(*) as total FROM products");
                    $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
                    $totalPages = ceil($totalRecords / $limit);

                    // Buscar registros da página atual
                    $stmt = $conn->prepare("SELECT id, name, price, quantity, company, description FROM products LIMIT :limit OFFSET :offset");
                    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td class='border border-gray-300 p-2'>{$row['id']}</td>";
                        echo "<td class='border border-gray-300 p-2'>{$row['name']}</td>";
                        echo "<td class='border border-gray-300 p-2'>R$ " . number_format($row['price'], 2, ',', '.') . "</td>";
                        echo "<td class='border border-gray-300 p-2'>{$row['quantity']}</td>";
                        echo "<td class='border border-gray-300 p-2'>{$row['company']}</td>";
                        echo "<td class='border border-gray-300 p-2'>{$row['description']}</td>";
                        echo "<td class='border border-gray-300 p-2'>
                            <a href='edit_product.php?id={$row['id']}' class='text-blue-500 hover:text-blue-700'>Edit</a> | 
                            <a href='delete_product.php?id={$row['id']}' class='text-red-500 hover:text-red-700'>Delete</a>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Navegação de Paginação -->
        <div class="flex justify-center mt-8 space-x-4">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1; ?>" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i; ?>" class="px-4 py-2 rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-gray-300 hover:bg-gray-400'; ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1; ?>" class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded">Próxima</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="text-center mt-10 text-gray-600">
        <p>&copy; <?php echo date('Y'); ?> Inventory System. All rights reserved.</p>
    </footer>

    <!-- Script para Filtro de Pesquisa -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const productTable = document.getElementById('productTable');

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            const rows = productTable.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        found = true;
                        break;
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        });
    </script>
</body>

</html>