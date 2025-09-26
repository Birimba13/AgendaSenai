<?php
include("conexao.php");

$mensagem = "";
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $mensagem = "Email é obrigatório!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Email inválido!";
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            // Gera nova senha temporária
            $nova_senha = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 6);
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            // Atualiza senha no banco
            $sql_update = "UPDATE usuarios SET senha = ? WHERE email = ?";
            $stmt_update = $mysqli->prepare($sql_update);
            $stmt_update->bind_param("ss", $senha_hash, $email);
            
            if ($stmt_update->execute()) {
                $mensagem = "Nova senha gerada: <strong>$nova_senha</strong><br>Use esta senha para fazer login.";
                $sucesso = true;
            } else {
                $mensagem = "Erro ao gerar nova senha!";
            }
            $stmt_update->close();
        } else {
            $mensagem = "Email não encontrado!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha</title>
    <link rel="stylesheet" href="../css/esqueceuasenha.css">
</head>
<body>
    <div class="caixa-formulario">
        <h2 class="titulo">Recuperar Senha</h2>
        
        <?php if (!empty($mensagem)): ?>
            <div class="<?php echo $sucesso ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$sucesso): ?>
        <form method="POST">
            <div class="campo-grupo">
                <label class="rotulo" for="email">Digite seu email:</label>
                <input class="campo-entrada" type="email" id="email" name="email" required>
            </div>
            
            <button class="botao-principal" type="submit">Gerar Nova Senha</button>
        </form>
        <?php endif; ?>
        
        <div class="links-navegacao">
            <a href="login.php">Voltar para o login</a>
        </div>
    </div>
</body>
</html>