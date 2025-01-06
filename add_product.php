<?php include 'menu.php'; ?>
<?php require 'src/db_connection.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold mb-6 text-blue-700">Add a New Product</h1>
        <form id="productForm" action="src/add_product_backend.php" method="POST">
            <!-- Nome -->
            <label for="name" class="block font-bold mb-2">Name:</label>
            <input
                type="text"
                id="name"
                name="name"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Enter product name"
                required>

            <!-- Preço -->
            <label for="price" class="block font-bold mb-2">Price:</label>
            <input
                type="number"
                id="price"
                name="price"
                step="0.01"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Enter product price"
                required>

            <!-- Quantidade -->
            <label for="quantity" class="block font-bold mb-2">Quantity:</label>
            <input
                type="number"
                id="quantity"
                name="quantity"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Enter product quantity"
                required>


            <!-- Empresa -->
            <label for="company" class="block font-bold mb-2">Company:</label>
            <input
                type="text"
                id="company"
                name="company"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Enter company name"
                required>

            <!-- Descrição -->
            <label for="description" class="block font-bold mb-2">Description:</label>
            <textarea
                id="description"
                name="description"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Enter product description"
                required></textarea>

            <!-- QR Code Hidden -->
            <input type="hidden" id="qrcode" name="qrcode">

            <!-- Botão de Envio -->
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Add Product
            </button>
        </form>

        <!-- QR Code Preview -->
        <div class="mt-6 text-center">
            <canvas id="qrCanvas" class="mx-auto"></canvas>
            <p class="text-gray-500 mt-2">QR Code preview</p>
        </div>
    </div>

    <script>
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
            qr.value = qrData; // Atualizar QR Code
            qrCodeInput.value = qrData; // Preencher o campo oculto
        });
    </script>
</body>

</html>