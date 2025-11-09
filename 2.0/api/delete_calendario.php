<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id'])) {
        throw new Exception('ID do evento é obrigatório');
    }

    $evento_id = (int)$dados['id'];

    // Verifica se o evento existe
    $query = "SELECT id FROM calendario WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Evento não encontrado');
    }

    // Deleta o evento
    $query = "DELETE FROM calendario WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $evento_id);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir evento');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Evento excluído com sucesso!'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
