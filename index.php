<?php
// Redirecionar para a versão 2.0 do sistema
session_start();

// Obter o diretório base dinamicamente
$base_dir = dirname($_SERVER['PHP_SELF']);

// Verificar se usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Se já está logado, redirecionar para o index do sistema
    header('Location: ' . $base_dir . '/2.0/index.php');
} else {
    // Se não está logado, redirecionar para login
    header('Location: ' . $base_dir . '/2.0/login.php');
}
exit();
?>