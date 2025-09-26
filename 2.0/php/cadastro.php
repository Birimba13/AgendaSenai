<?php
include("conexao.php");

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // Validações simples
    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "Todos os campos são obrigatórios!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Email inválido!";
    } elseif (strlen($senha) < 4) {
        $mensagem = "Senha deve ter pelo menos 4 caracteres!";
    } else {
        // Verifica se email já existe
        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $mysqli->prepare($sql_check);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $mensagem = "Este email já está cadastrado!";
        } else {
            // Cadastra usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql_insert);
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            
            if ($stmt->execute()) {
                $mensagem = "Usuário cadastrado com sucesso!";
            } else {
                $mensagem = "Erro ao cadastrar usuário!";
            }
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
    <title>Cadastro</title>
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>
    <div class="caixa-formulario">
        <h2 class="titulo">Cadastro</h2>
        
        <?php if (!empty($mensagem)): ?>
            <div class="mensagem <?php echo strpos($mensagem, 'sucesso') ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="campo-grupo">
                <label class="rotulo" for="nome">Nome:</label>
                <input class="campo-entrada" type="text" id="nome" name="nome" required>
            </div>
            
            <div class="campo-grupo">
                <label class="rotulo" for="email">Email:</label>
                <input class="campo-entrada" type="email" id="email" name="email" required>
            </div>
            
            <div class="campo-grupo">
                <label class="rotulo" for="senha">Senha:</label>
                <input class="campo-entrada" type="password" id="senha" name="senha" required>
            </div>
            
            <button class="botao-principal" type="submit">Cadastrar</button>
        </form>
        
        <div class="links-navegacao">
            <a href="login.php">Já tenho conta - Fazer login</a>
        </div>
    </div>
</body>
</html>