<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados

// Obtém a quantidade de itens no carrinho do usuário logado
$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
$totalCarrinho = $id_usuario ? contarItensCarrinho($id_usuario) : 0;

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


// Define a variável de controle
$mostrar_carrinho = true;

// Verifica se o usuário está logado
if (isset($_SESSION['id_user'])) {
    $tipo_usuario = verificarTipoUsuario($_SESSION['id_user']); // Obtém o tipo do usuário

    // Se for admin ou trabalhador, oculta o carrinho
    if ($tipo_usuario === "admin" || $tipo_usuario === "trabalhador") {
        $mostrar_carrinho = false;
    }
}

// Verifica se o ID da marca foi passado
if (!isset($_GET['id'])) {
    die('Marca não especificada.');
}

// Obter o ID da marca
$id_marca = intval($_GET['id']);
// Obter os detalhes da marca
$marca = buscarInformacoesMarca($id_marca);
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

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>

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