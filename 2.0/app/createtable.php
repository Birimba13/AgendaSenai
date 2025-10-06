<?php
include "conexao.php";

$sqls = [
    "CREATE TABLE usuarios (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      email VARCHAR(150) NOT NULL UNIQUE,
      senha VARCHAR(255) NOT NULL,
      ativo BOOLEAN NOT NULL DEFAULT TRUE,
      data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE administradores (
      id INT AUTO_INCREMENT PRIMARY KEY,
      usuario_id INT NOT NULL UNIQUE,
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE professores (
      id INT AUTO_INCREMENT PRIMARY KEY,
      usuario_id INT NOT NULL UNIQUE,
      turno_manha BOOLEAN NOT NULL DEFAULT FALSE,
      turno_tarde BOOLEAN NOT NULL DEFAULT FALSE,
      carga_horaria_total INT NOT NULL DEFAULT 0,
      carga_horaria_usada INT NOT NULL DEFAULT 0,
      ativo BOOLEAN NOT NULL DEFAULT TRUE,
      FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE disciplinas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      sigla VARCHAR(10) NOT NULL,
      carga_horaria INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE professor_disciplinas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      professor_id INT NOT NULL,
      disciplina_id INT NOT NULL,
      nivel ENUM('N0','N1','N2','N3') NOT NULL DEFAULT 'N0',
      FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
      FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
      UNIQUE KEY unique_prof_disc (professor_id, disciplina_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE cursos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nome VARCHAR(100) NOT NULL,
      data_inicio DATE,
      data_fim DATE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE curso_disciplinas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      curso_id INT NOT NULL,
      disciplina_id INT NOT NULL,
      FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
      FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
      UNIQUE KEY unique_curso_disc (curso_id, disciplina_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE horarios (
      id INT AUTO_INCREMENT PRIMARY KEY,
      data DATE NOT NULL,
      turno ENUM('Manhã','Tarde') NOT NULL,
      bloco_inicio TIME NOT NULL,
      bloco_fim TIME NOT NULL,
      UNIQUE KEY unique_horario (data, turno, bloco_inicio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE agendamentos (
      id INT AUTO_INCREMENT PRIMARY KEY,
      professor_id INT NOT NULL,
      curso_id INT NOT NULL,
      disciplina_id INT NOT NULL,
      horario_id INT NOT NULL,
      data DATE NOT NULL,
      observacoes TEXT,
      FOREIGN KEY (professor_id) REFERENCES professores(id) ON DELETE CASCADE,
      FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE,
      FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
      FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

// Executa cada query
foreach ($sqls as $sql) {
    if (mysqli_query($mysqli, $sql)) {
        echo "Tabela criada ou já existe.<br>";
    } else {
        echo "Erro ao criar tabela: " . mysqli_error($mysqli) . "<br>";
    }
}
?>
