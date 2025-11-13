<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Parâmetro obrigatório
    $curso_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : null;

    if (!$curso_id) {
        throw new Exception('ID do curso é obrigatório');
    }

    // Buscar disciplinas do curso
    $query = "SELECT d.id, d.nome, d.sigla, d.carga_horaria
              FROM disciplinas d
              WHERE d.curso_id = ?
              AND d.status = 'ativa'
              ORDER BY d.nome";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $disciplinas = [];
    while ($row = $result->fetch_assoc()) {
        $disciplinas[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'sigla' => $row['sigla'],
            'carga_horaria' => (int)$row['carga_horaria']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $disciplinas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
