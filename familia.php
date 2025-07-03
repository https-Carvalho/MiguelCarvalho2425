<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados e a função listarPerfumes

// Obtém a quantidade de itens no carrinho do usuário logado
$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';

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

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>


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