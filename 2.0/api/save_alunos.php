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
    
    if (empty($dados['email'])) {
        throw new Exception('Email é obrigatório');
    }
    
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }
    
    if (empty($dados['data_matricula'])) {
        throw new Exception('Data de matrícula é obrigatória');
    }
    
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $cpf = isset($dados['cpf']) ? trim($dados['cpf']) : null;
    $telefone = isset($dados['telefone']) ? trim($dados['telefone']) : null;
    $data_nascimento = isset($dados['data_nascimento']) ? $dados['data_nascimento'] : null;
    $curso_id = isset($dados['curso_id']) && !empty($dados['curso_id']) ? (int)$dados['curso_id'] : null;
    $data_matricula = $dados['data_matricula'];
    $status = isset($dados['status']) ? $dados['status'] : 'ativo';
    $aluno_id = isset($dados['id']) ? (int)$dados['id'] : null;
    
    $mysqli->begin_transaction();
    
    if ($aluno_id) {
        // ATUALIZAR aluno existente
        
        // Verifica se o email já existe em outro aluno
        $query = "SELECT id FROM alunos WHERE email = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $email, $aluno_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este email já está cadastrado');
        }
        
        // Verifica CPF se fornecido
        if ($cpf) {
            $query = "SELECT id FROM alunos WHERE cpf = ? AND id != ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("si", $cpf, $aluno_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('Este CPF já está cadastrado');
            }
        }
        
        // Atualiza aluno
        $query = "UPDATE alunos SET 
                    nome = ?, 
                    email = ?, 
                    cpf = ?,
                    telefone = ?,
                    data_nascimento = ?,
                    curso_id = ?,
                    data_matricula = ?,
                    status = ?
                  WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssssi", $nome, $email, $cpf, $telefone, $data_nascimento, 
                          $curso_id, $data_matricula, $status, $aluno_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar aluno');
        }
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Aluno atualizado com sucesso!',
            'id' => $aluno_id
        ]);
        
    } else {
        // CRIAR novo aluno
        
        // Verifica se o email já existe
        $query = "SELECT id FROM alunos WHERE email = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este email já está cadastrado');
        }
        
        // Verifica CPF se fornecido
        if ($cpf) {
            $query = "SELECT id FROM alunos WHERE cpf = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $cpf);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                throw new Exception('Este CPF já está cadastrado');
            }
        }
        
        // Insere aluno
        $query = "INSERT INTO alunos 
                  (nome, email, cpf, telefone, data_nascimento, curso_id, data_matricula, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssssssss", $nome, $email, $cpf, $telefone, $data_nascimento,
                          $curso_id, $data_matricula, $status);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar aluno');
        }
        
        $aluno_id = $mysqli->insert_id;
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Aluno cadastrado com sucesso!',
            'id' => $aluno_id
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