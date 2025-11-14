<?php
include __DIR__ . '/../config/database.php';
$mysqli = new mysqli($servidor, $usuario, $senha, $banco);

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

$mysqli->set_charset("utf8");
?>