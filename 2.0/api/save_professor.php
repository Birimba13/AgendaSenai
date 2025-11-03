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
    // Recebe os dados JSON
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
    
    if (empty($dados['carga_horaria']) || $dados['carga_horaria'] < 0) {
        throw new Exception('Carga horária inválida');
    }
    
    $nome = trim($dados['nome']);
    $email = trim($dados['email']);
    $turno_manha = isset($dados['turno_manha']) ? (bool)$dados['turno_manha'] : false;
    $turno_tarde = isset($dados['turno_tarde']) ? (bool)$dados['turno_tarde'] : false;
    $turno_noite = isset($dados['turno_noite']) ? (bool)$dados['turno_noite'] : false;
    $carga_horaria = (int)$dados['carga_horaria'];
    $ativo = ($dados['status'] === 'ativo') ? 1 : 0;
    $professor_id = isset($dados['id']) ? (int)$dados['id'] : null;
    
    // Verifica se pelo menos um turno foi selecionado
    if (!$turno_manha && !$turno_tarde && !$turno_noite) {
        throw new Exception('Selecione pelo menos um turno');
    }
    
    $mysqli->begin_transaction();
    
    if ($professor_id) {
        // ATUALIZAR professor existente
        
        // Busca o usuario_id do professor
        $query = "SELECT usuario_id FROM professores WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $professor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Professor não encontrado');
        }
        
        $row = $result->fetch_assoc();
        $usuario_id = $row['usuario_id'];
        
        // Verifica se o email já existe em outro usuário
        $query = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $email, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este email já está cadastrado');
        }
        
        // Atualiza usuário
        $query = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ssi", $nome, $email, $usuario_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar usuário');
        }
        
        // Atualiza professor
        $query = "UPDATE professores SET 
            turno_manha = ?, 
            turno_tarde = ?,
            turno_noite = ?, 
            carga_horaria_total = ?, 
            ativo = ? 
          WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iiiiii", $turno_manha, $turno_tarde, $turno_noite, $carga_horaria, $ativo, $professor_id);
        // Na inserção
        $query = "INSERT INTO professores 
          (usuario_id, turno_manha, turno_tarde, turno_noite, carga_horaria_total, carga_horaria_usada, ativo) 
          VALUES (?, ?, ?, ?, ?, 0, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iiiiii", $usuario_id, $turno_manha, $turno_tarde, $turno_noite, $carga_horaria, $ativo);
        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar professor');
        }
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Professor atualizado com sucesso!',
            'id' => $professor_id
        ]);
        
    } else {
        // CRIAR novo professor
        
        // Verifica se o email já existe
        $query = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception('Este email já está cadastrado');
        }
        
        // Cria senha padrão (pode ser alterada depois)
        $senha_padrao = 'senai123';
        $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);
        
        // Insere usuário
        $query = "INSERT INTO usuarios (nome, email, senha, ativo) VALUES (?, ?, ?, 1)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("sss", $nome, $email, $senha_hash);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar usuário');
        }
        
        $usuario_id = $mysqli->insert_id;
        
        // Insere professor
        $query = "INSERT INTO professores 
                  (usuario_id, turno_manha, turno_tarde, carga_horaria_total, carga_horaria_usada, ativo) 
                  VALUES (?, ?, ?, ?, 0, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("iiiii", $usuario_id, $turno_manha, $turno_tarde, $carga_horaria, $ativo);
        
        if (!$stmt->execute()) {
            throw new Exception('Erro ao criar professor');
        }
        
        $professor_id = $mysqli->insert_id;
        
        $mysqli->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Professor cadastrado com sucesso!',
            'id' => $professor_id
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