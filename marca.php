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
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas

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
            <li> <a href="discoveryKit.php">Discovery Kit</li>
            <li class="dropdown">
                <a href="#">Marcas</a>
                <div class="dropdown-content_under">
                    <div class="dropdown-content">
                        <div class="view-all">
                            <a href="todas_marcas.php">Ver todas as marcas</a>
                        </div>
                        <?php foreach ($marcas as $inicial => $grupoMarcas): ?>
                            <div class="column">
                                <h3><?php echo htmlspecialchars($inicial); ?></h3>
                                <?php foreach ($grupoMarcas as $marcas): ?>
                                    <p>
                                        <a href="marca.php?id=<?php echo htmlspecialchars($marcas['id_marca']); ?>">
                                            <?php echo htmlspecialchars($marcas['nome']); ?>
                                        </a>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </li>
            <li class="dropdown">
                <a href="#">Famílias Olfativas</a>
                <div class="dropdown-content_under">
                    <div class="dropdown-content">
                        <?php if (!empty($familias)): ?>
                            <?php foreach ($familias as $familia): ?>
                                <div class="column">
                                    <p>
                                        <a class="familia"
                                            href="familia.php?id=<?php echo htmlspecialchars($familia['id_familia']); ?>">
                                            <?php echo htmlspecialchars($familia['nome_familia']); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="column">
                                <p>Nenhuma família olfativa disponível no momento.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
            <li>Categorias</li>
            <li>Sobre Nós</li>


            <!-- Overlay de Pesquisa -->
            <input type="checkbox" id="toggleSearch" style="display: none;">
            <li>
                <label for="toggleSearch">
                    <img src="icones/pesquisa.png" alt="Pesquisa"
                        style="width: 20px; vertical-align: middle; margin-right: 8px; cursor: pointer;">
                </label>
            </li>
            <div id="searchOverlay">
                <label for="toggleSearch" id="closeSearch">&times;</label>
                <div class="search-content">
                    <h2>O que você quer procurar?</h2>
                    <input type="text" id="searchInput" placeholder="Start typing...">
                    <div id="searchResults"></div>
                </div>
            </div>


            <li>
                <img src="icones/carrinho.png" alt="Carrinho de compras"
                    style="width: 20px; vertical-align: middle; margin-right: 8px;">
                <a href="carrinho.php"></a>
            </li>
        </ul>
    </nav>

    <header class="marca-header">
        <div class="marca-banner"
            style="background-image: url('<?php echo htmlspecialchars($marca['caminho_imagem']); ?>');"></div>
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
                        <?php if ($perfume['stock'] == 0): ?>
                            <div class="esgotado-label">Esgotado</div>
                        <?php endif; ?>
                            
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