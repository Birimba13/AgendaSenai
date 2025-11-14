<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Parâmetros obrigatórios
    $data = isset($_GET['data']) ? $_GET['data'] : null;
    $hora_inicio = isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;
    $hora_fim = isset($_GET['hora_fim']) ? $_GET['hora_fim'] : null;
    $agendamento_id = isset($_GET['agendamento_id']) ? intval($_GET['agendamento_id']) : null;

    if (!$data || !$hora_inicio || !$hora_fim) {
        throw new Exception('Data, hora de início e hora de fim são obrigatórias');
    }

    // Buscar todas as salas
    $query_salas = "SELECT id, nome, capacidade, tipo
                    FROM salas
                    WHERE ativo = TRUE
                    ORDER BY nome";

    $salas_result = $mysqli->query($query_salas);

    // Buscar salas ocupadas no horário especificado
    $query_ocupadas = "SELECT DISTINCT sala
                       FROM agendamentos
                       WHERE data = ?
                       AND ((hora_inicio < ? AND hora_fim > ?) OR
                            (hora_inicio < ? AND hora_fim > ?) OR
                            (hora_inicio >= ? AND hora_fim <= ?))
                       AND status != 'cancelado'";

    if ($agendamento_id) {
        $query_ocupadas .= " AND id != ?";
    }

    $stmt = $mysqli->prepare($query_ocupadas);

    if ($agendamento_id) {
        $stmt->bind_param('ssssssi',
            $data,
            $hora_fim, $hora_inicio,
            $hora_fim, $hora_inicio,
            $hora_inicio, $hora_fim,
            $agendamento_id
        );
    } else {
        $stmt->bind_param('sssssss',
            $data,
            $hora_fim, $hora_inicio,
            $hora_fim, $hora_inicio,
            $hora_inicio, $hora_fim
        );
    }

    $stmt->execute();
    $ocupadas_result = $stmt->get_result();

    // Criar array de salas ocupadas (nome da sala)
    $ocupadas = [];
    while ($row = $ocupadas_result->fetch_assoc()) {
        $ocupadas[] = $row['sala'];
    }

    // Filtrar salas disponíveis
    $salas_disponiveis = [];
    while ($sala = $salas_result->fetch_assoc()) {
        $sala_nome = $sala['nome'];

        if (!in_array($sala_nome, $ocupadas)) {
            $salas_disponiveis[] = [
                'id' => (int)$sala['id'],
                'nome' => $sala_nome,
                'capacidade' => (int)$sala['capacidade'],
                'tipo' => $sala['tipo']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $salas_disponiveis
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
