<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // 1. Verifica se o ID do curso (turma) foi fornecido
    if (!isset($_GET['curso_id'])) {
        throw new Exception('ID do curso não fornecido.');
    }
    
    $curso_id = (int)$_GET['curso_id'];

    // 2. Busca os IDs das disciplinas vinculadas
    $query = "SELECT disciplina_id FROM curso_disciplinas WHERE curso_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $disciplina_ids = [];
    while ($row = $result->fetch_assoc()) {
        // 3. Adiciona o ID ao array, garantindo que seja um inteiro
        $disciplina_ids[] = (int)$row['disciplina_id']; 
    }
    
    // 4. Retorna o array de IDs
    echo json_encode([
        'success' => true,
        'data' => $disciplina_ids
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar disciplinas da turma: ' . $e->getMessage()
    ]);
}
