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
        <ul>
            <li><a href="index.php">Início</a></li>
            <li>Discovery Kit</li>
            <li class="dropdown">
                <a href="#">Marcas ▼</a>
                <div class="dropdown-content">
                    <?php foreach ($marcas as $inicial => $grupoMarcas): ?>
                        <div class="brands-column">
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
                                    <img src="images/notes.jpg" alt="Ícone de notas">
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
                                    <img src="images/notes.jpg" alt="Ícone de notas">
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
                                    <img src="images/notes.jpg" alt="Ícone de notas">
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

    <script src="notas.js"></script>
    <script src="slide.js"></script>
</body>

</html>