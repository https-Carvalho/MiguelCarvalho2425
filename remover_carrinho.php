<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'])) {
    $id_usuario = $_SESSION['id_user'];
    $id_produto = (int) $_POST['id_produto'];

    // Chama a função para remover do carrinho
    if (removerDoCarrinho($id_usuario, $id_produto)) {
        header("Location: carrinho.php"); // Redireciona ao carrinho
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Erro ao remover o item do carrinho"]);
    }
} else {
    echo json_encode(["error" => "Requisição inválida"]);
}
?>

