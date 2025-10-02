<?php
include "conexao.php";

$sql = "
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";
if (mysqli_query($mysqli, $sql)) {
    echo "Tabela 'usuarios' criada com sucesso.";
} else {
    echo "Erro ao criar tabela: " . mysqli_error($conexao);
}
?>