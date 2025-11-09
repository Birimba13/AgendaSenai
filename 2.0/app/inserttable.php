-- =====================================================
-- DADOS COMPLETOS - AgendaSenai
-- Popular todas as tabelas com dados de exemplo
-- Execute DEPOIS do banco_completo_do_zero.sql
-- =====================================================

USE agendasenai;

-- =====================================================
-- 1. USUÁRIOS (Base para professores e administradores)
-- =====================================================
-- Senha padrão para todos: "senai123" (hash MD5 simples para exemplo)
-- Em produção, use password_hash() do PHP com bcrypt

INSERT INTO usuarios (nome, email, senha, tipo, ativo) VALUES
-- Administradores
('Carlos Silva', 'carlos.silva@senai.br', MD5('senai123'), 'Administrador', TRUE),
('Ana Paula Santos', 'ana.santos@senai.br', MD5('senai123'), 'Coordenador', TRUE),

-- Professores de Automação
('João Pedro Oliveira', 'joao.oliveira@senai.br', MD5('senai123'), 'Professor', TRUE),
('Maria Clara Costa', 'maria.costa@senai.br', MD5('senai123'), 'Professor', TRUE),
('Roberto Fernandes', 'roberto.fernandes@senai.br', MD5('senai123'), 'Professor', TRUE),

-- Professores de Mecânica
('Fernando Alves', 'fernando.alves@senai.br', MD5('senai123'), 'Professor', TRUE),
('Juliana Rodrigues', 'juliana.rodrigues@senai.br', MD5('senai123'), 'Professor', TRUE),

-- Professores de Desenvolvimento de Sistemas
('Paulo Henrique Lima', 'paulo.lima@senai.br', MD5('senai123'), 'Professor', TRUE),
('Beatriz Souza', 'beatriz.souza@senai.br', MD5('senai123'), 'Professor', TRUE),
('Lucas Martins', 'lucas.martins@senai.br', MD5('senai123'), 'Professor', TRUE),

-- Professores de Web
('Camila Pereira', 'camila.pereira@senai.br', MD5('senai123'), 'Professor', TRUE),
('Rafael Santos', 'rafael.santos@senai.br', MD5('senai123'), 'Professor', TRUE);

-- =====================================================
-- 2. ADMINISTRADORES
-- =====================================================
INSERT INTO administradores (usuario_id, nivel) VALUES
(1, 'super'),  -- Carlos Silva
(2, 'normal'); -- Ana Paula Santos

-- =====================================================
-- 3. PROFESSORES
-- =====================================================
INSERT INTO professores (usuario_id, turno_manha, turno_tarde, turno_noite, carga_horaria_semanal, carga_horaria_usada, local_lotacao, celular, ativo) VALUES
-- João Pedro - Automação (manhã e tarde)
(3, TRUE, TRUE, FALSE, 40, 0, 'Afonso Pena', '(44) 99123-4567', TRUE),

-- Maria Clara - Automação (tarde e noite)
(4, FALSE, TRUE, TRUE, 40, 0, 'Afonso Pena', '(44) 99234-5678', TRUE),

-- Roberto - Automação (manhã)
(5, TRUE, FALSE, FALSE, 20, 0, 'Afonso Pena', '(44) 99345-6789', TRUE),

-- Fernando - Mecânica (noite)
(6, FALSE, FALSE, TRUE, 40, 0, 'Afonso Pena', '(44) 99456-7890', TRUE),

-- Juliana - Mecânica (tarde e noite)
(7, FALSE, TRUE, TRUE, 40, 0, 'Afonso Pena', '(44) 99567-8901', TRUE),

-- Paulo - Desenvolvimento (manhã e tarde)
(8, TRUE, TRUE, FALSE, 40, 0, 'Afonso Pena', '(44) 99678-9012', TRUE),

-- Beatriz - Desenvolvimento (tarde)
(9, FALSE, TRUE, FALSE, 40, 0, 'Afonso Pena', '(44) 99789-0123', TRUE),

-- Lucas - Desenvolvimento (manhã e noite)
(10, TRUE, FALSE, TRUE, 40, 0, 'Afonso Pena', '(44) 99890-1234', TRUE),

-- Camila - Web (noite)
(11, FALSE, FALSE, TRUE, 40, 0, 'Afonso Pena', '(44) 99901-2345', TRUE),

-- Rafael - Web (tarde e noite)
(12, FALSE, TRUE, TRUE, 40, 0, 'Afonso Pena', '(44) 99012-3456', TRUE);

-- =====================================================
-- 4. COMPETÊNCIAS DOS PROFESSORES
-- =====================================================
-- N0 = Não atende
-- N1 = Conhecimento básico
-- N2 = Atende teoria
-- N3 = Domínio completo

-- Professor 1: João Pedro (id=1) - Automação
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(1, 1, 'N3', 'Especialista em POO - Java e Python'),
(1, 2, 'N2', 'Conhecimento teórico em Sistemas de Controle'),
(1, 3, 'N2', 'Experiência com redes industriais'),
(1, 4, 'N3', 'Certificado Siemens S7-1200');

-- Professor 2: Maria Clara (id=2) - Automação
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(2, 2, 'N3', 'Mestrado em Controle e Automação'),
(2, 3, 'N3', 'Profibus e Modbus'),
(2, 4, 'N2', 'CLP Básico'),
(2, 5, 'N3', 'Especialista em robótica ABB');

-- Professor 3: Roberto (id=3) - Automação
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(3, 1, 'N2', 'POO com Java'),
(3, 4, 'N3', 'Allen-Bradley e Siemens'),
(3, 5, 'N2', 'Robótica básica');

-- Professor 4: Fernando (id=4) - Mecânica
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(4, 6, 'N3', '15 anos de experiência em desenho técnico'),
(4, 7, 'N3', 'Processos de usinagem CNC'),
(4, 8, 'N2', 'Manutenção preventiva'),
(4, 9, 'N3', 'Metrologia dimensional');

-- Professor 5: Juliana (id=5) - Mecânica
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(5, 7, 'N3', 'Soldagem e conformação'),
(5, 8, 'N3', 'Especialista em manutenção preditiva'),
(5, 9, 'N2', 'Instrumentação básica');

-- Professor 6: Paulo (id=6) - Desenvolvimento
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(6, 10, 'N3', 'DBA MySQL e PostgreSQL'),
(6, 11, 'N3', 'Full Stack - React e Node.js'),
(6, 13, 'N3', 'Scrum Master certificado'),
(6, 14, 'N2', 'POO com Java');

-- Professor 7: Beatriz (id=7) - Desenvolvimento
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(7, 10, 'N2', 'SQL e modelagem'),
(7, 11, 'N3', 'Especialista em Front-end - Vue.js'),
(7, 12, 'N3', 'Flutter e React Native'),
(7, 14, 'N3', 'POO e Design Patterns');

-- Professor 8: Lucas (id=8) - Desenvolvimento
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(8, 10, 'N3', 'Banco de Dados e NoSQL'),
(8, 12, 'N3', 'Android nativo e Kotlin'),
(8, 13, 'N2', 'Metodologias ágeis'),
(8, 14, 'N3', 'Java e Python avançado');

-- Professor 9: Camila (id=9) - Web
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(9, 15, 'N3', 'HTML5 e CSS3 avançado'),
(9, 16, 'N3', 'JavaScript ES6+ e TypeScript'),
(9, 17, 'N2', 'PHP básico'),
(9, 18, 'N3', 'React e Next.js');

-- Professor 10: Rafael (id=10) - Web
INSERT INTO professor_disciplinas (professor_id, disciplina_id, nivel, observacoes) VALUES
(10, 15, 'N2', 'HTML e CSS responsivo'),
(10, 16, 'N3', 'JavaScript avançado'),
(10, 17, 'N3', 'PHP Laravel e APIs REST'),
(10, 18, 'N3', 'Vue.js e Nuxt.js');

-- =====================================================
-- 5. ALUNOS
-- =====================================================
-- Turma 1: 3º Técnico Automação - Manhã (id=1)
INSERT INTO alunos (curso_id, turma_id, nome, matricula, email, cpf, telefone, data_nascimento, data_matricula, status) VALUES
(1, 1, 'Gabriel Silva Santos', '2025010001', 'gabriel.santos@aluno.senai.br', '123.456.789-01', '(44) 98001-0001', '2007-03-15', '2025-02-01', 'ativo'),
(1, 1, 'Isabella Costa Oliveira', '2025010002', 'isabella.oliveira@aluno.senai.br', '123.456.789-02', '(44) 98001-0002', '2006-07-22', '2025-02-01', 'ativo'),
(1, 1, 'Miguel Alves Pereira', '2025010003', 'miguel.pereira@aluno.senai.br', '123.456.789-03', '(44) 98001-0003', '2007-01-10', '2025-02-01', 'ativo'),
(1, 1, 'Sophia Lima Rodrigues', '2025010004', 'sophia.rodrigues@aluno.senai.br', '123.456.789-04', '(44) 98001-0004', '2006-11-05', '2025-02-01', 'ativo'),
(1, 1, 'Arthur Santos Ferreira', '2025010005', 'arthur.ferreira@aluno.senai.br', '123.456.789-05', '(44) 98001-0005', '2007-05-18', '2025-02-01', 'ativo'),

-- Turma 2: 4º Técnico Automação - Tarde (id=2)
(1, 2, 'Heloísa Martins Costa', '2024010001', 'heloisa.costa@aluno.senai.br', '123.456.789-06', '(44) 98001-0006', '2006-02-20', '2024-02-01', 'ativo'),
(1, 2, 'Davi Oliveira Santos', '2024010002', 'davi.santos@aluno.senai.br', '123.456.789-07', '(44) 98001-0007', '2005-09-14', '2024-02-01', 'ativo'),
(1, 2, 'Alice Souza Lima', '2024010003', 'alice.lima@aluno.senai.br', '123.456.789-08', '(44) 98001-0008', '2006-04-30', '2024-02-01', 'ativo'),
(1, 2, 'Lorenzo Costa Silva', '2024010004', 'lorenzo.silva@aluno.senai.br', '123.456.789-09', '(44) 98001-0009', '2005-12-08', '2024-02-01', 'ativo'),

-- Turma 3: 3º Técnico Mecânica - Noite (id=3)
(2, 3, 'Valentina Rodrigues Alves', '2025020001', 'valentina.alves@aluno.senai.br', '123.456.789-10', '(44) 98001-0010', '2005-06-12', '2025-02-01', 'ativo'),
(2, 3, 'Enzo Ferreira Costa', '2025020002', 'enzo.costa@aluno.senai.br', '123.456.789-11', '(44) 98001-0011', '2004-10-25', '2025-02-01', 'ativo'),
(2, 3, 'Helena Santos Pereira', '2025020003', 'helena.pereira@aluno.senai.br', '123.456.789-12', '(44) 98001-0012', '2005-03-17', '2025-02-01', 'ativo'),
(2, 3, 'Théo Lima Santos', '2025020004', 'theo.santos@aluno.senai.br', '123.456.789-13', '(44) 98001-0013', '2004-08-22', '2025-02-01', 'ativo'),

-- Turma 4: 2º Técnico DS - Manhã (id=4)
(3, 4, 'Laura Oliveira Costa', '2025030001', 'laura.costa@aluno.senai.br', '123.456.789-14', '(44) 98001-0014', '2007-09-05', '2025-02-01', 'ativo'),
(3, 4, 'Bernardo Costa Lima', '2025030002', 'bernardo.lima@aluno.senai.br', '123.456.789-15', '(44) 98001-0015', '2007-02-28', '2025-02-01', 'ativo'),
(3, 4, 'Manuela Santos Silva', '2025030003', 'manuela.silva@aluno.senai.br', '123.456.789-16', '(44) 98001-0016', '2008-01-15', '2025-02-01', 'ativo'),
(3, 4, 'Pedro Henrique Alves', '2025030004', 'pedro.alves@aluno.senai.br', '123.456.789-17', '(44) 98001-0017', '2007-11-20', '2025-02-01', 'ativo'),
(3, 4, 'Julia Rodrigues Santos', '2025030005', 'julia.santos@aluno.senai.br', '123.456.789-18', '(44) 98001-0018', '2008-04-10', '2025-02-01', 'ativo'),

-- Turma 5: 3º Técnico DS - Tarde (id=5)
(3, 5, 'Lucas Gabriel Costa', '2025030006', 'lucas.costa@aluno.senai.br', '123.456.789-19', '(44) 98001-0019', '2006-12-03', '2025-02-01', 'ativo'),
(3, 5, 'Maria Eduarda Lima', '2025030007', 'maria.lima@aluno.senai.br', '123.456.789-20', '(44) 98001-0020', '2007-06-18', '2025-02-01', 'ativo'),
(3, 5, 'Rafael Santos Silva', '2025030008', 'rafael.silva@aluno.senai.br', '123.456.789-21', '(44) 98001-0021', '2006-10-09', '2025-02-01', 'ativo'),
(3, 5, 'Yasmin Oliveira Costa', '2025030009', 'yasmin.costa@aluno.senai.br', '123.456.789-22', '(44) 98001-0022', '2007-03-25', '2025-02-01', 'ativo'),

-- Turma 6: Qualificação Web - Noite (id=6)
(4, 6, 'Gustavo Alves Santos', '2025040001', 'gustavo.santos@aluno.senai.br', '123.456.789-23', '(44) 98001-0023', '2003-05-14', '2025-02-01', 'ativo'),
(4, 6, 'Beatriz Costa Ferreira', '2025040002', 'beatriz.ferreira@aluno.senai.br', '123.456.789-24', '(44) 98001-0024', '2004-08-20', '2025-02-01', 'ativo'),
(4, 6, 'Vitor Hugo Lima', '2025040003', 'vitor.lima@aluno.senai.br', '123.456.789-25', '(44) 98001-0025', '2002-11-30', '2025-02-01', 'ativo'),
(4, 6, 'Larissa Santos Silva', '2025040004', 'larissa.silva@aluno.senai.br', '123.456.789-26', '(44) 98001-0026', '2003-02-17', '2025-02-01', 'ativo');

-- =====================================================
-- 6. HORÁRIOS PADRÃO (Grade de horários)
-- =====================================================
-- Segunda-feira
INSERT INTO horarios (data, turno, bloco_inicio, bloco_fim, ordem, carga_horaria, ativo) VALUES
('2025-02-03', 'Manha', '08:00:00', '09:40:00', 1, 1.67, TRUE),
('2025-02-03', 'Manha', '09:50:00', '11:30:00', 2, 1.67, TRUE),
('2025-02-03', 'Tarde', '13:00:00', '14:40:00', 3, 1.67, TRUE),
('2025-02-03', 'Tarde', '14:50:00', '16:30:00', 4, 1.67, TRUE),
('2025-02-03', 'Tarde', '16:40:00', '17:30:00', 5, 0.83, TRUE),
('2025-02-03', 'Noite', '18:30:00', '20:10:00', 6, 1.67, TRUE),
('2025-02-03', 'Noite', '20:20:00', '22:00:00', 7, 1.67, TRUE);

-- =====================================================
-- 7. AGENDAMENTOS DE EXEMPLO (Uma semana)
-- =====================================================
-- Segunda-feira, 03/02/2025

-- Manhã - Turma 1 (3º Automação Manhã) - Professor João (id=1)
INSERT INTO agendamentos (professor_id, turma_id, disciplina_id, sala, data, dia_semana, hora_inicio, hora_fim, tipo, modalidade, status, criado_por) VALUES
(1, 1, 1, 'LAB-INF-01', '2025-02-03', 'Segunda', '08:00:00', '09:40:00', 'aula', 'presencial', 'confirmado', 1),
(1, 1, 1, 'LAB-INF-01', '2025-02-03', 'Segunda', '09:50:00', '11:30:00', 'aula', 'presencial', 'confirmado', 1),

-- Manhã - Turma 4 (2º DS Manhã) - Professor Paulo (id=6)
(6, 4, 10, 'LAB-INF-02', '2025-02-03', 'Segunda', '08:00:00', '09:40:00', 'aula', 'presencial', 'confirmado', 1),
(6, 4, 10, 'LAB-INF-02', '2025-02-03', 'Segunda', '09:50:00', '11:30:00', 'aula', 'presencial', 'confirmado', 1),

-- Tarde - Turma 2 (4º Automação Tarde) - Professor Maria (id=2)
(2, 2, 2, 'SALA-201', '2025-02-03', 'Segunda', '13:00:00', '14:40:00', 'aula', 'presencial', 'confirmado', 1),
(2, 2, 5, 'LAB-AUTO-01', '2025-02-03', 'Segunda', '14:50:00', '16:30:00', 'aula', 'presencial', 'confirmado', 1),

-- Tarde - Turma 5 (3º DS Tarde) - Professor Beatriz (id=7)
(7, 5, 11, 'LAB-INF-03', '2025-02-03', 'Segunda', '13:00:00', '14:40:00', 'aula', 'presencial', 'confirmado', 1),
(7, 5, 12, 'LAB-INF-03', '2025-02-03', 'Segunda', '14:50:00', '16:30:00', 'aula', 'presencial', 'confirmado', 1),

-- Noite - Turma 3 (3º Mecânica Noite) - Professor Fernando (id=4)
(4, 3, 6, 'SALA-202', '2025-02-03', 'Segunda', '18:30:00', '20:10:00', 'aula', 'presencial', 'confirmado', 1),
(4, 3, 7, 'LAB-MEC-01', '2025-02-03', 'Segunda', '20:20:00', '22:00:00', 'aula', 'presencial', 'confirmado', 1),

-- Noite - Turma 6 (Qualificação Web) - Professor Camila (id=9)
(9, 6, 15, 'LAB-INF-01', '2025-02-03', 'Segunda', '18:30:00', '20:10:00', 'aula', 'presencial', 'confirmado', 1),
(9, 6, 16, 'LAB-INF-01', '2025-02-03', 'Segunda', '20:20:00', '22:00:00', 'aula', 'presencial', 'confirmado', 1);

-- Terça-feira, 04/02/2025
INSERT INTO agendamentos (professor_id, turma_id, disciplina_id, sala, data, dia_semana, hora_inicio, hora_fim, tipo, modalidade, status, criado_por) VALUES
(3, 1, 4, 'LAB-AUTO-01', '2025-02-04', 'Terca', '08:00:00', '09:40:00', 'aula', 'presencial', 'confirmado', 1),
(3, 1, 4, 'LAB-AUTO-01', '2025-02-04', 'Terca', '09:50:00', '11:30:00', 'aula', 'presencial', 'confirmado', 1),
(8, 4, 14, 'LAB-INF-02', '2025-02-04', 'Terca', '08:00:00', '09:40:00', 'aula', 'presencial', 'confirmado', 1),
(2, 2, 3, 'SALA-201', '2025-02-04', 'Terca', '13:00:00', '14:40:00', 'aula', 'presencial', 'confirmado', 1),
(7, 5, 14, 'LAB-INF-03', '2025-02-04', 'Terca', '13:00:00', '14:40:00', 'aula', 'presencial', 'confirmado', 1),
(5, 3, 8, 'LAB-MEC-01', '2025-02-04', 'Terca', '18:30:00', '20:10:00', 'aula', 'presencial', 'confirmado', 1),
(10, 6, 17, 'LAB-INF-01', '2025-02-04', 'Terca', '18:30:00', '20:10:00', 'aula', 'presencial', 'confirmado', 1);

-- =====================================================
-- 8. DISPONIBILIDADE DOS PROFESSORES
-- =====================================================
-- Professor 1: João (Manhã e Tarde)
INSERT INTO disponibilidade_professor (professor_id, dia_semana, hora_inicio, hora_fim, tipo, ativo) VALUES
(1, 'Segunda', '08:00:00', '12:00:00', 'disponivel', TRUE),
(1, 'Segunda', '13:00:00', '17:30:00', 'disponivel', TRUE),
(1, 'Terca', '08:00:00', '12:00:00', 'disponivel', TRUE),
(1, 'Terca', '13:00:00', '17:30:00', 'disponivel', TRUE),
(1, 'Quarta', '08:00:00', '12:00:00', 'preferencia', TRUE),
(1, 'Quinta', '08:00:00', '12:00:00', 'disponivel', TRUE),
(1, 'Sexta', '08:00:00', '12:00:00', 'disponivel', TRUE),

-- Professor 2: Maria (Tarde e Noite)
(2, 'Segunda', '13:00:00', '22:00:00', 'disponivel', TRUE),
(2, 'Terca', '13:00:00', '22:00:00', 'disponivel', TRUE),
(2, 'Quarta', '13:00:00', '22:00:00', 'disponivel', TRUE),
(2, 'Quinta', '13:00:00', '22:00:00', 'disponivel', TRUE),
(2, 'Sexta', '13:00:00', '17:30:00', 'disponivel', TRUE),

-- Professor 4: Fernando (Noite)
(4, 'Segunda', '18:30:00', '22:00:00', 'disponivel', TRUE),
(4, 'Terca', '18:30:00', '22:00:00', 'disponivel', TRUE),
(4, 'Quarta', '18:30:00', '22:00:00', 'disponivel', TRUE),
(4, 'Quinta', '18:30:00', '22:00:00', 'disponivel', TRUE),
(4, 'Sexta', '18:30:00', '22:00:00', 'disponivel', TRUE);

-- =====================================================
-- VERIFICAÇÕES FINAIS
-- =====================================================
SELECT '✅ DADOS COMPLETOS INSERIDOS COM SUCESSO!' AS Status;

-- Estatísticas
SELECT 'USUÁRIOS' AS Tabela, COUNT(*) AS Total FROM usuarios
UNION ALL SELECT 'PROFESSORES', COUNT(*) FROM professores
UNION ALL SELECT 'ALUNOS', COUNT(*) FROM alunos
UNION ALL SELECT 'COMPETÊNCIAS', COUNT(*) FROM professor_disciplinas
UNION ALL SELECT 'AGENDAMENTOS', COUNT(*) FROM agendamentos
UNION ALL SELECT 'DISPONIBILIDADES', COUNT(*) FROM disponibilidade_professor;

-- =====================================================
-- CREDENCIAIS DE ACESSO
-- =====================================================
SELECT '
========================================
CREDENCIAIS DE ACESSO (EXEMPLO)
========================================

ADMINISTRADOR:
Email: carlos.silva@senai.br
Senha: senai123

COORDENADOR:
Email: ana.santos@senai.br
Senha: senai123

PROFESSORES:
Email: joao.oliveira@senai.br
Senha: senai123
(Todos os professores usam senha: senai123)

⚠️ IMPORTANTE: Altere as senhas em produção!
⚠️ Use password_hash() do PHP com bcrypt

========================================
' AS Credenciais;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================