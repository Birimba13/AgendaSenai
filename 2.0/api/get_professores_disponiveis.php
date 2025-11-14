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

    // Buscar todos os professores
    $query_professores = "SELECT p.id, u.nome
                         FROM professores p
                         INNER JOIN usuarios u ON p.usuario_id = u.id
                         WHERE p.ativo = TRUE";

    $professores_result = $mysqli->query($query_professores);

    // Buscar professores ocupados no horário especificado
    $query_ocupados = "SELECT DISTINCT professor_id
                       FROM agendamentos
                       WHERE data = ?
                       AND ((hora_inicio < ? AND hora_fim > ?) OR
                            (hora_inicio < ? AND hora_fim > ?) OR
                            (hora_inicio >= ? AND hora_fim <= ?))
                       AND status != 'cancelado'";

    if ($agendamento_id) {
        $query_ocupados .= " AND id != ?";
    }

    $stmt = $mysqli->prepare($query_ocupados);

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
    $ocupados_result = $stmt->get_result();

    // Criar array de IDs ocupados
    $ocupados = [];
    while ($row = $ocupados_result->fetch_assoc()) {
        $ocupados[] = (int)$row['professor_id'];
    }

    // Filtrar professores disponíveis
    $professores_disponiveis = [];
    while ($professor = $professores_result->fetch_assoc()) {
        $professor_id = (int)$professor['id'];

        if (!in_array($professor_id, $ocupados)) {
            $professores_disponiveis[] = [
                'id' => $professor_id,
                'nome' => $professor['nome']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $professores_disponiveis
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
