<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    $aluno_id = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $dados = json_decode(file_get_contents('php://input'), true);
        $aluno_id = isset($dados['id']) ? (int)$dados['id'] : null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $aluno_id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    } else {
        throw new Exception('Método não permitido');
    }
    
    if (!$aluno_id) {
        throw new Exception('ID do aluno não fornecido');
    }
    
    // Verifica se o aluno existe
    $query = "SELECT nome FROM alunos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Aluno não encontrado');
    }
    
    $mysqli->begin_transaction();
    
    // Deleta o aluno
    $query = "DELETE FROM alunos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $aluno_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir aluno');
    }
    
    $mysqli->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Aluno excluído com sucesso!'
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