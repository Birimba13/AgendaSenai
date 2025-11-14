<?php
// Script de teste de conexão e estrutura do banco
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste de Conexão - AgendaSenai</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #16a34a; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .warning { color: #ea580c; font-weight: bold; }
        h2 { color: #0a2342; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0a2342; color: white; }
    </style>
</head>
<body>
    <h1>🔍 Diagnóstico do Sistema AgendaSenai</h1>

    <?php
    // Teste 1: Verificar arquivo de configuração
    echo '<div class="box">';
    echo '<h2>1. Arquivo de Configuração</h2>';
    $config_file = __DIR__ . '/../config/database.php';
    if (file_exists($config_file)) {
        echo '<p class="success">✓ Arquivo database.php encontrado</p>';
        include $config_file;
        echo "<p>Servidor: <strong>$servidor</strong></p>";
        echo "<p>Usuário: <strong>$usuario</strong></p>";
        echo "<p>Banco: <strong>$banco</strong></p>";
    } else {
        echo '<p class="error">✗ Arquivo database.php NÃO encontrado em: ' . $config_file . '</p>';
    }
    echo '</div>';

    // Teste 2: Conexão com MySQL
    echo '<div class="box">';
    echo '<h2>2. Conexão com MySQL</h2>';
    $mysqli = @new mysqli($servidor, $usuario, $senha);
    if ($mysqli->connect_error) {
        echo '<p class="error">✗ ERRO na conexão: ' . $mysqli->connect_error . '</p>';
        echo '<p class="warning">⚠️ Verifique se o MySQL está rodando e as credenciais estão corretas.</p>';
    } else {
        echo '<p class="success">✓ Conexão com MySQL estabelecida com sucesso</p>';

        // Teste 3: Verificar se banco existe
        echo '</div><div class="box">';
        echo '<h2>3. Banco de Dados</h2>';
        $db_exists = $mysqli->select_db($banco);
        if (!$db_exists) {
            echo '<p class="error">✗ Banco de dados "' . $banco . '" NÃO existe</p>';
            echo '<p class="warning">⚠️ Você precisa criar o banco. Execute:</p>';
            echo '<pre style="background: #f0f0f0; padding: 10px; border-radius: 5px;">CREATE DATABASE agendasenai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>';
            echo '<p>Depois acesse: <a href="http://localhost/AgendaSenai/2.0/app/createtable.php">createtable.php</a></p>';
        } else {
            echo '<p class="success">✓ Banco de dados "' . $banco . '" existe</p>';

            // Teste 4: Verificar tabelas
            echo '</div><div class="box">';
            echo '<h2>4. Tabelas do Banco</h2>';
            $tabelas_necessarias = ['usuarios', 'professores', 'cursos', 'disciplinas', 'turmas', 'salas', 'agendamentos', 'calendario'];

            echo '<table>';
            echo '<tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>';

            $todas_ok = true;
            foreach ($tabelas_necessarias as $tabela) {
                $result = $mysqli->query("SHOW TABLES LIKE '$tabela'");
                if ($result && $result->num_rows > 0) {
                    $count_result = $mysqli->query("SELECT COUNT(*) as total FROM $tabela");
                    $count = $count_result->fetch_assoc()['total'];
                    echo '<tr><td>' . $tabela . '</td><td class="success">✓ Existe</td><td>' . $count . '</td></tr>';
                } else {
                    echo '<tr><td>' . $tabela . '</td><td class="error">✗ NÃO existe</td><td>-</td></tr>';
                    $todas_ok = false;
                }
            }
            echo '</table>';

            if (!$todas_ok) {
                echo '<p class="warning">⚠️ Algumas tabelas estão faltando. Execute o script de criação:</p>';
                echo '<p><a href="../app/createtable.php" style="display: inline-block; background: #0a2342; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Criar Tabelas Agora</a></p>';
            }

            // Teste 5: Verificar APIs
            echo '</div><div class="box">';
            echo '<h2>5. APIs</h2>';
            $apis = [
                'get_professores.php' => '../api/get_professores.php',
                'get_cursos.php' => '../api/get_cursos.php',
                'get_disciplinas.php' => '../api/get_disciplinas.php',
                'get_turmas_select.php' => '../api/get_turmas_select.php'
            ];

            echo '<table>';
            echo '<tr><th>API</th><th>Status</th></tr>';
            foreach ($apis as $nome => $caminho) {
                $path = __DIR__ . '/' . $caminho;
                if (file_exists($path)) {
                    echo '<tr><td>' . $nome . '</td><td class="success">✓ Arquivo existe</td></tr>';
                } else {
                    echo '<tr><td>' . $nome . '</td><td class="error">✗ Arquivo NÃO encontrado</td></tr>';
                }
            }
            echo '</table>';
        }

        $mysqli->close();
    }
    echo '</div>';

    // Teste 6: Verificar .htaccess
    echo '<div class="box">';
    echo '<h2>6. Configuração Apache (.htaccess)</h2>';
    $htaccess_raiz = __DIR__ . '/../../.htaccess';
    if (file_exists($htaccess_raiz)) {
        echo '<p class="success">✓ Arquivo .htaccess existe na raiz</p>';
    } else {
        echo '<p class="warning">⚠️ Arquivo .htaccess NÃO encontrado na raiz</p>';
    }

    // Verificar se mod_rewrite está ativo
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();
        if (in_array('mod_rewrite', $modules)) {
            echo '<p class="success">✓ mod_rewrite está ATIVO</p>';
        } else {
            echo '<p class="error">✗ mod_rewrite está INATIVO</p>';
            echo '<p class="warning">⚠️ Você precisa ativar o mod_rewrite no Apache</p>';
        }
    } else {
        echo '<p class="warning">⚠️ Não foi possível verificar módulos do Apache</p>';
    }
    echo '</div>';

    // Resumo
    echo '<div class="box">';
    echo '<h2>📋 Resumo e Próximos Passos</h2>';
    echo '<ol>';
    echo '<li>Se o banco não existe: crie-o no MySQL</li>';
    echo '<li>Se as tabelas não existem: execute <a href="../app/createtable.php">createtable.php</a></li>';
    echo '<li>Acesse a página de login: <a href="login.php">login.php</a></li>';
    echo '<li>Se houver erro 404 nas APIs, verifique se está acessando pelo caminho correto: /AgendaSenai/2.0/public/</li>';
    echo '</ol>';
    echo '</div>';
    ?>

    <div class="box">
        <p><a href="index.php">← Voltar para o sistema</a></p>
    </div>
</body>
</html>
