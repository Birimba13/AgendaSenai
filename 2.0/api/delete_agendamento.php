<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id']);

    if (empty($id)) {
        throw new Exception('ID do agendamento é obrigatório');
    }

    // Verificar se existe
    $check = $mysqli->prepare("SELECT id FROM agendamentos WHERE id = ?");
    $check->bind_param('i', $id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        throw new Exception('Agendamento não encontrado');
    }

    // Deletar agendamento
    $stmt = $mysqli->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Agendamento excluído com sucesso'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
