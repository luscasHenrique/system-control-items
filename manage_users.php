<?php
session_start();
require 'src/db_connection.php';

// Verificar se o usuário é admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Criar um novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (!empty($username) && !empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
    }
}

// Atualizar cargo do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET role = :role WHERE id = :id");
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

// Alterar senha do usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->bindParam(':password', $new_password);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

// Excluir usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

// Buscar todos os usuários
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include 'menu.php'; ?>

    <div class="max-w-5xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 text-center">Gerenciar Usuários</h1>

        <!-- Criar novo usuário -->
        <form method="POST" class="mb-6 bg-gray-50 p-4 rounded-lg">
            <h2 class="text-lg font-semibold mb-4 text-gray-800">Criar Novo Usuário</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" name="username" placeholder="Usuário" required class="border p-2 rounded w-full">
                <input type="password" name="password" placeholder="Senha" required class="border p-2 rounded w-full">
                <select name="role" class="border p-2 rounded w-full">
                    <option value="user">Usuário</option>
                    <option value="admin">Administrador</option>
                </select>
                <button type="submit" name="create_user" class="bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto">Criar</button>
            </div>
        </form>

        <!-- Listagem de usuários -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 text-center">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border border-gray-300 p-2">ID</th>
                        <th class="border border-gray-300 p-2">Usuário</th>
                        <th class="border border-gray-300 p-2">Cargo</th>
                        <th class="border border-gray-300 p-2">Criado em</th>
                        <th class="border border-gray-300 p-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="border border-gray-300 p-2"><?= $user['id']; ?></td>
                            <td class="border border-gray-300 p-2"><?= $user['username']; ?></td>
                            <td class="border border-gray-300 p-2">
                                <form method="POST" class="flex items-center justify-center space-x-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <select name="role" class="border p-1 rounded">
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : ''; ?>>Usuário</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                    <button type="submit" name="update_role" class="bg-blue-500 text-white px-3 py-1 rounded">✔️</button>
                                </form>
                            </td>
                            <td class="border border-gray-300 p-2"><?= $user['created_at']; ?></td>
                            <td class="border border-gray-300 p-2 flex justify-center space-x-2">
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <input type="password" name="new_password" placeholder="Nova Senha" required class="border p-1 rounded w-32">
                                    <button type="submit" name="update_password" class="bg-yellow-500 text-white px-3 py-1 rounded">🔑</button>
                                </form>
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="bg-red-500 text-white px-3 py-1 rounded" onclick="return confirm('Tem certeza que deseja excluir?');">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
