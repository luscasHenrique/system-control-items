<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'src/db_connection.php';

// **Registrar log de acesso à página**
$user_id = $_SESSION['user_id'];
$action = "Usuário $user_id acessou a página de lista de produtos.";
$logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
$logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$logStmt->bindParam(':action', $action);
$logStmt->execute();

include 'menu.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Produtos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="max-w-7xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-blue-700 text-center">Lista de Produtos</h1>

        <!-- Campo de Pesquisa e Exportação -->
        <div class="flex justify-between items-center mb-4">
            <input type="text" id="searchInput" placeholder="Pesquisar produtos..." class="w-2/3 border border-gray-300 rounded-lg p-2 text-center">
            <a href="export_to_csv.php" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                Download CSV
            </a>
        </div>

        <!-- Tabela de Produtos -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 p-2">ID</th>
                        <th class="border border-gray-300 p-2">Nome</th>
                        <th class="border border-gray-300 p-2">Preço</th>
                        <th class="border border-gray-300 p-2">Quantidade</th>
                        <th class="border border-gray-300 p-2">Empresa</th>
                        <th class="border border-gray-300 p-2">Descrição</th>
                        <th class="border border-gray-300 p-2">Usuário</th>
                        <th class="border border-gray-300 p-2">Valor Total</th>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th class="border border-gray-300 p-2">Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="productTable">
                    <?php
                    // Configuração de paginação
                    $limit = 10;
                    $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Buscar produtos
                    $stmt = $conn->prepare("
                        SELECT p.id, p.name, p.price, p.quantity, p.company, p.description, u.username, 
                        (p.price * p.quantity) AS total_value
                        FROM products p 
                        JOIN users u ON p.user_id = u.id 
                        LIMIT :limit OFFSET :offset
                    ");
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
                        echo "<td class='border border-gray-300 p-2'>{$row['username']}</td>";
                        echo "<td class='border border-gray-300 p-2'>R$ " . number_format($row['total_value'], 2, ',', '.') . "</td>";

                        if ($_SESSION['role'] === 'admin') {
                            echo "<td class='border border-gray-300 p-2'>
                                    <button onclick='openEditModal({$row['id']})' class='text-blue-500 hover:text-blue-700'>Editar</button>
                                  </td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
            <h2 class="text-xl font-bold mb-4 text-blue-700">Editar Produto</h2>
            <form id="editForm">
                <input type="hidden" id="editProductId">
                <label class="block font-bold">Nome:</label>
                <input type="text" id="editName" class="w-full border p-2 rounded mb-2">
                <label class="block font-bold">Preço:</label>
                <input type="number" id="editPrice" class="w-full border p-2 rounded mb-2" step="0.01">
                <label class="block font-bold">Quantidade:</label>
                <input type="number" id="editQuantity" class="w-full border p-2 rounded mb-2">
                <label class="block font-bold">Empresa:</label>
                <input type="text" id="editCompany" class="w-full border p-2 rounded mb-2">
                <label class="block font-bold">Descrição:</label>
                <textarea id="editDescription" class="w-full border p-2 rounded mb-4"></textarea>
                <div class="flex justify-between">
                    <button type="button" id="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        async function openEditModal(productId) {
            try {
                const response = await fetch(`src/get_product.php?id=${productId}`);
                const product = await response.json();

                if (!product.success) {
                    alert("Erro ao carregar os dados do produto.");
                    return;
                }

                document.getElementById('editProductId').value = product.data.id;
                document.getElementById('editName').value = product.data.name;
                document.getElementById('editPrice').value = product.data.price;
                document.getElementById('editQuantity').value = product.data.quantity;
                document.getElementById('editCompany').value = product.data.company;
                document.getElementById('editDescription').value = product.data.description;

                document.getElementById('editModal').classList.remove('hidden');
            } catch (error) {
                alert("Erro ao abrir o modal.");
            }
        }

        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('editModal').classList.add('hidden');
        });

        document.getElementById('editForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            const productId = document.getElementById('editProductId').value;
            const name = document.getElementById('editName').value;
            const price = document.getElementById('editPrice').value;
            const quantity = document.getElementById('editQuantity').value;
            const company = document.getElementById('editCompany').value;
            const description = document.getElementById('editDescription').value;

            const data = {
                productId,
                name,
                price,
                quantity,
                company,
                description
            };

            try {
                const response = await fetch('src/update_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                console.log(result); // Exibe a resposta no console para depuração

                if (result.success) {
                    alert('Produto atualizado com sucesso!');
                    document.getElementById('editModal').classList.add('hidden'); // Fecha o modal
                    location.reload(); // Atualiza a página para refletir os dados atualizados
                } else {
                    alert('Erro ao atualizar o produto: ' + result.message);
                }
            } catch (error) {
                alert('Erro ao enviar os dados: ' + error.message);
                console.error(error);
            }
        });
    </script>

</body>

</html>