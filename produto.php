<?php
include('config.php'); // Inclui a configuração da base de dados e as funções

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
        <div class="logo">
            <a href="index.php">LuxFragrance</a>
        </div>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li>Discovery Kit</li>
            <li class="dropdown">
                <a href="#">Marcas</a>
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
            </li>
            <li class="dropdown">
                <a href="#">Famílias Olfativas</a>
                <div class="dropdown-content">
                    <?php
                    //$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas
                    if (!empty($familias)): ?>
                        <div class="column">
                            <?php foreach ($familias as $familia): ?>
                                <p>
                                    <a href="familia.php?id=<?php echo htmlspecialchars($familia['id_familia']); ?>">
                                        <?php echo htmlspecialchars($familia['nome_familia']); ?>
                                    </a>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="column">
                            <p>Nenhuma família olfativa disponível.</p>
                        </div>
                    <?php endif; ?>
                    <div class="view-all">
                        <a href="todas_familias.php">Ver todas as famílias</a>
                    </div>
                </div>
            </li>
            <li>Categorias</li>
            <li>Sobre Nós</li>
            <li>Contactos</li>
            <li>
                <img src="icones/carrinho.png" alt="Carrinho de compras"
                    style="width: 20px; vertical-align: middle; margin-right: 8px;">
                <a href="carrinho.php"></a>
            </li>
            <li>
                <img src="">
                <a href="pesquisa.php"></a>
            </li>
        </ul>
    </nav>

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
                        <button id="prev"><</button>
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

                        <?php if (!empty($perfume['notas']['coração'])): ?>
                            <div class="nota">
                                <button class="nota-titulo">
                                    <img src="icones/notes.jpg" alt="Ícone de notas">
                                    Notas de coração
                                </button>
                                <div class="nota-conteudo">
                                    <p><?php echo implode(", ", $perfume['notas']['coração']); ?></p>
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
                </div>
            </div>
        </div>
    </section>
    <script>let slider = document.querySelector('.slider .list');
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

    <script></script>
</body>

</html>