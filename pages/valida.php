<?php
include('../includes/connect.php');  // Só connect.php aqui (página pública)
session_start();  // Inicia sessão

$erro = '';
if (isset($_POST['usuario']) && isset($_POST['senha'])) {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Busca usuário (prepared statement)
        $sql = "SELECT id, nome, senha FROM usuarios WHERE user = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $usuarioData = $result->fetch_assoc();
                if (password_verify($senha, $usuarioData['senha'])) {
                    // Login sucesso: Setar sessão com ID para multi-usuário
                    $_SESSION['id_usuario'] = $usuarioData['id'];
                    $_SESSION['nome_usuario'] = $usuarioData['nome'];
                    $_SESSION['last_activity'] = time();
                    
                    // CORREÇÃO: Redirecionamento para pasta correta (ajuste se não for /pages/)
                    header("Location: lido.php");  // Ou "lendo.php" ou "index.php"
                    exit();
                } else {
                    $erro = "Senha incorreta!";
                }
            } else {
                $erro = "Usuário não encontrado!";
            }
            $stmt->close();
        } else {
            $erro = "Erro na consulta: " . $conn->error;
        }
    }
}

$conn->close();

// Se erro, redireciona com alert (para compatibilidade com HTML)
if (!empty($erro)) {
    echo "<script>alert('$erro'); window.location.href = 'login.html';</script>";
    exit();
}
?>
