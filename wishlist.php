<?php
session_start();
include('config.php'); // Conexão com a base de dados

// Verifica se o usuário está logado
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}


$id_usuario = $_SESSION['id_user'];
$wishlist = buscarWishlist($id_usuario); // Função que busca os itens da wishlist do usuário

$totalCarrinho = isset($_SESSION['id_user']) ? contarItensCarrinho($_SESSION['id_user']) : 0;

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
                            <td class="wishlist-produto-info">
                                <img src="<?php echo htmlspecialchars($item['caminho_imagem']); ?>" class="wishlist-imagem">
                                <div class="wishlist-nome"><?php echo htmlspecialchars($item['nome']); ?></div>
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