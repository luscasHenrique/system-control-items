<?php
session_start();
require 'src/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php'); // Redireciona para a página principal
            exit();
        } else {
            $error = "Usuário ou senha incorretos.";
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h1 class="text-2xl font-bold mb-4 text-center text-blue-700">Login</h1>

        <?php if (isset($error)): ?>
            <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="username" class="block font-semibold mb-2">Usuário:</label>
            <input
                type="text"
                id="username"
                name="username"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite seu usuário"
                required>

            <label for="password" class="block font-semibold mb-2">Senha:</label>
            <input
                type="password"
                id="password"
                name="password"
                class="w-full border border-gray-300 rounded-lg p-2 mb-4"
                placeholder="Digite sua senha"
                required>

            <button
                type="submit"
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Entrar
            </button>
        </form>

        <p class="text-gray-600 text-center mt-4">
            Não tem uma conta?
            <a href="register.php" class="text-blue-500 hover:underline">Registre-se aqui</a>.
        </p>
    </div>
</body>

</html>