<?php
include('config.php'); // Inclui a configuração da base de dados e a função listarPerfumes

// Obtém os perfumes da base de dados
$perfumes = listarPerfumes();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrâncias Nicho</title>
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>
    <!-- Menu de Navegação -->
    <nav class="menu">
        <ul>
            <li>Início</li>
            <li>Discovery Kit</li>
            <li>Marcas ▼</li>
            <li>Família Olfativa ▼</li>
            <li>Categorias ▼</li>
            <li>Sobre Nós ▼</li>
            <li>Contactos</li>
        </ul>
    </nav>

    <!-- Lista de Fragrâncias -->
    <section class="lista-fragrancias">
        <?php foreach ($perfumes as $perfume): ?>
            <div class="fragrancia-item" caminho_imagem_hover="<?php echo htmlspecialchars($perfume['caminho_imagem_hover']); ?>">
                <a href="produto.php?id=<?php echo $perfume['id']; ?>">
                    <div class="imagem-fragrancia">
                        <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                    </div>
                    <div class="informacoes-fragrancia">
                        <h2><?php echo htmlspecialchars($perfume['nome']); ?></h2>
                        <p class="marca"><?php echo htmlspecialchars($perfume['marca']); ?></p>
                        <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                        </div>
                </a>
            </div>
        <?php endforeach; ?>
    </section>

    <script>
        const items = document.querySelectorAll('.fragrancia-item');
        items.forEach(item => {
            const caminhoImagemOriginal = item.querySelector('img').src;
            const caminhoImagemAlternativa = item.getAttribute('caminho_imagem_hover');
            item.addEventListener('mouseover', () => {
                item.querySelector('img').src = caminhoImagemAlternativa;
            });
            item.addEventListener('mouseout', () => {
                item.querySelector('img').src = caminhoImagemOriginal;
            });
        });
    </script>
</body>
</html>
