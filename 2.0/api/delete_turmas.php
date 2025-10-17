<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    $turma_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $turma_id = isset($dados['id']) ? (int)$dados['id'] : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $turma_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    } else {
        throw new Exception('Método não permitido');
    }
    
    if (!$turma_id) {
        throw new Exception('ID da turma não fornecido');
    }
    
    // Verifica se a turma existe
    $query = "SELECT nome FROM cursos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Turma não encontrada');
    }
    
    // Verifica se a turma tem agendamentos
    $query = "SELECT COUNT(*) as total FROM agendamentos WHERE curso_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir. Turma possui agendamentos vinculados.');
    }
    
    $mysqli->begin_transaction();
    
    // Deleta as disciplinas da turma
    $query = "DELETE FROM curso_disciplinas WHERE curso_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    
    // Deleta a turma
    $query = "DELETE FROM cursos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $turma_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir turma');
    }
    
    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Turma excluída com sucesso!'
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