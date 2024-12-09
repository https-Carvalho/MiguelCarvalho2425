<?php
include 'config.php';

// Verifica se o ID da marca foi passado
if (!isset($_GET['id'])) {
    die('Marca não especificada.');
}

// Obter o ID da marca
$id_marca = intval($_GET['id']);

// Obter os detalhes da marca
$marca = getMarca($id_marca);
if (!$marca) {
    die('Marca não encontrada.');
}


// Buscar todas as marcas agrupadas
$marcas = buscarMarcasAgrupadas();
// Obter os perfumes da marca
$perfumes = getPerfumesPorMarca(id_marca: $id_marca);

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
    <div class="logo">
        <a href="index.php">LuxFragrance</a>
    </div>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li>Discovery Kit</li>
            <li class="dropdown">
                <a href="#">Marcas ▼</a>
                <div class="dropdown-content">
                    <?php foreach ($marcas as $inicial => $grupoMarcas): ?>
                        <div class="brands-column">
                            <h3><?php echo htmlspecialchars($inicial); ?></h3>
                            <?php foreach ($grupoMarcas as $marcagrupo): ?>
                                <p>
                                    <a href="marca.php?id=<?php echo htmlspecialchars($marcagrupo['id_marca']); ?>">
                                        <?php echo htmlspecialchars($marcagrupo['nome']); ?>
                                    </a>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="view-all">
                        <a href="todas_marcas.php">Ver todas as marcas</a>
                    </div>
                </div>
            </li>
            <li>Família Olfativa ▼</li>
            <li>Categorias ▼</li>
            <li>Sobre Nós ▼</li>
            <li>Contactos</li>
        </ul>
    </nav>

    <header class="marca-header">
        <div class="marca-banner" style="background-image: url('<?php echo htmlspecialchars($marca['caminho_imagem']); ?>');"></div>
        <div class="marca-descricao">
            <img src="<?php echo htmlspecialchars($marca['caminho_imagem']); ?>" alt="">
            <h1><?php echo htmlspecialchars($marca['nome']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($marca['descricao'])); ?></p>
        </div>
    </header>

    <main>
        <section class="lista-fragrancias">
            <?php if (!empty($perfumes)): ?>
                <?php foreach ($perfumes as $perfume): ?>
                    <div class="fragrancia-item"
                        caminho_imagem_hover="<?php echo htmlspecialchars($perfume['caminho_imagem_hover']); ?>">
                        <a href="produto.php?id=<?php echo $perfume['id_perfume']; ?>">
                            <div class="imagem-fragrancia">
                                <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>"
                                    alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                            </div>
                            <div class="informacoes-fragrancia">
                                <h2><?php echo htmlspecialchars($perfume['nome']); ?></h2>
                                <p class="marca"><?php echo htmlspecialchars($perfume['marca']); ?></p>
                                <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum perfume disponível para esta marca.</p>
            <?php endif; ?>
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
    </main>
</body>
</html>