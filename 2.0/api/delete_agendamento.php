<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id']);

    if (empty($id)) {
        throw new Exception('ID do agendamento é obrigatório');
    }

    // Buscar informações do agendamento ANTES de excluir
    $query_agend = "SELECT a.professor_id, a.disciplina_id, d.curso_id
                    FROM agendamentos a
                    LEFT JOIN disciplinas d ON a.disciplina_id = d.id
                    WHERE a.id = ?";
    $stmt_agend = $mysqli->prepare($query_agend);
    $stmt_agend->bind_param('i', $id);
    $stmt_agend->execute();
    $agend_data = $stmt_agend->get_result()->fetch_assoc();

    if (!$agend_data) {
        throw new Exception('Agendamento não encontrado');
    }

    $professor_id = $agend_data['professor_id'];
    $curso_id = $agend_data['curso_id'];

    // Deletar agendamento
    $stmt = $mysqli->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Atualizar carga horária do professor (-1 hora)
    if ($professor_id) {
        $update_prof = "UPDATE professores SET carga_horaria_usada = GREATEST(0, carga_horaria_usada - 1) WHERE id = ?";
        $stmt_prof = $mysqli->prepare($update_prof);
        $stmt_prof->bind_param('i', $professor_id);
        $stmt_prof->execute();
    }

    // Recalcular carga horária do curso
    if ($curso_id) {
        $update_curso = "UPDATE cursos c
                        SET c.carga_horaria_preenchida = (
                            SELECT COUNT(*)
                            FROM agendamentos a
                            INNER JOIN disciplinas d ON a.disciplina_id = d.id
                            WHERE d.curso_id = ?
                            AND a.status != 'cancelado'
                        )
                        WHERE c.id = ?";
        $stmt_curso = $mysqli->prepare($update_curso);
        $stmt_curso->bind_param('ii', $curso_id, $curso_id);
        $stmt_curso->execute();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Agendamento excluído com sucesso'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
