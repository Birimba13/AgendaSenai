<?php
session_start();
include 'protect.php';
protect();
include 'conexao.php';

$sql = "SELECT nome, email FROM usuarios WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="cabecalho">
        <div class="container-cabecalho">
            <h1 class="titulo-sistema">Sistema Agenda SENAI</h1>
            <div class="info-usuario">
                <span>Olá, <?php echo htmlspecialchars($usuario['nome']); ?>!</span>
                <a href="logout.php" class="botao-sair">Sair</a>
            </div>
        </div>
    </div>
    
    <div class="conteudo-principal">
        <div class="cartao-boas-vindas">
            <h2>Bem-vindo ao Sistema!</h2>
            <p>Você está logado com sucesso no sistema de agenda do SENAI.</p>
            
            <div class="detalhes-usuario">
                <h3>Suas informações:</h3>
                <div class="item-detalhe">
                    <span class="rotulo-detalhe">Nome:</span> <?php echo htmlspecialchars($usuario['nome']); ?>
                </div>
                <div class="item-detalhe">
                    <span class="rotulo-detalhe">Email:</span> <?php echo htmlspecialchars($usuario['email']); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>