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

        <?php
        $limit = 9;
        $page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        // Contar apenas produtos que não foram excluídos
        $totalStmt = $conn->query("SELECT COUNT(*) as total FROM products WHERE deleted_at IS NULL");
        $totalRecords = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);

        // Buscar apenas produtos que não foram excluídos
        $stmt = $conn->prepare("SELECT id, qrcode FROM products WHERE deleted_at IS NULL LIMIT :limit OFFSET :offset");
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
                    ?>
                    <div class="bg-white p-4 shadow-md rounded-lg text-center">
                        <label class="block mb-2">
                            <input type="checkbox" name="selected[]" value="<?= $row['id']; ?>">
                            Selecionar
                        </label>
                        <div id="card-<?= $row['id']; ?>"
                            class="flex flex-col items-center gap-1 bg-white p-2 rounded-lg"
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
            </div>
        </form>

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
</body>

</html>