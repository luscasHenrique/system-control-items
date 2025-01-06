<?php include 'menu.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Biblioteca para leitura de QR Code -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" integrity="sha512-r6rDA7W6ZeQhvl8S7yRVQUKVHdexq+GAlNkNNqVC7YyIV+NwqCTJe2hDWCiffTyRNOeGEzRRJ9ifvRm/HCzGYg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4 text-center">Scan QR Code</h1>
        <!-- Div onde o leitor será exibido -->
        <div id="reader" class="mb-6"></div>
        <!-- Área para exibir informações do produto -->
        <div id="productInfo" class="hidden bg-gray-50 p-4 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 text-blue-700">Informações do Produto</h2>
            <p><strong>ID:</strong> <span id="productId"></span></p>
            <p><strong>Nome:</strong> <span id="productName"></span></p>
            <p><strong>Preço:</strong> <span id="productPrice"></span></p>
            <p><strong>Descrição:</strong> <span id="productDescription"></span></p>
            <p><strong>Empresa:</strong> <span id="productCompany"></span></p>
            <p><strong>Quantidade:</strong> <span id="productQuantity"></span></p>
        </div>
        <!-- Botão de refresh -->
        <div class="text-center mt-4">
            <button id="refreshBtn" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 hidden">Limpar Campos</button>
        </div>
        <!-- Mensagem de erro, se necessário -->
        <div id="errorMsg" class="text-red-500 text-center mt-4"></div>
    </div>

    <script>
        // Inicializar o leitor de QR Code
        function onScanSuccess(decodedText) {
            console.log(`QR Code lido: ${decodedText}`);
            fetchProductInfo(decodedText); // Buscar informações no backend
        }

        function onScanError(errorMessage) {
            console.error(`Erro ao escanear: ${errorMessage}`);
        }

        // Inicializar o Html5Qrcode
        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start({
                facingMode: "environment"
            }, // Usar câmera traseira
            {
                fps: 10,
                qrbox: {
                    width: 200,
                    height: 200
                }
            },
            onScanSuccess,
            onScanError
        ).catch(err => {
            console.error("Erro ao inicializar câmera: ", err);
            document.getElementById('errorMsg').innerText = "Erro ao acessar a câmera. Verifique permissões e HTTPS.";
        });

        // Buscar informações do produto no backend
        function fetchProductInfo(qrCodeValue) {
            fetch(`src/fetch_by_qr.php?qrCode=${encodeURIComponent(qrCodeValue)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Exibir as informações do produto
                        document.getElementById('productId').innerText = data.product.id;
                        document.getElementById('productName').innerText = data.product.name;
                        document.getElementById('productPrice').innerText = parseFloat(data.product.price).toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        });
                        document.getElementById('productQuantity').innerText = data.product.quantity;
                        document.getElementById('productCompany').innerText = data.product.company;
                        document.getElementById('productDescription').innerText = data.product.description;
                        document.getElementById('productInfo').classList.remove('hidden');
                        document.getElementById('refreshBtn').classList.remove('hidden');
                    } else {
                        document.getElementById('errorMsg').innerText = data.message || "Produto não encontrado!";
                    }
                })
                .catch(error => {
                    console.error("Erro ao buscar informações do produto:", error);
                    document.getElementById('errorMsg').innerText = "Erro ao buscar informações do produto.";
                });
        }

        // Função para limpar os campos
        document.getElementById('refreshBtn').addEventListener('click', () => {
            document.getElementById('productId').innerText = '';
            document.getElementById('productName').innerText = '';
            document.getElementById('productPrice').innerText = '';
            document.getElementById('productQuantity').innerText = '';
            document.getElementById('productCompany').innerText = '';
            document.getElementById('productDescription').innerText = '';
            document.getElementById('productInfo').classList.add('hidden');
            document.getElementById('refreshBtn').classList.add('hidden');
            document.getElementById('errorMsg').innerText = '';
        });
    </script>
</body>

</html>