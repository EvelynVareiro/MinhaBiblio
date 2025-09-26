<?php
include('../includes/auth.php');  // Único include: sessão + connect + verificação

$id_usuario = $id_usuario_logado;  // Do auth.php (int seguro)

$livro = null;
$media_avaliacao = null;

function getRedirectPage($status) {
    $map = [
        'lido' => 'lido.php',
        'lendo' => 'lendo.php',
        'quero_ler' => 'quero_ler.php'
    ];
    return $map[$status] ?? 'index.php';
}

if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
    $id_livro = (int)$_GET['id'];
    
    // CORREÇÃO: SQL com 2 ? (filtro multi-usuário)
    $sql = "SELECT l.*, g.nome_genero
            FROM tbl_livros l
            JOIN tbl_generos g ON l.id_genero = g.id_genero
            WHERE l.id_livro = ? AND l.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ii", $id_livro, $id_usuario);  // 2 vars para 2 ?
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $livro = $result->fetch_assoc();

            // CORREÇÃO: AVG com 2 ? (filtro por usuário)
            $sql_avg_avaliacao = "SELECT AVG(nota) as media FROM tbl_avaliacoes WHERE id_livro = ? AND id_usuario = ?";
            $stmt_avg = $conn->prepare($sql_avg_avaliacao);
            if ($stmt_avg) {
                $stmt_avg->bind_param("ii", $id_livro, $id_usuario);  // 2 vars para 2 ?
                $stmt_avg->execute();
                $result_avg = $stmt_avg->get_result();
                $row_avg = $result_avg->fetch_assoc();
                $media_avaliacao = $row_avg['media'] ?? null;  // Trata null se sem avaliações
                $stmt_avg->close();
            }
        } else {
            die("Livro não encontrado ou você não tem permissão. <a href='" . getRedirectPage('lido') . "'>Voltar</a>");
        }
        $stmt->close();
    } else {
        die("Erro na consulta: " . $conn->error);
    }
} else {
    die("ID inválido. <a href='index.php'>Voltar</a>");
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../includes/estilos.php">
    <title>Conteúdo | <?php echo $livro ? ucfirst(str_replace('_', ' ', $livro['status'])) : 'Livro'; ?></title>
</head>
<body>
    <style>
        .parte_seis { display: flex; align-items: center; justify-content: center; margin-top: 30px; }
        .parte_seis .btns { display: flex; flex-direction: row; gap: 20px; }
        .excluir-btn, .editar-btn, .transferir_livro {
            color: #fff; border: none; padding: 10px 20px; cursor: pointer; border-radius: 10px; font-size: 16px;
        }
        .excluir-btn { background-color: var(--border-red); }
        .excluir-btn:hover { background-color: darkred; }
        .editar-btn { background-color: var(--border-yellow); }
        .editar-btn:hover { background-color: var(--text-yellow); }
        .transferir_livro { background-color: #3b6dd1; text-decoration: none; padding: 10px 20px; color: #fff; border-radius: 10px; }
        .transferir_livro:hover { background-color: #20448c; }
        .input { display: flex; flex-direction: column; }
        .input label { text-transform: uppercase; font-family: 'Poppins'; }
        .input input, .input textarea {
            width: 370px; padding: 10px 15px; background-color: #fff; border-radius: 10px;
            border: 1px solid var(--input-border); outline: none;
        }
        .input textarea { height: 140px; width: 1193px; max-width: 1193px; }
        .avaliacao-estrelas {
            color: gold;
            font-size: 1.5em;
            margin-top: 10px;
        }
    </style>

    <?php include '../includes/nav.php'; ?>

    <main class="novo_main cadastro editar">
        <div class="container">
            <?php if ($livro): ?>
            <form action="" method="POST">
                <section class="parte_um">
                    <div class="voltar">
                        <!-- CORREÇÃO: Link de voltar com mapeamento correto -->
                        <a href="<?php echo getRedirectPage($livro['status']); ?>"><i class="ri-arrow-left-line"></i></a>
                    </div>
                    <div class="titulo">
                        <h1><?php echo htmlspecialchars($livro['titulo']); ?></h1>
                    </div>
                </section>

                <section class="parte_dois">
                    <div class="input">
                        <label for="titulo">Título</label>
                        <input type="text" name="titulo" value="<?php echo htmlspecialchars($livro['titulo']); ?>" disabled>
                    </div>
                    <div class="input">
                        <label for="autor">Autor</label>
                        <input type="text" name="autor" value="<?php echo htmlspecialchars($livro['autor']); ?>" disabled>
                    </div>
                    <div class="input">
                        <label for="genero">Gênero</label>
                        <input type="text" name="genero" value="<?php echo htmlspecialchars($livro['nome_genero']); ?>" disabled>
                    </div>
                </section>

                <section class="parte_tres" style="display: flex; flex-direction: row; gap: 40px; align-items: center; justify-content: center;">
                    <?php if ($livro['status'] == 'lendo' || $livro['status'] == 'lido'): ?>
                    <div class="input">
                        <label for="total_paginas">Total de páginas:</label>
                        <input type="number" name="total_paginas" value="<?php echo htmlspecialchars($livro['total_paginas']); ?>" disabled>
                    </div>
                    <?php endif; ?>

                    <?php if ($livro['status'] == 'lendo'): ?>
                    <div class="input">
                        <label for="pagina_atual">Página atual:</label>
                        <input type="number" id="pagina_atual" name="pagina_atual" value="<?php echo htmlspecialchars($livro['pagina_atual']); ?>" disabled>
                    </div>
                    <?php endif; ?>

                    <?php if ($livro['status'] == 'lido' && $media_avaliacao !== null): ?>
                    <div class="input">
                        <label>Avaliação Média:</label>
                        <div class="avaliacao-estrelas">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= round($media_avaliacao)) {
                                    echo '<i class="ri-star-fill"></i>';
                                } else {
                                    echo '<i class="ri-star-line"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <section class="parte_quatro">
                    <div class="input">
                        <label for="comentario">Comentário</label>
                        <textarea name="comentario" id="comentario" disabled><?php echo htmlspecialchars($livro['comentario']); ?></textarea>
                    </div>
                </section>

                <section class="parte_cinco">
                    <div class="btns">
                        <a href="editarLivro.php?id=<?php echo $livro['id_livro']; ?>"><button class="editar-btn" id="editar" value="editar" type="button">EDITAR</button></a>
                        <!-- CORREÇÃO: onclick com cast int (já estava, mas confirmei) -->

                        <button type="button" onclick="confirmDelete(<?php echo (int)$livro['id_livro']; ?>)" class="excluir-btn" style="background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">EXCLUIR</button>

                        <?php if ($livro['status'] == 'lendo'): ?>
                            <a href="transferirLivro.php?id=<?php echo $livro['id_livro']; ?>&action=to_lido" class="transferir_livro">Marcar como Lido</a>
                        <?php elseif ($livro['status'] == 'quero_ler'): ?>
                            <a href="transferirLivro.php?id=<?php echo $livro['id_livro']; ?>&action=to_lendo" class="transferir_livro">Começar a Ler</a>
                        <?php endif; ?>
                    </div>
                </section>
            </form>
            <?php else: ?>
                <p>Livro não encontrado. <a href="index.php">Voltar ao início</a></p>
            <?php endif; ?>
        </div>
    </main>


    <script>
        function confirmDelete(id) {
            console.log("ID recebido no confirmDelete:", id);  // DEBUG: Abra F12 > Console
            console.log("Tipo do ID:", typeof id);

            if (confirm("Tem certeza que deseja excluir este livro? Esta ação não pode ser desfeita!")) {
                var url = "excluirLivro.php?id=" + id;
                console.log("Redirecionando para:", url);
                window.location.href = url;
            } else {
                console.log("Exclusão cancelada.");
            }
        }
    </script>

</body>
</html>
