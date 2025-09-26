<?php
// error_reporting(E_ALL);  // Comente para produção; descomente para debug
// ini_set('display_errors', 1);

include('../includes/auth.php');  // Sessão + connect
$id_usuario = $id_usuario_logado;  // Deve ser 1 ou 2

$usa_transacao = false;  // Inicializa para catch (evita undefined)

function getRedirectPage($status) {
    $map = ['lido' => 'lido.php', 'lendo' => 'lendo.php', 'quero_ler' => 'quero_ler.php'];
    return $map[$status] ?? 'index.php';
}

try {  // ← ADICIONADO: Inicia o try para capturar exceções
    if (isset($_GET['id']) && is_numeric($_GET['id']) && $_GET['id'] > 0) {
        $id_livro = (int)$_GET['id'];

        // SELECT com filtro por id_usuario
        $sql_select = "SELECT capa, status FROM tbl_livros WHERE id_livro = ? AND id_usuario = ?";
        $stmt_select = $conn->prepare($sql_select);
        if (!$stmt_select) {
            throw new Exception("Erro na preparação da seleção: " . $conn->error);
        }
        $stmt_select->bind_param("ii", $id_livro, $id_usuario);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $livro_info = $result_select->fetch_assoc();
        $stmt_select->close();

        if ($livro_info) {
            $capa_path = $livro_info['capa'];
            $status_redirecionar = $livro_info['status'];

            // Transação para InnoDB (agora dentro do try)
            $result_engine = $conn->query("SHOW TABLE STATUS LIKE 'tbl_livros'");
            $table_engine = $result_engine ? $result_engine->fetch_assoc()['Engine'] : 'UNKNOWN';
            $usa_transacao = ($table_engine === 'InnoDB');

            if ($usa_transacao) {
                if (!$conn->begin_transaction()) {
                    throw new Exception("Erro ao iniciar transação: " . $conn->error);
                }
            }

            // DELETE: Só o livro – CASCADE cuida das avaliações
            $sql_delete_livro = "DELETE FROM tbl_livros WHERE id_livro = ? AND id_usuario = ?";
            $stmt_delete_livro = $conn->prepare($sql_delete_livro);
            if (!$stmt_delete_livro) {
                throw new Exception("Erro na preparação da deleção: " . $conn->error);
            }
            $stmt_delete_livro->bind_param("ii", $id_livro, $id_usuario);
            if (!$stmt_delete_livro->execute()) {
                throw new Exception("Erro ao executar deleção: " . $stmt_delete_livro->error);
            }
            $affected_livros = $stmt_delete_livro->affected_rows;
            $stmt_delete_livro->close();

            if ($affected_livros > 0) {
                // Deleção de capa
                if (!empty($capa_path) && file_exists($capa_path)) {
                    unlink($capa_path);  // Sem check de erro – loga se falhar
                }

                if ($usa_transacao) {
                    $conn->commit();
                }

                // REDIRECIONAMENTO: Com msg de sucesso
                $redirect_page = getRedirectPage($status_redirecionar);
                header("Location: " . $redirect_page . "?msg=sucesso_delete");
                exit();

            } else {
                throw new Exception("Nenhuma linha afetada. Livro não encontrado ou sem permissão.");
            }

        } else {
            throw new Exception("Livro não encontrado ou sem permissão.");
        }

    } else {
        throw new Exception("ID inválido.");
    }

} catch (Exception $e) {  // ← AGORA FUNCIONA: Catch pareado com try
    if ($usa_transacao) {
        $conn->rollback();
    }
    die("Erro na exclusão: " . $e->getMessage() . ". <a href='lido.php'>Voltar</a>");
}

$conn->close();
?>
