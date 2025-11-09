<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                t.id,
                t.nome,
                t.turno,
                c.nome AS curso_nome
              FROM turmas t
              INNER JOIN cursos c ON t.curso_id = c.id
              WHERE t.ativo = TRUE
              ORDER BY t.nome";

    $result = $mysqli->query($query);
    $turmas = [];

    while ($row = $result->fetch_assoc()) {
        $turmas[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'turno' => $row['turno'],
            'curso_nome' => $row['curso_nome']
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
