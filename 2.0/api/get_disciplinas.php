<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                d.id,
                d.curso_id,
                d.nome,
                d.sigla,
                d.carga_horaria,
                d.descricao,
                d.ativo,
                c.nome AS curso_nome,
                c.codigo AS curso_codigo
              FROM disciplinas d
              INNER JOIN cursos c ON d.curso_id = c.id
              ORDER BY c.nome, d.nome";

    $result = $mysqli->query($query);

    $disciplinas = [];

    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = [
            'id' => (int)$row['id'],
            'curso_id' => (int)$row['curso_id'],
            'nome' => $row['nome'],
            'sigla' => $row['sigla'],
            'carga_horaria' => (int)$row['carga_horaria'],
            'descricao' => $row['descricao'],
            'ativo' => (bool)$row['ativo'],
            'curso_nome' => $row['curso_nome'],
            'curso_codigo' => $row['curso_codigo']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $disciplinas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar disciplinas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}