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

    if (empty($dados['codigo'])) {
        throw new Exception('Código é obrigatório');
    }

    $nome = trim($dados['nome']);
    $codigo = trim(strtoupper($dados['codigo']));
    $nivel = isset($dados['nivel']) ? $dados['nivel'] : 'tecnico';
    $carga_horaria_total = isset($dados['carga_horaria_total']) && !empty($dados['carga_horaria_total'])
                          ? (int)$dados['carga_horaria_total'] : null;
    $duracao_meses = isset($dados['duracao_meses']) && !empty($dados['duracao_meses'])
                    ? (int)$dados['duracao_meses'] : null;
    $carga_horaria_semanal = isset($dados['carga_horaria_semanal']) && !empty($dados['carga_horaria_semanal'])
                            ? (int)$dados['carga_horaria_semanal'] : 0;
    $descricao = isset($dados['descricao']) ? trim($dados['descricao']) : null;
    $ativo = isset($dados['ativo']) ? (int)$dados['ativo'] : 1;
    $curso_id = isset($dados['id']) ? (int)$dados['id'] : null;

    $mysqli->begin_transaction();

    if ($curso_id) {
        // ATUALIZAR curso existente

        // Verifica se o código já existe em outro curso
        $query = "SELECT id FROM cursos WHERE codigo = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $codigo, $curso_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este código já está cadastrado');
        }

        // Atualiza curso
        $query = "UPDATE cursos SET
                    nome = ?,
                    codigo = ?,
                    nivel = ?,
                    carga_horaria_total = ?,
                    duracao_meses = ?,
                    carga_horaria_semanal = ?,
                    descricao = ?,
                    ativo = ?
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssiiisii", $nome, $codigo, $nivel, $carga_horaria_total,
                         $duracao_meses, $carga_horaria_semanal, $descricao, $ativo, $curso_id);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar curso');
        }

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Curso atualizado com sucesso!',
            'id' => $curso_id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // CRIAR novo curso

        // Verifica se o código já existe
        $query = "SELECT id FROM cursos WHERE codigo = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception('Este código já está cadastrado');
        }

        // Insere curso
        $query = "INSERT INTO cursos
                  (nome, codigo, nivel, carga_horaria_total, duracao_meses, carga_horaria_semanal, carga_horaria_preenchida, descricao, ativo)
                  VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sssiiisi", $nome, $codigo, $nivel, $carga_horaria_total,
                         $duracao_meses, $carga_horaria_semanal, $descricao, $ativo);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar curso');
        }

        $curso_id = $mysqli->insert_id;

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Curso cadastrado com sucesso!',
            'id' => $curso_id
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
