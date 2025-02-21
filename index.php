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
            <!-- Campo de Pesquisa -->
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
                    WHERE p.deleted_at IS NULL
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
                                    <div class='flex gap-4 justify-center'>
                                        <button onclick='openEditModal({$row['id']})' class='flex items-center gap-2 text-blue-500 hover:text-blue-700 transition duration-200'>📝 Editar</button>
                                        <button onclick='deleteProduct({$row['id']})' class='flex items-center gap-2 text-red-500 hover:text-red-700 transition duration-200'>🗑️ Excluir</button>
                                    </div>
                                  </td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="flex justify-center mt-4">
            <?php
            // Paginacao - Exibir links de página
            $stmt = $conn->prepare("SELECT COUNT(id) FROM products WHERE deleted_at IS NULL");
            $stmt->execute();
            $totalProducts = $stmt->fetchColumn();
            $totalPages = ceil($totalProducts / $limit);

            for ($i = 1; $i <= $totalPages; $i++) {
                echo "<a href='?page=$i' class='px-4 py-2 mx-1 border border-gray-300 rounded-md hover:bg-gray-200'>$i</a>";
            }
            ?>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div id="editModal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden p-4">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full sm:w-3/4 md:w-2/3 lg:w-1/3 max-h-screen overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 text-blue-700 text-center">Editar Produto</h2>
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

                <!-- Botões -->
                <div class="flex justify-between">
                    <button type="button" id="closeModal" class="bg-gray-500 text-white px-4 py-2 rounded w-1/2 mr-2">Cancelar</button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-1/2">Salvar</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Scripts -->
    <script>
        document.getElementById('searchInput').addEventListener('input', async function() {
            const query = this.value.trim(); // Pega o texto digitado

            try {
                const response = await fetch(`src/search_products.php?query=${query}`);
                const result = await response.json();

                if (result.success) {
                    const tableBody = document.getElementById('productTable');
                    tableBody.innerHTML = ""; // Limpa a tabela para exibir os novos resultados

                    result.data.forEach(product => {
                        tableBody.innerHTML += `
                    <tr>
                        <td class="border border-gray-300 p-2">${product.id}</td>
                        <td class="border border-gray-300 p-2">${product.name}</td>
                        <td class="border border-gray-300 p-2">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</td>
                        <td class="border border-gray-300 p-2">${product.quantity}</td>
                        <td class="border border-gray-300 p-2">${product.company}</td>
                        <td class="border border-gray-300 p-2">${product.description}</td>
                        <td class="border border-gray-300 p-2">${product.username}</td>
                        <td class="border border-gray-300 p-2">R$ ${parseFloat(product.total_value).toFixed(2).replace('.', ',')}</td>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <td class="border border-gray-300 p-2">
                                <button onclick="openEditModal(${product.id})" class="text-blue-500 hover:text-blue-700">Editar</button>
                                <button onclick="deleteProduct(${product.id})" class="text-red-500 hover:text-red-700 ml-2">Excluir</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                `;
                    });
                }
            } catch (error) {
                console.error('Erro na pesquisa:', error);
            }
        });


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

        async function deleteProduct(productId) {
            if (confirm('Tem certeza que deseja excluir este produto?')) {
                try {
                    const response = await fetch(`src/delete_product.php?id=${productId}`, {
                        method: 'GET'
                    });

                    const text = await response.text(); // Captura a resposta como texto
                    console.log(text); // Exibe a resposta no console para depuração

                    const result = JSON.parse(text); // Tenta converter para JSON

                    if (result.success) {
                        alert('Produto excluído com sucesso!');
                        location.reload(); // Recarrega a página para atualizar a lista de produtos
                    } else {
                        alert('Erro ao excluir o produto: ' + result.message);
                    }
                } catch (error) {
                    alert('Erro ao enviar a requisição: ' + error.message);
                    console.error(error);
                }
            }
        }
    </script>

</body>

</html>