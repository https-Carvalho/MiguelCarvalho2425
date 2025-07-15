<?php
session_start();
include('config.php');

// Verifica se está autenticado como cliente
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';

if (!$id_sessao || $tipo_utilizador !== 'cliente') {
    header("Location: login.php");
    exit;
}

$id_cliente = $id_sessao;
$itens = buscarItensCarrinho($id_cliente);
$total = 0;

// Calcula o total da encomenda
foreach ($itens as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

$total = number_format($total, 2, '.', '');

// Cria a encomenda
$id_encomenda = criarEncomenda($id_cliente, $total);

foreach ($itens as $item) {
    adicionarProdutoEncomenda(
        $id_encomenda,
        $item['id_produto'],
        $item['quantidade'],
        $item['preco']
    );
}

// Redireciona para o PayPal Sandbox
$query = http_build_query([
    'cmd' => '_xclick',
    'business' => 'luxfragrances0@gmail.com',
    'item_name' => 'Encomenda LuxFragrance',
    'amount' => $total,
    'currency_code' => 'EUR',
    'return' => 'http://localhost/MiguelCarvalho2425/sucesso.php?id_encomenda=' . $id_encomenda,
    'cancel_return' => 'http://localhost/MiguelCarvalho2425/carrinho.php',
    'notify_url' => 'http://localhost/MiguelCarvalho2425/ipn.php'
]);

header("Location: https://www.sandbox.paypal.com/cgi-bin/webscr?" . $query);
exit;
