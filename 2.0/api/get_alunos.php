<?php
// Desabilita exibição de erros para não quebrar o JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Verifica se a conexão foi estabelecida
    if (!isset($mysqli) || $mysqli->connect_error) {
        throw new Exception('Erro na conexão com o banco de dados');
    }
    
    $query = "SELECT
                a.id,
                a.nome,
                a.matricula,
                a.email,
                a.cpf,
                a.telefone,
                a.data_nascimento,
                a.data_matricula,
                a.status,
                a.observacoes,
                c.nome as curso_nome,
                c.id as curso_id,
                t.nome as turma_nome,
                t.id as turma_id
              FROM alunos a
              LEFT JOIN cursos c ON a.curso_id = c.id
              LEFT JOIN turmas t ON a.turma_id = t.id
              ORDER BY a.nome";
    
    $result = $mysqli->query($query);
    
    if (!$result) {
        throw new Exception('Erro ao executar query: ' . $mysqli->error);
    }
    
    $alunos = [];
    
    while ($row = $result->fetch_assoc()) {
        $alunos[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'matricula' => $row['matricula'],
            'email' => $row['email'],
            'cpf' => $row['cpf'],
            'telefone' => $row['telefone'],
            'data_nascimento' => $row['data_nascimento'],
            'data_matricula' => $row['data_matricula'],
            'status' => $row['status'],
            'observacoes' => $row['observacoes'],
            'curso_id' => $row['curso_id'] ? (int)$row['curso_id'] : null,
            'curso_nome' => $row['curso_nome'],
            'turma_id' => $row['turma_id'] ? (int)$row['turma_id'] : null,
            'turma_nome' => $row['turma_nome']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $alunos
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>