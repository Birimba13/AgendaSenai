<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                id,
                codigo,
                nome,
                tipo,
                capacidade,
                local
              FROM salas
              WHERE ativo = TRUE
              ORDER BY nome";

    $result = $mysqli->query($query);
    $salas = [];

    while ($row = $result->fetch_assoc()) {
        $salas[] = [
            'id' => (int)$row['id'],
            'codigo' => $row['codigo'],
            'nome' => $row['nome'],
            'tipo' => $row['tipo'],
            'capacidade' => $row['capacidade'] ? (int)$row['capacidade'] : null,
            'local' => $row['local']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $salas
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar salas: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
