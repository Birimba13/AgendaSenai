<?php

include("conexao.php");

if(isset($_POST['email']) && strlen($_POST['email']) > 0){
    $erro = [];

    if(!isset($_SESSION))
        session_start();

    $_SESSION['email'] = mysqli->escape_string($_POST['email']);
    $_SESSION['senha'] = md5(md5($_POST['senha']));

    $sql_code = "SELECT senha, nome, codigo FROM usuario WHERE email = '".$_SESSION['email']."'";
    $sql_query = mysqli->query($sql_code) or die($mysqli->error);
    $dado = $sql_query->fetch_assoc();
    $total = $sql_query->num_rows;

    if($total == 0){
        $erro[] = "Este email não pertence a nenhum usuário.";
    }else{

        if($dado['senha'] == $_SESSION['senha']){

            $_SESSION['usuario'] = $dado['codigo'];
        }
    }
    if(count[$erro] == 0 || !isset($erro)){
        echo "<script>alert('Login efetuado com sucesso!'); location.href='sucesso.php';</script>";
    }
}

?>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <?php
    $erro = []; 
    if (!isset($erro) || count($erro) === 0) {
        foreach($erro as $msg){
            echo "<p>$msg</p>";
        }  
    }   
    ?> 
  <body>
    <form method="POST" action="">
      <p><input value="" name="email" placeholder=" E-mail" type="text"></p>
      <p><input name="senha" type="password" placeholder=" Senha"></p>
      <p><a href="esqueceusenha.php">Esqueceu sua senha?</a></p>
      <p><input value="Entrar" type="button"></p>
    </form>
    
</body>
</html>
