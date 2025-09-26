<?php
include('../includes/auth.php'); 
$id_usuario = $_SESSION['id_usuario'];  
include('../includes/connect.php');

    $status_livro = isset($_GET['status']) ? $_GET['status'] : 'quero_ler'; 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titulo = $_POST['titulo'];
        $autor = $_POST['autor'];
        $id_genero = $_POST['id_genero'];
        $capa_path = '';
        $total_paginas = isset($_POST['total_paginas']) && $_POST['total_paginas'] !== '' ? $_POST['total_paginas'] : null;
        $pagina_atual = isset($_POST['pagina_atual']) && $_POST['pagina_atual'] !== '' ? $_POST['pagina_atual'] : 0;
        $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
        $avaliacao = isset($_POST['avaliacao']) && $_POST['avaliacao'] !== '' ? $_POST['avaliacao'] : null; 

        if (isset($_FILES['capa']) && $_FILES['capa']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../assets/uploads/capas/";
            $file_name = basename($_FILES["capa"]["name"]);
            $target_file = $target_dir . uniqid() . "_" . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES["capa"]["tmp_name"]);
            if ($check !== false) {
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                    echo "Desculpe, apenas arquivos JPG, JPEG, PNG & GIF são permitidos.";
                } else {
                    if (move_uploaded_file($_FILES["capa"]["tmp_name"], $target_file)) {
                        $capa_path = $target_file;
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

        if (empty($id_genero)) {
            echo "Erro: Gênero não selecionado ou inválido.";
            $conn->close();
            exit();
        }

        $progresso = 0.00;
        if ($status_livro == 'lendo' && $total_paginas > 0) {
            $progresso = ($pagina_atual / $total_paginas) * 100;
        } elseif ($status_livro == 'lido') {
            $progresso = 100.00;
            $pagina_atual = $total_paginas; 
        }

        // Inserir na tabela tbl_livros
        $sql = "INSERT INTO tbl_livros (titulo, autor, id_genero, capa, total_paginas, pagina_atual, progresso, comentario, status, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisiidssi", $titulo, $autor, $id_genero, $capa_path, $total_paginas, $pagina_atual, $progresso, $comentario, $status_livro, $id_usuario);


        if ($stmt->execute()) {
            $last_id = $stmt->insert_id;

            if ($status_livro == 'lido' && $avaliacao !== null) {
                $stmt_avaliacao = $conn->prepare("INSERT INTO tbl_avaliacoes (id_livro, nota) VALUES (?, ?)");
                $stmt_avaliacao->bind_param("ii", $last_id, $avaliacao);
                $stmt_avaliacao->execute();
                $stmt_avaliacao->close();
            }

            header("Location: " . $status_livro . ".php");
            exit();
        } else {
            echo "Erro ao cadastrar livro: " . $stmt->error;
        }
        $stmt->close();
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
        <title>Cadastrar | <?php echo ucfirst(str_replace('_', ' ', $status_livro)); ?></title>
    </head>
    <body>
        <style>
            .input { display: flex; flex-direction: column; }
            .input label { text-transform: uppercase; font-family: 'Poppins'; }
            .input input, .input select {
                width: 370px; padding: 10px 15px; background-color: #fff;
                border-radius: 10px; border: 1px solid var(--input-border); outline: none;
            }
            .cadastro .container .parte_cinco .comentario textarea {
                border: 1px solid var(--input-border); border-radius: 10px; padding: 20px;
                outline: none; height: 140px; width: 1193px; max-width: 1193px;
            }
            .parte_quatro { display: flex; align-items: center; justify-content: center; }
            .btn_adicionar button {
                padding: 10px 30px; border-radius: 10px; cursor: pointer; border: 1px solid var(--input-border);
                background-color: var(--background-novo);
            }
        </style>

        <?php include '../includes/nav.php'; ?>

        <main class="novo_main cadastro editar">
            <div class="container">
                <form action="cadLivro.php?status=<?php echo $status_livro; ?>" method="POST" enctype="multipart/form-data">
                    <section class="parte_um">
                        <div class="voltar">
                            <a href="<?php echo $status_livro; ?>.php"><i class="ri-arrow-left-line"></i></a>
                        </div>
                        <div class="titulo">
                            <h1>Adicione aqui o livro que você <?php echo ($status_livro == 'lido' ? 'já leu' : ($status_livro == 'lendo' ? 'está lendo' : 'quer ler')); ?></h1>
                        </div>
                    </section>

                    <section class="parte_dois">
                        <div class="input">
                            <label for="titulo">Título</label>
                            <input type="text" name="titulo" required>
                        </div>
                        <div class="input">
                            <label for="autor">Autor</label>
                            <input type="text" name="autor" required>
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
                                        echo '<option value="' . $row_genero['id_genero'] . '">' . $row_genero['nome_genero'] . '</option>';
                                    }
                                }
                                $conn_generos->close();
                                ?>
                                <option value="novo">Outro (digitar novo gênero)</option>
                            </select>
                            <input type="text" name="novo_genero" id="novo_genero_input" placeholder="Digite o novo gênero" style="display:none;">
                        </div>
                    </section>

                    <section class="parte_tres" style="display: flex; flex-direction: row; gap: 30px;">
                        <div class="input">
                            <label for="capa">Capa do Livro</label>
                            <input type="file" name="capa" id="capa" accept="image/*">
                        </div>
                        <div class="input" id="total_paginas_div" <?php echo ($status_livro == 'quero_ler' ? '' : ''); ?>>
                            <label for="total_paginas">Total de páginas:</label>
                            <input type="number" name="total_paginas" id="total_paginas" <?php echo ($status_livro == 'quero_ler' ? '' : 'required'); ?>>
                        </div>
                        <div class="input" id="pagina_atual_div" <?php echo ($status_livro == 'lido' || $status_livro == 'quero_ler' ? 'style="display:none;"' : ''); ?>>
                            <label for="pagina_atual">Página atual:</label>
                            <input type="number" id="pagina_atual" name="pagina_atual" <?php echo ($status_livro == 'lido' || $status_livro == 'quero_ler' ? '' : 'required'); ?>>
                        </div>
                    </section>

                    <section class="parte_quatro">
                        <div class="input" id="avaliacao_div" <?php echo ($status_livro == 'lido' ? '' : 'style="display:none;"'); ?>>
                            <label for="avaliacao">Avalie o livro de 1 a 5</label>
                            <input type="number" id="avaliacao" name="avaliacao" min="1" max="5" <?php echo ($status_livro == 'lido' ? 'required' : ''); ?>>
                        </div>
                    </section>

                    <section class="parte_cinco">
                        <div class="comentario">
                            <label for="comentario">Comentário</label>
                            <textarea name="comentario" id="comentario"></textarea>
                        </div>
                    </section>

                    <section class="parte_seis">
                        <div class="btn_adicionar btns">
                            <button id="adicionar" type="submit">ADICIONAR LIVRO</button>
                        </div>
                    </section>
                </form>
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

            // Lógica para mostrar/esconder campos baseados no status
            document.addEventListener('DOMContentLoaded', function() {
                var status = "<?php echo $status_livro; ?>";
                var totalPaginasDiv = document.getElementById('total_paginas_div');
                var paginaAtualDiv = document.getElementById('pagina_atual_div');
                var avaliacaoDiv = document.getElementById('avaliacao_div');
                var totalPaginasInput = document.getElementById('total_paginas');
                var paginaAtualInput = document.getElementById('pagina_atual');
                var avaliacaoInput = document.getElementById('avaliacao');

                if (status === 'lido') {
                    totalPaginasDiv.style.display = 'flex';
                    paginaAtualDiv.style.display = 'none'; // Não precisa de página atual se já leu
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
                    totalPaginasDiv.style.display = 'flex'; // Pode ser útil para saber o tamanho do livro
                    paginaAtualDiv.style.display = 'none';
                    avaliacaoDiv.style.display = 'none';
                    totalPaginasInput.removeAttribute('required'); // Não é obrigatório para "Quero Ler"
                    paginaAtualInput.removeAttribute('required');
                    avaliacaoInput.removeAttribute('required');
                }
            });
        </script>
    </body>
    </html>
    