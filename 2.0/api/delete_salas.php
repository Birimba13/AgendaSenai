<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

try {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['id'])) {
        throw new Exception('ID da sala é obrigatório');
    }

    $sala_id = (int)$dados['id'];

    // Verifica se a sala existe
    $query = "SELECT id FROM salas WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $sala_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Sala não encontrada');
    }

    // Deleta a sala
    $query = "DELETE FROM salas WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $sala_id);

    if (!$stmt->execute()) {
        throw new Exception('Erro ao excluir sala');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Sala excluída com sucesso!'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
