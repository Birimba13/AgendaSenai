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
    if (empty($dados['nome'])) {
        throw new Exception('Nome é obrigatório');
    }

    if (empty($dados['curso_id'])) {
        throw new Exception('Curso é obrigatório');
    }

    if (empty($dados['turno'])) {
        throw new Exception('Turno é obrigatório');
    }

    if (empty($dados['data_inicio'])) {
        throw new Exception('Data de início é obrigatória');
    }

    if (empty($dados['data_fim'])) {
        throw new Exception('Data de fim é obrigatória');
    }

    $nome = trim($dados['nome']);
    $curso_id = (int)$dados['curso_id'];
    $periodo = isset($dados['periodo']) ? trim($dados['periodo']) : null;
    $turno = $dados['turno'];
    $data_inicio = $dados['data_inicio'];
    $data_fim = $dados['data_fim'];
    $status = isset($dados['status']) ? $dados['status'] : 'ativo';
    $observacoes = isset($dados['observacoes']) ? trim($dados['observacoes']) : null;
    $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;
    $turma_id = isset($dados['id']) ? (int)$dados['id'] : null;

    // Valida se data fim é maior que data início
    if (strtotime($data_fim) <= strtotime($data_inicio)) {
        throw new Exception('Data de fim deve ser maior que data de início');
    }

    // Verifica se o curso existe
    $query = "SELECT id FROM cursos WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $curso_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Curso não encontrado');
    }

    $mysqli->begin_transaction();

    if ($turma_id) {
        // ATUALIZAR turma existente

        // Verifica se o nome já existe em outra turma
        $query = "SELECT id FROM turmas WHERE nome = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $nome, $turma_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este nome já está cadastrado');
        }

        // Atualiza turma
        $query = "UPDATE turmas SET
                          nome = ?,
                          curso_id = ?,
                          periodo = ?,
                          turno = ?,
                          data_inicio = ?,
                          data_fim = ?,
                          status = ?,
                          observacoes = ?,
                          ativo = ?
                        WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sissssssii", $nome, $curso_id, $periodo, $turno,
                         $data_inicio, $data_fim, $status, $observacoes, $ativo, $turma_id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar turma');
        }

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Turma atualizada com sucesso!',
            'id' => $turma_id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // CRIAR nova turma

        // Verifica se o nome já existe
        $query = "SELECT id FROM turmas WHERE nome = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este nome já está cadastrado');
        }

        // Insere turma
        $query = "INSERT INTO turmas
                  (nome, curso_id, periodo, turno, data_inicio, data_fim, status, observacoes, ativo)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sissssssi", $nome, $curso_id, $periodo, $turno,
                         $data_inicio, $data_fim, $status, $observacoes, $ativo);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar turma');
        }

        $turma_id = $mysqli->insert_id;

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Turma cadastrada com sucesso!',
            'id' => $turma_id
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
    ]);
}