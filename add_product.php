<?php include 'menu.php'; ?>
<?php require 'src/db_connection.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-blue-700">Adicionar Novo Produto</h1>
        <form id="productForm" action="src/add_product_backend.php" method="POST">
            <!-- Nome -->
            <label for="name" class="block font-bold mb-2">Nome:</label>
            <input
                type="text"
                id="name"
                name="name"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite o nome do produto"
                required>

            <!-- Preço -->
            <label for="price" class="block font-bold mb-2">Preço:</label>
            <input
                type="text"
                id="price"
                name="price"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite o preço do produto"
                required>

            <!-- Quantidade -->
            <label for="quantity" class="block font-bold mb-2">Quantidade:</label>
            <input
                type="number"
                id="quantity"
                name="quantity"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite a quantidade do produto"
                required>

            <!-- Empresa -->
            <label for="company" class="block font-bold mb-2">Empresa:</label>
            <input
                type="text"
                id="company"
                name="company"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite o nome da empresa"
                required>

            <!-- Descrição -->
            <label for="description" class="block font-bold mb-2">Descrição:</label>
            <textarea
                id="description"
                name="description"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite a descrição do produto"
                required></textarea>

            <!-- QR Code Hidden -->
            <input type="hidden" id="qrcode" name="qrcode">

            <!-- Botão de Envio -->
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Adicionar Produto
            </button>
        </form>

        <!-- QR Code Preview -->
        <div class="mt-6 text-center">
            <canvas id="qrCanvas" class="mx-auto"></canvas>
            <p class="text-gray-500 mt-2">Pré-visualização do QR Code</p>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const priceInput = document.getElementById("price");

            priceInput.addEventListener("input", function(event) {
                let value = event.target.value;

                // Remove tudo que não for número
                value = value.replace(/\D/g, "");

                // Divide por 100 para garantir duas casas decimais
                let floatValue = parseFloat(value) / 100;

                // Formata no padrão brasileiro (ex: R$ 1.250,00)
                event.target.value = floatValue.toLocaleString("pt-BR", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
            });

            // Antes de enviar, converte o valor para um formato numérico correto (ex: 1250.00)
            document.getElementById("productForm").addEventListener("submit", function() {
                let value = priceInput.value.replace(/\./g, "").replace(",", ".");
                priceInput.value = value; // Transforma para o formato correto antes de enviar
            });

            // Gerador de QR Code
            const form = document.getElementById('productForm');
            const qrCodeInput = document.getElementById('qrcode');
            const qrCanvas = document.getElementById('qrCanvas');
            const qr = new QRious({
                element: qrCanvas,
                size: 200,
            });

            form.addEventListener('input', () => {
                // Gerar dados do QR Code
                const name = document.getElementById('name').value;
                const price = document.getElementById('price').value;
                const company = document.getElementById('company').value;
                const description = document.getElementById('description').value;

                const qrData = JSON.stringify({
                    name,
                    price,
                    company,
                    description
                });
                qr.value = qrData; // Atualiza o QR Code
                qrCodeInput.value = qrData; // Preenche o campo oculto
            });
        });
    </script>
</body>

</html>