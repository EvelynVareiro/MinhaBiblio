    <?php
    include('../includes/auth.php'); 
    $id_usuario = $_SESSION['id_usuario'];  
    include('../includes/connect.php');

    $livro = null;
    if (isset($_GET['id'])) {
        $id_livro = $_GET['id'];
        $sql = "SELECT l.*, g.nome_genero
                FROM tbl_livros l
                JOIN tbl_generos g ON l.id_genero = g.id_genero
                WHERE l.id_livro = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_livro);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $livro = $result->fetch_assoc();
        }
        $stmt->close();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_livro'])) {
        $id_editar = $_POST['id_livro'];
        $titulo = $_POST['titulo'];
        $autor = $_POST['autor'];
        $id_genero = $_POST['id_genero'];
        $total_paginas = isset($_POST['total_paginas']) && $_POST['total_paginas'] !== '' ? $_POST['total_paginas'] : null;
        $pagina_atual = isset($_POST['pagina_atual']) && $_POST['pagina_atual'] !== '' ? $_POST['pagina_atual'] : 0;
        $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
        $status_livro = $_POST['status']; 
        $avaliacao = isset($_POST['avaliacao']) && $_POST['avaliacao'] !== '' ? $_POST['avaliacao'] : null;

        $capa_path = $_POST['capa_existente']; 


        if (isset($_FILES['capa']) && $_FILES['capa']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../assets/uploads/capas/";
            $file_name = basename($_FILES["capa"]["name"]);
            $new_capa_path = $target_dir . uniqid() . "_" . $file_name;
            $imageFileType = strtolower(pathinfo($new_capa_path, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES["capa"]["tmp_name"]);
            if ($check !== false) {
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                    echo "Desculpe, apenas arquivos JPG, JPEG, PNG & GIF são permitidos.";
                } else {
                    if (move_uploaded_file($_FILES["capa"]["tmp_name"], $new_capa_path)) {
                        if (!empty($capa_path) && file_exists($capa_path)) {
                            unlink($capa_path);
                        }
                        $capa_path = $new_capa_path;
                    } else {
                        echo "Desculpe, houve um erro ao fazer o upload do seu arquivo.";
                    }
                }
            } else {
                echo "O arquivo não é uma imagem.";
            }
        }

        if ($id_genero === 'novo') {
            $novo_genero_nome = trim($_POST['novo_genero']);
            if (!empty($novo_genero_nome)) {
                $stmt_insert_genre = $conn->prepare("INSERT IGNORE INTO tbl_generos (nome_genero) VALUES (?)");
                $stmt_insert_genre->bind_param("s", $novo_genero_nome);
                $stmt_insert_genre->execute();
                $stmt_insert_genre->close();

                $stmt_get_genre_id = $conn->prepare("SELECT id_genero FROM tbl_generos WHERE nome_genero = ?");
                $stmt_get_genre_id->bind_param("s", $novo_genero_nome);
                $stmt_get_genre_id->execute();
                $result_genre_id = $stmt_get_genre_id->get_result();
                if ($result_genre_id->num_rows > 0) {
                    $id_genero = $result_genre_id->fetch_assoc()['id_genero'];
                } else {
                    echo "Erro: Não foi possível obter o ID do novo gênero.";
                    $conn->close();
                    exit();
                }
                $stmt_get_genre_id->close();
            } else {
                echo "Erro: O nome do novo gênero não pode ser vazio.";
                $conn->close();
                exit();
            }
        }

        // Cálculo do progresso
        $progresso = 0.00;
        if ($status_livro == 'lendo' && $total_paginas > 0) {
            $progresso = ($pagina_atual / $total_paginas) * 100;
        } elseif ($status_livro == 'lido') {
            $progresso = 100.00;
            $pagina_atual = $total_paginas; // Se lido, página atual é igual ao total
        }

        // Atualiza na tabela tbl_livros
        $sql_update = "UPDATE tbl_livros SET titulo=?, autor=?, id_genero=?, capa=?, total_paginas=?, pagina_atual=?, progresso=?, comentario=?, status=? WHERE id_livro=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssisiiissi", $titulo, $autor, $id_genero, $capa_path, $total_paginas, $pagina_atual, $progresso, $comentario, $status_livro, $id_editar);

        if ($stmt_update->execute()) {
            // Se o status for 'lido' e houver avaliação, atualiza/insere na tabela tbl_avaliacoes
            if ($status_livro == 'lido' && $avaliacao !== null) {
                // Verifica se já existe uma avaliação para este livro
                $stmt_check_avaliacao = $conn->prepare("SELECT id_avaliacao FROM tbl_avaliacoes WHERE id_livro = ?");
                $stmt_check_avaliacao->bind_param("i", $id_editar);
                $stmt_check_avaliacao->execute();
                $result_check_avaliacao = $stmt_check_avaliacao->get_result();

                if ($result_check_avaliacao->num_rows > 0) {
                    // Atualiza a avaliação existente
                    $stmt_update_avaliacao = $conn->prepare("UPDATE tbl_avaliacoes SET nota = ?, data_avaliacao = CURRENT_TIMESTAMP WHERE id_livro = ?");
                    $stmt_update_avaliacao->bind_param("ii", $avaliacao, $id_editar);
                    $stmt_update_avaliacao->execute();
                    $stmt_update_avaliacao->close();
                } else {
                    // Insere uma nova avaliação
                    $stmt_insert_avaliacao = $conn->prepare("INSERT INTO tbl_avaliacoes (id_livro, nota) VALUES (?, ?)");
                    $stmt_insert_avaliacao->bind_param("ii", $id_editar, $avaliacao);
                    $stmt_insert_avaliacao->execute();
                    $stmt_insert_avaliacao->close();
                }
                $stmt_check_avaliacao->close();
            }

            header("Location: conteudoLivro.php?id=" . $id_editar);
            exit();
        } else {
            echo "Erro ao atualizar: " . $stmt_update->error;
        }
        $stmt_update->close();
        $conn->close();
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
        <link rel="stylesheet" href="../includes/estilos.php">
        <title>Editar | Livro</title>
    </head>
    <body>
        <style>
            /* Seus estilos existentes */
            main .parte_seis #editar_livro {
                padding: 10px 30px; margin-top: 20px; border-radius: 10px; cursor: pointer; border: 1px solid var(--input-border);
                background-color: var(--background-novo);
            }
            .input { display: flex; flex-direction: column; }
            .input label { text-transform: uppercase; font-family: 'Poppins'; }
            .input input, .input select {
                width: 370px; padding: 10px 15px; background-color: #fff; border-radius: 10px;
                border: 1px solid var(--input-border); outline: none;
            }
            .input textarea {
                border: 1px solid var(--input-border); border-radius: 10px; padding: 20px;
                outline: none; height: 140px; width: 1193px; max-width: 1193px;
            }
        </style>

        <?php include '../includes/nav.php'; ?>

        <main class="novo_main cadastro editar">
            <div class="container">
                <?php if ($livro): ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_livro" value="<?php echo $livro['id_livro']; ?>">
                    <input type="hidden" name="status" value="<?php echo $livro['status']; ?>">
                    <input type="hidden" name="capa_existente" value="<?php echo $livro['capa']; ?>">

                    <section class="parte_um">
                        <div class="voltar">
                            <a href="conteudoLivro.php?id=<?php echo $livro['id_livro']; ?>"><i class="ri-arrow-left-line"></i></a>
                        </div>
                        <div class="titulo">
                            <h1>Editar livro: <?php echo $livro['titulo']; ?></h1>
                        </div>
                    </section>

                    <section class="parte_dois">
                        <div class="input">
                            <label for="titulo">Título</label>
                            <input type="text" name="titulo" value="<?php echo $livro['titulo']; ?>" required>
                        </div>
                        <div class="input">
                            <label for="autor">Autor</label>
                            <input type="text" name="autor" value="<?php echo $livro['autor']; ?>" required>
                        </div>
                        <div class="input">
                            <label for="genero_select">Gênero</label>
                            <select name="id_genero" id="genero_select" onchange="toggleNewGenreInput()" required>
                                <option value="">Selecione um gênero</option>
                                <?php
                                $conn_generos = new mysqli($servername, $username, $password, $dbname, 3306);
                                $sql_generos = "SELECT id_genero, nome_genero FROM tbl_generos ORDER BY nome_genero";
                                $result_generos = $conn_generos->query($sql_generos);
                                if ($result_generos->num_rows > 0) {
                                    while($row_genero = $result_generos->fetch_assoc()) {
                                        $selected = ($row_genero['id_genero'] == $livro['id_genero']) ? 'selected' : '';
                                        echo '<option value="' . $row_genero['id_genero'] . '" ' . $selected . '>' . $row_genero['nome_genero'] . '</option>';
                                    }
                                }
                                $conn_generos->close();
                                ?>
                                <option value="novo">Outro (digitar novo gênero)</option>
                            </select>
                            <input type="text" name="novo_genero" id="novo_genero_input" placeholder="Digite o novo gênero" style="display:none;">
                        </div>
                    </section>

                    <section class="parte_tres" style="display: flex; flex-direction: row; gap: 40px;">
                        <div class="input">
                            <label for="capa">Capa do Livro</label>
                            <input type="file" name="capa" id="capa" accept="image/*">
                            <?php if (!empty($livro['capa'])): ?>
                                <p>Capa atual: <img src="<?php echo $livro['capa']; ?>" alt="Capa atual" style="width: 50px; height: auto; vertical-align: middle;"></p>
                            <?php endif; ?>
                        </div>

                        <div class="input" id="total_paginas_div">
                            <label for="total_paginas">Total de páginas:</label>
                            <input type="number" name="total_paginas" id="total_paginas" value="<?php echo $livro['total_paginas']; ?>" <?php echo ($livro['status'] == 'quero_ler' ? '' : 'required'); ?>>
                        </div>

                        <div class="input" id="pagina_atual_div" <?php echo ($livro['status'] == 'lido' || $livro['status'] == 'quero_ler' ? 'style="display:none;"' : ''); ?>>
                            <label for="pagina_atual">Página atual:</label>
                            <input type="number" id="pagina_atual" name="pagina_atual" value="<?php echo $livro['pagina_atual']; ?>" <?php echo ($livro['status'] == 'lido' || $livro['status'] == 'quero_ler' ? '' : 'required'); ?>>
                        </div>
                    </section>

                    <section class="parte_quatro">
                        <div class="input" id="avaliacao_div" <?php echo ($livro['status'] == 'lido' ? '' : 'style="display:none;"'); ?>>
                            <label for="avaliacao">Avalie o livro de 1 a 5</label>
                            <?php
                            $current_avaliacao = null;
                            $stmt_get_avaliacao = $conn->prepare("SELECT nota FROM tbl_avaliacoes WHERE id_livro = ? ORDER BY data_avaliacao DESC LIMIT 1");
                            $stmt_get_avaliacao->bind_param("i", $livro['id_livro']);
                            $stmt_get_avaliacao->execute();
                            $result_get_avaliacao = $stmt_get_avaliacao->get_result();
                            if ($result_get_avaliacao->num_rows > 0) {
                                $current_avaliacao = $result_get_avaliacao->fetch_assoc()['nota'];
                            }
                            $stmt_get_avaliacao->close();
                            ?>
                            <input type="number" id="avaliacao" name="avaliacao" min="1" max="5" value="<?php echo $current_avaliacao; ?>" <?php echo ($livro['status'] == 'lido' ? 'required' : ''); ?>>
                        </div>
                    </section>

                    <section class="parte_cinco">
                        <div class="input">
                            <label for="comentario">Comentário</label>
                            <textarea name="comentario" id="comentario"><?php echo $livro['comentario']; ?></textarea>
                        </div>
                    </section>

                    <section class="parte_seis">
                        <div class="btns editar">
                            <button id="editar_livro" type="submit">EDITAR</button>
                        </div>
                    </section>
                </form>
                <?php else: ?>
                    <p>Livro não encontrado para edição.</p>
                <?php endif; ?>
            </div>
        </main>

        <script>
            function toggleNewGenreInput() {
                var select = document.getElementById('genero_select');
                var input = document.getElementById('novo_genero_input');
                if (select.value === 'novo') {
                    input.style.display = 'block';
                    input.setAttribute('required', 'required');
                } else {
                    input.style.display = 'none';
                    input.removeAttribute('required');
                    input.value = '';
                }
            }

            // Lógica para mostrar/esconder campos baseados no status do livro
            document.addEventListener('DOMContentLoaded', function() {
                var status = "<?php echo $livro['status']; ?>";
                var totalPaginasDiv = document.getElementById('total_paginas_div');
                var paginaAtualDiv = document.getElementById('pagina_atual_div');
                var avaliacaoDiv = document.getElementById('avaliacao_div');
                var totalPaginasInput = document.getElementById('total_paginas');
                var paginaAtualInput = document.getElementById('pagina_atual');
                var avaliacaoInput = document.getElementById('avaliacao');

                if (status === 'lido') {
                    totalPaginasDiv.style.display = 'flex';
                    paginaAtualDiv.style.display = 'none';
                    avaliacaoDiv.style.display = 'flex';
                    totalPaginasInput.setAttribute('required', 'required');
                    paginaAtualInput.removeAttribute('required');
                    avaliacaoInput.setAttribute('required', 'required');
                } else if (status === 'lendo') {
                    totalPaginasDiv.style.display = 'flex';
                    paginaAtualDiv.style.display = 'flex';
                    avaliacaoDiv.style.display = 'none';
                    totalPaginasInput.setAttribute('required', 'required');
                    paginaAtualInput.setAttribute('required', 'required');
                    avaliacaoInput.removeAttribute('required');
                } else if (status === 'quero_ler') {
                    totalPaginasDiv.style.display = 'flex';
                    paginaAtualDiv.style.display = 'none';
                    avaliacaoDiv.style.display = 'none';
                    totalPaginasInput.removeAttribute('required');
                    paginaAtualInput.removeAttribute('required');
                    avaliacaoInput.removeAttribute('required');
                }
            });
        </script>
    </body>
    </html>
    