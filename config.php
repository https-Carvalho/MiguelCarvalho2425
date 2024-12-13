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
function listarPerfumes() {
    global $liga; // Usar a conexão global

    // SQL para selecionar detalhes do perfume e da marca
    $sql = "SELECT perfumes.id_perfume, perfumes.nome, perfumes.preco, perfumes.caminho_imagem, perfumes.caminho_imagem_hover, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id_marca";
    
    $result = mysqli_query($liga, $sql); // Executa a query

    $perfumes = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $perfumes[] = $row; // Adiciona perfume ao array
        }
    }

    return $perfumes; // Retorna o array de perfumes
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
    
    $stmt = mysqli_prepare($liga, query: $sql);
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

function buscarMarcasAgrupadas() {
    global $liga;

    $sql = "SELECT id_marca, nome, descricao, caminho_imagem FROM marcas ORDER BY nome ASC";
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
                'nome' => $marca['nome'],
                'descricao' => $marca['descricao'],
                'caminho_imagem' => $marca['caminho_imagem'] // Inclui o caminho da imagem
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

/*function atribuirFamiliaDominante() {
    global $liga;

    // Obter todos os perfumes
    $sqlPerfumes = "SELECT id_perfume FROM perfumes";
    $resultPerfumes = mysqli_query($liga, $sqlPerfumes);

    if ($resultPerfumes && mysqli_num_rows($resultPerfumes) > 0) {
        while ($row = mysqli_fetch_assoc($resultPerfumes)) {
            $id_perfume = $row['id_perfume'];

            // Determinar a família dominante com base nas notas
            $sqlFamiliaDominante = "
                SELECT f.id_familia
                FROM notas_olfativas n
                JOIN familias_olfativas f ON n.id_familia = f.id_familia
                JOIN perfume_notas pn ON n.id_notes = pn.id_notes
                WHERE pn.id_perfume = $id_perfume
                GROUP BY f.id_familia
                ORDER BY COUNT(n.id_notes) DESC
                LIMIT 1
            ";

            $resultFamilia = mysqli_query($liga, $sqlFamiliaDominante);

            if ($resultFamilia && mysqli_num_rows($resultFamilia) > 0) {
                $familia = mysqli_fetch_assoc($resultFamilia);
                $id_familia = $familia['id_familia'];

                // Atualizar a tabela perfumes com a família dominante
                $sqlUpdate = "
                    UPDATE perfumes
                    SET id_familia = $id_familia
                    WHERE id_perfume = $id_perfume
                ";
                if (mysqli_query($liga, $sqlUpdate)) {
                    echo "Família atribuída ao perfume $id_perfume com sucesso.<br>";
                } else {
                    echo "Erro ao atualizar o perfume $id_perfume: " . mysqli_error($liga) . "<br>";
                }
            }
        }
    } else {
        echo "Nenhum perfume encontrado.<br>";
    }
}*/

/*function buscarFamiliasOlfativas() {
    global $liga; // Usar a conexão global

    // Query para buscar todas as famílias disponíveis
    $sql = "SELECT id_familia, nome_familia FROM familias_olfativas ORDER BY nome_familia ASC";
    $result = mysqli_query($liga, $sql);

    $familias = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $familias[] = $row; // Adiciona a família ao array
        }
    }

    return $familias; // Retorna o array de famílias
}*/
