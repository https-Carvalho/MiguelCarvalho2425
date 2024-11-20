<?php

$host = 'localhost';
$user = 'root';  // Nome de utilizador 
$password = '';  // Senha
$dbname = 'perfumes_nicho';  // Nome da base de dados

// Conectar à base de dados
$liga = mysqli_connect($host, $user, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if (!$liga) {
    die("Erro na conexão à base de dados: " . mysqli_connect_error());
}

// Função para listar perfumes
function listarPerfumes()
{
    global $liga;  // Usar a conexão global

    $sql = "SELECT perfumes.id, perfumes.nome, perfumes.preco, perfumes.caminho_imagem, perfumes.caminho_imagem_hover, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id";
    $result = mysqli_query($liga, $sql);  // Usar a função mysqli_query com a conexão existente

    $perfumes = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $perfumes[] = $row;
        }
    }

    return $perfumes;
}



 function buscarInformacoesPerfume($idPerfume) {
    global $liga;

    $sql = "SELECT perfumes.id, perfumes.nome, perfumes.descricao, perfumes.preco, perfumes.caminho_imagem, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id
            WHERE perfumes.id = ?";
    
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $perfume = mysqli_fetch_assoc($result);

    return $perfume; // Retorna apenas as informações do perfume
}

function buscarImagensPerfume($idPerfume) {
    global $liga;

    $sql = "SELECT caminho_imagem FROM imagens_perfume WHERE perfume_id = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $imagens = [];
    while ($imagem = mysqli_fetch_assoc($result)) {
        $imagens[] = $imagem['caminho_imagem'];
    }

    return $imagens; // Retorna apenas as imagens
}


