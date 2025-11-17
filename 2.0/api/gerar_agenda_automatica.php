<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../app/conexao.php';
require_once '../app/protect.php';

protect();

try {
    // Receber configurações
    $config = json_decode(file_get_contents('php://input'), true);

    // Validações
    if (empty($config['dias']) || !is_array($config['dias'])) {
        throw new Exception('Selecione pelo menos um dia da semana');
    }

    // Iniciar transação
    $mysqli->begin_transaction();

    // PASSO 1: Limpar agenda se modo = substituir
    if ($config['modo'] === 'substituir') {
        $query_delete = "DELETE FROM agendamentos WHERE status != 'confirmado'";
        $mysqli->query($query_delete);

        // Resetar carga horária de todos os cursos e professores
        $mysqli->query("UPDATE cursos SET carga_horaria_preenchida = 0");
        $mysqli->query("UPDATE professores SET carga_horaria_usada = 0");
    }

    // PASSO 2: Buscar cursos que precisam de aulas
    $cursos_query = "SELECT id, nome, carga_horaria_semanal, carga_horaria_preenchida
                     FROM cursos
                     WHERE ativo = 1
                     AND carga_horaria_semanal > 0
                     AND carga_horaria_preenchida < carga_horaria_semanal";

    if (isset($config['cursos']) && $config['cursos'] !== 'todos' && !empty($config['cursos'])) {
        $ids = array_map('intval', $config['cursos']);
        $ids_str = implode(',', $ids);
        $cursos_query .= " AND id IN ($ids_str)";
    }

    $cursos_result = $mysqli->query($cursos_query);
    $cursos_pendentes = [];

    while ($curso = $cursos_result->fetch_assoc()) {
        $cursos_pendentes[] = $curso;
    }

    if (empty($cursos_pendentes)) {
        throw new Exception('Nenhum curso precisa de aulas no momento');
    }

    // PASSO 3: Buscar recursos disponíveis
    $professores = $mysqli->query("
        SELECT p.id, p.usuario_id, u.nome, p.carga_horaria_semanal, p.carga_horaria_usada,
               p.turno_manha, p.turno_tarde, p.turno_noite
        FROM professores p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.ativo = 1 AND p.carga_horaria_usada < p.carga_horaria_semanal
    ")->fetch_all(MYSQLI_ASSOC);

    $salas = $mysqli->query("SELECT codigo, nome FROM salas WHERE ativo = 1")->fetch_all(MYSQLI_ASSOC);

    if (empty($professores)) {
        throw new Exception('Nenhum professor disponível com carga horária livre');
    }

    if (empty($salas)) {
        throw new Exception('Nenhuma sala cadastrada');
    }

    // PASSO 4: Gerar grade horária
    $grade_horaria = gerarGradeHoraria($config['horario_inicio'], $config['horario_fim'], $config['dias']);

    if (empty($grade_horaria)) {
        throw new Exception('Nenhum horário disponível na configuração fornecida');
    }

    // PASSO 5: Algoritmo de preenchimento
    $aulas_criadas = 0;
    $tentativas_maximas = 2000;
    $tentativas_global = 0;
    $resumo_por_curso = [];
    $usuario_id = $_SESSION['usuario_id'];

    // Criar índice de professores por ID para acesso rápido
    $professores_idx = [];
    foreach ($professores as &$prof) {
        $professores_idx[$prof['id']] = &$prof;
    }
    unset($prof);

    foreach ($cursos_pendentes as $curso) {
        $resumo_por_curso[$curso['id']] = [
            'curso_nome' => $curso['nome'],
            'total' => $curso['carga_horaria_semanal'],
            'preenchida' => $curso['carga_horaria_preenchida'],
            'criadas' => 0
        ];

        // Buscar disciplinas e turmas do curso
        $disciplinas = $mysqli->query("
            SELECT id, nome FROM disciplinas
            WHERE curso_id = {$curso['id']} AND ativo = 1
        ")->fetch_all(MYSQLI_ASSOC);

        $turmas = $mysqli->query("
            SELECT id, nome FROM turmas
            WHERE curso_id = {$curso['id']} AND status = 'ativo'
        ")->fetch_all(MYSQLI_ASSOC);

        if (empty($disciplinas) || empty($turmas)) {
            continue;
        }

        // Calcular quantas aulas faltam para completar
        $aulas_necessarias = $curso['carga_horaria_semanal'] - $curso['carga_horaria_preenchida'];
        $tentativas_curso = 0;
        $max_tentativas_curso = $tentativas_maximas;

        // Tentar criar as aulas que faltam
        while ($aulas_necessarias > 0 && $tentativas_curso < $max_tentativas_curso && $tentativas_global < $tentativas_maximas) {
            $tentativas_curso++;
            $tentativas_global++;

            // Sortear aleatoriamente
            $professor = $professores[array_rand($professores)];
            $sala = $salas[array_rand($salas)];
            $disciplina = $disciplinas[array_rand($disciplinas)];
            $turma = $turmas[array_rand($turmas)];
            $horario = $grade_horaria[array_rand($grade_horaria)];

            // Verificar se professor ainda tem carga disponível
            if ($professor['carga_horaria_usada'] >= $professor['carga_horaria_semanal']) {
                continue;
            }

            // Verificar conflitos
            $conflito = verificarConflito(
                $mysqli,
                $professor['id'],
                $turma['id'],
                $sala['codigo'],
                $horario['data'],
                $horario['hora_inicio'],
                $horario['hora_fim']
            );

            if ($conflito) {
                continue; // Tenta outro horário
            }

            // CRIAR AULA
            $query_insert = "INSERT INTO agendamentos (
                professor_id, turma_id, disciplina_id, sala,
                data, dia_semana, hora_inicio, hora_fim,
                tipo, modalidade, status, criado_por, criado_em
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aula', 'presencial', 'pendente', ?, NOW())";

            $stmt = $mysqli->prepare($query_insert);
            $stmt->bind_param(
                "iiisssssi",
                $professor['id'],
                $turma['id'],
                $disciplina['id'],
                $sala['codigo'],
                $horario['data'],
                $horario['dia_semana'],
                $horario['hora_inicio'],
                $horario['hora_fim'],
                $usuario_id
            );

            if ($stmt->execute()) {
                // Atualizar contadores
                $mysqli->query("UPDATE cursos SET carga_horaria_preenchida = carga_horaria_preenchida + 1 WHERE id = {$curso['id']}");
                $mysqli->query("UPDATE professores SET carga_horaria_usada = carga_horaria_usada + 1 WHERE id = {$professor['id']}");

                // Atualizar variáveis locais
                $professores_idx[$professor['id']]['carga_horaria_usada']++;
                $aulas_necessarias--;
                $resumo_por_curso[$curso['id']]['criadas']++;
                $aulas_criadas++;
                $tentativas_curso = 0; // Reset tentativas ao ter sucesso
            }
        }

        $resumo_por_curso[$curso['id']]['preenchida'] = $curso['carga_horaria_preenchida'] + $resumo_por_curso[$curso['id']]['criadas'];
    }

    // PASSO 6: Preparar resumo
    $cursos_completos = 0;
    $avisos = [];

    foreach ($resumo_por_curso as $id => $info) {
        if ($info['preenchida'] >= $info['total']) {
            $cursos_completos++;
        } else {
            $faltam = $info['total'] - $info['preenchida'];
            $avisos[] = "{$info['curso_nome']}: faltam {$faltam} horas (motivo: falta de horários/professores/salas disponíveis)";
        }
    }

    // Contar professores utilizados
    $professores_utilizados_count = 0;
    foreach ($professores_idx as $prof) {
        if ($prof['carga_horaria_usada'] > 0) {
            $professores_utilizados_count++;
        }
    }

    // Commit
    $mysqli->commit();

    echo json_encode([
        'success' => true,
        'resumo' => [
            'total_aulas_criadas' => $aulas_criadas,
            'total_cursos' => count($cursos_pendentes),
            'cursos_completos' => $cursos_completos,
            'professores_utilizados' => $professores_utilizados_count,
            'detalhamento' => array_values($resumo_por_curso),
            'avisos' => $avisos
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// ========== FUNÇÕES AUXILIARES ==========

function gerarGradeHoraria($hora_inicio, $hora_fim, $dias_semana) {
    $grade = [];
    $data_base = new DateTime('next Monday'); // Próxima segunda-feira

    $mapeamento_dias = [
        'Segunda' => 0,
        'Terca' => 1,
        'Quarta' => 2,
        'Quinta' => 3,
        'Sexta' => 4,
        'Sabado' => 5
    ];

    foreach ($dias_semana as $dia) {
        if (!isset($mapeamento_dias[$dia])) {
            continue;
        }

        $offset = $mapeamento_dias[$dia];
        $data = clone $data_base;
        $data->modify("+{$offset} days");

        $hora_atual = new DateTime($data->format('Y-m-d') . ' ' . $hora_inicio);
        $hora_final = new DateTime($data->format('Y-m-d') . ' ' . $hora_fim);

        while ($hora_atual < $hora_final) {
            $hora_fim_aula = clone $hora_atual;
            $hora_fim_aula->modify('+1 hour');

            // Não adicionar se ultrapassar o horário final
            if ($hora_fim_aula > $hora_final) {
                break;
            }

            $grade[] = [
                'data' => $data->format('Y-m-d'),
                'dia_semana' => $dia,
                'hora_inicio' => $hora_atual->format('H:i'),
                'hora_fim' => $hora_fim_aula->format('H:i')
            ];

            $hora_atual->modify('+1 hour');
        }
    }

    return $grade;
}

function verificarConflito($mysqli, $professor_id, $turma_id, $sala, $data, $hora_inicio, $hora_fim) {
    $query = "SELECT id FROM agendamentos
              WHERE data = ?
              AND ((hora_inicio < ? AND hora_fim > ?) OR
                   (hora_inicio < ? AND hora_fim > ?) OR
                   (hora_inicio >= ? AND hora_fim <= ?))
              AND status != 'cancelado'
              AND (professor_id = ? OR turma_id = ? OR sala = ?)";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param(
        "sssssssiis",
        $data, $hora_fim, $hora_inicio,
        $hora_fim, $hora_inicio,
        $hora_inicio, $hora_fim,
        $professor_id, $turma_id, $sala
    );
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}
?>
