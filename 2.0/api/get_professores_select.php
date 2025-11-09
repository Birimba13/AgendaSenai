<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                p.id,
                u.nome
              FROM professores p
              INNER JOIN usuarios u ON p.usuario_id = u.id
              WHERE p.ativo = TRUE
              ORDER BY u.nome";

    $result = $mysqli->query($query);
    $professores = [];

    while ($row = $result->fetch_assoc()) {
        $professores[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $professores
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar professores: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
