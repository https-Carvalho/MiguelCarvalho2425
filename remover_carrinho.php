<?php
session_start();
include("config.php");

$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $_SESSION['tipo_utilizador'] ?? null;

if ($tipo_utilizador !== 'cliente' || !$id_sessao) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'])) {
    $id_produto = (int) $_POST['id_produto'];

    if (removerDoCarrinho($id_sessao, $id_produto)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Erro ao remover o item do carrinho"]);
    }
} else {
    echo json_encode(["error" => "Requisição inválida"]);
}
?>
