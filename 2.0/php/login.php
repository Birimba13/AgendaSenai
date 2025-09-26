<?php
session_start();
include("conexao.php");

$mensagem = "";

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $mensagem = "Email e senha são obrigatórios!";
    } else {
        $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                header("Location: index.php");
                exit();
            } else {
                $mensagem = "Email ou senha incorretos!";
            }
        } else {
            $mensagem = "Email ou senha incorretos!";
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
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="caixa-formulario">
        <h2 class="titulo">Login</h2>
        
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-erro">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="campo-grupo">
                <label class="rotulo" for="email">Email:</label>
                <input class="campo-entrada" type="email" id="email" name="email" required>
            </div>
            
            <div class="campo-grupo">
                <label class="rotulo" for="senha">Senha:</label>
                <input class="campo-entrada" type="password" id="senha" name="senha" required>
            </div>
            
            <button class="botao-principal" type="submit">Entrar</button>
        </form>
        
        <div class="links-navegacao">
            <a href="esqueceusenha.php">Esqueci minha senha</a>
            <a href="cadastro.php">Criar conta</a>
        </div>
    </div>
</body>
</html>
