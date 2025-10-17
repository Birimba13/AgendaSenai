<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $turma_id = isset($_GET['turma_id']) ? (int)$_GET['turma_id'] : 0;
    
    if ($turma_id <= 0) {
        throw new Exception('ID da turma inválido');
    }
    
    $query = "SELECT d.id, d.nome, d.sigla, d.carga_horaria
              FROM disciplinas d
              INNER JOIN curso_disciplinas cd ON d.id = cd.disciplina_id
              WHERE cd.curso_id = ?
              ORDER BY d.nome";
    
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $turma_id);
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
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}