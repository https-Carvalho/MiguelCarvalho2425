<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
$nome = $_SESSION['username'] ?? 'Desconhecido';

if ($tipo_usuario !== 'Admin') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin - <?= htmlspecialchars($nome) ?></h2>
    <ul>
        <li><a href="dashboard.php">📊 Painel</a></li>
        <li><a href="produtos.php">📦 Produtos</a></li>
        <li><a href="marcas.php">🏷️ Marcas</a></li>
        <li><a href="encomendas.php">📑 Encomendas</a></li>
        <li><a href="contas.php">👤 Contas</a></li>
        <li><a href="../index.php">← Voltar à Loja</a></li>
    </ul>
</div>

<div class="main-content">
