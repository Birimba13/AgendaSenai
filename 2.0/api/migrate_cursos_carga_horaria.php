<?php
/**
 * MIGRAÇÃO: Adicionar campos de carga horária semanal aos cursos
 *
 * Esta migração adiciona os campos necessários para o sistema
 * de agendamento automático de aulas.
 *
 * Campos adicionados:
 * - carga_horaria_semanal: Total de horas que o curso precisa ter na semana
 * - carga_horaria_preenchida: Total de horas já agendadas (atualizado automaticamente)
 */

header('Content-Type: text/html; charset=utf-8');
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Migração - Carga Horária de Cursos</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .step {
            padding: 10px;
            margin: 10px 0;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 3px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Migração de Banco de Dados</h1>
        <h2>Sistema de Carga Horária Semanal para Cursos</h2>
";

try {
    // Criar conexão
    $mysqli = new mysqli($servidor, $usuario, $senha, $banco);

    if ($mysqli->connect_error) {
        throw new Exception("Erro de conexão: " . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8mb4");

    echo "<div class='info'>✅ Conexão com o banco de dados estabelecida com sucesso!</div>";

    // PASSO 1: Verificar se os campos já existem
    echo "<div class='step'><strong>PASSO 1:</strong> Verificando estrutura atual da tabela...</div>";

    $result = $mysqli->query("SHOW COLUMNS FROM cursos LIKE 'carga_horaria_semanal'");
    $campo_semanal_existe = $result->num_rows > 0;

    $result = $mysqli->query("SHOW COLUMNS FROM cursos LIKE 'carga_horaria_preenchida'");
    $campo_preenchida_existe = $result->num_rows > 0;

    if ($campo_semanal_existe && $campo_preenchida_existe) {
        echo "<div class='info'>ℹ️ Os campos já existem na tabela. Nenhuma alteração necessária.</div>";
    } else {
        // PASSO 2: Adicionar campos se não existirem
        echo "<div class='step'><strong>PASSO 2:</strong> Adicionando novos campos à tabela <code>cursos</code>...</div>";

        $alteracoes = [];

        if (!$campo_semanal_existe) {
            $sql = "ALTER TABLE cursos ADD COLUMN carga_horaria_semanal INT DEFAULT 0 COMMENT 'Horas totais que o curso precisa ter na semana'";

            if ($mysqli->query($sql)) {
                $alteracoes[] = "✅ Campo <code>carga_horaria_semanal</code> adicionado com sucesso!";
            } else {
                throw new Exception("Erro ao adicionar campo carga_horaria_semanal: " . $mysqli->error);
            }
        }

        if (!$campo_preenchida_existe) {
            $sql = "ALTER TABLE cursos ADD COLUMN carga_horaria_preenchida INT DEFAULT 0 COMMENT 'Horas já agendadas (atualizado automaticamente)'";

            if ($mysqli->query($sql)) {
                $alteracoes[] = "✅ Campo <code>carga_horaria_preenchida</code> adicionado com sucesso!";
            } else {
                throw new Exception("Erro ao adicionar campo carga_horaria_preenchida: " . $mysqli->error);
            }
        }

        foreach ($alteracoes as $msg) {
            echo "<div class='success'>$msg</div>";
        }
    }

    // PASSO 3: Calcular carga horária preenchida para cursos existentes
    echo "<div class='step'><strong>PASSO 3:</strong> Calculando carga horária preenchida dos cursos existentes...</div>";

    $sql = "UPDATE cursos c
            SET carga_horaria_preenchida = (
                SELECT COUNT(*)
                FROM agendamentos a
                INNER JOIN disciplinas d ON a.disciplina_id = d.id
                WHERE d.curso_id = c.id
                AND a.status != 'cancelado'
            )";

    if ($mysqli->query($sql)) {
        echo "<div class='success'>✅ Carga horária preenchida calculada para todos os cursos!</div>";
    } else {
        echo "<div class='error'>⚠️ Erro ao calcular carga preenchida: " . $mysqli->error . "</div>";
    }

    // PASSO 4: Exibir resumo
    echo "<div class='step'><strong>PASSO 4:</strong> Verificando resultados...</div>";

    $result = $mysqli->query("SELECT
        id,
        nome,
        carga_horaria_semanal,
        carga_horaria_preenchida
        FROM cursos
        ORDER BY nome");

    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='width:100%; margin:20px 0; border-collapse: collapse;'>
            <thead style='background: #f8f9fa;'>
                <tr>
                    <th>ID</th>
                    <th>Curso</th>
                    <th>Carga Semanal</th>
                    <th>Carga Preenchida</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>";

        while ($row = $result->fetch_assoc()) {
            $percentual = $row['carga_horaria_semanal'] > 0
                ? round(($row['carga_horaria_preenchida'] / $row['carga_horaria_semanal']) * 100, 1)
                : 0;

            $status_cor = $percentual >= 100 ? '#d4edda' : ($percentual >= 80 ? '#fff3cd' : '#f8f9fa');

            echo "<tr style='background: $status_cor;'>
                <td>{$row['id']}</td>
                <td>{$row['nome']}</td>
                <td style='text-align: center;'>{$row['carga_horaria_semanal']}h</td>
                <td style='text-align: center;'>{$row['carga_horaria_preenchida']}h</td>
                <td style='text-align: center;'>{$percentual}%</td>
            </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<div class='info'>ℹ️ Nenhum curso cadastrado no sistema ainda.</div>";
    }

    // PASSO 5: Instruções finais
    echo "<div class='success' style='margin-top: 30px;'>
        <h3>✅ Migração Concluída com Sucesso!</h3>
        <p><strong>Próximos passos:</strong></p>
        <ol>
            <li>Acesse o CRUD de Cursos e defina a <strong>Carga Horária Semanal</strong> para cada curso</li>
            <li>O campo <strong>Carga Horária Preenchida</strong> será atualizado automaticamente conforme aulas são agendadas</li>
            <li>Use o botão <strong>\"Gerar Agenda Automática\"</strong> na tela de agenda para preencher automaticamente</li>
        </ol>
    </div>";

    echo "<a href='../cursos.php' class='back-button'>📚 Ir para CRUD de Cursos</a>";
    echo "<a href='../visualizar_agenda.php' class='back-button' style='background: #28a745; margin-left: 10px;'>📅 Ir para Agenda</a>";

    $mysqli->close();

} catch (Exception $e) {
    echo "<div class='error'>
        <h3>❌ Erro na Migração</h3>
        <p><strong>Mensagem:</strong> {$e->getMessage()}</p>
        <p><strong>Solução:</strong> Verifique as configurações do banco de dados e tente novamente.</p>
    </div>";

    echo "<a href='javascript:history.back()' class='back-button' style='background: #dc3545;'>⬅️ Voltar</a>";
}

echo "
    </div>
</body>
</html>";
?>
