<?php include 'menu.php'; ?>
<?php require 'src/db_connection.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Selected Cards</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto mt-10">
        <h1 class="text-3xl font-bold mb-6 text-center text-blue-700">Export Product Cards</h1>
        <!-- Barra de pesquisa -->
        <div class="text-center mb-6">
            <input type="text" id="searchBar" placeholder="Pesquisar..." class="w-1/2 p-2 border border-gray-300 rounded-lg">
        </div>
        <?php
        $limit = 9;
        $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        $totalStmt = $conn->query("SELECT COUNT(*) as total FROM products WHERE deleted_at IS NULL");
        $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);

        // Correção: adicionando os campos necessários na consulta
        $stmt = $conn->prepare("SELECT id, qrcode, name, price, company, description, quantity FROM products WHERE deleted_at IS NULL LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <form id="exportForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($products as $row): ?>
                    <?php
                    $productId = str_pad($row['id'], 6, '0', STR_PAD_LEFT);
                    $qrcodeContent = $row['qrcode'];

                    // Correção: garantindo que os campos existam antes de concatenar
                    $name = isset($row['name']) ? $row['name'] : '';
                    $price = isset($row['price']) ? $row['price'] : '';
                    $company = isset($row['company']) ? $row['company'] : '';
                    $description = isset($row['description']) ? $row['description'] : '';
                    $quantity = isset($row['quantity']) ? $row['quantity'] : '';

                    // Criando a string de busca com os dados corretos
                    $searchData = strtolower(
                        trim(
                            htmlspecialchars(
                                "{$row['id']} {$row['qrcode']} " .
                                    (!empty($row['name']) ? $row['name'] : '') . " " .
                                    (!empty($row['price']) ? $row['price'] : '') . " " .
                                    (!empty($row['company']) ? $row['company'] : '') . " " .
                                    (!empty($row['description']) ? $row['description'] : '') . " " .
                                    (!empty($row['quantity']) ? $row['quantity'] : ''),
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        )
                    );
                    ?>
                    <div class="bg-white p-4 shadow-md rounded-lg text-center card" data-search="<?= $searchData; ?>">
                        <label class="block mb-2">
                            <input type="checkbox" name="selected[]" value="<?= $row['id']; ?>">
                            Selecionar
                        </label>
                        <div id="card-<?= $row['id']; ?>" class="flex flex-col items-center gap-1 bg-white p-2 rounded-lg"
                            style="width: 380px; height: 120px;">
                            <div class="flex items-center justify-center space-x-4">
                                <img src="assets/img/Logo.png" alt="Logo" class="h-[80px] w-[80px]">
                                <canvas id="qrCanvas-<?= $row['id']; ?>" class="h-[80px] w-[80px]"></canvas>
                            </div>
                            <p class="text-sm font-bold">Código: <?= $productId; ?></p>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const qrContent = '<?= $qrcodeContent; ?>';
                            if (qrContent) {
                                new QRious({
                                    element: document.getElementById('qrCanvas-<?= $row['id']; ?>'),
                                    value: qrContent,
                                    size: 180
                                });
                            }
                        });
                    </script>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-8">
                <button type="button" id="exportPdf" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Exportar para PDF
                </button>
                <button type="button" id="exportImage" class="hidden bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Exportar como Imagem
                </button>
                <button type="button" id="exportPdfBatch" class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                    Exportar PDF em Lote
                </button>
            </div>
        </form>

        <!-- Paginação com setas e o marcador de página -->
        <div class="flex justify-center mt-8 space-x-4">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1; ?>" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded flex items-center space-x-2">
                    <span>&lt;</span>
                    <span>Anterior</span>
                </a>
            <?php endif; ?>

            <span class="flex items-center space-x-2 text-gray-700">
                <span>Página <?= $page; ?> de <?= $totalPages; ?></span>
            </span>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1; ?>" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded flex items-center space-x-2">
                    <span>Próxima</span>
                    <span>&gt;</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.getElementById('searchBar').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.card');

            cards.forEach(card => {
                const searchData = (card.getAttribute('data-search') || '').toLowerCase().trim();
                if (searchData.includes(searchValue)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });



        document.getElementById('exportPdf').addEventListener('click', async () => {
            const selectedCards = document.querySelectorAll('input[name="selected[]"]:checked');
            if (selectedCards.length === 0) {
                alert('Selecione pelo menos um cartão para exportar.');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            const cardWidth = 100;
            const cardHeight = 30;
            const marginTop = 5;
            const marginLeft = 8;
            const columnGap = 5;
            const rowGap = 7;
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            let x = marginLeft;
            let y = marginTop;

            for (const card of selectedCards) {
                const cardId = card.value;
                const cardElement = document.getElementById(`card-${cardId}`);
                if (!cardElement) continue;

                const canvas = await html2canvas(cardElement, {
                    scale: 2
                });
                const imgData = canvas.toDataURL('image/png');

                pdf.addImage(imgData, 'PNG', x, y, cardWidth, cardHeight);

                if ((x + cardWidth + columnGap) > (pageWidth - marginLeft)) {
                    x = marginLeft;
                    y += cardHeight + rowGap;
                } else {
                    x += cardWidth + columnGap;
                }

                if (y + cardHeight > pageHeight - marginTop) {
                    pdf.addPage();
                    x = marginLeft;
                    y = marginTop;
                }
            }

            pdf.save('selected-cards.pdf');
        });

        document.getElementById('exportImage').addEventListener('click', async () => {
            const selectedCards = document.querySelectorAll('input[name="selected[]"]:checked');
            if (selectedCards.length === 0) {
                alert('Selecione pelo menos um cartão para exportar.');
                return;
            }

            for (const card of selectedCards) {
                const cardId = card.value;
                const cardElement = document.getElementById(`card-${cardId}`);
                if (!cardElement) continue;

                const canvas = await html2canvas(cardElement, {
                    scale: 2
                });
                const imgData = canvas.toDataURL('image/png');

                const link = document.createElement('a');
                link.href = imgData;
                link.download = `card_${cardId}.png`;
                link.click();
            }
        });

        document.getElementById('exportPdfBatch').addEventListener('click', async () => {
            const selectedCards = document.querySelectorAll('input[name="selected[]"]:checked');
            if (selectedCards.length === 0) {
                alert('Selecione pelo menos um cartão para exportar.');
                return;
            }

            if (selectedCards.length > 1) {
                alert('Por favor, selecione apenas um cartão para preencher toda a folha.');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = pdf.internal.pageSize.getWidth(); // 210mm
            const pageHeight = pdf.internal.pageSize.getHeight(); // 297mm

            const cardWidth = 100; // Largura da etiqueta
            const cardHeight = 30; // Altura da etiqueta
            const columns = 2; // Número de colunas por linha
            const rows = 9; // Número de linhas por página
            const marginX = 8; // Margem esquerda
            const marginY = 5; // Margem superior
            const columnGap = 5; // Espaço entre colunas
            const rowGap = 7; // Espaço entre linhas

            let x = marginX;
            let y = marginY;
            let count = 0;

            const cardId = selectedCards[0].value;
            const cardElement = document.getElementById(`card-${cardId}`);
            if (!cardElement) return;

            const canvas = await html2canvas(cardElement, {
                scale: 2
            });
            const imgData = canvas.toDataURL('image/png');

            for (let i = 0; i < columns * rows; i++) {
                pdf.addImage(imgData, 'PNG', x, y, cardWidth, cardHeight);

                count++;
                if (count % columns === 0) {
                    x = marginX;
                    y += cardHeight + rowGap;
                } else {
                    x += cardWidth + columnGap;
                }

                // Adiciona uma nova página se atingir o limite de etiquetas na folha
                if (count === columns * rows && i !== columns * rows - 1) {
                    pdf.addPage();
                    x = marginX;
                    y = marginY;
                    count = 0;
                }
            }

            pdf.save(`etiqueta_lote_${cardId}.pdf`);
        });
    </script>
</body>

</html>