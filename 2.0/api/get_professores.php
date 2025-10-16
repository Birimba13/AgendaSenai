<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Query para buscar professores com suas informações
    $query = "SELECT 
                p.id,
                u.nome,
                u.email,
                p.turno_manha,
                p.turno_tarde,
                p.carga_horaria_total,
                p.carga_horaria_usada,
                p.ativo
              FROM professores p
              INNER JOIN usuarios u ON p.usuario_id = u.id
              ORDER BY u.nome";
    
    $result = $mysqli->query($query);
    
    $professores = [];
    
    while ($row = $result->fetch_assoc()) {
        // Monta array de turnos
        $turnos = [];
        if ($row['turno_manha']) {
            $turnos[] = 'Manhã';
        }
        if ($row['turno_tarde']) {
            $turnos[] = 'Tarde';
        }
        
        $professor = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'email' => $row['email'],
            'turnos' => $turnos,
            'turno_manha' => (bool)$row['turno_manha'],
            'turno_tarde' => (bool)$row['turno_tarde'],
            'carga_horaria_total' => (int)$row['carga_horaria_total'],
            'carga_horaria_usada' => (int)$row['carga_horaria_usada'],
            'ativo' => (bool)$row['ativo']
        ];
        
        $professores[] = $professor;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $professores
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar professores: ' . $e->getMessage()
    ]);
}