<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'], $_POST['acao'])) {
    $id_usuario = $_SESSION['id_user'];
    $id_produto = (int) $_POST['id_produto'];
    $acao = $_POST['acao'];

    if ($acao === "aumentar") {
        atualizarQuantidadeCarrinho($id_usuario, $id_produto, 1);
    } elseif ($acao === "diminuir") {
        atualizarQuantidadeCarrinho($id_usuario, $id_produto, -1);
    }

    // Busca novamente os itens atualizados no carrinho
    $itensCarrinho = buscarItensCarrinho($id_usuario);
    $totalCompra = array_sum(array_map(function ($item) {
        return $item['preco'] * $item['quantidade'];
    }, $itensCarrinho));

    $novoSubtotal = 0;
    $itemRemovido = false;
    foreach ($itensCarrinho as $item) {
        if ($item['id_produto'] == $id_produto) {
            $novaQuantidade = $item['quantidade'];
            $novoSubtotal = $item['preco'] * $item['quantidade'];
            break;
        }
    }

    if (!isset($novaQuantidade)) {
        // O item foi removido do carrinho
        $itemRemovido = true;
    }

    echo json_encode([
        "success" => true,
        "novaQuantidade" => $novaQuantidade ?? 0,
        "novoSubtotal" => number_format($novoSubtotal, 2, ',', ' '),
        "totalCompra" => number_format($totalCompra, 2, ',', ' '),
        "itemRemovido" => $itemRemovido
    ]);
    exit();
} else {
    echo json_encode(["error" => "Requisição inválida"]);
    exit();
}
?>