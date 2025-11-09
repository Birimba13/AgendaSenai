<?php
include "conexao.php";

// Script de criação completo do banco AgendaSenai
// Versão: 2.0 - Reestruturado em 09/11/2025

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
echo "<pre>";

foreach ($sqls as $index => $sql) {
    $numero = $index + 1;
    if (mysqli_query($mysqli, $sql)) {
        echo "[$numero] ✓ Tabela criada/atualizada com sucesso\n";
    } else {
        echo "[$numero] ✗ Erro: " . mysqli_error($mysqli) . "\n";
    }
}

echo "\n=== PROCESSO CONCLUÍDO ===\n";
echo "</pre>";
?>
