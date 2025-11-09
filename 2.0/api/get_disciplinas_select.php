<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $curso_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : null;

    $query = "SELECT
                d.id,
                d.nome,
                d.sigla,
                c.nome AS curso_nome
              FROM disciplinas d
              INNER JOIN cursos c ON d.curso_id = c.id
              WHERE d.ativo = TRUE";

    if ($curso_id) {
        $query .= " AND d.curso_id = ?";
    }

    $query .= " ORDER BY d.nome";

    if ($curso_id) {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $curso_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query($query);
    }

    $disciplinas = [];

    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'sigla' => $row['sigla'],
            'curso_nome' => $row['curso_nome']
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
