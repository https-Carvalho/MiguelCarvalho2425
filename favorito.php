<?php
session_start();
include('config.php'); // Conexão com a BD

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["error" => "Usuário não autenticado"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'], $_POST['acao'])) {
    $id_user = $_SESSION['id_user'];
    $id_produto = (int) $_POST['id_produto'];
    $acao = $_POST['acao'];

    if ($acao === "adicionar") {
        if (adicionarAosFavoritos($id_user, $id_produto)) {
            echo json_encode(["success" => true, "estado" => "remover", "novoTexto" => "❤️ Remover dos Favoritos"]);
        } else {
            echo json_encode(["error" => "Erro ao adicionar aos favoritos"]);
        }
    } elseif ($acao === "remover") {
        if (removerDosFavoritos($id_user, $id_produto)) {
            echo json_encode(["success" => true, "estado" => "adicionar", "novoTexto" => "🤍 Adicionar aos Favoritos"]);
        } else {
            echo json_encode(["error" => "Erro ao remover dos favoritos"]);
        }
    } else {
        echo json_encode(["error" => "Ação inválida"]);
    }
} else {
    echo json_encode(["error" => "Requisição inválida"]);
}
?>
