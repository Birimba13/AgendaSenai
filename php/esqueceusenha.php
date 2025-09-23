<?php
    include("conexao.php");
    if(isset($_POST[ok])){
        $novasenha = substr(md5(time()),0,6);
        $nscriptografada = md5(md5($novasenha));

        $sql_code = "UPDATE usuario SET senha = '$nsenha'"
    }
?>

<form action="">
    <input placeholder="Digite seu email" type="text" name="email">
    <input type="submit" name = "ok" value = "ok">    
</form>