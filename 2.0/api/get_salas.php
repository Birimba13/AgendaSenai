<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT
                id,
                codigo,
                nome,
                capacidade,
                tipo,
                recursos,
                local,
                ativo
              FROM salas
              ORDER BY codigo";

    $result = $mysqli->query($query);

    $salas = [];

    while ($row = $result->fetch_assoc()) {
        $salas[] = [
            'id' => (int)$row['id'],
            'codigo' => $row['codigo'],
            'nome' => $row['nome'],
            'capacidade' => $row['capacidade'] ? (int)$row['capacidade'] : null,
            'tipo' => $row['tipo'],
            'recursos' => $row['recursos'] ? json_decode($row['recursos'], true) : null,
            'local' => $row['local'],
            'ativo' => (bool)$row['ativo']
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
