<?php
include('config.php');

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_SESSION['id_user'];
    $id_produto = (int) ($_POST['id_produto'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    if ($id_produto <= 0 || $quantidade <= 0) {
        $_SESSION['erro'] = "Produto ou quantidade inválida.";
        header("Location: produto.php?id=$id_produto");
        exit();
    }

    // Usa a nova função para verificar o stock
    $produto = verificarStockProduto($id_produto);

    if (!$produto || $produto['stock'] < $quantidade) {
        $_SESSION['erro'] = "Estoque insuficiente!";
        header("Location: produto.php?id=$id_produto");
        exit();
    }

    adicionarAoCarrinho($id_usuario, $id_produto, $quantidade);

    $_SESSION['sucesso'] = "Produto adicionado ao carrinho!";
    header("Location: carrinho.php");
    exit();
}

?>
    