<?php
session_start(); // Iniciar sessão ANTES de usar $_SESSION

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

protect(); // Verificar se o usuário está autenticado

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
        // Atualizar - verificar se houve mudança de professor
        $query_old = "SELECT professor_id FROM agendamentos WHERE id = ?";
        $stmt_old = $mysqli->prepare($query_old);
        $stmt_old->bind_param('i', $id);
        $stmt_old->execute();
        $old_data = $stmt_old->get_result()->fetch_assoc();
        $old_professor_id = $old_data['professor_id'];

        // Atualizar agendamento
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

        // Se mudou de professor, atualizar carga horária
        if ($old_professor_id != $professor_id) {
            // Remover 1 hora do professor antigo
            $update_old = "UPDATE professores SET carga_horaria_usada = carga_horaria_usada - 1 WHERE id = ?";
            $stmt_update_old = $mysqli->prepare($update_old);
            $stmt_update_old->bind_param('i', $old_professor_id);
            $stmt_update_old->execute();

            // Adicionar 1 hora ao novo professor
            $update_new = "UPDATE professores SET carga_horaria_usada = carga_horaria_usada + 1 WHERE id = ?";
            $stmt_update_new = $mysqli->prepare($update_new);
            $stmt_update_new->bind_param('i', $professor_id);
            $stmt_update_new->execute();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Agendamento atualizado com sucesso',
            'id' => $id
        ], JSON_UNESCAPED_UNICODE);

    } else {
        // Inserir - verificar carga horária disponível do professor
        $query_prof = "SELECT p.carga_horaria_semanal, p.carga_horaria_usada, u.nome
                       FROM professores p
                       INNER JOIN usuarios u ON p.usuario_id = u.id
                       WHERE p.id = ?";
        $stmt_prof = $mysqli->prepare($query_prof);
        $stmt_prof->bind_param('i', $professor_id);
        $stmt_prof->execute();
        $prof_data = $stmt_prof->get_result()->fetch_assoc();

        $carga_maxima = $prof_data['carga_horaria_semanal'];
        $carga_usada = $prof_data['carga_horaria_usada'];
        $nome_professor = $prof_data['nome'];
        $carga_apos_aula = $carga_usada + 1;

        // Inserir agendamento
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

        // Atualizar carga horária do professor (+1 hora)
        $update_carga = "UPDATE professores SET carga_horaria_usada = carga_horaria_usada + 1 WHERE id = ?";
        $stmt_update = $mysqli->prepare($update_carga);
        $stmt_update->bind_param('i', $professor_id);
        $stmt_update->execute();

        // Mensagem de sucesso com informações sobre carga horária
        $mensagem = "Aula criada com sucesso! 1 hora adicionada à carga horária de {$nome_professor}.";

        // Verificar se está próximo ou excedeu o limite
        $alerta = '';
        if ($carga_apos_aula >= $carga_maxima) {
            $alerta = "⚠️ ATENÇÃO: Professor atingiu a carga horária máxima ({$carga_maxima}h). Carga atual: {$carga_apos_aula}h.";
        } elseif ($carga_apos_aula >= ($carga_maxima * 0.9)) {
            $horas_restantes = $carga_maxima - $carga_apos_aula;
            $alerta = "⚠️ Professor próximo do limite. Restam {$horas_restantes}h de {$carga_maxima}h semanais.";
        }

        echo json_encode([
            'success' => true,
            'message' => $mensagem,
            'alerta' => $alerta,
            'id' => $novo_id,
            'carga_horaria' => [
                'usada' => $carga_apos_aula,
                'maxima' => $carga_maxima,
                'percentual' => round(($carga_apos_aula / $carga_maxima) * 100, 1)
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
