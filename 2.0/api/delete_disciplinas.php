<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    $disciplina_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $disciplina_id = isset($dados['id']) ? (int)$dados['id'] : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $disciplina_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    } else {
        throw new Exception('Método não permitido');
    }
    
    if (!$disciplina_id) {
        throw new Exception('ID da disciplina não fornecido');
    }
    
    // Verifica se a disciplina existe
    $query = "SELECT nome FROM disciplinas WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $disciplina_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Disciplina não encontrada');
    }
    
    // Verifica se a disciplina tem professores vinculados
    $query = "SELECT COUNT(*) as total FROM professor_disciplinas WHERE disciplina_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $disciplina_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir. Disciplina possui professores vinculados.');
    }
    
    // Verifica se a disciplina tem cursos vinculados
    $query = "SELECT COUNT(*) as total FROM curso_disciplinas WHERE disciplina_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $disciplina_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir. Disciplina possui cursos vinculados.');
    }
    
    $mysqli->begin_transaction();
    
    // Deleta a disciplina
    $query = "DELETE FROM disciplinas WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $disciplina_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir disciplina');
    }
    
    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Disciplina excluída com sucesso!'
    ]);
    
} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}