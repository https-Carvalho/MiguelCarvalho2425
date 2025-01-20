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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados com PDO: " . $e->getMessage());
}

// Função para listar perfumes
// Função de pesquisa de perfumes
function listarPerfumes($termo = '')
{
    global $pdo; // Usa a conexão PDO global com a base de dados

    // Query base para buscar perfumes com seus detalhes
    $sql = "SELECT perfumes.id_perfume, perfumes.nome, perfumes.preco, perfumes.caminho_imagem, 
                   perfumes.caminho_imagem_hover, perfumes.stock, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id_marca";

    // Inicializa os parâmetros para evitar erros
    $parametros = [];

    // Se houver um termo de pesquisa, cria a condição WHERE para buscar no nome da marca e do perfume
    if (!empty($termo)) {
        // Divide o termo de pesquisa em palavras individuais
        $termos = explode(' ', $termo);
        $condicoes = [];

        // Para cada palavra, adiciona uma condição LIKE para nome da marca ou do perfume
        foreach ($termos as $index => $palavra) {
            $palavra = trim($palavra); // Remove espaços extras
            if (!empty($palavra)) {
                if (strlen($palavra) === 1) {
                    // Para termos de apenas 1 caractere, busca apenas no início da palavra
                    $condicoes[] = "(marcas.nome LIKE :termo$index OR perfumes.nome LIKE :termo$index)";
                    $parametros[":termo$index"] = $palavra . "%"; // Começa com a letra
                } else {
                    // Para termos maiores, busca em qualquer lugar
                    $condicoes[] = "(marcas.nome LIKE :termo$index OR perfumes.nome LIKE :termo$index)";
                    $parametros[":termo$index"] = "%" . $palavra . "%"; // Substring
                }
            }
        }

        // Junta todas as condições com 'AND' (todos os termos precisam estar presentes no nome)
        if (!empty($condicoes)) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        } else {
            // Se não houver condições válidas, retorna uma lista vazia
            return [];
        }
    }

    // Prepara a query com PDO
    $stmt = $pdo->prepare($sql);

    // Liga os parâmetros de forma segura
    foreach ($parametros as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    // Executa a query
    $stmt->execute();

    // Obtém os resultados
    $perfumes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $perfumes; // Retorna os perfumes encontrados
}

function buscarInformacoesComNotas($idPerfume)
{
    global $liga;

    // Query principal para obter detalhes do perfume
    $sql = "SELECT 
                perfumes.id_perfume,
                perfumes.nome, 
                perfumes.descricao,
                perfumes.preco,
                perfumes.caminho_imagem,
                perfumes.stock,
                marcas.nome AS marca
            FROM 
                perfumes
            JOIN 
                marcas ON perfumes.id_marca = marcas.id_marca
            WHERE 
                perfumes.id_perfume = ?";

    $stmt = mysqli_prepare($liga, $sql);
    if (!$stmt) {
        die("Erro na preparação da query: " . mysqli_error($liga));
    }

    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        die("Erro na execução da query principal: " . mysqli_error($liga));
    }

    $perfume = mysqli_fetch_assoc($result);

    if (!$perfume) {
        return null; // Retorna null se o perfume não for encontrado
    }

    // Query para buscar as notas olfativas organizadas por tipo
    $sqlNotas = "SELECT 
                    notas_geral.nome_nota,
                    perfume_notas.tipo_nota
                FROM 
                    perfume_notas
                JOIN 
                    notas_geral ON perfume_notas.id_nota = notas_geral.id_nota
                WHERE 
                    perfume_notas.id_perfume = ?
                ORDER BY 
                    FIELD(perfume_notas.tipo_nota, 'topo', 'coração', 'base')";

    $stmtNotas = mysqli_prepare($liga, $sqlNotas);
    if (!$stmtNotas) {
        die("Erro na preparação da query de notas: " . mysqli_error($liga));
    }

    mysqli_stmt_bind_param($stmtNotas, 'i', $idPerfume);
    mysqli_stmt_execute($stmtNotas);
    $resultNotas = mysqli_stmt_get_result($stmtNotas);

    if (!$resultNotas) {
        die("Erro na execução da query de notas: " . mysqli_error($liga));
    }

    // Inicializa os arrays para cada tipo de nota
    $notas = [
        'topo' => [],
        'coracao' => [],
        'base' => []
    ];

    // Organiza as notas por tipo
    while ($nota = mysqli_fetch_assoc($resultNotas)) {
        $tipoNota = strtolower($nota['tipo_nota']); // Certifique-se de que o tipo de nota é consistente
        if (isset($notas[$tipoNota])) {
            $notas[$tipoNota][] = $nota['nome_nota'];
        }
    }

    // Adiciona as notas ao array do perfume
    $perfume['notas'] = $notas;

    return $perfume;
}

function buscarImagensPerfume($idPerfume)
{
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

    $sql = "SELECT id_marca, nome, descricao, caminho_imagem FROM marcas ORDER BY nome ASC";
    $result = mysqli_query($liga, $sql);

    $marcasAgrupadas = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($marca = mysqli_fetch_assoc($result)) {
            $inicial = strtoupper($marca['nome'][0]);
            if (!isset($marcasAgrupadas[$inicial])) {
                $marcasAgrupadas[$inicial] = [];
            }
            $marcasAgrupadas[$inicial][] = [
                'id_marca' => $marca['id_marca'],
                'nome' => $marca['nome'],
                'descricao' => $marca['descricao'],
                'caminho_imagem' => $marca['caminho_imagem']
            ];
        }
    }

    return $marcasAgrupadas;
}

function getMarca($id_marca)
{
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
function getPerfumesPorMarca($id_marca)
{
    global $liga; // Usar a conexão mysqli global

    $sql = "SELECT p.id_perfume AS id_perfume, p.nome AS nome, p.caminho_imagem, p.caminho_imagem_hover, 
    p.preco, p.id_marca, p.stock, m.nome AS marca
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

function buscarFamiliasOlfativas()
{
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
}

function buscarPerfumesPorFamilia($id_familia)
{
    global $liga;

    $sql = "
        SELECT 
            p.id_perfume, 
            p.nome, 
            p.caminho_imagem, 
            p.preco,
            p.stock,
            p.caminho_imagem_hover,
            m.nome AS nome_marca
        FROM perfumes p
        LEFT JOIN marcas m ON p.id_marca = m.id_marca
        WHERE p.id_familia = ?
    ";

    // Prepara a query
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_familia);
    mysqli_stmt_execute($stmt);

    // Retorna os resultados
    return mysqli_stmt_get_result($stmt);
}

// Função para buscar detalhes de uma família
function buscarDetalhesFamilia($id_familia)
{
    global $liga; // Assume que a variável de conexão está definida em outro lugar

    // Consulta SQL para buscar os detalhes da família
    $sql = "SELECT nome_familia, descricao FROM familias_olfativas WHERE id_familia = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_familia);
    mysqli_stmt_execute($stmt);

    // Obter o resultado
    $result = mysqli_stmt_get_result($stmt);
    $familia = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);
    return $familia;
}

function atribuirFamiliaDominante() {
    global $liga; // Conexão mysqli global

    // Busca todos os perfumes
    $sqlPerfumes = "SELECT id_perfume FROM perfumes_nicho.perfumes";
    $resultPerfumes = mysqli_query($liga, $sqlPerfumes);

    if ($resultPerfumes && mysqli_num_rows($resultPerfumes) > 0) {
        while ($row = mysqli_fetch_assoc($resultPerfumes)) {
            $id_perfume = $row['id_perfume'];

            // Determina a família predominante com base nas notas associadas
            $sqlFamiliaDominante = "
                SELECT fn.id_familia, COUNT(pn.id_nota) AS total_notas
                FROM perfumes_nicho.familia_notas fn
                JOIN perfumes_nicho.perfume_notas pn ON fn.id_nota = pn.id_nota
                WHERE pn.id_perfume = ?
                GROUP BY fn.id_familia
                ORDER BY total_notas DESC
                LIMIT 1
            ";

            // Prepara e executa a query
            $stmtFamilia = mysqli_prepare($liga, $sqlFamiliaDominante);
            if ($stmtFamilia === false) {
                echo "Erro ao preparar a consulta para encontrar a família predominante.<br>";
                continue;
            }
            mysqli_stmt_bind_param($stmtFamilia, "i", $id_perfume);
            mysqli_stmt_execute($stmtFamilia);
            $resultFamilia = mysqli_stmt_get_result($stmtFamilia);

            // Se houver uma família predominante
            if ($resultFamilia && mysqli_num_rows($resultFamilia) > 0) {
                $familia = mysqli_fetch_assoc($resultFamilia);
                $id_familia = $familia['id_familia'];

                // Atualiza a família dominante no perfume
                $sqlUpdate = "UPDATE perfumes_nicho.perfumes SET id_familia = ? WHERE id_perfume = ?";
                $stmtUpdate = mysqli_prepare($liga, $sqlUpdate);
                if ($stmtUpdate === false) {
                    echo "Erro ao preparar a consulta para atualizar a família do perfume.<br>";
                    continue;
                }

                mysqli_stmt_bind_param($stmtUpdate, "ii", $id_familia, $id_perfume);
                if (!mysqli_stmt_execute($stmtUpdate)) {
                    echo "Erro ao atualizar o perfume ID $id_perfume com a família ID $id_familia.<br>";
                }
                mysqli_stmt_close($stmtUpdate);
            }
            mysqli_stmt_close($stmtFamilia);
        }
    }
}
