<?php
$servidor = "localhost";   // geralmente é localhost
$usuario  = "root";        // usuário do banco (no XAMPP/MAMP/WAMP costuma ser "root")
$senha    = "";            // senha do banco (no XAMPP normalmente é vazia)
$banco    = "agendasenai";   // nome do banco de dados

// Criando a conexão
$mysqli = new mysqli($servidor, $usuario, $senha, $banco);

// Verificando se houve erro
if ($mysqli->connect_errno) {
    echo "Falha na conexão: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}
?>
