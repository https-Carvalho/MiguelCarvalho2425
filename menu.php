<nav class="menu">
    <div class="logo">
        <a href="index.php">LuxFragrance</a>
    </div>
    <ul>
        <li><a href="index.php">Início</a></li>
        <li><a href="discoveryKit.php">Discovery Kit</a></li>
        <li class="dropdown">
            <a href="#" class="trigger">Marcas</a>
            <div class="dropdown-content_under">
                <div class="dropdown-content">
                    <div class="view-all">
                        <a href="todas_marcas.php">Ver todas as marcas</a>
                    </div>
                    <?php foreach ($marcas as $inicial => $grupoMarcas): ?>
                        <div class="column">
                            <h3><?php echo htmlspecialchars($inicial); ?></h3>
                            <?php foreach ($grupoMarcas as $item_marca): ?>
                                <p>
                                    <a href="marca.php?id=<?php echo htmlspecialchars($item_marca['id_marca']); ?>">
                                        <?php echo htmlspecialchars($item_marca['nome']); ?>
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
                        <?php foreach ($familias as $item_familia): ?>
                            <div class="column">
                                <p>
                                    <a class="familia"
                                        href="familia.php?id=<?php echo htmlspecialchars($item_familia['id_familia']); ?>">
                                        <?php echo htmlspecialchars($item_familia['nome_familia']); ?>
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
        <li><a href="sobre.php">Sobre Nós</a></li>

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

        <!-- Carrinho -->
        <?php if ($mostrar_carrinho): ?>
            <li class="carrinho-menu">
                <a href="carrinho.php" class="carrinho-link">
                    <img src="icones/carrinho.png" alt="Carrinho"
                        style="width: 20px; vertical-align: middle; margin-right: 8px;">
                    <?php if ($totalCarrinho > 0): ?>
                        <span class="carrinho-count"><?php echo $totalCarrinho; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endif; ?>

        <!-- Perfil do usuário -->
        <li class="user-dropdown">
            <?php if ($id_sessao && $tipo_utilizador !== 'visitante'): ?>
                <button class="user-btn">
                    <img src="icones/user.png" alt="Perfil" style="width: 20px; margin-right: 8px;">
                    <span><?php echo htmlspecialchars($nome_utilizador); ?></span>
                </button>
                <div class="dropdown-content-user">
                    <a href="<?php echo ($tipo_utilizador === 'cliente') ? 'perfil.php' : 'dashboard/dashboard.php'; ?>">Meu
                        Perfil</a>
                    <?php if ($tipo_utilizador === 'cliente'): ?>
                        <a href="wishlist.php">Minha Wishlist</a>
                    <?php endif; ?>
                    <a href="logout.php">Sair</a>
                </div>
            <?php else: ?>
                <a href="login.php">
                    <div class="user-btn">
                        <img src="icones/user.png" alt="Login" style="width: 20px; margin-right: 8px;">
                        <span>Entrar</span>
                    </div>
                </a>
            <?php endif; ?>
        </li>
    </ul>
</nav>

<!-- Script de Pesquisa Dinâmica -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
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
    });
</script>