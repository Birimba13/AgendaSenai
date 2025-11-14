<?php
// Redirecionar para a versão 2.0 do sistema
session_start();

// Verificar se usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Se já está logado, redirecionar para o index do sistema
    header('Location: 2.0/public/index.php');
} else {
    // Se não está logado, redirecionar para login
    header('Location: 2.0/public/login.php');
}
exit();
?>