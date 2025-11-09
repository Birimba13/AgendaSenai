<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../app/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $dados = json_decode(file_get_contents('php://input'), true);

    // Validações
    if (empty($dados['data'])) {
        throw new Exception('Data é obrigatória');
    }

    if (empty($dados['tipo'])) {
        throw new Exception('Tipo é obrigatório');
    }

    if (empty($dados['descricao'])) {
        throw new Exception('Descrição é obrigatória');
    }

    $data = $dados['data'];
    $tipo = $dados['tipo'];
    $descricao = trim($dados['descricao']);
    $dia_letivo = isset($dados['dia_letivo']) ? (int)$dados['dia_letivo'] : 0;
    $observacoes = isset($dados['observacoes']) ? trim($dados['observacoes']) : null;
    $evento_id = isset($dados['id']) ? (int)$dados['id'] : null;

    $mysqli->begin_transaction();

    if ($evento_id) {
        // ATUALIZAR evento existente

        // Verifica se a data já existe em outro evento
        $query = "SELECT id FROM calendario WHERE data = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $data, $evento_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Já existe um evento cadastrado para esta data');
        }

        // Atualiza evento
        $query = "UPDATE calendario SET
                    data = ?,
                    tipo = ?,
                    descricao = ?,
                    dia_letivo = ?,
                    observacoes = ?
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssisi", $data, $tipo, $descricao, $dia_letivo, $observacoes, $evento_id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar evento');
        }

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Evento atualizado com sucesso!',
            'id' => $evento_id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // CRIAR novo evento

        // Verifica se a data já existe
        $query = "SELECT id FROM calendario WHERE data = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $data);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Já existe um evento cadastrado para esta data');
        }

        // Insere evento
        $query = "INSERT INTO calendario (data, tipo, descricao, dia_letivo, observacoes)
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssiss", $data, $tipo, $descricao, $dia_letivo, $observacoes);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar evento');
        }

        $evento_id = $mysqli->insert_id;

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Evento cadastrado com sucesso!',
            'id' => $evento_id
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
