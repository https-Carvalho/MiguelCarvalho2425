<?php
session_start();
include('config.php');

// garante que nao de para ir o resumo sem ter passado pelo carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_compra'])) {
    $_SESSION['acesso_morada'] = true;
    header("Location: selecionar_morada.php");
    exit;
}

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
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['clientname'] ?? 'Conta';
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

// Garante que é mesmo cliente
if ($tipo_utilizador !== 'cliente') {
    header("Location: index.php");
    exit();
}

// ✅ Aqui sim: usar o nome correto
$itensCarrinho = buscarItensCarrinho($id_cliente);
$totalCarrinho = contarItensCarrinho($id_cliente);
$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas();
?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Meu Carrinho</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>

    <div class="carrinho-container">
        <h1>Meu Carrinho</h1>
        <?php if (!empty($itensCarrinho)): ?>
            <table class="carrinho-tabela">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                        <th>Remover</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalCompra = 0; ?>
                    <?php foreach ($itensCarrinho as $item): ?>
                        <?php $subtotal = $item['preco'] * $item['quantidade']; ?>
                        <tr data-id="<?php echo $item['id_produto']; ?>">
                            <td class="produto-info">
                                <img src="<?php echo htmlspecialchars($item['caminho_imagem']); ?>" class="produto-imagem">
                                <div class="produto-nome"><?php echo htmlspecialchars($item['nome']); ?></div>
                            </td>
                            <td class="quantidade">
                                <button class="btn-menos" data-id="<?php echo $item['id_produto']; ?>">-</button>
                                <span class="item-qty"
                                    data-id="<?php echo $item['id_produto']; ?>"><?php echo $item['quantidade']; ?></span>
                                <button class="btn-mais" data-id="<?php echo $item['id_produto']; ?>">+</button>
                            </td>
                            <td class="preco-unitario"><?php echo number_format($item['preco'], 2, ',', ' ') . ' €'; ?></td>
                            <td class="subtotal" data-id="<?php echo $item['id_produto']; ?>">
                                <?php echo number_format($subtotal, 2, ',', ' ') . ' €'; ?>
                            </td>
                            <td>
                                <button class="remove-item" data-id="<?php echo $item['id_produto']; ?>">X</button>

                            </td>
                        </tr>
                        <?php $totalCompra += $subtotal; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="checkout-container">
                <div class="checkout-total">
                    <span>Total:</span>
                    <span class="total-valor"><?php echo number_format($totalCompra, 2, ',', ' ') . ' €'; ?></span>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="finalizar_compra" value="1">
                    <button type="submit" class="checkout-button">Finalizar Compra</button>
                </form>
            </div>
        <?php else: ?>
            <p class="carrinho-vazio">Seu carrinho está vazio.</p>
        <?php endif; ?>
    </div>

    <!-- Script de Atualização AJAX -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function atualizarCarrinho(idProduto, acao) {
                fetch("atualizar_carrinho.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `id_produto=${idProduto}&acao=${acao}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let quantidadeSpan = document.querySelector(`.item-qty[data-id="${idProduto}"]`);
                            let subtotalSpan = document.querySelector(`.subtotal[data-id="${idProduto}"]`);
                            let totalSpan = document.querySelector(".total-valor");
                            let itemLinha = document.querySelector(`tr[data-id="${idProduto}"]`);

                            if (data.itemRemovido) {
                                itemLinha.remove();
                            } else {
                                quantidadeSpan.textContent = data.novaQuantidade;
                                subtotalSpan.textContent = data.novoSubtotal + " €";
                            }

                            totalSpan.textContent = data.totalCompra + " €";
                        } else {
                            console.error(data.error);
                        }
                    });
            }

            document.querySelectorAll(".btn-mais").forEach(button => {
                button.addEventListener("click", function () {
                    atualizarCarrinho(this.dataset.id, "aumentar");
                });
            });

            document.querySelectorAll(".btn-menos").forEach(button => {
                button.addEventListener("click", function () {
                    atualizarCarrinho(this.dataset.id, "diminuir");
                });
            });

            document.querySelectorAll(".remove-item").forEach(button => {
                button.addEventListener("click", function () {
                    atualizarCarrinho(this.dataset.id, "remover");
                });
            });
        });
    </script>

</body>

</html>