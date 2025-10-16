<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    // Recebe o ID (pode vir por DELETE ou POST)
    $professor_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $professor_id = isset($dados['id']) ? (int)$dados['id'] : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $professor_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    } else {
        throw new Exception('Método não permitido');
    }
    
    if (!$professor_id) {
        throw new Exception('ID do professor não fornecido');
    }
    
    // Busca o usuario_id do professor
    $query = "SELECT usuario_id FROM professores WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Professor não encontrado');
    }
    
    $row = $result->fetch_assoc();
    $usuario_id = $row['usuario_id'];
    
    // Verifica se o professor tem agendamentos
    $query = "SELECT COUNT(*) as total FROM agendamentos WHERE professor_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir. Professor possui agendamentos vinculados.');
    }
    
    $mysqli->begin_transaction();
    
    // Deleta as disciplinas do professor
    $query = "DELETE FROM professor_disciplinas WHERE professor_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    
    // Deleta o professor (isso também deletará o usuário por CASCADE)
    $query = "DELETE FROM professores WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $professor_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir professor');
    }
    
    // Deleta o usuário
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir usuário');
    }
    
    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Professor excluído com sucesso!'
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