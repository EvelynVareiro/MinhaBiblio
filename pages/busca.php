<?php
$pagina_atual = 'busca';  // Para destaque no menu, se quiser
include('../includes/auth.php');  // Sessão + connect + verificação de login
$id_usuario = $id_usuario_logado;  // Do auth.php

$search_query = "";
if (isset($_GET['menu_search']) && !empty(trim($_GET['menu_search']))) {
    $search_query = trim($_GET['menu_search']);
} else {
    // Se sem termo, redireciona para página inicial (ex.: lido.php) para evitar página vazia
    header("Location: lido.php");
    exit();
}

$params = [];
$types = "";

// SQL ATUALIZADA: Busca em título, autor, gênero E NOTA (avaliação) – SEM filtro de status (todas categorias)
$sql = "SELECT l.id_livro, l.titulo, l.autor, l.capa, l.progresso, l.status, g.nome_genero
        FROM tbl_livros l
        JOIN tbl_generos g ON l.id_genero = g.id_genero
        LEFT JOIN tbl_avaliacoes a ON l.id_livro = a.id_livro AND a.id_usuario = ?
        WHERE l.id_usuario = ? 
        AND (l.titulo LIKE ? OR l.autor LIKE ? OR g.nome_genero LIKE ? OR CAST(a.nota AS CHAR) LIKE ?)
        GROUP BY l.id_livro  -- Evita duplicatas se múltiplas avaliações (raro)
        ORDER BY l.titulo ASC";  // Ordena por título para resultados limpos

$search_term = "%" . $search_query . "%";
$params = [$id_usuario, $id_usuario, $search_term, $search_term, $search_term, $search_term];  // id_usuario (duplicado para JOIN e WHERE), + 4 termos
$types = "iissss";  // i (id_usuario JOIN), i (id_usuario WHERE), ssss (4 buscas: titulo, autor, genero, nota)

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param($types, ...$params);
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
    <title>Busca</title>
</head>
<body>
    <style>
        /* Estilos base para lista (similar às suas páginas) */
        main .lista-livros {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px 0;
        }

        main .lista-livros .livro-card {
            width: 250px;
            height: 450px;  /* Altura maior para badge + progresso */
            border: 2px solid var(--input-border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        main .lista-livros .livro-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        main .lista-livros .livro-card a {
            text-decoration: none;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        main .lista-livros .livro-card h3 {
            color: #313131;
            margin: 10px 10px 5px;
            font-size: 1em;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        main .lista-livros .livro-card p.autor {
            color: #666;
            margin: 0 10px 5px;
            font-size: 0.9em;
        }

        main .lista-livros .livro-card p.genero {
            color: #888;
            margin: 0 10px 5px;
            font-size: 0.8em;
            font-style: italic;
        }

        /* BADGES PARA IDENTIFICAÇÃO DE CATEGORIA (NOVO!) */
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            min-width: 60px;
            text-align: center;
        }

        .badge-lido { background-color: #52874f; }      /* Verde para Lido */
        .badge-lendo { background-color: #fadb53; }     /* Azul para Lendo */
        .badge-quero_ler { background-color: #ff5a5a; } /* Laranja para Quero Ler */

        /* Progresso (só se >0, como em lendo.php) */
        .progresso {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #333;
        }

        /* Cabeçalho da Busca */
        .busca-header {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
        }

        .busca-header h1 {
            color: #313131;
            margin-bottom: 10px;
        }

        .busca-header p {
            color: #666;
            font-size: 1.1em;
        }

        .sem-resultados {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 1.2em;
        }
    </style>

    <?php include '../includes/nav.php'; ?>
    <?php include '../includes/menu.php'; ?>

    <main>
        <div class="busca-header">
            <h1>Resultados para: "<?php echo htmlspecialchars($search_query); ?>"</h1>
            <p>Encontrados <?php echo $result->num_rows; ?> livro(s) em todas as suas listas (incluindo avaliações).</p>
        </div>

        <div class="lista-livros">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $progresso_formatado = ($row['progresso'] > 0) ? round($row['progresso'], 2) . '%' : '';  // Só mostra se >0
                    $badge_class = 'badge-' . str_replace('_', '-', $row['status']);  // Ex.: badge-lido

                    echo '<div class="livro-card">';
                    echo '<a href="conteudoLivro.php?id=' . $row['id_livro'] . '">';
                    echo '<span class="status-badge ' . $badge_class . '">' . ucfirst(str_replace('_', ' ', $row['status'])) . '</span>';  // Badge: "Lido", "Lendo", etc.
                    echo '<img src="' . ($row['capa'] ?: '../assets/images/default-book.jpg') . '" alt="Capa de ' . htmlspecialchars($row['titulo']) . '">';
                    echo '<h3>' . htmlspecialchars($row['titulo']) . '</h3>';
                    echo '<p class="autor">' . htmlspecialchars($row['autor']) . '</p>';
                    echo '<p class="genero">' . htmlspecialchars($row['nome_genero']) . '</p>';
                    if ($progresso_formatado) {
                        echo '<p class="progresso">' . $progresso_formatado . '</p>';
                    }
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<div class="sem-resultados">';
                echo '<p>Nenhum livro encontrado para "' . htmlspecialchars($search_query) . '".</p>';
                echo '<p>Tente outro termo (ex.: título, autor, gênero ou nota como "5") ou verifique a ortografia. <a href="lido.php">Voltar às listas</a></p>';
                echo '</div>';
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>
</body>
</html>
