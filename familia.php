<?php
include 'config.php';

// Atribui a família dominante aos perfumes
atribuirFamiliaDominante();

// Obtém o ID da família da query string
$id_familia = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verifica se o ID da família foi passado na URL e é válido
if ($id_familia > 0) {
    // Chama a função para buscar os detalhes da família
    $familia = buscarDetalhesFamilia($id_familia);

    // Chama a função para buscar os perfumes associados à família
    $resultPerfumes = buscarPerfumesPorFamilia($id_familia);
} else {
    // Se não for um ID válido, redireciona para a página inicial
    header("Location: index.php");
    exit;
}

$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas
$marcas = buscarMarcasAgrupadas();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($familia['nome_familia']); ?> - Famílias Olfativas</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="menu">
        <div class="logo">
            <a href="index.php">LuxFragrance</a>
        </div>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li>Discovery Kit</li>
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
                        <?php
                        // Verificando se $familias é um array e não está vazio
                             foreach ($familias as $family): ?>
                                <div class="column">
                                    <p>
                                        <a class="familia"
                                            href="familia.php?id=<?php echo htmlspecialchars($family['id_familia']); ?>">
                                            <?php echo htmlspecialchars($family['nome_familia']); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endforeach;?>
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

    <!-- Cabeçalho -->
    <header class="familia-header">
        <?php echo htmlspecialchars($familia['nome_familia']); ?>
    </header>

    <main>
        <section class="lista-fragrancias">
            <?php if (mysqli_num_rows($resultPerfumes) > 0): ?>
                <?php while ($perfume = mysqli_fetch_assoc($resultPerfumes)): ?>
                    <div class="fragrancia-item"
                        caminho_imagem_hover="<?php echo htmlspecialchars($perfume['caminho_imagem_hover'] ?? ''); ?>">
                        
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
                                <p class="marca"><?php echo htmlspecialchars($perfume['nome_marca'] ?? 'Marca Desconhecida'); ?>
                                </p>
                                <p class="preco">
                                    <?php
                                    echo isset($perfume['preco'])
                                        ? number_format($perfume['preco'], 2, ',', ' ') . ' €'
                                        : 'Preço Indisponível';
                                    ?>
                                </p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum perfume disponível nesta família olfativa.</p>
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