<?php
$pagina_atual = 'lido';
include('../includes/auth.php');  // Único include: sessão + connect + verificação
// Remova: include("verifica.php"); e include('../includes/connect.php'); – duplicados!

$id_usuario = $_SESSION['id_usuario'];  // ID logado para filtro

$search_query = "";
if (isset($_GET['menu_search']) && !empty($_GET['menu_search'])) {
    $search_query = $_GET['menu_search'];
}

$params = [];
$types = "";

// SQL CORRIGIDA: Única query com filtro por id_usuario + LEFT JOIN para média
$sql = "SELECT l.id_livro, l.titulo, l.capa, AVG(a.nota) as media_avaliacao
        FROM tbl_livros l
        LEFT JOIN tbl_avaliacoes a ON l.id_livro = a.id_livro AND a.id_usuario = ?  -- Filtro também na avaliação!
        WHERE l.status = 'lido' AND l.id_usuario = ?";  // Filtro chave por usuário

$params[] = $id_usuario;  // Primeiro param: para JOIN
$params[] = $id_usuario;  // Segundo: para WHERE
$types .= "ii";  // Tipos: dois ints

if (!empty($search_query)) {
    $sql .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR (SELECT nome_genero FROM tbl_generos WHERE id_genero = l.id_genero) LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$sql .= " GROUP BY l.id_livro";  // Para AVG

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../includes/estilos.php">
    <!-- <link rel="stylesheet" href="../assets/css/fonts.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/style.css"> -->
    <title>Lido</title>
</head>
<body>
    <style>
        main .lista-livros {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        main .lista-livros .livro-card {
            width: 250px;
            height: 400px; /* Aumenta a altura do card */
            border: 2px solid var(--input-border);
            overflow: hidden; /* Previne o conteúdo de transbordar */
            display: flex;
            flex-direction: column; /* Alinha o conteúdo verticalmente */
        }

        main .lista-livros .livro-card img {
            width: 100%; /* Faz a imagem ocupar toda a largura do card */
            height: 200px;
            object-fit: cover; /* Faz com que a imagem se ajuste sem distorcer */
        }

        main .lista-livros .livro-card a {
            text-decoration: none;
        }

        main .lista-livros .livro-card h3 {
            color: #313131;
            margin: 6px; /* Adiciona margens ao título */
            font-size: 1em; /* Tamanho de fonte que se adapte */
            height: 60px; /* Define uma altura fixa para o título */
            overflow: hidden; /* Oculta o texto que excede a altura */
            text-overflow: ellipsis; /* Adiciona '...' ao final do texto cortado */
            white-space: nowrap; /* Impede que o título mande para a linha seguinte */
        }

        .avaliacao-estrelas {
            color: gold; /* Cor das estrelas */
            font-size: 1.2em;
            margin: 5px 0;
        }
    </style>

    <?php include '../includes/nav.php'; ?>
    <?php include '../includes/menu.php'; ?>

    <main>
        <div class="novo">
            <a href="cadLivro.php?status=lido"><button class="btn_novo"><i class="ri-add-circle-fill"></i> <p>NOVO</p></button></a>
        </div>

        <div class="lista-livros">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="livro-card">';
                    echo '<a href="conteudoLivro.php?id=' . $row['id_livro'] . '">';
                    echo '<img src="' . $row['capa'] . '" alt="Capa de ' . $row['titulo'] . '">';
                    echo '<h3>' . $row['titulo'] . '</h3>';
                    if ($row['media_avaliacao'] !== null) {
                        echo '<div class="avaliacao-estrelas">';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= round($row['media_avaliacao'])) {
                                echo '<i class="ri-star-fill"></i>'; // Estrela preenchida
                            } else {
                                echo '<i class="ri-star-line"></i>'; // Estrela vazia
                            }
                        }
                        echo '</div>';
                    }
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo "<p>Nenhum livro lido cadastrado ainda.</p>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>