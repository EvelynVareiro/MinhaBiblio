<?php
// CORREÇÃO CHAVE: Inicia sessão só se não estiver ativa (evita notice duplicado)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui conexão com BD
include('connect.php');  // Define $conn

// Verificação de login: Se não logado, redireciona para login
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    header("Location: ../login.html");  // CORREÇÃO: Para .html (seu arquivo estático)
    exit();
}

// Opcional: Timeout de sessão (30 min de inatividade)
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif ((time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
    header("Location: ../login.html?msg=Sessão expirada. Faça login novamente.");
    exit();
}
$_SESSION['last_activity'] = time();

// ID do usuário logado (de usuarios.id) – use nas queries
$id_usuario_logado = (int)$_SESSION['id_usuario'];  // Cast para int por segurança

// Opcional: Nome do usuário para saudação (setado no login)
$_SESSION['nome_usuario'] = isset($_SESSION['nome_usuario']) ? $_SESSION['nome_usuario'] : '';
?>
