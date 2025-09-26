    <?php
    include('../includes/auth.php');  // Agora inclui sessão + connect + verificação
    $id_usuario = $_SESSION['id_usuario'];  // Pronto para usar nas queries
    include('../includes/connect.php');

    if (isset($_GET['id']) && isset($_GET['action'])) {
        $id_livro = $_GET['id'];
        $action = $_GET['action'];

        $conn->begin_transaction();

        try {
            $new_status = '';
            $redirect_page = '';

            if ($action == 'to_lido') {
                $new_status = 'lido';
                $redirect_page = 'lido.php';

                // Ao marcar como lido, atualiza pagina_atual para total_paginas e progresso para 100%
                $stmt_update_lido = $conn->prepare("UPDATE tbl_livros SET status = ?, pagina_atual = total_paginas, progresso = 100.00 WHERE id_livro = ?");
                $stmt_update_lido->bind_param("si", $new_status, $id_livro);
                if (!$stmt_update_lido->execute()) {
                    throw new Exception("Erro ao atualizar status para 'lido': " . $stmt_update_lido->error);
                }
                $stmt_update_lido->close();

                // Opcional: Inserir uma avaliação padrão (ou deixar para o usuário editar)
                // Verifique se já existe uma avaliação para este livro antes de inserir
                $stmt_check_avaliacao = $conn->prepare("SELECT id_avaliacao FROM tbl_avaliacoes WHERE id_livro = ?");
                $stmt_check_avaliacao->bind_param("i", $id_livro);
                $stmt_check_avaliacao->execute();
                $result_check_avaliacao = $stmt_check_avaliacao->get_result();
                if ($result_check_avaliacao->num_rows == 0) {
                    $default_avaliacao = 3; // Exemplo de avaliação padrão
                    $stmt_insert_avaliacao = $conn->prepare("INSERT INTO tbl_avaliacoes (id_livro, nota) VALUES (?, ?)");
                    $stmt_insert_avaliacao->bind_param("ii", $id_livro, $default_avaliacao);
                    $stmt_insert_avaliacao->execute();
                    $stmt_insert_avaliacao->close();
                }
                $stmt_check_avaliacao->close();


            } elseif ($action == 'to_lendo') {
                $new_status = 'lendo';
                $redirect_page = 'lendo.php';

                // Ao começar a ler, define pagina_atual para 0 e progresso para 0%
                $stmt_update_lendo = $conn->prepare("UPDATE tbl_livros SET status = ?, pagina_atual = 0, progresso = 0.00 WHERE id_livro = ?");
                $stmt_update_lendo->bind_param("si", $new_status, $id_livro);
                if (!$stmt_update_lendo->execute()) {
                    throw new Exception("Erro ao atualizar status para 'lendo': " . $stmt_update_lendo->error);
                }
                $stmt_update_lendo->close();

            } else {
                throw new Exception("Ação de transferência inválida.");
            }

            $conn->commit();
            header("Location: " . $redirect_page);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            echo "Erro na transferência: " . $e->getMessage();
        }
        $conn->close();
    } else {
        echo "Parâmetros inválidos para transferência.";
    }
    ?>
    