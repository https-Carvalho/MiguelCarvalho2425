<?php

$host = 'root'; 
$user = 'root';  
$password = '';  
$dbname = 'perfumes_nicho';  

// Conectar à base de dados
$liga = mysqli_connect($host, $user, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if (!$liga) {
    die("Erro na conexão à base de dados: " . mysqli_connect_error());
}

// Função para listar perfumes
function listarPerfumes() {
    global $liga;  // Usar a conexão global

    $sql = "SELECT perfumes.nome, perfumes.preco, perfumes.caminho_imagem, perfumes.estacao, marcas.nome AS marca
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

function buscarDetalhesPerfume($idPerfume) {
    global $liga;

    // Consulta para obter os detalhes do perfume e da marca associada
    $sql = "SELECT perfumes.nome, perfumes.descricao, perfumes.preco, perfumes.caminho_imagem, perfumes.estacao, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id
            WHERE perfumes.id = ?";
    
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $perfume = mysqli_fetch_assoc($result);

    // Verifica se o perfume foi encontrado
    if ($perfume) {
        // Nova consulta para buscar as imagens adicionais
        $sqlImagens = "SELECT caminho_imagem FROM imagens_perfume WHERE perfume_id = ?";
        $stmtImagens = mysqli_prepare($liga, $sqlImagens);
        mysqli_stmt_bind_param($stmtImagens, 'i', $idPerfume);
        mysqli_stmt_execute($stmtImagens);
        $resultImagens = mysqli_stmt_get_result($stmtImagens);

        // Armazena todas as imagens adicionais em um array
        $imagens = [];
        while ($imagem = mysqli_fetch_assoc($resultImagens)) {
            $imagens[] = $imagem['caminho_imagem'];
        }

        // Adiciona o array de imagens adicionais ao array principal do perfume
        $perfume['imagens_adicionais'] = $imagens;
    }

    return $perfume;
}
