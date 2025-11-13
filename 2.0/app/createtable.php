<?php
include "conexao.php";

// Script de criação completo do banco AgendaSenai
// Versão: 2.0 - Reestruturado em 09/11/2025
// Incluindo dados iniciais

$sqls = [
    // =====================================================
    // 1. TABELA DE USUÁRIOS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('Administrador','Professor','Coordenador') DEFAULT 'Professor',
        ativo BOOLEAN NOT NULL DEFAULT TRUE,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 2. TABELA DE ADMINISTRADORES
    // =====================================================
    "CREATE TABLE IF NOT EXISTS administradores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL UNIQUE,
        nivel ENUM('super','normal') DEFAULT 'normal',
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        INDEX idx_usuario (usuario_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 3. TABELA DE PROFESSORES
    // =====================================================
    "CREATE TABLE IF NOT EXISTS professores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL UNIQUE,
        turno_manha BOOLEAN DEFAULT FALSE,
        turno_tarde BOOLEAN DEFAULT FALSE,
        turno_noite BOOLEAN DEFAULT FALSE,
        carga_horaria_semanal INT NOT NULL DEFAULT 40,
        carga_horaria_usada INT NOT NULL DEFAULT 0,
        local_lotacao VARCHAR(100) DEFAULT 'Afonso Pena',
        celular VARCHAR(20),
        ativo BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        INDEX idx_usuario (usuario_id),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 4. TABELA DE CURSOS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS cursos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        codigo VARCHAR(20) UNIQUE,
        nivel ENUM('tecnico','qualificacao','aperfeicoamento','aprendizagem') DEFAULT 'tecnico',
        carga_horaria_total INT COMMENT 'Carga horária total do curso em horas',
        duracao_meses INT COMMENT 'Duração prevista em meses',
        descricao TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_codigo (codigo),
        INDEX idx_ativo (ativo),
        INDEX idx_nivel (nivel)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // DADOS INICIAIS - CURSOS
    "INSERT IGNORE INTO cursos (nome, codigo, nivel, carga_horaria_total, duracao_meses, descricao) VALUES
        ('Técnico em Automação Industrial', 'TEC-AUTO', 'tecnico', 1200, 18, 'Curso técnico em Automação Industrial com foco em sistemas de controle, CLP e robótica'),
        ('Técnico em Mecânica', 'TEC-MEC', 'tecnico', 1200, 18, 'Curso técnico em Mecânica com ênfase em processos de fabricação e manutenção'),
        ('Técnico em Desenvolvimento de Sistemas', 'TEC-DS', 'tecnico', 1200, 18, 'Curso técnico em Desenvolvimento de Sistemas com foco em programação e banco de dados'),
        ('Qualificação em Programação Web', 'QUAL-WEB', 'qualificacao', 400, 6, 'Qualificação profissional em Programação Web - HTML, CSS, JavaScript e PHP')",

    // =====================================================
    // 5. TABELA DE DISCIPLINAS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS disciplinas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        curso_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        sigla VARCHAR(10),
        carga_horaria INT NOT NULL,
        descricao TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
        INDEX idx_curso (curso_id),
        INDEX idx_sigla (sigla),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // DADOS INICIAIS - DISCIPLINAS
    "INSERT IGNORE INTO disciplinas (curso_id, nome, sigla, carga_horaria, descricao) VALUES
        -- Curso 1: Técnico em Automação
        (1, 'Programação Orientada a Objetos', 'POO', 80, 'Fundamentos de POO com Java e Python'),
        (1, 'Sistemas de Controle', 'SC', 60, 'Teoria de controle e sistemas automáticos'),
        (1, 'Redes Industriais', 'RI', 40, 'Protocolos e redes para automação industrial'),
        (1, 'CLP - Controlador Lógico Programável', 'CLP', 80, 'Programação de CLPs Siemens e Allen-Bradley'),
        (1, 'Robótica Industrial', 'ROB', 60, 'Fundamentos de robótica e programação de robôs'),
        -- Curso 2: Técnico em Mecânica
        (2, 'Desenho Técnico Mecânico', 'DTM', 60, 'Leitura e interpretação de desenhos técnicos'),
        (2, 'Processos de Fabricação', 'PF', 80, 'Processos de usinagem, soldagem e conformação'),
        (2, 'Manutenção Mecânica', 'MM', 60, 'Técnicas de manutenção preventiva e corretiva'),
        (2, 'Metrologia', 'MET', 40, 'Instrumentos de medição e controle dimensional'),
        -- Curso 3: Técnico em Desenvolvimento de Sistemas
        (3, 'Banco de Dados', 'BD', 80, 'Modelagem e SQL com MySQL e PostgreSQL'),
        (3, 'Desenvolvimento Web', 'DW', 80, 'HTML, CSS, JavaScript e frameworks modernos'),
        (3, 'Programação Mobile', 'PM', 60, 'Desenvolvimento de aplicativos Android e iOS'),
        (3, 'Engenharia de Software', 'ES', 60, 'Metodologias ágeis e boas práticas'),
        (3, 'Programação Orientada a Objetos', 'POO', 80, 'POO com Java e padrões de projeto'),
        -- Curso 4: Qualificação Web
        (4, 'HTML e CSS', 'HTML', 80, 'Fundamentos de HTML5 e CSS3'),
        (4, 'JavaScript', 'JS', 80, 'JavaScript ES6+ e DOM'),
        (4, 'PHP e MySQL', 'PHP', 80, 'Backend com PHP e integração com MySQL'),
        (4, 'Desenvolvimento Front-end', 'FRONT', 80, 'React ou Vue.js para interfaces modernas')",

    // =====================================================
    // 6. TABELA DE TURMAS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS turmas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        curso_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL,
        periodo VARCHAR(20) COMMENT 'Ex: 2025/1, 2025/2',
        turno ENUM('Manha','Tarde','Noite','Integral') NOT NULL,
        data_inicio DATE,
        data_fim DATE,
        status ENUM('planejamento','ativo','concluido','cancelado') DEFAULT 'ativo',
        observacoes TEXT,
        ativo BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
        INDEX idx_curso (curso_id),
        INDEX idx_turno (turno),
        INDEX idx_status (status),
        INDEX idx_periodo (periodo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // DADOS INICIAIS - TURMAS
    "INSERT IGNORE INTO turmas (curso_id, nome, periodo, turno, data_inicio, data_fim, status) VALUES
        (1, '3º Técnico Automação - Manhã', '2025/1', 'Manha', '2025-02-01', '2025-07-31', 'ativo'),
        (1, '4º Técnico Automação - Tarde', '2025/1', 'Tarde', '2025-02-01', '2025-07-31', 'ativo'),
        (2, '3º Técnico Mecânica - Noite', '2025/1', 'Noite', '2025-02-01', '2025-07-31', 'ativo'),
        (3, '2º Técnico DS - Manhã', '2025/1', 'Manha', '2025-02-01', '2025-07-31', 'ativo'),
        (3, '3º Técnico DS - Tarde', '2025/1', 'Tarde', '2025-02-01', '2025-07-31', 'ativo'),
        (4, 'Qualificação Web - Noite', '2025/1', 'Noite', '2025-02-01', '2025-05-31', 'ativo')",

    // =====================================================
    // 7. TABELA DE ALUNOS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS alunos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        curso_id INT,
        turma_id INT,
        nome VARCHAR(100) NOT NULL,
        matricula VARCHAR(20) UNIQUE,
        email VARCHAR(150) UNIQUE,
        cpf VARCHAR(14) UNIQUE,
        telefone VARCHAR(20),
        data_nascimento DATE,
        data_matricula DATE NOT NULL,
        status ENUM('ativo','inativo','concluido','trancado') DEFAULT 'ativo',
        observacoes TEXT,
        FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL,
        FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE SET NULL,
        INDEX idx_curso (curso_id),
        INDEX idx_turma (turma_id),
        INDEX idx_status (status),
        INDEX idx_matricula (matricula),
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 8. TABELA DE COMPETÊNCIAS (Professor x Disciplina)
    // =====================================================
    "CREATE TABLE IF NOT EXISTS professor_disciplinas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        professor_id INT NOT NULL,
        disciplina_id INT NOT NULL,
        nivel ENUM('N0','N1','N2','N3') NOT NULL DEFAULT 'N1' COMMENT 'N0=Não atende, N1=Básico, N2=Teoria, N3=Domínio completo',
        observacoes TEXT,
        data_certificacao DATE,
        FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
        UNIQUE KEY unique_prof_disc (professor_id, disciplina_id),
        INDEX idx_professor (professor_id),
        INDEX idx_disciplina (disciplina_id),
        INDEX idx_nivel (nivel)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 9. TABELA DE RELACIONAMENTO CURSO-DISCIPLINA
    // =====================================================
    "CREATE TABLE IF NOT EXISTS curso_disciplinas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        curso_id INT NOT NULL,
        disciplina_id INT NOT NULL,
        periodo INT COMMENT 'Em qual período do curso (1, 2, 3...)',
        ordem INT COMMENT 'Ordem de exibição',
        obrigatoria BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
        UNIQUE KEY unique_curso_disciplina (curso_id, disciplina_id),
        INDEX idx_curso (curso_id),
        INDEX idx_disciplina (disciplina_id),
        INDEX idx_periodo (periodo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 10. TABELA DE HORÁRIOS PADRÃO
    // =====================================================
    "CREATE TABLE IF NOT EXISTS horarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data DATE NOT NULL,
        turno ENUM('Manha','Tarde','Noite') NOT NULL,
        bloco_inicio TIME NOT NULL,
        bloco_fim TIME NOT NULL,
        ordem INT COMMENT 'Ordem do bloco de horário',
        carga_horaria DECIMAL(3,1) COMMENT 'Duração em horas',
        ativo BOOLEAN DEFAULT TRUE,
        INDEX idx_data (data),
        INDEX idx_turno (turno),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 11. TABELA DE AGENDAMENTOS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,

        -- Quem, O quê, Onde
        professor_id INT NOT NULL,
        turma_id INT NOT NULL,
        disciplina_id INT NOT NULL,
        sala VARCHAR(50) COMMENT 'Ex: Lab Informática 1, Sala 205',

        -- Quando
        data DATE NOT NULL,
        dia_semana ENUM('Segunda','Terca','Quarta','Quinta','Sexta','Sabado') NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fim TIME NOT NULL,

        -- Controle
        tipo ENUM('aula','reposicao','prova','evento','avaliacao') DEFAULT 'aula',
        modalidade ENUM('presencial','EAD','hibrido') DEFAULT 'presencial',
        status ENUM('confirmado','pendente','cancelado','concluido') DEFAULT 'confirmado',

        -- Extras
        observacoes TEXT,
        compartilhado_com INT COMMENT 'ID de outro professor se a aula for compartilhada',

        -- Auditoria
        criado_por INT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        cancelado_em TIMESTAMP NULL,
        motivo_cancelamento TEXT,

        -- Foreign Keys
        FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE RESTRICT,
        FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
        FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE RESTRICT,
        FOREIGN KEY (compartilhado_com) REFERENCES professores(id) ON DELETE SET NULL,
        FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,

        -- Índices
        INDEX idx_data (data),
        INDEX idx_professor_data (professor_id, data),
        INDEX idx_turma_data (turma_id, data),
        INDEX idx_disciplina (disciplina_id),
        INDEX idx_dia_semana (dia_semana),
        INDEX idx_status (status),
        INDEX idx_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 12. TABELA DE SALAS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS salas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(20) NOT NULL UNIQUE,
        nome VARCHAR(100) NOT NULL,
        capacidade INT,
        tipo ENUM('sala_aula','laboratorio','auditorio','oficina') DEFAULT 'sala_aula',
        recursos JSON COMMENT 'Ex: {\"projetor\": true, \"ar_condicionado\": true}',
        local VARCHAR(100) DEFAULT 'Afonso Pena',
        ativo BOOLEAN DEFAULT TRUE,
        INDEX idx_codigo (codigo),
        INDEX idx_tipo (tipo),
        INDEX idx_local (local),
        INDEX idx_ativo (ativo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // DADOS INICIAIS - SALAS
    "INSERT IGNORE INTO salas (codigo, nome, capacidade, tipo, local) VALUES
        ('LAB-INF-01', 'Laboratório de Informática 1', 30, 'laboratorio', 'Afonso Pena'),
        ('LAB-INF-02', 'Laboratório de Informática 2', 30, 'laboratorio', 'Afonso Pena'),
        ('LAB-INF-03', 'Laboratório de Informática 3', 25, 'laboratorio', 'Afonso Pena'),
        ('SALA-201', 'Sala 201', 40, 'sala_aula', 'Afonso Pena'),
        ('SALA-202', 'Sala 202', 40, 'sala_aula', 'Afonso Pena'),
        ('SALA-203', 'Sala 203', 35, 'sala_aula', 'Afonso Pena'),
        ('LAB-MEC-01', 'Oficina Mecânica', 25, 'oficina', 'Afonso Pena'),
        ('LAB-AUTO-01', 'Laboratório de Automação', 20, 'laboratorio', 'Afonso Pena'),
        ('AUD-01', 'Auditório Principal', 100, 'auditorio', 'Afonso Pena'),
        ('SALA-REUNIAO', 'Sala de Reuniões', 15, 'sala_aula', 'Afonso Pena')",

    // =====================================================
    // 13. TABELA DE CALENDÁRIO (Feriados/Eventos)
    // =====================================================
    "CREATE TABLE IF NOT EXISTS calendario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data DATE NOT NULL UNIQUE,
        tipo ENUM('feriado','recesso','semana_pedagogica','evento','suspensao') NOT NULL,
        descricao VARCHAR(150) NOT NULL,
        dia_letivo BOOLEAN DEFAULT FALSE,
        observacoes TEXT,
        INDEX idx_data (data),
        INDEX idx_tipo (tipo),
        INDEX idx_dia_letivo (dia_letivo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // DADOS INICIAIS - CALENDÁRIO (Feriados 2025)
    "INSERT IGNORE INTO calendario (data, tipo, descricao, dia_letivo) VALUES
        ('2025-01-01', 'feriado', 'Ano Novo', FALSE),
        ('2025-02-24', 'recesso', 'Início do Carnaval', FALSE),
        ('2025-02-25', 'recesso', 'Carnaval', FALSE),
        ('2025-02-26', 'recesso', 'Quarta-feira de Cinzas', FALSE),
        ('2025-04-18', 'feriado', 'Sexta-feira Santa', FALSE),
        ('2025-04-21', 'feriado', 'Tiradentes', FALSE),
        ('2025-05-01', 'feriado', 'Dia do Trabalho', FALSE),
        ('2025-06-19', 'feriado', 'Corpus Christi', FALSE),
        ('2025-09-07', 'feriado', 'Independência do Brasil', FALSE),
        ('2025-10-12', 'feriado', 'Nossa Senhora Aparecida', FALSE),
        ('2025-11-02', 'feriado', 'Finados', FALSE),
        ('2025-11-15', 'feriado', 'Proclamação da República', FALSE),
        ('2025-11-20', 'feriado', 'Consciência Negra', FALSE),
        ('2025-12-25', 'feriado', 'Natal', FALSE),
        ('2025-12-31', 'recesso', 'Véspera de Ano Novo', FALSE)",

    // =====================================================
    // 14. TABELA DE DISPONIBILIDADE DE PROFESSORES
    // =====================================================
    "CREATE TABLE IF NOT EXISTS disponibilidade_professor (
        id INT AUTO_INCREMENT PRIMARY KEY,
        professor_id INT NOT NULL,
        dia_semana ENUM('Segunda','Terca','Quarta','Quinta','Sexta','Sabado') NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fim TIME NOT NULL,
        tipo ENUM('disponivel','preferencia','indisponivel') DEFAULT 'disponivel',
        observacoes VARCHAR(255),
        ativo BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
        INDEX idx_professor (professor_id),
        INDEX idx_dia (dia_semana),
        INDEX idx_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // =====================================================
    // 15. TABELA DE HISTÓRICO DE AGENDAMENTOS
    // =====================================================
    "CREATE TABLE IF NOT EXISTS historico_agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        agendamento_id INT NOT NULL,
        usuario_id INT NOT NULL,
        acao ENUM('criar','editar','cancelar','restaurar','confirmar') NOT NULL,
        dados_anteriores JSON,
        dados_novos JSON,
        motivo TEXT,
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        INDEX idx_agendamento (agendamento_id),
        INDEX idx_usuario (usuario_id),
        INDEX idx_data (data_hora),
        INDEX idx_acao (acao)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Executa cada query
echo "<h2>Criando/Atualizando Tabelas do Banco AgendaSenai</h2>";
echo "<h3>Incluindo dados iniciais de Cursos, Disciplinas, Turmas, Salas e Calendário</h3>";
echo "<pre>";

$sucesso = 0;
$erros = 0;

foreach ($sqls as $index => $sql) {
    $numero = $index + 1;

    // Identifica se é CREATE ou INSERT
    $tipo = (stripos($sql, 'INSERT') !== false) ? 'INSERT' : 'CREATE';

    if (mysqli_query($mysqli, $sql)) {
        echo "[$numero] ✓ $tipo executado com sucesso\n";
        $sucesso++;
    } else {
        echo "[$numero] ✗ Erro no $tipo: " . mysqli_error($mysqli) . "\n";
        $erros++;
    }
}

echo "\n=== RESUMO ===\n";
echo "Total de operações: " . count($sqls) . "\n";
echo "Sucesso: $sucesso\n";
echo "Erros: $erros\n";
echo "\n=== PROCESSO CONCLUÍDO ===\n";
echo "</pre>";
?>
