<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit(); // Apenas administradores podem criar usuários
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Usuário</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Criar Usuário</h1>
        <form action="src/register_user.php" method="POST">
            <label for="username" class="block font-bold mb-2">Usuário:</label>
            <input
                type="text"
                id="username"
                name="username"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite o nome de usuário"
                required>

            <label for="password" class="block font-bold mb-2">Senha:</label>
            <input
                type="password"
                id="password"
                name="password"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite a senha"
                required>

            <label for="role" class="block font-bold mb-2">Role:</label>
            <select
                id="role"
                name="role"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                required>
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-700">
                Criar
            </button>
        </form>

        <?php if (isset($_GET['success'])): ?>
            <p class="text-green-500 text-center mt-4">Usuário criado com sucesso!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="text-red-500 text-center mt-4"><?= htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>