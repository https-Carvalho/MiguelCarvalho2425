<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados e as funções

$totalCarrinho = isset($_SESSION['id_user']) ? contarItensCarrinho($_SESSION['id_user']) : 0;

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $termo = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
    $perfumes = listarPerfumes($termo);

    // Gera os resultados como HTML
    if (!empty($perfumes)): ?>
        <?php foreach ($perfumes as $perfume): ?>
            <a href="produto.php?id=<?php echo $perfume['id_perfume']; ?>" class="result-item">
                <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>"
                    alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                <div class="info">
                    <h3><?php echo htmlspecialchars($perfume['nome']); ?></h3>
                    <p><?php echo htmlspecialchars($perfume['marca']); ?></p>
                    <p><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum resultado encontrado.</p>
    <?php endif;

    exit; // Encerra a execução para evitar renderizar o restante do HTML
}

// Obtém as marcas agrupadas em ordem alfabética
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas
$marcas = buscarMarcasAgrupadas();

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcas - Perfumes Nicho</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="<?php echo strtolower($tipo_usuario); ?>">
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
                                <?php foreach ($grupoMarcas as $marca): ?>
                                    <p>
                                        <a href="marca.php?id=<?php echo htmlspecialchars($marca['id_marca']); ?>">
                                            <?php echo htmlspecialchars($marca['nome']); ?>
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
                <a href="carrinho.php" class="carrinho-link">
                    <img src="icones/carrinho.png" alt="Carrinho de compras"
                        style="width: 20px; vertical-align: middle; margin-right: 8px;">
                    <?php if ($totalCarrinho > 0): ?>
                        <span class="carrinho-count"><?php echo $totalCarrinho; ?></span>
                    <?php endif; ?>
                </a>
            </li>


            <li class="user-dropdown">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <button class="user-btn">
                        <img src="icones/user.png" alt="Perfil"
                            style="width: 20px; vertical-align: middle; margin-right: 8px;">
                        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </button>
                    <div class="dropdown-content-user">
                        <a href="perfil.php">Meu Perfil</a>
                        <a href="logout.php">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="user-btn">
                        <img src="icones/user.png" alt="Login"
                            style="width: 20px; vertical-align: middle; margin-right: 8px;">
                        <span>Entrar</span>
                    </a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>


    <!-- Cabeçalho -->
    <header class="marca-header">
        Marcas
    </header>

    <!-- Conteúdo da página -->
    <main>
        <section class="marcas-lista">
            <div class="marcas-container">
                <?php
                // Exibe todas as marcas sem agrupar por inicial
                $marcas = buscarMarcasAgrupadas();
                foreach ($marcas as $inicial => $nomes): ?>
                    <?php foreach ($nomes as $marca): ?>
                        <div class="marca-item">
                            <a href="marca.php?id=<?php echo $marca['id_marca']; ?>">
                                <div class="marca-box">
                                    <div class="marca-image">
                                        <img src="<?php echo htmlspecialchars($marca['caminho_imagem']); ?>"
                                            alt="<?php echo htmlspecialchars($marca['nome']); ?>">
                                    </div>
                                    <h4 class="marca-nome"><?php echo htmlspecialchars($marca['nome']); ?></h4>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <!-- Script de Pesquisa Dinâmica -->
    <script>
        document.getElementById('searchInput').addEventListener('input', function () {
            const query = this.value.trim();
            const searchResults = document.getElementById('searchResults');

            if (query.length > 0) {
                fetch(`?ajax=1&q=${encodeURIComponent(query)}`)
                    .then(response => response.text())
                    .then(data => {
                        searchResults.innerHTML = data;
                    })
                    .catch(error => console.error('Erro na pesquisa:', error));
            } else {
                searchResults.innerHTML = ''; // Limpa os resultados
            }
        });
    </script>
</body>

</html>