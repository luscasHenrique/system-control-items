<?php
session_start();
require 'src/db_connection.php'; // Conexão com o banco

if (isset($_SESSION['user_id'])) {
    // Registrar log de logout
    $user_id = $_SESSION['user_id'];
    $action = "Usuário {$user_id} fez logout.";
    $logStmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (:user_id, :action)");
    $logStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $logStmt->bindParam(':action', $action);
    $logStmt->execute();
}

// Remover todas as variáveis de sessão e destruir a sessão
session_unset();
session_destroy();

// Redireciona para a página de login
header('Location: login.php');
exit();
