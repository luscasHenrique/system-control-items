<?php
require 'src/db_connection.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id'])) {
    die("ID do produto não fornecido!");
}

$id = $_GET['id'];

// Buscar o produto no banco de dados
$stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produto não encontrado!");
}

// Atualizar o produto ao enviar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity']; // Adicionar o campo quantity
    $company = $_POST['company'];
    $description = $_POST['description'];

    $updateStmt = $conn->prepare("UPDATE products SET name = :name, price = :price, quantity = :quantity, company = :company, description = :description WHERE id = :id");
    $updateStmt->bindParam(':name', $name);
    $updateStmt->bindParam(':price', $price);
    $updateStmt->bindParam(':quantity', $quantity, PDO::PARAM_INT); // Associar quantity
    $updateStmt->bindParam(':company', $company);
    $updateStmt->bindParam(':description', $description);
    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($updateStmt->execute()) {
        header("Location: index.php?success=Produto atualizado com sucesso!");
        exit();
    } else {
        echo "Erro ao atualizar o produto!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Edit Product</h1>
        <form method="POST">
            <label for="name" class="block font-bold mb-2">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']); ?>" class="w-full border border-gray-300 rounded-lg p-2 mb-4" required>

            <label for="price" class="block font-bold mb-2">Price:</label>
            <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($product['price']); ?>" class="w-full border border-gray-300 rounded-lg p-2 mb-4" required>

            <label for="quantity" class="block font-bold mb-2">Quantity:</label>
            <input 
                type="number" 
                id="quantity" 
                name="quantity" 
                value="<?= htmlspecialchars($product['quantity']); ?>" 
                class="w-full border border-gray-300 rounded-lg p-2 mb-4" 
                required>

            <label for="company" class="block font-bold mb-2">Company:</label>
            <input type="text" id="company" name="company" value="<?= htmlspecialchars($product['company']); ?>" class="w-full border border-gray-300 rounded-lg p-2 mb-4" required>

            <label for="description" class="block font-bold mb-2">Description:</label>
            <textarea id="description" name="description" class="w-full border border-gray-300 rounded-lg p-2 mb-4" required><?= htmlspecialchars($product['description']); ?></textarea>

            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-700">Save Changes</button>
        </form>
    </div>
</body>
</html>
