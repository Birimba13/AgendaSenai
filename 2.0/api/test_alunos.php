<?php
// Teste rápido - salve como test_alunos.php na pasta api/
header('Content-Type: application/json');

require_once '../app/conexao.php';

echo json_encode([
    'conexao' => $mysqli ? 'OK' : 'ERRO',
    'database' => $mysqli->select_db('agendasenai') ? 'OK' : 'ERRO',
    'tabela_existe' => $mysqli->query("SHOW TABLES LIKE 'alunos'") ? 'OK' : 'ERRO'
]);
?>