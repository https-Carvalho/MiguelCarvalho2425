<?php
include('config.php'); // Inclui a configuração da base de dados e as funções

// Obtém o ID do produto a partir da URL
$idPerfume = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Busca as informações e imagens do perfume
$perfume = buscarInformacoesPerfume($idPerfume);
$imagensPerfume = buscarImagensPerfume($idPerfume);

// Verifica se o perfume foi encontrado
if (!$perfume) {
    echo "<h1>Produto não encontrado.</h1>";
    exit;
}

// Verifica se a quantidade de imagens é menor que 1
if (count($imagensPerfume) < 1) {
    echo "<h1>Não há imagens disponíveis para este produto.</h1>";
    exit;
}

if (empty($imagensPerfume)) {
    $imagensPerfume = ['images/default.jpg']; // Imagem padrão caso não haja imagens
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
        <div class="produto-container">
            <!-- Slider -->
            <div class="slider">
                <div class="list">
                    <?php foreach ($imagensPerfume as $imagem): ?>
                        <div class="item">
                            <img src="<?php echo htmlspecialchars($imagem); ?>">
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

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                inicializarSlider();
                });
            </script>

            <div class="separator"></div>

            <!-- Informações do Produto -->
            <div class="descricao-produto">
                <h1><?php echo htmlspecialchars($perfume['nome']); ?></h1>
                <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                <p class="descricao"><?php echo htmlspecialchars($perfume['descricao']); ?></p>
                <p class="marca">Marca: <?php echo htmlspecialchars($perfume['marca']); ?></p>
            </div>
        </div>
    </section>

</body>

</html>