<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                c.id,
                c.nome,
                c.codigo,
                c.nivel,
                c.carga_horaria_total,
                c.duracao_meses,
                c.carga_horaria_semanal,
                c.carga_horaria_preenchida,
                c.descricao,
                c.ativo,
                c.data_criacao,
                COUNT(DISTINCT d.id) AS total_disciplinas,
                COUNT(DISTINCT t.id) AS total_turmas
              FROM cursos c
              LEFT JOIN disciplinas d ON c.id = d.curso_id AND d.ativo = TRUE
              LEFT JOIN turmas t ON c.id = t.curso_id AND t.ativo = TRUE
              GROUP BY c.id, c.nome, c.codigo, c.nivel, c.carga_horaria_total,
                       c.duracao_meses, c.carga_horaria_semanal, c.carga_horaria_preenchida,
                       c.descricao, c.ativo, c.data_criacao
              ORDER BY c.nome";

    $result = $mysqli->query($query);

    $cursos = [];

    while ($row = $result->fetch_assoc()) {
        $cursos[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'codigo' => $row['codigo'],
            'nivel' => $row['nivel'],
            'carga_horaria_total' => $row['carga_horaria_total'] ? (int)$row['carga_horaria_total'] : null,
            'duracao_meses' => $row['duracao_meses'] ? (int)$row['duracao_meses'] : null,
            'carga_horaria_semanal' => $row['carga_horaria_semanal'] ? (int)$row['carga_horaria_semanal'] : 0,
            'carga_horaria_preenchida' => $row['carga_horaria_preenchida'] ? (int)$row['carga_horaria_preenchida'] : 0,
            'descricao' => $row['descricao'],
            'ativo' => (bool)$row['ativo'],
            'data_criacao' => $row['data_criacao'],
            'total_disciplinas' => (int)$row['total_disciplinas'],
            'total_turmas' => (int)$row['total_turmas']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $cursos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar cursos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
