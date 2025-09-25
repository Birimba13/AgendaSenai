<?php

    include("conexao.php");

    if(isset($_POST[ok])){

        $email = $mysqli->escape_string($_POST['email']);

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $erro[] = "Email inválido, tente novamente.";
        }

        $sql_code = "SELECT senha, nome, codigo FROM usuario WHERE email = .'$email.'";
        $sql_query = mysqli->query($sql_code) or die($mysqli->error);
        $dado = $sql_query->fetch_assoc();
        $total = $sql_query->num_rows;

        if(count($erro) == 0 && $total){
            $novasenha = substr(md5(time()),0,6);
            $nscriptografada = md5(md5($novasenha));

            $sql_code = "UPDATE usuario SET senha = '$nsenha'";
            $sql_query = $mysqli->$query($sql_code) or die($mysqli->error);

            mail($email, "Sua nova senha, sua nova senha: ".$novasenha);

        }
    }
        
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <form action=""method="POST">
            <input placeholder="Digite seu email" type="text" name="email">
            <input type="submit" name = "ok" value = "ok">
        </form>    
    </body>
</html>
    