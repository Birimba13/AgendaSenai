<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    // Aceita tanto DELETE quanto POST
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id'])) {
        throw new Exception('ID do curso é obrigatório');
    }

    $curso_id = (int)$dados['id'];

    // Verifica se o curso existe
    $query = "SELECT id FROM cursos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Curso não encontrado');
    }

    // Verifica se há turmas associadas ao curso
    $query = "SELECT COUNT(*) as total FROM turmas WHERE curso_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        throw new Exception('Não é possível excluir este curso pois existem ' . $row['total'] . ' turma(s) associada(s)');
    }

    // Deleta o curso (disciplinas e curso_disciplinas serão deletadas em cascata)
    $query = "DELETE FROM cursos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $curso_id);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir curso');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Curso excluído com sucesso!'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
