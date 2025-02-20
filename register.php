<?php
session_start();
require 'src/db_connection.php';

// Verifica se o usuário está logado e se é administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?error=Acesso negado.');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'user'; // Função padrão

    if (empty($username) || empty($password)) {
        header('Location: register.php?error=Preencha todos os campos');
        exit();
    }

    try {
        // Verificar se o usuário já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header('Location: register.php?error=Usuário já existe');
            exit();
        }

        // Inserir novo usuário com função padrão 'user'
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $hashedPassword);
        $insertStmt->bindParam(':role', $role);

        if ($insertStmt->execute()) {
            header('Location: register.php?success=Conta criada com sucesso!');
            exit();
        } else {
            header('Location: register.php?error=Erro ao criar usuário');
            exit();
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuário</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">Registrar Usuário</h1>

        <?php if (isset($_GET['error'])): ?>
            <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p class="text-green-500 text-center mb-4"><?= htmlspecialchars($_GET['success']); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="username" class="block font-semibold mb-2">Usuário:</label>
            <input
                type="text"
                id="username"
                name="username"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite o nome de usuário"
                required>

            <label for="password" class="block font-semibold mb-2">Senha:</label>
            <input
                type="password"
                id="password"
                name="password"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite a senha"
                required>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Registrar
            </button>
        </form>

        <p class="text-gray-600 text-center mt-4">
            <a href="index.php" class="text-blue-500 hover:underline">Voltar para a página inicial</a>.
        </p>
    </div>
</body>

</html>
