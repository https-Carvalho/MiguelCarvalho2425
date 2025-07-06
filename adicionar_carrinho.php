<?php
session_start();
include("config.php");

$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $_SESSION['tipo_utilizador'] ?? null;

if ($tipo_utilizador !== 'cliente' || !$id_sessao) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['id_produto'])) {
    $id_produto = (int) $_POST['id_produto'];
    $quantidade = (int) ($_POST['quantidade'] ?? 1);

    $produto = verificarStockProduto($id_produto);
    if (!$produto || $produto['stock'] < $quantidade) {
        $_SESSION['erro'] = "Estoque insuficiente!";
        header("Location: produto.php?id=$id_produto");
        exit;
    }

    adicionarAoCarrinho($id_sessao, $id_produto, $quantidade);
    $_SESSION['sucesso'] = "Produto adicionado ao carrinho!";
    header("Location: carrinho.php");
    exit;
}
?>
