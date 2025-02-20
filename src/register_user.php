<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = 'user'; // Função padrão para todos os novos usuários

    if (empty($username) || empty($password)) {
        header('Location: ../register.php?error=Preencha todos os campos');
        exit();
    }

    try {
        // Verificar se o usuário já existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header('Location: ../register.php?error=Usuário já existe');
            exit();
        }

        // Inserir novo usuário com função padrão 'user'
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $insertStmt->bindParam(':username', $username);
        $insertStmt->bindParam(':password', $hashedPassword);
        $insertStmt->bindParam(':role', $role);

        if ($insertStmt->execute()) {
            // Capturar ID do novo usuário criado
            $newUserId = $conn->lastInsertId();

            // **Registrar log da criação do usuário**
            if (isset($_SESSION['user_id'])) { // Garante que há um usuário logado
                $action = "Usuário " . $_SESSION['user_id'] . " criou o usuário ID " . $newUserId . " (" . $username . ")";
                $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
                $logStmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $logStmt->bindParam(':action', $action);
                $logStmt->execute();
            }

            header('Location: ../register.php?success=true');
            exit();
        } else {
            header('Location: ../register.php?error=Erro ao criar usuário');
            exit();
        }
    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
} else {
    header('Location: ../register.php');
    exit();
}
