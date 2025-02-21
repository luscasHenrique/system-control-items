<?php
include 'menu.php';
require 'src/db_connection.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verifica se o usuário é admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualizar Estoque via QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">Atualizar Estoque</h1>

        <!-- Leitor de QR Code -->
        <div id="reader" class="mb-6"></div>

        <!-- Área para exibir as informações do produto -->
        <div id="productInfo" class="hidden bg-gray-50 p-4 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 text-blue-700">Produto Selecionado</h2>
            <p><strong>ID:</strong> <span id="productId"></span></p>
            <p><strong>Nome:</strong> <span id="productName"></span></p>
            <p><strong>Preço:</strong> <span id="productPrice"></span></p>
            <p><strong>Quantidade Atual:</strong> <span id="productQuantity"></span></p>

            <!-- Seletor de quantidade -->
            <div class="mt-4 flex items-center space-x-2">
                <button onclick="adjustQuantity(-1)" class="bg-red-500 text-white px-3 py-2 rounded-lg">-</button>
                <input type="number" id="quantityChange" value="1" min="1" class="border px-3 py-2 rounded w-16 text-center">
                <button onclick="adjustQuantity(1)" class="bg-green-500 text-white px-3 py-2 rounded-lg">+</button>
            </div>

            <!-- Botões de ação -->
            <div class="flex justify-between mt-6">
                <button id="subtractStock" class="bg-yellow-500 text-white px-4 py-2 rounded-lg <?php echo !$isAdmin ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo !$isAdmin ? 'disabled' : ''; ?>>Baixar Estoque</button>
                <button id="addStock" class="bg-blue-500 text-white px-4 py-2 rounded-lg <?php echo !$isAdmin ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo !$isAdmin ? 'disabled' : ''; ?>>Adicionar Estoque</button>
            </div>
        </div>

        <!-- Mensagem de erro -->
        <div id="errorMsg" class="text-red-500 text-center mt-4"></div>
    </div>

    <script>
        function onScanSuccess(decodedText) {
            console.log(`QR Code lido: ${decodedText}`);
            fetchProductInfo(decodedText);
        }

        function onScanError(errorMessage) {
            console.error(`Erro ao escanear: ${errorMessage}`);
        }

        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 200,
                    height: 200
                }
            },
            onScanSuccess,
            onScanError
        ).catch(err => {
            document.getElementById('errorMsg').innerText = "Erro ao acessar a câmera. Verifique permissões e HTTPS.";
        });

        function fetchProductInfo(qrCodeValue) {
            fetch(`src/fetch_by_qr.php?qrCode=${encodeURIComponent(qrCodeValue)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('productId').innerText = data.product.id;
                        document.getElementById('productName').innerText = data.product.name;
                        document.getElementById('productPrice').innerText = `R$ ${parseFloat(data.product.price).toFixed(2)}`;
                        document.getElementById('productQuantity').innerText = data.product.quantity;
                        document.getElementById('productInfo').classList.remove('hidden');
                    } else {
                        document.getElementById('errorMsg').innerText = data.message || "Produto não encontrado!";
                    }
                })
                .catch(error => {
                    document.getElementById('errorMsg').innerText = "Erro ao buscar informações do produto.";
                });
        }

        function adjustQuantity(value) {
            const quantityInput = document.getElementById('quantityChange');
            let currentValue = parseInt(quantityInput.value);
            if (isNaN(currentValue) || currentValue < 1) currentValue = 1;
            quantityInput.value = Math.max(1, currentValue + value);
        }

        // Bloquear a atualização do estoque caso o usuário não seja administrador
        const isAdmin = <?php echo json_encode($isAdmin); ?>;
        if (!isAdmin) {
            document.getElementById('subtractStock').addEventListener('click', () => alert('Apenas administradores podem atualizar o estoque.'));
            document.getElementById('addStock').addEventListener('click', () => alert('Apenas administradores podem atualizar o estoque.'));
        } else {
            document.getElementById('subtractStock').addEventListener('click', () => updateStock('subtract'));
            document.getElementById('addStock').addEventListener('click', () => updateStock('add'));
        }

        function updateStock(action) {
            const productId = document.getElementById('productId').innerText;
            const quantity = document.getElementById('quantityChange').value;

            fetch('src/update_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        productId,
                        quantity,
                        action
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('productQuantity').innerText = data.newQuantity;
                        alert(data.message);
                    } else {
                        alert("Erro: " + data.message);
                    }
                })
                .catch(error => console.error("Erro ao atualizar estoque:", error));
        }
    </script>
</body>

</html>