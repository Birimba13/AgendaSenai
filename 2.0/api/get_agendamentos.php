<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';

try {
    // Parâmetros opcionais para filtrar
    $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
    $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;
    $professor_id = isset($_GET['professor_id']) ? intval($_GET['professor_id']) : null;
    $turma_id = isset($_GET['turma_id']) ? intval($_GET['turma_id']) : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;

    $query = "SELECT
                a.id,
                a.professor_id,
                a.turma_id,
                a.disciplina_id,
                a.sala,
                a.data,
                a.dia_semana,
                a.hora_inicio,
                a.hora_fim,
                a.tipo,
                a.modalidade,
                a.status,
                a.observacoes,
                a.compartilhado_com,
                a.criado_por,
                a.criado_em,
                a.atualizado_em,
                a.cancelado_em,
                a.motivo_cancelamento,
                -- Dados do professor
                u1.nome AS professor_nome,
                -- Dados da turma
                t.nome AS turma_nome,
                t.turno AS turma_turno,
                -- Dados da disciplina
                d.nome AS disciplina_nome,
                d.sigla AS disciplina_sigla,
                -- Dados do curso
                c.nome AS curso_nome,
                -- Professor compartilhado
                u2.nome AS compartilhado_nome
              FROM agendamentos a
              INNER JOIN professores p ON a.professor_id = p.id
              INNER JOIN usuarios u1 ON p.usuario_id = u1.id
              INNER JOIN turmas t ON a.turma_id = t.id
              INNER JOIN disciplinas d ON a.disciplina_id = d.id
              INNER JOIN cursos c ON t.curso_id = c.id
              LEFT JOIN professores p2 ON a.compartilhado_com = p2.id
              LEFT JOIN usuarios u2 ON p2.usuario_id = u2.id
              WHERE 1=1";

    // Aplicar filtros
    $params = [];
    $types = '';

    if ($data_inicio) {
        $query .= " AND a.data >= ?";
        $params[] = $data_inicio;
        $types .= 's';
    }

    if ($data_fim) {
        $query .= " AND a.data <= ?";
        $params[] = $data_fim;
        $types .= 's';
    }

    if ($professor_id) {
        $query .= " AND a.professor_id = ?";
        $params[] = $professor_id;
        $types .= 'i';
    }

    if ($turma_id) {
        $query .= " AND a.turma_id = ?";
        $params[] = $turma_id;
        $types .= 'i';
    }

    if ($status) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $query .= " ORDER BY a.data, a.hora_inicio";

    // Preparar e executar query
    if (!empty($params)) {
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $mysqli->query($query);
    }

    $agendamentos = [];

    while ($row = $result->fetch_assoc()) {
        $agendamentos[] = [
            'id' => (int)$row['id'],
            'professor_id' => (int)$row['professor_id'],
            'professor_nome' => $row['professor_nome'],
            'turma_id' => (int)$row['turma_id'],
            'turma_nome' => $row['turma_nome'],
            'turma_turno' => $row['turma_turno'],
            'disciplina_id' => (int)$row['disciplina_id'],
            'disciplina_nome' => $row['disciplina_nome'],
            'disciplina_sigla' => $row['disciplina_sigla'],
            'curso_nome' => $row['curso_nome'],
            'sala' => $row['sala'],
            'data' => $row['data'],
            'dia_semana' => $row['dia_semana'],
            'hora_inicio' => substr($row['hora_inicio'], 0, 5), // HH:MM
            'hora_fim' => substr($row['hora_fim'], 0, 5),
            'tipo' => $row['tipo'],
            'modalidade' => $row['modalidade'],
            'status' => $row['status'],
            'observacoes' => $row['observacoes'],
            'compartilhado_com' => $row['compartilhado_com'] ? (int)$row['compartilhado_com'] : null,
            'compartilhado_nome' => $row['compartilhado_nome'],
            'criado_por' => $row['criado_por'] ? (int)$row['criado_por'] : null,
            'criado_em' => $row['criado_em'],
            'atualizado_em' => $row['atualizado_em'],
            'cancelado_em' => $row['cancelado_em'],
            'motivo_cancelamento' => $row['motivo_cancelamento']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $agendamentos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar agendamentos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
