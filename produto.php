<?php
include('config.php'); // Inclui a configuração da base de dados e a função getPerfumePorId

// Obtém o ID do produto a partir da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$perfume = buscarDetalhesPerfume($id);

if (!$perfume) {
    echo "Produto não encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($perfume['nome']); ?> - Detalhes do Produto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="menu">
        <ul>
            <li><a href="index.php">Início</a></li>
            <li>Discovery Kit</li>
            <li>Marcas ▼</li>
            <li>Família Olfativa ▼</li>
            <li>Categorias ▼</li>
            <li>Sobre Nós ▼</li>
            <li>Contactos</li>
        </ul>
    </nav>

    <!-- Detalhes do Produto -->
    <section class="detalhes-produto">
        <div class="imagens-produto">
            <?php foreach ($perfume['imagens'] as $imagem): ?>
                <img src="<?php echo htmlspecialchars($imagem); ?>" alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
            <?php endforeach; ?>
        </div>
        <div class="informacoes-produto">
            <h1><?php echo htmlspecialchars($perfume['nome']); ?></h1>
            <p class="tipo"><?php echo htmlspecialchars($perfume['tipo']); ?></p>
            <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
            <p class="descricao"><?php echo htmlspecialchars($perfume['descricao']); ?></p>
        </div>
    </section>
</body>
</html>
