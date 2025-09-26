<?php
include('../includes/connect.php');

if(isset($_POST['usuario']) && isset($_POST['senha']) && isset($_POST['nome'])) {

    $usuario = $conn->real_escape_string($_POST['usuario']);
    $senha   = $_POST['senha']; 
    $senha1  = $_POST['senha1'];
    $nome    = $conn->real_escape_string($_POST['nome']);
    $aviso   = null;

    if ($senha !== $senha1) {
        $aviso = "As senhas informadas não são iguais";
    } elseif (strlen($senha) < 8) {
        $aviso = "A senha deve conter ao menos 8 caracteres";
    } elseif (!preg_match('/[a-z]/', $senha)) {
        $aviso = "A senha deve conter pelo menos uma letra minúscula";
    } elseif (!preg_match('/[A-Z]/', $senha)) {
        $aviso = "A senha deve conter pelo menos uma letra maiúscula";
    } elseif (!preg_match('/[0-9]/', $senha)) {
        $aviso = "A senha deve conter pelo menos um número";
    } elseif (!preg_match('/[\W_]/', $senha)) {
        $aviso = "A senha deve conter pelo menos um caractere especial";
    } elseif (preg_match('/\s/', $senha)) {
        $aviso = "A senha não pode conter espaços";
    }
    if(!$aviso){

        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "SELECT * FROM usuarios WHERE user = '$usuario'";
        $dados = $conn->query($sql) or die("Erro: " . $conn->error);

        if($dados->num_rows > 0) {
            echo "Usuário já existe!";
        } else {
            $sql = "INSERT INTO usuarios (nome, user, senha) VALUES ('$nome', '$usuario', '$hash')";
            if($conn->query($sql) === TRUE) {
                header ("location: login.html");
            } else {
                echo "Erro ao criar conta: " . $conn->error;
            }
        }
    }else{
        echo "<script>alert('$aviso'); window.location.href = 'cadastro.html';</script>";
    }
  
}
?>