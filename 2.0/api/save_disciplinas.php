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
    
    if (empty($dados['sigla'])) {
        throw new Exception('Sigla é obrigatória');
    }
    
    if (empty($dados['carga_horaria']) || $dados['carga_horaria'] < 1) {
        throw new Exception('Carga horária inválida');
    }
    
    $nome = trim($dados['nome']);
    $sigla = trim(strtoupper($dados['sigla']));
    $carga_horaria = (int)$dados['carga_horaria'];
    $disciplina_id = isset($dados['id']) ? (int)$dados['id'] : null;
    
    $mysqli->begin_transaction();
    
    if ($disciplina_id) {
        // ATUALIZAR disciplina existente
        
        // Verifica se a sigla já existe em outra disciplina
        $query = "SELECT id FROM disciplinas WHERE sigla = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $sigla, $disciplina_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Esta sigla já está cadastrada');
        }
        
        // Atualiza disciplina
        $query = "UPDATE disciplinas SET 
                    nome = ?, 
                    sigla = ?, 
                    carga_horaria = ? 
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssii", $nome, $sigla, $carga_horaria, $disciplina_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar disciplina');
        }
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Disciplina atualizada com sucesso!',
            'id' => $disciplina_id
        ]);
        
    } else {
        // CRIAR nova disciplina
        
        // Verifica se a sigla já existe
        $query = "SELECT id FROM disciplinas WHERE sigla = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $sigla);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Esta sigla já está cadastrada');
        }
        
        // Insere disciplina
        $query = "INSERT INTO disciplinas (nome, sigla, carga_horaria) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssi", $nome, $sigla, $carga_horaria);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar disciplina');
        }
        
        $disciplina_id = $mysqli->insert_id;
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Disciplina cadastrada com sucesso!',
            'id' => $disciplina_id
        ]);
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