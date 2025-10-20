<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    $query = "SELECT 
                c.id,
                c.nome,
                c.data_inicio,
                c.data_fim,
                -- 1. CONTAGEM para o card
                COUNT(cd.disciplina_id) AS total_disciplinas,
                -- 2. CRUCIAL: IDs concatenados para filtro e edição no JavaScript
                GROUP_CONCAT(cd.disciplina_id) AS disciplinas_ids
              FROM cursos c
              LEFT JOIN curso_disciplinas cd ON c.id = cd.curso_id
              -- Garante que todas as turmas apareçam, mesmo sem disciplinas
              GROUP BY c.id, c.nome, c.data_inicio, c.data_fim
              ORDER BY c.data_inicio DESC, c.nome";
    
    $result = $mysqli->query($query);
    
    $turmas = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determina o status baseado nas datas
        $hoje = new DateTime();
        $dataInicio = new DateTime($row['data_inicio']);
        $dataFim = new DateTime($row['data_fim']);
        
        $status = 'ativo';
        if ($hoje > $dataFim) {
            $status = 'concluido';
        } elseif ($hoje < $dataInicio) {
            $status = 'aguardando';
        }
        
        $turmas[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'data_inicio' => $row['data_inicio'],
            'data_fim' => $row['data_fim'],
            'total_disciplinas' => (int)$row['total_disciplinas'],
            'disciplinas_ids' => $row['disciplinas_ids'], 
            'status' => $status
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $turmas
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar turmas: ' . $e->getMessage()
    ]);
}