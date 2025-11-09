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
    if (empty($dados['codigo'])) {
        throw new Exception('Código é obrigatório');
    }

    if (empty($dados['nome'])) {
        throw new Exception('Nome é obrigatório');
    }

    $codigo = trim(strtoupper($dados['codigo']));
    $nome = trim($dados['nome']);
    $capacidade = isset($dados['capacidade']) && !empty($dados['capacidade']) ? (int)$dados['capacidade'] : null;
    $tipo = isset($dados['tipo']) ? $dados['tipo'] : 'sala_aula';
    $recursos = isset($dados['recursos']) ? json_encode($dados['recursos']) : null;
    $local = isset($dados['local']) ? trim($dados['local']) : 'Afonso Pena';
    $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;
    $sala_id = isset($dados['id']) ? (int)$dados['id'] : null;

    $mysqli->begin_transaction();

    if ($sala_id) {
        // ATUALIZAR sala existente

        // Verifica se o código já existe em outra sala
        $query = "SELECT id FROM salas WHERE codigo = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $codigo, $sala_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este código já está cadastrado');
        }

        // Atualiza sala
        $query = "UPDATE salas SET
                    codigo = ?,
                    nome = ?,
                    capacidade = ?,
                    tipo = ?,
                    recursos = ?,
                    local = ?,
                    ativo = ?
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssisssii", $codigo, $nome, $capacidade, $tipo, $recursos, $local, $ativo, $sala_id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar sala');
        }

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Sala atualizada com sucesso!',
            'id' => $sala_id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // CRIAR nova sala

        // Verifica se o código já existe
        $query = "SELECT id FROM salas WHERE codigo = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este código já está cadastrado');
        }

        // Insere sala
        $query = "INSERT INTO salas (codigo, nome, capacidade, tipo, recursos, local, ativo)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssisssi", $codigo, $nome, $capacidade, $tipo, $recursos, $local, $ativo);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar sala');
        }

        $sala_id = $mysqli->insert_id;

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Sala cadastrada com sucesso!',
            'id' => $sala_id
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
