<?php
include('../includes/auth.php');  // Único include: sessão + connect + verificação
// Remova: include("verifica.php"); e include('../includes/connect.php'); – duplicados!

$id_usuario = $_SESSION['id_usuario'];  // ID logado para filtro
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/fonts.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <title>nav</title>
</head>
<body>
    <style>
        .dropdown {
            position: relative; /* Permite posicionar o conteúdo absoluto dentro dele */
            display: inline-block;
        }

        .dropdown-content {
            display: none; /* Oculta o conteúdo por padrão */
            position: absolute;
            background-color: #313131;
            min-width: 200px;
            border: 2px solid #fff;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 15px;
        }

        .dropdown-content li {
            list-style: none; /* Remove as bolinhas da lista */
        }

        .dropdown-content li a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block; /* Faz o link ocupar toda a largura */
        }

        .dropdown-btn {
            background-color: transparent;
            border: 2px solid #fff;
            color: #fff;
            width: 200px;
            border-radius: 15px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .dropdown-btn p {
            display: flex;
            align-items: center;
            justify-content: space-around;
            font-size: 17px;
        }

        .dropdown-btn p i {
            font-size: 20px;
        }

        .dropdown:hover .dropdown-content {
            display: block; /* Mostra o menu ao passar o mouse sobre o contêiner .dropdown */
        }
    </style>

    <header>
        <nav>
            <div class="logo">
                <h1>MinhaBiblio</h1>
            </div>

            <div class="perfil dropdown">
                <button class="dropdown-btn"><p><i class="ri-user-line"></i> <?php echo $_SESSION['nome_usuario']; ?></p></button>
                <ul class="dropdown-content">
                    <li><a href="logout.php">Sair</a></li>
                </ul>
            </div>

  
        </nav>
    </header>
</body>
</html>