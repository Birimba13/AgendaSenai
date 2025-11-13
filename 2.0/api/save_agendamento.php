<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = isset($data['id']) ? intval($data['id']) : null;
    $professor_id = intval($data['professor_id']);
    $turma_id = intval($data['turma_id']);
    $disciplina_id = intval($data['disciplina_id']);
    $sala = $data['sala'];
    $data_agendamento = $data['data'];
    $dia_semana = $data['dia_semana'];
    $hora_inicio = $data['hora_inicio'];
    $hora_fim = $data['hora_fim'];
    $tipo = isset($data['tipo']) ? $data['tipo'] : 'aula';
    $modalidade = isset($data['modalidade']) ? $data['modalidade'] : 'presencial';
    $status = isset($data['status']) ? $data['status'] : 'confirmado';
    $observacoes = isset($data['observacoes']) ? $data['observacoes'] : null;
    $compartilhado_com = isset($data['compartilhado_com']) ? intval($data['compartilhado_com']) : null;

    // Validações
    if (empty($professor_id) || empty($turma_id) || empty($disciplina_id)) {
        throw new Exception('Professor, turma e disciplina são obrigatórios');
    }

    if (empty($data_agendamento) || empty($hora_inicio) || empty($hora_fim)) {
        throw new Exception('Data, hora de início e hora de fim são obrigatórias');
    }

    // Validar duração máxima de 1 hora
    $inicio = strtotime($hora_inicio);
    $fim = strtotime($hora_fim);
    $duracao_minutos = ($fim - $inicio) / 60;

    if ($duracao_minutos > 60) {
        throw new Exception('A duração da aula não pode ser maior que 1 hora (60 minutos)');
    }

    if ($duracao_minutos <= 0) {
        throw new Exception('A hora de fim deve ser posterior à hora de início');
    }

    // Verificar conflitos de horário
    $conflito_query = "SELECT id FROM agendamentos
                      WHERE data = ?
                      AND ((hora_inicio < ? AND hora_fim > ?) OR
                           (hora_inicio < ? AND hora_fim > ?) OR
                           (hora_inicio >= ? AND hora_fim <= ?))
                      AND status != 'cancelado'
                      AND (professor_id = ? OR turma_id = ? OR sala = ?)";

    if ($id) {
        $conflito_query .= " AND id != ?";
    }

    $stmt = $mysqli->prepare($conflito_query);

    if ($id) {
        $stmt->bind_param('sssssssiisi',
            $data_agendamento,
            $hora_fim, $hora_inicio,
            $hora_fim, $hora_inicio,
            $hora_inicio, $hora_fim,
            $professor_id, $turma_id, $sala,
            $id
        );
    } else {
        $stmt->bind_param('sssssssiis',
            $data_agendamento,
            $hora_fim, $hora_inicio,
            $hora_fim, $hora_inicio,
            $hora_inicio, $hora_fim,
            $professor_id, $turma_id, $sala
        );
    }

    $stmt->execute();
    $conflito = $stmt->get_result()->fetch_assoc();

    if ($conflito) {
        throw new Exception('Conflito de horário detectado. Professor, turma ou sala já estão ocupados neste horário.');
    }

    // Inserir ou atualizar
    if ($id) {
        // Atualizar
        $query = "UPDATE agendamentos SET
                    professor_id = ?,
                    turma_id = ?,
                    disciplina_id = ?,
                    sala = ?,
                    data = ?,
                    dia_semana = ?,
                    hora_inicio = ?,
                    hora_fim = ?,
                    tipo = ?,
                    modalidade = ?,
                    status = ?,
                    observacoes = ?,
                    compartilhado_com = ?
                  WHERE id = ?";

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiisssssssssii',
            $professor_id, $turma_id, $disciplina_id, $sala,
            $data_agendamento, $dia_semana, $hora_inicio, $hora_fim,
            $tipo, $modalidade, $status, $observacoes, $compartilhado_com,
            $id
        );

        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // Inserir
        $query = "INSERT INTO agendamentos
                    (professor_id, turma_id, disciplina_id, sala, data, dia_semana,
                     hora_inicio, hora_fim, tipo, modalidade, status, observacoes,
                     compartilhado_com, criado_por)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $criado_por = $_SESSION['usuario_id'];

        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('iiisssssssssii',
            $professor_id, $turma_id, $disciplina_id, $sala,
            $data_agendamento, $dia_semana, $hora_inicio, $hora_fim,
            $tipo, $modalidade, $status, $observacoes, $compartilhado_com,
            $criado_por
        );

        $stmt->execute();
        $novo_id = $mysqli->insert_id;

        echo json_encode([
            'success' => true,
            'message' => 'Agendamento criado com sucesso',
            'id' => $novo_id
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
