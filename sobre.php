<?php
session_start();
include('config.php'); // Configurações da base de dados

// Autenticação e identificação do utilizador
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';

// Carrinho só para cliente
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas();

// Se for pedido AJAX para a barra de pesquisa
// Função de pesquisa AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $termo = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
    $perfumes = listarPerfumes($termo);

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

    exit; // Encerra para não carregar o resto do HTML
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Sobre Nós - Fragrâncias Nicho</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <?php include('menu.php'); ?>

    <div class="pagina-estatica">
        <h1>Sobre Nós</h1>
        <p>
            Bem-vindo à nossa loja de fragrâncias nicho. Somos apaixonados por perfumes únicos, autênticos e sofisticados. Acreditamos que cada fragrância conta uma história e desperta emoções distintas.
        </p>
        <p>
            Trabalhamos com marcas exclusivas que priorizam a qualidade dos ingredientes e a criatividade das composições. O nosso compromisso é oferecer uma seleção curada de perfumes para quem procura algo verdadeiramente especial.
        </p>
        <p>
            A nossa equipa está sempre disponível para te ajudar a encontrar a fragrância ideal, seja para uso pessoal ou para oferecer. Explora, descobre e deixa-te levar pelo mundo das fragrâncias.
        </p>
        <p><strong>Obrigado por confiares em nós.</strong></p>
    </div>

    <!-- Script de pesquisa AJAX -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const input = document.getElementById("barra-pesquisa");
            const resultados = document.getElementById("resultados-pesquisa");

            if (input && resultados) {
                input.addEventListener("input", function () {
                    const termo = this.value;
                    if (termo.length < 2) {
                        resultados.innerHTML = "";
                        return;
                    }

                    fetch(`?ajax=1&q=${encodeURIComponent(termo)}`)
                        .then(response => response.text())
                        .then(data => {
                            resultados.innerHTML = data;
                        });
                });
            }
        });
    </script>
</body>
</html>
