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
function listarPerfumes(){
    global $liga;  // Usar a conexão globals
    
    $sql = "SELECT perfumes.id_perfume, perfumes.nome, perfumes.preco, perfumes.caminho_imagem, perfumes.caminho_imagem_hover, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id_marca";
    $result = mysqli_query($liga, $sql);  // Usar a função mysqli_query com a conexão existente

    $perfumes = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $perfumes[] = $row;
        }
    }

    return $perfumes;
}


function buscarInformacoesComNotas($idPerfume) {
    global $liga;

    // Query principal para obter detalhes do perfume
    $sql = "SELECT 
                perfumes.id_perfume,
                perfumes.nome,
                perfumes.descricao,
                perfumes.preco,
                perfumes.caminho_imagem,
                marcas.nome AS marca
            FROM 
                perfumes
            JOIN 
                marcas ON perfumes.id_marca = marcas.id_marca
            WHERE 
                perfumes.id_perfume = ?";
    
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $perfume = mysqli_fetch_assoc($result);

    // Query para buscar as notas olfativas organizadas por tipo
    $sqlNotas = "SELECT 
                    notas_olfativas.descricao,
                    notas_olfativas.tipo
                FROM 
                    perfume_notas
                JOIN 
                    notas_olfativas ON perfume_notas.id_notes = notas_olfativas.id_notes
                WHERE 
                    perfume_notas.id_perfume = ?
                ORDER BY 
                    FIELD(notas_olfativas.tipo, 'topo', 'coração', 'base')";
    
    $stmtNotas = mysqli_prepare($liga, $sqlNotas);
    mysqli_stmt_bind_param($stmtNotas, 'i', $idPerfume);
    mysqli_stmt_execute($stmtNotas);
    $resultNotas = mysqli_stmt_get_result($stmtNotas);

    $notas = [
        'topo' => [],
        'coração' => [],
        'base' => []
    ];

    // Organiza as notas por tipo
    while ($nota = mysqli_fetch_assoc($resultNotas)) {
        $notas[$nota['tipo']][] = $nota['descricao'];
    }

    // Adiciona as notas ao array do perfume
    $perfume['notas'] = $notas;

    return $perfume;
}


function buscarImagensPerfume($idPerfume) {
    global $liga;

    $sql = "SELECT caminho_imagem 
            FROM imagens_perfume 
            WHERE perfume_id = ?";
    
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $imagens = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagens[] = $row['caminho_imagem'];
    }

    return $imagens;
}

function buscarMarcasAgrupadas()
{
    global $liga;

    $sql = "SELECT id_marca, nome FROM marcas ORDER BY nome ASC";
    $result = mysqli_query($liga, $sql);

    $marcasAgrupadas = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($marca = mysqli_fetch_assoc($result)) {
            $inicial = strtoupper($marca['nome'][0]); // Obter a inicial do nome da marca
            if (!isset($marcasAgrupadas[$inicial])) {
                $marcasAgrupadas[$inicial] = [];
            }
            $marcasAgrupadas[$inicial][] = [
                'id_marca' => $marca['id_marca'],
                'nome' => $marca['nome']
            ];
        }
    }

    return $marcasAgrupadas;
}


function getMarca($id_marca) {
    global $liga; // Conexão usando mysqli

    $sql = "SELECT nome, descricao, caminho_imagem FROM marcas WHERE id_marca = ?";
    $stmt = mysqli_prepare($liga, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_marca); // Bind do ID da marca
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Retornar os dados da marca se encontrados
        return mysqli_fetch_assoc($result);
    } else {
        return null; // Retornar null em caso de falha
    }
}


// Função para obter os perfumes de uma marca
function getPerfumesPorMarca($id_marca) {
    global $liga; // Usar a conexão mysqli global
    
    $sql = "SELECT p.id_perfume AS id_perfume, p.nome AS nome, p.caminho_imagem, p.caminho_imagem_hover, 
    p.preco, p.id_marca, m.nome AS marca
    FROM perfumes p
    JOIN marcas m ON p.id_marca = m.id_marca
    WHERE p.id_marca = ?";

    
    $stmt = mysqli_prepare($liga, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id_marca); // Bind do ID da marca
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $perfumes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $perfumes[] = $row; // Adicionar cada perfume ao array
        }

        return $perfumes; // Retornar todos os perfumes da marca
    } else {
        return null; // Retornar null em caso de falha
    }
}
