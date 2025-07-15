<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados e as funções

// Autenticação e identificação do utilizador
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['clientname'] ?? 'Conta';

// Carrinho só para cliente
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);


//funcao de busca
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

// Obtém o ID do produto a partir da URL
$idPerfume = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Busca as informações do perfume, imagens e notas olfativas
$perfume = buscarInformacoesComNotas($idPerfume); // Função com notas organizadas
$imagensPerfume = buscarImagensPerfume($idPerfume); // Busca as imagens adicionais

// Verifica se o perfume foi encontrado
if (!$perfume) {
    echo "<h1>Produto não encontrado.</h1>";
    exit;
}

// Verifica se a quantidade de imagens é menor que 1
if (empty($imagensPerfume)) {
    $imagensPerfume = ['images/default.jpg']; // Imagem padrão caso não haja imagens
}

$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($perfume['nome']); ?> - Detalhes do Produto</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>

    <!-- Detalhes do Produto -->
    <section class="detalhes-produto">
        <div class="produto-layout"> <!-- Div principal para layout flex -->
            <!-- Container exclusivo do slider -->
            <div class="slider-container">
                <div class="slider">
                    <div class="list">
                        <?php foreach ($imagensPerfume as $imagem): ?>
                            <div class="item">
                                <img src="<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do Perfume">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="buttons">
                        <button id="prev">
                            << /button>
                                <button id="next">></button>
                    </div>
                    <ul class="dots">
                        <?php foreach ($imagensPerfume as $key => $imagem): ?>
                            <li class="<?php echo $key === 0 ? 'active' : ''; ?>"></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="separator"></div>
            <!-- Container exclusivo das informações -->
            <div class="descricao-container">
                <div class="descricao-produto">
                    <h1><?php echo htmlspecialchars($perfume['nome']); ?></h1>
                    <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                    <p class="descricao"><?php echo htmlspecialchars($perfume['descricao']); ?></p>
                    <p class="marca">Marca: <?php echo htmlspecialchars($perfume['marca']); ?></p>

                    <!-- Mostrar o stock -->
                    <?php if ($perfume['stock'] > 10): ?>
                        <p class="stock">Em stock: <?php echo $perfume['stock']; ?> unidades.</p>
                    <?php elseif ($perfume['stock'] > 0): ?>
                        <p class="stock" style="color: red;">Apenas <?php echo $perfume['stock']; ?> unidades restantes!</p>
                    <?php else: ?>
                        <p class="stock" style="color: red;">Produto esgotado!</p>
                    <?php endif; ?>

                    <div class="notas-olfativas">
                        <?php if (!empty($perfume['notas']['topo'])): ?>
                            <div class="nota">
                                <button class="nota-titulo">
                                    <img src="icones/notes.jpg" alt="Ícone de notas">
                                    Notas de topo
                                </button>
                                <div class="nota-conteudo">
                                    <p><?php echo implode(", ", $perfume['notas']['topo']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($perfume['notas']['coracao'])): ?>
                            <div class="nota">
                                <button class="nota-titulo">
                                    <img src="icones/notes.jpg" alt="Ícone de notas">
                                    Notas de coração
                                </button>
                                <div class="nota-conteudo">
                                    <p><?php echo implode(", ", $perfume['notas']['coracao']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($perfume['notas']['base'])): ?>
                            <div class="nota">
                                <button class="nota-titulo">
                                    <img src="icones/notes.jpg" alt="Ícone de notas">
                                    Notas de base
                                </button>
                                <div class="nota-conteudo">
                                    <p><?php echo implode(", ", $perfume['notas']['base']); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Botão para adicionar ao carrinho -->
                    <form action="adicionar_carrinho.php" method="POST">
                        <input type="hidden" name="id_produto" value="<?php echo $perfume['id_perfume']; ?>">
                        <input type="number" name="quantidade" value="1" min="1" max="<?php echo $perfume['stock']; ?>">
                        <button type="submit" <?php echo ($perfume['stock'] == 0) ? 'disabled' : ''; ?>>
                            <?php echo ($perfume['stock'] == 0) ? 'Esgotado' : 'Adicionar ao Carrinho'; ?>
                        </button>
                    </form>


                    <?php if ($id_sessao && $tipo_utilizador === 'cliente'):
                        $estadoFavorito = verificarFavorito($id_sessao, $perfume['id_perfume'], $tipo_utilizador) ? 'remover' : 'adicionar';
                        $textoFavorito = ($estadoFavorito === 'remover') ? '❤️ Remover dos Favoritos' : '🤍 Adicionar aos Favoritos';
                        ?>
                        <button class="favorito-btn" data-id="<?php echo $perfume['id_perfume']; ?>"
                            data-estado="<?php echo $estadoFavorito; ?>">
                            <?php echo $textoFavorito; ?>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="favorito-btn">🤍 Entrar para Favoritar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- slider -->
    <script>
        let slider = document.querySelector('.slider .list');
        let items = document.querySelectorAll('.slider .list .item');
        let next = document.getElementById('next');
        let prev = document.getElementById('prev');
        let dots = document.querySelectorAll('.slider .dots li');

        let active = 0; // Índice da imagem atual
        let lengthItems = items.length;

        // Função para mudar o slider
        function mudarSlide(index) {
            active = index;

            // Move o slider
            slider.style.transform = `translateX(-${active * 100}%)`;

            // Atualiza os *dots*
            dots.forEach(dot => dot.classList.remove('active'));
            dots[active].classList.add('active');
        }

        // Botões de navegação
        next.addEventListener('click', () => {
            active = (active + 1) % lengthItems; // Próximo índice
            mudarSlide(active);
        });

        prev.addEventListener('click', () => {
            active = (active - 1 + lengthItems) % lengthItems; // Índice anterior
            mudarSlide(active);
        });

        // Navegação pelos *dots*
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => mudarSlide(index));
        });

        // Atualiza automaticamente
        setInterval(() => {
            active = (active + 1) % lengthItems;
            mudarSlide(active);
        }, 3000); // Tempo em milissegundos
    </script>

    <!-- Botão das notas -->
    <script>document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.nota-titulo').forEach(button => {
                button.addEventListener('click', () => {
                    const content = button.nextElementSibling;
                    const isOpen = button.classList.contains('active');

                    // Recolher todas as outras
                    document.querySelectorAll('.nota-conteudo').forEach(c => {
                        c.style.display = 'none';
                    });
                    document.querySelectorAll('.nota-titulo').forEach(b => {
                        b.classList.remove('active');
                    });

                    // Expandir o atual se não estiver aberto
                    if (!isOpen) {
                        content.style.display = 'block';
                        button.classList.add('active');
                    }
                });
            });
        });
    </script>

    <!-- script pra adicionar aos favs -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".favorito-btn").forEach(button => {
                button.addEventListener("click", function () {
                    let idProduto = this.dataset.id;
                    let acao = this.dataset.estado;

                    fetch("favorito.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `id_produto=${idProduto}&acao=${acao}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.dataset.estado = data.estado;
                                this.textContent = data.novoTexto;
                            } else {
                                alert(data.error);
                            }
                        })
                        .catch(error => console.error("Erro na requisição:", error));
                });
            });
        });
    </script>
</body>

</html>