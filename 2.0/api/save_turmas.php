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
    
    if (empty($dados['professor_id'])) {
        throw new Exception('Professor é obrigatório');
    }
    
    if (empty($dados['data_inicio'])) {
        throw new Exception('Data de início é obrigatória');
    }
    
    if (empty($dados['data_fim'])) {
        throw new Exception('Data de fim é obrigatória');
    }
    
    $nome = trim($dados['nome']);
    $professor_id = (int)$dados['professor_id'];
    $data_inicio = $dados['data_inicio'];
    $data_fim = $dados['data_fim'];
    $disciplinas = isset($dados['disciplinas']) ? $dados['disciplinas'] : [];
    $turma_id = isset($dados['id']) ? (int)$dados['id'] : null;
    
    // Valida se data fim é maior que data início
    if (strtotime($data_fim) <= strtotime($data_inicio)) {
        throw new Exception('Data de fim deve ser maior que data de início');
    }
    
    // Verifica se o professor existe
    $query = "SELECT id FROM professores WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $professor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Professor não encontrado');
    }
    
    $mysqli->begin_transaction();
    
    if ($turma_id) {
        // ATUALIZAR turma existente
        
        // Verifica se o nome já existe em outra turma
        $query = "SELECT id FROM cursos WHERE nome = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $nome, $turma_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este nome já está cadastrado');
        }
        
        // Atualiza turma
        $query = "UPDATE cursos SET 
                          nome = ?, 
                          professor_id = ?,
                          data_inicio = ?, 
                          data_fim = ? 
                        WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sissi", $nome, $professor_id, $data_inicio, $data_fim, $turma_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar turma');
        }
        
        // Remove disciplinas antigas
        $query = "DELETE FROM curso_disciplinas WHERE curso_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $turma_id);
        $stmt->execute();
        
        // Adiciona novas disciplinas
        if (!empty($disciplinas)) {
            $query = "INSERT INTO curso_disciplinas (curso_id, disciplina_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            
            foreach ($disciplinas as $disciplina_id) {
                $disciplina_id_int = (int)$disciplina_id; 
                $stmt->bind_param("ii", $turma_id, $disciplina_id_int);
                $stmt->execute();
            }
        }
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Turma atualizada com sucesso!',
            'id' => $turma_id
        ]);
        
    } else {
        // CRIAR nova turma
        
        // Verifica se o nome já existe
        $query = "SELECT id FROM cursos WHERE nome = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este nome já está cadastrado');
        }
        
        // Insere turma
        $query = "INSERT INTO cursos (nome, professor_id, data_inicio, data_fim) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("siss", $nome, $professor_id, $data_inicio, $data_fim);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar turma');
        }
        
        $turma_id = $mysqli->insert_id;
        
        // Adiciona disciplinas
        if (!empty($disciplinas)) {
            $query = "INSERT INTO curso_disciplinas (curso_id, disciplina_id) VALUES (?, ?)";
            $stmt = $mysqli->prepare($query);
            
            foreach ($disciplinas as $disciplina_id) {
                $disciplina_id_int = (int)$disciplina_id;
                $stmt->bind_param("ii", $turma_id, $disciplina_id_int);
                $stmt->execute();
            }
        }
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Turma cadastrada com sucesso!',
            'id' => $turma_id
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