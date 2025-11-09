<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Permite filtrar por ano/mês se fornecido
    $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : null;
    $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : null;

    $query = "SELECT
                id,
                data,
                tipo,
                descricao,
                dia_letivo,
                observacoes
              FROM calendario";

    $conditions = [];
    $params = [];
    $types = '';

    if ($ano) {
        $conditions[] = "YEAR(data) = ?";
        $params[] = $ano;
        $types .= 'i';
    }

    if ($mes) {
        $conditions[] = "MONTH(data) = ?";
        $params[] = $mes;
        $types .= 'i';
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY data";

    $stmt = $mysqli->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $eventos = [];

    while ($row = $result->fetch_assoc()) {
        $eventos[] = [
            'id' => (int)$row['id'],
            'data' => $row['data'],
            'tipo' => $row['tipo'],
            'descricao' => $row['descricao'],
            'dia_letivo' => (bool)$row['dia_letivo'],
            'observacoes' => $row['observacoes']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $eventos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar calendário: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
