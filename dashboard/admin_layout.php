
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="sidebar">
    <h2>Admin - <?= htmlspecialchars($nome_utilizador) ?></h2>
    <ul>
        <li><a href="dashboard.php">📊 Painel</a></li>
        <li><a href="produtos.php">📦 Produtos</a></li>
        <li><a href="gestao_olfativa.php">Gestão Olfativa</a></li>
        <li><a href="marcas.php">🏷️ Marcas</a></li>
        <li><a href="encomendas.php">📑 Encomendas</a></li>
        <li><a href="contas.php">👤 Contas</a></li>
        <li><a href="../index.php">← Voltar à Loja</a></li>
    </ul>
</div>