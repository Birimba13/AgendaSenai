<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                t.id,
                t.nome,
                t.periodo,
                t.turno,
                t.data_inicio,
                t.data_fim,
                t.status,
                t.observacoes,
                t.ativo,
                t.curso_id,
                c.nome AS curso_nome,
                c.codigo AS curso_codigo,
                COUNT(DISTINCT a.id) AS total_alunos
              FROM turmas t
              INNER JOIN cursos c ON t.curso_id = c.id
              LEFT JOIN alunos a ON t.id = a.turma_id AND a.status = 'ativo'
              GROUP BY t.id, t.nome, t.periodo, t.turno, t.data_inicio, t.data_fim,
                       t.status, t.observacoes, t.ativo, t.curso_id, c.nome, c.codigo
              ORDER BY t.data_inicio DESC, t.nome";

    $result = $mysqli->query($query);

    $turmas = [];

    while ($row = $result->fetch_assoc()) {
        $turmas[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'periodo' => $row['periodo'],
            'turno' => $row['turno'],
            'data_inicio' => $row['data_inicio'],
            'data_fim' => $row['data_fim'],
            'status' => $row['status'],
            'observacoes' => $row['observacoes'],
            'ativo' => (bool)$row['ativo'],
            'curso_id' => (int)$row['curso_id'],
            'curso_nome' => $row['curso_nome'],
            'curso_codigo' => $row['curso_codigo'],
            'total_alunos' => (int)$row['total_alunos']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $turmas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar turmas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}