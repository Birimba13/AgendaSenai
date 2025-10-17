<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT 
                id,
                nome,
                sigla,
                carga_horaria
              FROM disciplinas 
              ORDER BY nome";
    
    $result = $mysqli->query($query);
    
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
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar disciplinas: ' . $e->getMessage()
    ]);
}