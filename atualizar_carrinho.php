<?php
session_start();
include("config.php");

$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $_SESSION['tipo_utilizador'] ?? null;

if ($tipo_utilizador !== 'cliente' || !$id_sessao) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'], $_POST['acao'])) {
    $id_produto = (int) $_POST['id_produto'];
    $acao = $_POST['acao'];

    if ($acao === "aumentar") {
        atualizarQuantidadeCarrinho($id_sessao, $id_produto, 1);
    } elseif ($acao === "diminuir") {
        atualizarQuantidadeCarrinho($id_sessao, $id_produto, -1);
    } elseif ($acao === "remover") {
        removerDoCarrinho($id_sessao, $id_produto);
    }

    $itensCarrinho = buscarItensCarrinho($id_sessao);
    $totalCompra = array_sum(array_map(fn($i) => $i['preco'] * $i['quantidade'], $itensCarrinho));
    $novoSubtotal = 0;
    $itemRemovido = true;

    foreach ($itensCarrinho as $item) {
        if ($item['id_produto'] == $id_produto) {
            $itemRemovido = false;
            $novoSubtotal = $item['preco'] * $item['quantidade'];
            $novaQuantidade = $item['quantidade'];
            break;
        }
    }

    echo json_encode([
        "success" => true,
        "novaQuantidade" => $novaQuantidade ?? 0,
        "novoSubtotal" => number_format($novoSubtotal, 2, ',', ' '),
        "totalCompra" => number_format($totalCompra, 2, ',', ' '),
        "itemRemovido" => $itemRemovido
    ]);
    exit;
}
?>
