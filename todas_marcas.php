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
    <?php include('menu.php'); ?>

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