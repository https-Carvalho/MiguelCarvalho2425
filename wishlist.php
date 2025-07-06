<?php
session_start();
include('config.php'); // Conexão com a base de dados

// ⚠️ Apenas permite clientes
if (!isset($_SESSION['id_sessao']) || $_SESSION['tipo_utilizador'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

$id_sessao = $_SESSION['id_sessao'];
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
if ($tipo_utilizador == 'cliente') {
    $id_cliente = $id_sessao;
}
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

// Garante que é mesmo cliente
if ($tipo_utilizador !== 'cliente') {
    header("Location: index.php");
    exit();
}
$wishlist = buscarWishlist($id_sessao, $tipo_utilizador);

$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Wishlist</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>
    <div class="wishlist-container">
        <h1>Minha Wishlist</h1>
        <?php if (!empty($wishlist)): ?>
            <table class="wishlist-tabela">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço Unitário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wishlist as $item): ?>
                        <tr>
                            <td>
                                <div class="wishlist-produto-info">
                                    <img src="<?php echo htmlspecialchars($item['caminho_imagem']); ?>" class="wishlist-imagem">
                                    <div class="wishlist-nome"><?php echo htmlspecialchars($item['nome']); ?></div>
                                </div>
                            </td>
                            <td class="wishlist-preco"><?php echo number_format($item['preco'], 2, ',', ' ') . ' €'; ?></td>
                            <td class="wishlist-acoes">
                                <button class="btn-wishlist-adicionar" data-id="<?php echo $item['id_perfume']; ?>">Adicionar ao
                                    Carrinho</button>
                                <button class="btn-wishlist-remover" data-id="<?php echo $item['id_perfume']; ?>">X</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="wishlist-vazia">Sua wishlist está vazia.</p>
        <?php endif; ?>
    </div>



    <!-- Script para Remover da Wishlist e Adicionar ao Carrinho -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Remover item da Wishlist
            document.querySelectorAll(".btn-wishlist-remover").forEach(button => {
                button.addEventListener("click", function () {
                    let idProduto = this.dataset.id;

                    fetch("wishlist_acao.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `acao=remover&id_produto=${idProduto}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload(); // Recarrega a página para atualizar a lista
                            } else {
                                console.error(data.error);
                            }
                        });
                });
            });

            // Adicionar item ao Carrinho
            document.querySelectorAll(".btn-wishlist-adicionar").forEach(button => {
                button.addEventListener("click", function () {
                    let idProduto = this.dataset.id;

                    fetch("carrinho_acao.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: `acao=adicionar&id_produto=${idProduto}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("Produto adicionado ao carrinho!");
                            } else {
                                console.error(data.error);
                            }
                        });
                });
            });
        });
    </script>
</body>

</html>