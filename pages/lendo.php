<?php
$pagina_atual = 'lendo';
include('../includes/auth.php');  // Único include: sessão + connect + verificação de login
// NÃO inclua connect.php ou verifica.php aqui – duplicado!

$id_usuario = $_SESSION['id_usuario'];  // ID do usuário logado para filtro

$search_query = "";
if (isset($_GET['menu_search']) && !empty($_GET['menu_search'])) {
    $search_query = $_GET['menu_search'];
}

$params = [];
$types = "";

// SQL BASE: Sem busca inicialmente – só o filtro por id_usuario (1 ?)
$sql = "SELECT l.id_livro, l.titulo, l.autor, l.capa, l.progresso
        FROM tbl_livros l
        WHERE l.status = 'lendo' AND l.id_usuario = ?";  // 1 ? para id_usuario

$params[] = $id_usuario;
$types .= "i";  // Tipo para id_usuario (int)

// CORREÇÃO CHAVE: Só adicione busca SE houver query (evita ? extras na SQL)
if (!empty($search_query)) {
    $sql .= " AND (l.titulo LIKE ? OR l.autor LIKE ? OR (SELECT nome_genero FROM tbl_generos WHERE id_genero = l.id_genero) LIKE ?)";
    $search_term = "%" . $search_query . "%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";  // 3 strings para busca
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

// Bind: Sempre passa TODAS as variáveis (1 para usuário + 0 ou 3 para busca)
$stmt->bind_param($types, ...$params);  // ...$params espalha o array corretamente

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
    <title>Lendo</title>
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
            height: 450px; /* Aumenta a altura do card */
            border: 2px solid var(--input-border);
            overflow: hidden; /* Previne o conteúdo de transbordar */
            display: flex;
            flex-direction: column; /* Alinha o conteúdo verticalmente */
            position: relative; /* Para posicionamento relativo do progresso */
        }

        main .lista-livros .livro-card img {
            width: 100%; /* Faz a imagem ocupar toda a largura do card */
            height: 200px;
            object-fit: cover; /* Faz com que a imagem se ajuste sem distorcer */
        }

        main .lista-livros .livro-card a {
            text-decoration: none;
            position: relative; /* Para o progresso se posicionar dentro do link */
            flex-grow: 1; /* Permite que o link ocupe o espaço restante */
        }

        main .lista-livros .livro-card p {
            font-size: 15px;
            color: #313131;
            position: absolute; /* Mudança: absolute para fixar no canto */
            bottom: 10px; /* Ajuste para posicionar no fundo do card */
            right: 10px; /* Canto inferior direito */
            background-color: rgba(255, 255, 255, 0.8); /* Fundo semi-transparente para legibilidade */
            padding: 2px 6px;
            border-radius: 4px;
            margin: 0; /* Remove margens padrão */
        }

        main .lista-livros .livro-card h3 {
            color: #313131;
            margin: 6px; /* Adiciona margens ao título */
            font-size: 1em; /* Tamanho de fonte que se adapte */
            overflow: hidden; /* Oculta o texto que excede a altura */
            text-overflow: ellipsis; /* Adiciona '...' ao final do texto cortado */
            white-space: nowrap; /* Impede que o título mande para a linha seguinte */
            padding: 0 6px; /* Padding interno para alinhar com a imagem */
        }
    </style>

    <?php include '../includes/nav.php'; ?>
    <?php include '../includes/menu.php'; ?>

    <main>
        <div class="novo">
            <a href="cadLivro.php?status=lendo"><button class="btn_novo"><i class="ri-add-circle-fill"></i> <p>NOVO</p></button></a>
        </div>

        <div class="lista-livros">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // CORREÇÃO: Agora $row['progresso'] está disponível
                    $progresso_formatado = round($row['progresso'], 2); // Arredonda para 2 casas
                    if ($progresso_formatado == 0) $progresso_formatado = 0.00; // Garante exibição de 0%

                    // DEBUG TEMPORÁRIO: echo "Livro: " . $row['titulo'] . " - Progresso: " . $progresso_formatado . "%<br>"; // Descomente para testar valores

                    echo '<div class="livro-card">';
                    echo '<a href="conteudoLivro.php?id=' . $row['id_livro'] . '">';
                    echo '<img src="' . ($row['capa'] ?: '../assets/images/default-book.jpg') . '" alt="Capa de ' . htmlspecialchars($row['titulo']) . '">'; // Fallback para capa vazia
                    echo '<h3>' . htmlspecialchars($row['titulo']) . '</h3>';
                    echo '<p>' . $progresso_formatado . '%</p>'; // CORREÇÃO: Agora exibe corretamente
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                // CORREÇÃO: Mensagem ajustada para a página "lendo"
                echo "<p>Nenhum livro sendo lido cadastrado ainda.</p>";
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>
