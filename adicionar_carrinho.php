<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_usuario = $_SESSION['id_user'];
    $id_produto = isset($_POST['id_produto']) ? (int)$_POST['id_produto'] : 0;
    $quantidade = isset($_POST['quantidade']) ? (int)$_POST['quantidade'] : 1;

    if ($id_produto <= 0 || $quantidade <= 0) {
        $_SESSION['erro'] = "Produto ou quantidade inválida.";
        header("Location: produto.php?id=$id_produto");
        exit();
    }

    // Verifica o estoque do produto
    $sql = "SELECT stock FROM perfumes WHERE id_perfume = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_produto' => $id_produto]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto || $produto['stock'] < $quantidade) {
        $_SESSION['erro'] = "Estoque insuficiente!";
        header("Location: produto.php?id=$id_produto");
        exit();
    }

    // Chama a função para adicionar ao carrinho
    adicionarAoCarrinho($id_usuario, $id_produto, $quantidade);

    $_SESSION['sucesso'] = "Produto adicionado ao carrinho!";
    header("Location: carrinho.php");
    exit();
}
?>
    