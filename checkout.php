<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_user'];
$itens = buscarItensCarrinho($id_usuario);
$total = 0;

foreach ($itens as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

$total = number_format($total, 2, '.', '');

// Cria encomenda com total incluído
$id_encomenda = criarEncomenda($id_usuario, $total);
foreach ($itens as $item) {
    adicionarProdutoEncomenda(
        $id_encomenda,
        $item['id_produto'],
        $item['quantidade'],
        $item['preco']
    );
}


// Redireciona para PayPal Sandbox
$query = http_build_query([
    'cmd' => '_xclick',
    'business' => 'carvalhomiguel319@gmail.com',
    'item_name' => 'Encomenda LuxFragrance',
    'amount' => $total,
    'currency_code' => 'EUR',
    'return' => 'http://localhost/MiguelCarvalho2425/sucesso.php?id_encomenda=' . $id_encomenda,
    'cancel_return' => 'http://localhost/MiguelCarvalho2425/carrinho.php',
    'notify_url' => 'http://localhost/MiguelCarvalho2425/ipn.php'
]);

header("Location: https://www.sandbox.paypal.com/cgi-bin/webscr?" . $query);
exit();
