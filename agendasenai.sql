-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/11/2025 às 11:42
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agendasenai`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nivel` enum('super','normal') DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `administradores`
--

INSERT INTO `administradores` (`id`, `usuario_id`, `nivel`) VALUES
(1, 1, 'super'),
(2, 2, 'normal');

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `turma_id` int(11) NOT NULL,
  `disciplina_id` int(11) NOT NULL,
  `sala` varchar(50) DEFAULT NULL COMMENT 'Ex: Lab Informática 1, Sala 205',
  `data` date NOT NULL,
  `dia_semana` enum('Segunda','Terca','Quarta','Quinta','Sexta','Sabado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `tipo` enum('aula','reposicao','prova','evento','avaliacao') DEFAULT 'aula',
  `modalidade` enum('presencial','EAD','hibrido') DEFAULT 'presencial',
  `status` enum('confirmado','pendente','cancelado','concluido') DEFAULT 'confirmado',
  `observacoes` text DEFAULT NULL,
  `compartilhado_com` int(11) DEFAULT NULL COMMENT 'ID de outro professor se a aula for compartilhada',
  `criado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cancelado_em` timestamp NULL DEFAULT NULL,
  `motivo_cancelamento` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `professor_id`, `turma_id`, `disciplina_id`, `sala`, `data`, `dia_semana`, `hora_inicio`, `hora_fim`, `tipo`, `modalidade`, `status`, `observacoes`, `compartilhado_com`, `criado_por`, `criado_em`, `atualizado_em`, `cancelado_em`, `motivo_cancelamento`) VALUES
(7, 3, 6, 15, 'Oficina Mecânica', '2025-11-17', 'Segunda', '07:00:00', '08:00:00', 'aula', 'presencial', 'confirmado', '', NULL, 1, '2025-11-17 10:39:05', '2025-11-17 10:39:05', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) DEFAULT NULL,
  `turma_id` int(11) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `matricula` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `data_matricula` date NOT NULL,
  `status` enum('ativo','inativo','concluido','trancado') DEFAULT 'ativo',
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `calendario`
--

CREATE TABLE `calendario` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `tipo` enum('feriado','recesso','semana_pedagogica','evento','suspensao') NOT NULL,
  `descricao` varchar(150) NOT NULL,
  `dia_letivo` tinyint(1) DEFAULT 0,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `calendario`
--

INSERT INTO `calendario` (`id`, `data`, `tipo`, `descricao`, `dia_letivo`, `observacoes`) VALUES
(1, '2025-01-01', 'feriado', 'Ano Novo', 0, NULL),
(2, '2025-02-24', 'recesso', 'Início do Carnaval', 0, NULL),
(3, '2025-02-25', 'recesso', 'Carnaval', 0, NULL),
(4, '2025-02-26', 'recesso', 'Quarta-feira de Cinzas', 0, NULL),
(5, '2025-04-18', 'feriado', 'Sexta-feira Santa', 0, NULL),
(6, '2025-04-21', 'feriado', 'Tiradentes', 0, NULL),
(7, '2025-05-01', 'feriado', 'Dia do Trabalho', 0, NULL),
(8, '2025-06-19', 'feriado', 'Corpus Christi', 0, NULL),
(9, '2025-09-07', 'feriado', 'Independência do Brasil', 0, NULL),
(10, '2025-10-12', 'feriado', 'Nossa Senhora Aparecida', 0, NULL),
(11, '2025-11-02', 'feriado', 'Finados', 0, NULL),
(12, '2025-11-15', 'feriado', 'Proclamação da República', 0, NULL),
(13, '2025-11-20', 'feriado', 'Consciência Negra', 0, NULL),
(14, '2025-12-25', 'feriado', 'Natal', 0, NULL),
(15, '2025-12-31', 'recesso', 'Véspera de Ano Novo', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `nivel` enum('tecnico','qualificacao','aperfeicoamento','aprendizagem') DEFAULT 'tecnico',
  `carga_horaria_total` int(11) DEFAULT NULL COMMENT 'Carga horária total do curso em horas',
  `duracao_meses` int(11) DEFAULT NULL COMMENT 'Duração prevista em meses',
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `carga_horaria_semanal` int(11) DEFAULT 0 COMMENT 'Horas totais que o curso precisa ter na semana',
  `carga_horaria_preenchida` int(11) DEFAULT 0 COMMENT 'Horas já agendadas (atualizado automaticamente)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`id`, `nome`, `codigo`, `nivel`, `carga_horaria_total`, `duracao_meses`, `descricao`, `ativo`, `data_criacao`, `carga_horaria_semanal`, `carga_horaria_preenchida`) VALUES
(1, 'Técnico em Automação Industrial', 'TEC-AUTO', 'tecnico', 1200, 18, 'Curso técnico em Automação Industrial com foco em sistemas de controle, CLP e robótica', 1, '2025-11-13 02:26:08', 10, 0),
(2, 'Técnico em Mecânica', 'TEC-MEC', 'tecnico', 1200, 18, 'Curso técnico em Mecânica com ênfase em processos de fabricação e manutenção', 1, '2025-11-13 02:26:08', 6, 0),
(3, 'Técnico em Desenvolvimento de Sistemas', 'TEC-DS', 'tecnico', 1200, 18, 'Curso técnico em Desenvolvimento de Sistemas com foco em programação e banco de dados', 1, '2025-11-13 02:26:08', 5, 0),
(4, 'Qualificação em Programação Web', 'QUAL-WEB', 'qualificacao', 400, 6, 'Qualificação profissional em Programação Web - HTML, CSS, JavaScript e PHP', 1, '2025-11-13 02:26:08', 13, 1),
(5, 'CURSO TESTE', 'TES-TE', 'qualificacao', 1000, 12, '0', 1, '2025-11-17 01:28:03', 20, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `curso_disciplinas`
--

CREATE TABLE `curso_disciplinas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `disciplina_id` int(11) NOT NULL,
  `periodo` int(11) DEFAULT NULL COMMENT 'Em qual período do curso (1, 2, 3...)',
  `ordem` int(11) DEFAULT NULL COMMENT 'Ordem de exibição',
  `obrigatoria` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `disciplinas`
--

CREATE TABLE `disciplinas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `sigla` varchar(10) DEFAULT NULL,
  `carga_horaria` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `disciplinas`
--

INSERT INTO `disciplinas` (`id`, `curso_id`, `nome`, `sigla`, `carga_horaria`, `descricao`, `ativo`) VALUES
(1, 1, 'Programação Orientada a Objetos', 'POO', 80, 'Fundamentos de POO com Java e Python', 1),
(2, 1, 'Sistemas de Controle', 'SC', 60, 'Teoria de controle e sistemas automáticos', 1),
(3, 1, 'Redes Industriais', 'RI', 40, 'Protocolos e redes para automação industrial', 1),
(4, 1, 'CLP - Controlador Lógico Programável', 'CLP', 80, 'Programação de CLPs Siemens e Allen-Bradley', 1),
(5, 1, 'Robótica Industrial', 'ROB', 60, 'Fundamentos de robótica e programação de robôs', 1),
(6, 2, 'Desenho Técnico Mecânico', 'DTM', 60, 'Leitura e interpretação de desenhos técnicos', 1),
(7, 2, 'Processos de Fabricação', 'PF', 80, 'Processos de usinagem, soldagem e conformação', 1),
(8, 2, 'Manutenção Mecânica', 'MM', 60, 'Técnicas de manutenção preventiva e corretiva', 1),
(9, 2, 'Metrologia', 'MET', 40, 'Instrumentos de medição e controle dimensional', 1),
(10, 3, 'Banco de Dados', 'BD', 80, 'Modelagem e SQL com MySQL e PostgreSQL', 1),
(11, 3, 'Desenvolvimento Web', 'DW', 80, 'HTML, CSS, JavaScript e frameworks modernos', 1),
(12, 3, 'Programação Mobile', 'PM', 60, 'Desenvolvimento de aplicativos Android e iOS', 1),
(13, 3, 'Engenharia de Software', 'ES', 60, 'Metodologias ágeis e boas práticas', 1),
(14, 3, 'Programação Orientada a Objetos', 'POO', 80, 'POO com Java e padrões de projeto', 1),
(15, 4, 'HTML e CSS', 'HTML', 80, 'Fundamentos de HTML5 e CSS3', 1),
(16, 4, 'JavaScript', 'JS', 80, 'JavaScript ES6+ e DOM', 1),
(17, 4, 'PHP e MySQL', 'PHP', 80, 'Backend com PHP e integração com MySQL', 1),
(18, 4, 'Desenvolvimento Front-end', 'FRONT', 80, 'React ou Vue.js para interfaces modernas', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `disponibilidade_professor`
--

CREATE TABLE `disponibilidade_professor` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `dia_semana` enum('Segunda','Terca','Quarta','Quinta','Sexta','Sabado') NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `tipo` enum('disponivel','preferencia','indisponivel') DEFAULT 'disponivel',
  `observacoes` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_agendamentos`
--

CREATE TABLE `historico_agendamentos` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` enum('criar','editar','cancelar','restaurar','confirmar') NOT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `motivo` text DEFAULT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `turno` enum('Manha','Tarde','Noite') NOT NULL,
  `bloco_inicio` time NOT NULL,
  `bloco_fim` time NOT NULL,
  `ordem` int(11) DEFAULT NULL COMMENT 'Ordem do bloco de horário',
  `carga_horaria` decimal(3,1) DEFAULT NULL COMMENT 'Duração em horas',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `turno_manha` tinyint(1) DEFAULT 0,
  `turno_tarde` tinyint(1) DEFAULT 0,
  `turno_noite` tinyint(1) DEFAULT 0,
  `carga_horaria_semanal` int(11) NOT NULL DEFAULT 40,
  `carga_horaria_usada` int(11) NOT NULL DEFAULT 0,
  `local_lotacao` varchar(100) DEFAULT 'Afonso Pena',
  `celular` varchar(20) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `professores`
--

INSERT INTO `professores` (`id`, `usuario_id`, `turno_manha`, `turno_tarde`, `turno_noite`, `carga_horaria_semanal`, `carga_horaria_usada`, `local_lotacao`, `celular`, `ativo`) VALUES
(1, 3, 1, 1, 0, 40, 0, 'Afonso Pena', '(44) 99123-4567', 1),
(2, 4, 0, 1, 1, 40, 0, 'Afonso Pena', '(44) 99234-5678', 1),
(3, 5, 1, 0, 0, 20, 1, 'Afonso Pena', '(44) 99345-6789', 1),
(4, 6, 0, 0, 1, 40, 0, 'Afonso Pena', '(44) 99456-7890', 1),
(5, 7, 0, 1, 1, 40, 0, 'Afonso Pena', '(44) 99567-8901', 1),
(6, 8, 1, 1, 0, 40, 0, 'Afonso Pena', '(44) 99678-9012', 1),
(7, 9, 0, 1, 0, 40, 0, 'Afonso Pena', '(44) 99789-0123', 1),
(8, 10, 1, 0, 1, 40, 0, 'Afonso Pena', '(44) 99890-1234', 1),
(9, 11, 0, 0, 1, 40, 0, 'Afonso Pena', '(44) 99901-2345', 1),
(10, 12, 0, 1, 1, 40, 0, 'Afonso Pena', '(44) 99012-3456', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `professor_disciplinas`
--

CREATE TABLE `professor_disciplinas` (
  `id` int(11) NOT NULL,
  `professor_id` int(11) NOT NULL,
  `disciplina_id` int(11) NOT NULL,
  `nivel` enum('N0','N1','N2','N3') NOT NULL DEFAULT 'N1' COMMENT 'N0=Não atende, N1=Básico, N2=Teoria, N3=Domínio completo',
  `observacoes` text DEFAULT NULL,
  `data_certificacao` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `salas`
--

CREATE TABLE `salas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `tipo` enum('sala_aula','laboratorio','auditorio','oficina') DEFAULT 'sala_aula',
  `recursos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Ex: {"projetor": true, "ar_condicionado": true}' CHECK (json_valid(`recursos`)),
  `local` varchar(100) DEFAULT 'Afonso Pena',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `salas`
--

INSERT INTO `salas` (`id`, `codigo`, `nome`, `capacidade`, `tipo`, `recursos`, `local`, `ativo`) VALUES
(1, 'LAB-INF-01', 'Laboratório de Informática 1', 30, 'laboratorio', NULL, 'Afonso Pena', 1),
(2, 'LAB-INF-02', 'Laboratório de Informática 2', 30, 'laboratorio', NULL, 'Afonso Pena', 1),
(3, 'LAB-INF-03', 'Laboratório de Informática 3', 25, 'laboratorio', NULL, 'Afonso Pena', 1),
(4, 'SALA-201', 'Sala 201', 40, 'sala_aula', NULL, 'Afonso Pena', 1),
(5, 'SALA-202', 'Sala 202', 40, 'sala_aula', NULL, 'Afonso Pena', 1),
(6, 'SALA-203', 'Sala 203', 35, 'sala_aula', NULL, 'Afonso Pena', 1),
(7, 'LAB-MEC-01', 'Oficina Mecânica', 25, 'oficina', NULL, 'Afonso Pena', 1),
(8, 'LAB-AUTO-01', 'Laboratório de Automação', 20, 'laboratorio', NULL, 'Afonso Pena', 1),
(9, 'AUD-01', 'Auditório Principal', 100, 'auditorio', NULL, 'Afonso Pena', 1),
(10, 'SALA-REUNIAO', 'Sala de Reuniões', 15, 'sala_aula', NULL, 'Afonso Pena', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `periodo` varchar(20) DEFAULT NULL COMMENT 'Ex: 2025/1, 2025/2',
  `turno` enum('Manha','Tarde','Noite','Integral') NOT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `status` enum('planejamento','ativo','concluido','cancelado') DEFAULT 'ativo',
  `observacoes` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`id`, `curso_id`, `nome`, `periodo`, `turno`, `data_inicio`, `data_fim`, `status`, `observacoes`, `ativo`) VALUES
(1, 1, '3º Técnico Automação - Manhã', '2025/1', 'Manha', '2025-02-01', '2025-07-31', 'ativo', NULL, 1),
(2, 1, '4º Técnico Automação - Tarde', '2025/1', 'Tarde', '2025-02-01', '2025-07-31', 'ativo', NULL, 1),
(3, 2, '3º Técnico Mecânica - Noite', '2025/1', 'Noite', '2025-02-01', '2025-07-31', 'ativo', NULL, 1),
(4, 3, '2º Técnico DS - Manhã', '2025/1', 'Manha', '2025-02-01', '2025-07-31', 'ativo', NULL, 1),
(5, 3, '3º Técnico DS - Tarde', '2025/1', 'Tarde', '2025-02-01', '2025-07-31', 'ativo', NULL, 1),
(6, 4, 'Qualificação Web - Noite', '2025/1', 'Noite', '2025-02-01', '2025-05-31', 'ativo', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('Administrador','Professor','Coordenador') DEFAULT 'Professor',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `ativo`, `data_cadastro`) VALUES
(1, 'Carlos Silva', 'carlos.silva@senai.br', '$2y$10$KU0yZzokwBMsHkkDgVSFG..dikyP3xCVUGclL9O9ZZ5Xv0puz7CuW', 'Administrador', 1, '2025-11-13 02:20:36'),
(2, 'Ana Paula Santos', 'ana.santos@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Coordenador', 1, '2025-11-13 02:20:36'),
(3, 'João Pedro Oliveira', 'joao.oliveira@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(4, 'Maria Clara Costa', 'maria.costa@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(5, 'Roberto Fernandes', 'roberto.fernandes@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(6, 'Fernando Alves', 'fernando.alves@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(7, 'Juliana Rodrigues', 'juliana.rodrigues@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(8, 'Paulo Henrique Lima', 'paulo.lima@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(9, 'Beatriz Souza', 'beatriz.souza@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(10, 'Lucas Martins', 'lucas.martins@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(11, 'Camila Pereira', 'camila.pereira@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36'),
(12, 'Rafael Santos', 'rafael.santos@senai.br', 'e4f35b48d297b9101325e20b9aca7488', 'Professor', 1, '2025-11-13 02:20:36');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compartilhado_com` (`compartilhado_com`),
  ADD KEY `criado_por` (`criado_por`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_professor_data` (`professor_id`,`data`),
  ADD KEY `idx_turma_data` (`turma_id`,`data`),
  ADD KEY `idx_disciplina` (`disciplina_id`),
  ADD KEY `idx_dia_semana` (`dia_semana`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `matricula` (`matricula`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_turma` (`turma_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_matricula` (`matricula`),
  ADD KEY `idx_email` (`email`);

--
-- Índices de tabela `calendario`
--
ALTER TABLE `calendario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `data` (`data`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_dia_letivo` (`dia_letivo`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_nivel` (`nivel`);

--
-- Índices de tabela `curso_disciplinas`
--
ALTER TABLE `curso_disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_curso_disciplina` (`curso_id`,`disciplina_id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_disciplina` (`disciplina_id`),
  ADD KEY `idx_periodo` (`periodo`);

--
-- Índices de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_sigla` (`sigla`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `disponibilidade_professor`
--
ALTER TABLE `disponibilidade_professor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_professor` (`professor_id`),
  ADD KEY `idx_dia` (`dia_semana`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Índices de tabela `historico_agendamentos`
--
ALTER TABLE `historico_agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_agendamento` (`agendamento_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_data` (`data_hora`),
  ADD KEY `idx_acao` (`acao`);

--
-- Índices de tabela `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_data` (`data`),
  ADD KEY `idx_turno` (`turno`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `professor_disciplinas`
--
ALTER TABLE `professor_disciplinas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prof_disc` (`professor_id`,`disciplina_id`),
  ADD KEY `idx_professor` (`professor_id`),
  ADD KEY `idx_disciplina` (`disciplina_id`),
  ADD KEY `idx_nivel` (`nivel`);

--
-- Índices de tabela `salas`
--
ALTER TABLE `salas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_local` (`local`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso` (`curso_id`),
  ADD KEY `idx_turno` (`turno`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_periodo` (`periodo`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `calendario`
--
ALTER TABLE `calendario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `curso_disciplinas`
--
ALTER TABLE `curso_disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `disciplinas`
--
ALTER TABLE `disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `disponibilidade_professor`
--
ALTER TABLE `disponibilidade_professor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_agendamentos`
--
ALTER TABLE `historico_agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `professor_disciplinas`
--
ALTER TABLE `professor_disciplinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `salas`
--
ALTER TABLE `salas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`),
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `agendamentos_ibfk_3` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`),
  ADD CONSTRAINT `agendamentos_ibfk_4` FOREIGN KEY (`compartilhado_com`) REFERENCES `professores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agendamentos_ibfk_5` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `alunos_ibfk_2` FOREIGN KEY (`turma_id`) REFERENCES `turmas` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `curso_disciplinas`
--
ALTER TABLE `curso_disciplinas`
  ADD CONSTRAINT `curso_disciplinas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `curso_disciplinas_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `disciplinas`
--
ALTER TABLE `disciplinas`
  ADD CONSTRAINT `disciplinas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `disponibilidade_professor`
--
ALTER TABLE `disponibilidade_professor`
  ADD CONSTRAINT `disponibilidade_professor_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_agendamentos`
--
ALTER TABLE `historico_agendamentos`
  ADD CONSTRAINT `historico_agendamentos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historico_agendamentos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `professores`
--
ALTER TABLE `professores`
  ADD CONSTRAINT `professores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `professor_disciplinas`
--
ALTER TABLE `professor_disciplinas`
  ADD CONSTRAINT `professor_disciplinas_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professor_disciplinas_ibfk_2` FOREIGN KEY (`disciplina_id`) REFERENCES `disciplinas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
