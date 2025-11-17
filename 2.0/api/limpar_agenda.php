<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

protect();

try {
    $mysqli->begin_transaction();

    // Contar aulas que serão excluídas
    $result = $mysqli->query("SELECT COUNT(*) as total FROM agendamentos WHERE status != 'confirmado'");
    $total = $result->fetch_assoc()['total'];

    // Excluir aulas não confirmadas
    $mysqli->query("DELETE FROM agendamentos WHERE status != 'confirmado'");

    // Resetar cargas horárias
    $mysqli->query("UPDATE cursos SET carga_horaria_preenchida = 0");
    $mysqli->query("UPDATE professores SET carga_horaria_usada = 0");

    // Recalcular apenas aulas confirmadas (se houver)
    $mysqli->query("
        UPDATE cursos c
        SET carga_horaria_preenchida = (
            SELECT COUNT(*)
            FROM agendamentos a
            INNER JOIN disciplinas d ON a.disciplina_id = d.id
            WHERE d.curso_id = c.id AND a.status = 'confirmado'
        )
    ");

    $mysqli->query("
        UPDATE professores p
        SET carga_horaria_usada = (
            SELECT COUNT(*)
            FROM agendamentos a
            WHERE a.professor_id = p.id AND a.status = 'confirmado'
        )
    ");

    $mysqli->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Agenda limpa com sucesso',
        'total_excluidas' => $total
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
