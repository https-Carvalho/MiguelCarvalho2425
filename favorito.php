<?php
session_start();
include('config.php'); // Conexão com a BD

$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo = $_SESSION['tipo_utilizador'] ?? null;

if (!$id_sessao || !in_array($tipo, ['cliente', 'Admin', 'trabalhador'])) {
    echo json_encode(["error" => "Utilizador não autenticado"]);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produto'], $_POST['acao'])) {
    $id_produto = (int) $_POST['id_produto'];
    $acao = $_POST['acao'];

    if ($acao === "adicionar") {
        if (adicionarAosFavoritos($id_sessao, $id_produto, $tipo)) {
            echo json_encode(["success" => true, "estado" => "remover", "novoTexto" => "❤️ Remover dos Favoritos"]);
        } else {
            echo json_encode(["error" => "Já está nos favoritos ou falha ao adicionar"]);
        }
    } elseif ($acao === "remover") {
        if (removerDosFavoritos($id_sessao, $id_produto, $tipo)) {
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
