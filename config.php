<?php
// ==========================================
// CONFIGURAÇÃO DE BASE DE DADOS
// ==========================================
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'perfumes_nicho';

$liga = mysqli_connect($host, $user, $password, $dbname);
if (!$liga) {
    die("Erro na conexão: " . mysqli_connect_error());
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro PDO: " . $e->getMessage());
}

//==========================================
#region PERFUMES    

function listarPerfumes($termo = '', $precoMin = null, $precoMax = null, $filtroMarcas = [], $familias = [], $disponibilidade = null, $ordenacao = '')
{
    global $pdo;

    // Query base com JOIN para trazer o nome da marca
    $sql = "SELECT perfumes.id_perfume, perfumes.nome, perfumes.preco, perfumes.caminho_imagem, 
                   perfumes.caminho_imagem_hover, perfumes.stock, marcas.nome AS marca
            FROM perfumes
            JOIN marcas ON perfumes.id_marca = marcas.id_marca
            WHERE 1=1"; // Condição base para facilitar a adição de filtros

    $params = [];

    /** 🔹 FILTRO: PESQUISA PELO TERMO **/
    if (!empty($termo)) {
        $termos = explode(' ', $termo);
        $condicoes = [];

        foreach ($termos as $index => $palavra) {
            $palavra = trim($palavra);
            if (!empty($palavra)) {
                $like = $index === 0 ? "$palavra%" : "%$palavra%"; // primeira palavra = começa com
                $condicoes[] = "(marcas.nome LIKE :termo$index OR perfumes.nome LIKE :termo$index)";
                $params[":termo$index"] = $like;
            }
        }


        if (!empty($condicoes)) {
            $sql .= ' AND (' . implode(' AND ', $condicoes) . ')';
        } else {
            return []; // Retorna vazio se não houver condições válidas
        }
    }

    /** 🔹 FILTRO: PREÇO **/
    if (!empty($precoMin)) {
        $sql .= " AND perfumes.preco >= :preco_min";
        $params[':preco_min'] = $precoMin;
    }
    if (!empty($precoMax)) {
        $sql .= " AND perfumes.preco <= :preco_max";
        $params[':preco_max'] = $precoMax;
    }

    /** 🔹 FILTRO: MARCAS **/
    if (!empty($filtroMarcas)) {
        $marcaPlaceholders = [];
        foreach ($filtroMarcas as $index => $marca_filtro) {
            $key = ":marca_$index";
            $marcaPlaceholders[] = $key;
            $params[$key] = $marca_filtro;
        }
        $sql .= " AND perfumes.id_marca IN (" . implode(',', $marcaPlaceholders) . ")";
    }

    /** 🔹 FILTRO: FAMÍLIAS OLFATIVAS **/
    if (!empty($familias)) {
        $familiaPlaceholders = [];
        foreach ($familias as $index => $familia) {
            $key = ":familia_$index";
            $familiaPlaceholders[] = $key;
            $params[$key] = $familia;
        }
        $sql .= " AND perfumes.id_familia IN (" . implode(',', $familiaPlaceholders) . ")";
    }

    /** 🔹 FILTRO: DISPONIBILIDADE (ESTOQUE) **/
    if ($disponibilidade !== null && $disponibilidade !== "") {
        $sql .= " AND perfumes.stock " . ($disponibilidade == 1 ? "> 0" : "= 0");
    }

    /** 🔹 ORDENAR DINAMICAMENTE SE FOR DEFINIDO **/
    if (!empty($ordenacao)) {
        switch ($ordenacao) {
            case 'nome_asc':
                $sql .= " ORDER BY perfumes.nome ASC";
                break;
            case 'nome_desc':
                $sql .= " ORDER BY perfumes.nome DESC";
                break;
            case 'preco_menor':
                $sql .= " ORDER BY perfumes.preco ASC";
                break;
            case 'preco_maior':
                $sql .= " ORDER BY perfumes.preco DESC";
                break;
            default:
                $sql .= " ORDER BY perfumes.id_perfume ASC";
                break;
        }
    } else {
        $sql .= " ORDER BY perfumes.id_perfume ASC"; // <-- fallback
    }


    // 🔹 NÃO ADICIONA `LIMIT` e `OFFSET`, pois a paginação será feita via JavaScript.

    // Preparar e executar a query
    $stmt = $pdo->prepare($sql);

    // Associar os parâmetros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function verificarStockProduto($id_produto)
{
    global $pdo;

    $sql = "SELECT stock FROM perfumes WHERE id_perfume = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_produto' => $id_produto]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function contarTotalPerfumes($termo = '', $precoMin = null, $precoMax = null, $filtroMarcas = [], $familias = [], $disponibilidade = null)
{
    global $pdo;

    $sql = "SELECT COUNT(*) AS total FROM perfumes JOIN marcas ON perfumes.id_marca = marcas.id_marca WHERE 1=1";
    $params = [];

    /** 🔹 Aplicar os mesmos filtros da listagem **/
    if (!empty($termo)) {
        $termos = explode(' ', $termo);
        $condicoes = [];
        foreach ($termos as $index => $palavra) {
            $condicoes[] = "(marcas.nome LIKE :termo$index OR perfumes.nome LIKE :termo$index)";
            $params[":termo$index"] = $palavra . "%";
        }
        $sql .= ' AND (' . implode(' AND ', $condicoes) . ')';
    }

    if (!empty($precoMin)) {
        $sql .= " AND perfumes.preco >= :preco_min";
        $params[':preco_min'] = $precoMin;
    }
    if (!empty($precoMax)) {
        $sql .= " AND perfumes.preco <= :preco_max";
        $params[':preco_max'] = $precoMax;
    }

    if (!empty($filtroMarcas)) {
        $marcaPlaceholders = [];
        foreach ($filtroMarcas as $index => $marca_filtro) {
            $marcaPlaceholders[] = ":marca_$index";
            $params[":marca_$index"] = $marca_filtro;
        }
        $sql .= " AND perfumes.id_marca IN (" . implode(',', $marcaPlaceholders) . ")";
    }

    if (!empty($familias)) {
        $familiaPlaceholders = [];
        foreach ($familias as $index => $familia) {
            $familiaPlaceholders[] = ":familia_$index";
            $params[":familia_$index"] = $familia;
        }
        $sql .= " AND perfumes.id_familia IN (" . implode(',', $familiaPlaceholders) . ")";
    }

    if ($disponibilidade !== null && $disponibilidade !== "") {
        $sql .= " AND perfumes.stock " . ($disponibilidade == 1 ? "> 0" : "= 0");
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}
function inserirPerfume($dados)
{
    global $pdo;
    $sql = "INSERT INTO perfumes 
            (nome, descricao, preco, stock, id_marca, caminho_imagem, caminho_imagem_hover, id_familia) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $dados['nome'],
        $dados['descricao'],
        $dados['preco'],
        $dados['stock'],
        $dados['id_marca'],
        $dados['caminho_imagem'],
        $dados['caminho_imagem_hover'],
        $dados['id_familia']
    ]);

    return $pdo->lastInsertId(); // devolve o ID do perfume inserido, caso precises
}


function editarPerfume($id, $dados)
{
    global $pdo;

    $stmt = $pdo->prepare("
        UPDATE perfumes 
        SET 
            nome = :nome,
            descricao = :descricao,
            preco = :preco,
            stock = :stock,
            id_marca = :id_marca,
            caminho_imagem = :caminho_imagem,
            caminho_imagem_hover = :caminho_imagem_hover,
            id_familia = :id_familia
        WHERE id_perfume = :id_perfume
    ");

    $stmt->execute([
        ':nome' => $dados['nome'],
        ':descricao' => $dados['descricao'],
        ':preco' => $dados['preco'],
        ':stock' => $dados['stock'],
        ':id_marca' => $dados['id_marca'],
        ':caminho_imagem' => $dados['caminho_imagem'],
        ':caminho_imagem_hover' => $dados['caminho_imagem_hover'],
        ':id_familia' => $dados['id_familia'],
        ':id_perfume' => $id
    ]);
}


function eliminarPerfume($id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM perfumes WHERE id_perfume = ?");
    $stmt->execute([$id]);
}

function buscarInformacoesComNotas($idPerfume, $comIds = false)
{
    global $liga;

    $sql = "SELECT 
                perfumes.id_perfume,
                perfumes.nome, 
                perfumes.descricao,
                perfumes.preco,
                perfumes.caminho_imagem,
                perfumes.caminho_imagem_hover,
                perfumes.stock,
                perfumes.id_familia,
                marcas.id_marca,
                marcas.nome AS marca,
                familias_olfativas.nome_familia
            FROM 
                perfumes
            JOIN 
                marcas ON perfumes.id_marca = marcas.id_marca
            LEFT JOIN 
                familias_olfativas ON perfumes.id_familia = familias_olfativas.id_familia
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
        return null;
    }

    // Query para buscar as notas (sempre traz nome e tipo, opcionalmente id)
    $sqlNotas = "SELECT 
                    " . ($comIds ? "notas_geral.id_nota," : "") . "
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

    $notas = [
        'topo' => [],
        'coracao' => [],
        'base' => []
    ];

    while ($nota = mysqli_fetch_assoc($resultNotas)) {
        $tipoNota = strtolower($nota['tipo_nota']);
        if (!isset($notas[$tipoNota])) continue;

        if ($comIds) {
            $notas[$tipoNota][] = [
                'id_nota' => (int) $nota['id_nota'],
                'nome_nota' => $nota['nome_nota']
            ];
        } else {
            $notas[$tipoNota][] = $nota['nome_nota'];
        }
    }

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

function buscarImagensPerfumeComId($idPerfume)
{
    global $liga;

    $sql = "SELECT id, caminho_imagem 
            FROM imagens_perfume 
            WHERE perfume_id = ?";

    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $idPerfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $imagens = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $imagens[] = [
            'id' => $row['id'],
            'caminho_imagem' => $row['caminho_imagem']
        ];
    }

    return $imagens;
}

function guardarImagem($file)
{
    $pasta = 'images/'; // Pasta real e visível no frontend
    $nomeFinal = $pasta . uniqid() . '_' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $nomeFinal);
    return $nomeFinal; // Caminho a guardar na base de dados
}

function inserirImagensAdicionais($id_perfume, $files)
{
    global $pdo;
    foreach ($files['tmp_name'] as $i => $tmp) {
        if ($tmp) {
            $nome = guardarImagem([
                'name' => $files['name'][$i],
                'tmp_name' => $tmp
            ]);
            $stmt = $pdo->prepare("INSERT INTO imagens_perfume (perfume_id, caminho_imagem) VALUES (?, ?)");
            $stmt->execute([$id_perfume, $nome]);
        }
    }
}

function atualizarNotasPerfume($id_perfume, $notas)
{
    global $pdo;
    $pdo->prepare("DELETE FROM perfume_notas WHERE id_perfume = ?")->execute([$id_perfume]);

    foreach ($notas as $tipo => $lista) {
        foreach ($lista as $id_nota) {
            $stmt = $pdo->prepare("INSERT INTO perfume_notas (id_perfume, id_nota, tipo_nota) VALUES (?, ?, ?)");
            $stmt->execute([$id_perfume, $id_nota, $tipo]);
        }
    }
}

function atribuirFamiliaDominante()
{
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

#endregion
//==========================================
//==========================================
#region MARCA

function buscarMarcas()
{
    global $liga;

    $sql = "SELECT id_marca, nome FROM marcas ORDER BY nome ASC";
    $result = mysqli_query($liga, $sql);

    $marcasLista = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($marca = mysqli_fetch_assoc($result)) {
            $marcasLista[] = [
                'id_marca' => $marca['id_marca'],
                'nome' => $marca['nome']
            ];
        }
    }

    return $marcasLista; // Retorna uma lista simples de marcas
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

function listarMarcasDashboard()
{
    global $pdo;
    return $pdo->query("SELECT * FROM marcas ORDER BY id_marca")->fetchAll(PDO::FETCH_ASSOC);
}

function inserirMarca($dados)
{
    global $pdo;
    $sql = "INSERT INTO marcas (nome, descricao, caminho_imagem) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $dados['nome'],
        $dados['descricao'],
        $dados['caminho_imagem']
    ]);

    return $pdo->lastInsertId(); // devolve o ID da marca inserida
}


function editarMarca($id, $nome, $descricao, $imagem)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE marcas SET nome = ?, descricao = ?, caminho_imagem = ? WHERE id_marca = ?");
    $stmt->execute([$nome, $descricao, $imagem, $id]);
}

function eliminarMarca($id)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM marcas WHERE id_marca = ?");
    $stmt->execute([$id]);
}

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

function buscarInformacoesMarca($id_marca)
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

#endregion
//==========================================
//==========================================
#region FAMÍLIAS

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

function buscarFamiliaPorNota()
{
    global $liga;
    $sql = "SELECT fn.id_nota, f.id_familia, f.nome_familia
            FROM familia_notas fn
            JOIN familias_olfativas f ON f.id_familia = fn.id_familia";
    $res = mysqli_query($liga, $sql);
    $mapa = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $mapa[$row['id_nota']] = [
            'id_familia' => $row['id_familia'],
            'nome_familia' => $row['nome_familia']
        ];
    }
    return $mapa;
}

function inserirFamilia($nome, $descricao) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO familias_olfativas (nome_familia, descricao) VALUES (?, ?)");
    return $stmt->execute([$nome, $descricao]);
}

function editarFamilia($id, $nome, $descricao) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE familias_olfativas SET nome_familia = ?, descricao = ? WHERE id_familia = ?");
    return $stmt->execute([$nome, $descricao, $id]);
}

function eliminarFamilia($id_familia) {
    global $pdo;
    $pdo->prepare("DELETE FROM familia_notas WHERE id_familia = ?")->execute([$id_familia]);
    return $pdo->prepare("DELETE FROM familias_olfativas WHERE id_familia = ?")->execute([$id_familia]);
}

function buscarNotasDaFamilia($id_familia) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ng.id_nota, ng.nome_nota
        FROM familia_notas fn
        JOIN notas_geral ng ON fn.id_nota = ng.id_nota
        WHERE fn.id_familia = ?
        ORDER BY ng.nome_nota
    ");
    $stmt->execute([$id_familia]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

#endregion
//==========================================
//==========================================
#region NOTAS OLFATIVAS

function buscarNotasOlfativas()
{
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM notas_geral ORDER BY nome_nota ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function inserirNotaEmFamilia($id_familia, $nome)
{
    global $pdo;
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare("INSERT INTO notas_geral (nome_nota) VALUES (?)");
        $stmt->execute([$nome]);
        $id_nota = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO familia_notas (id_familia, id_nota) VALUES (?, ?)");
        $stmt2->execute([$id_familia, $id_nota]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function editarNotaGeral($id_nota, $novo_nome)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notas_geral SET nome_nota = ? WHERE id_nota = ?");
    return $stmt->execute([$novo_nome, $id_nota]);
}

function removerNotaDeFamilia($id_familia, $id_nota)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM familia_notas WHERE id_familia = ? AND id_nota = ?");
    $stmt->execute([$id_familia, $id_nota]);

    // Elimina nota se não estiver em mais nenhuma família
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM familia_notas WHERE id_nota = ?");
    $stmt->execute([$id_nota]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("DELETE FROM notas_geral WHERE id_nota = ?");
        $stmt->execute([$id_nota]);
    }
}

#endregion
// ==========================================
// ==========================================
#region ENCOMENDAS

function listarEncomendas($estado = '')
{
    global $pdo;
    $sql = "SELECT e.*, u.username 
            FROM encomendas e 
            JOIN tbl_user u ON e.id_user = u.id_user";
    if ($estado !== '') {
        $sql .= " WHERE e.estado = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$estado]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function detalhesEncomenda($id_encomenda)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT ep.*, p.nome AS nome_produto 
                           FROM encomenda_produtos ep 
                           JOIN perfumes p ON ep.id_produto = p.id_perfume 
                           WHERE ep.id_encomenda = ?");
    $stmt->execute([$id_encomenda]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function alterarEstadoEncomenda($id_encomenda, $novo_estado)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE encomendas SET estado = ? WHERE id_encomenda = ?");
    $stmt->execute([$novo_estado, $id_encomenda]);
}

function criarEncomenda($id_user, $total)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO encomendas (id_user, total, data_encomenda) VALUES (?, ?, NOW())");
    $stmt->execute([$id_user, $total]);
    return $pdo->lastInsertId();
}

function adicionarProdutoEncomenda($id_encomenda, $id_produto, $quantidade, $preco_unitario)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO encomenda_produtos (id_encomenda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_encomenda, $id_produto, $quantidade, $preco_unitario]);
}


function limparCarrinho($id_user)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_usuario = ?");
    $stmt->execute([$id_user]);
}

function atualizarStock($id_produto, $quantidadeVendida)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE perfumes SET stock = stock - ? WHERE id_perfume = ?");
    $stmt->execute([$quantidadeVendida, $id_produto]);
}

#endregion
// ==========================================
// ==========================================
#region LOGIN E UTILIZADORES

function logarUtilizador($email, $password) {
    global $liga;
    $stmt = mysqli_prepare($liga, "SELECT * FROM tbl_user WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $utilizador = mysqli_fetch_assoc($resultado);

    if ($utilizador && password_verify($password, $utilizador['password'])) {
        return $utilizador;
    }

    return false;
}

function verificarTipoUsuario($id_usuario)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT tipo FROM tbl_user WHERE id_user = :id_usuario");
    $stmt->execute(['id_usuario' => $id_usuario]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['tipo'] ?? null;
}

function listarUtilizadores()
{
    global $pdo;
    return $pdo->query("SELECT id_user, username, email, tipo, criado_em FROM tbl_user ORDER BY criado_em DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function alterarTipoUtilizador($id_user, $novo_tipo)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tbl_user SET tipo = ? WHERE id_user = ?");
    $stmt->execute([$novo_tipo, $id_user]);
}

function eliminarUtilizador($id_user)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM tbl_user WHERE id_user = ?");
    $stmt->execute([$id_user]);
}

function obterUsuarioPorEmail($email) {
    global $liga;
    $stmt = mysqli_prepare($liga, "SELECT * FROM tbl_user WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($resultado);
}



// Atualiza a senha do utilizador
function atualizarSenhaPorId($id_user, $novaSenhaHash) {
    global $liga;
    $stmt = mysqli_prepare($liga, "UPDATE tbl_user SET password = ? WHERE id_user = ?");
    mysqli_stmt_bind_param($stmt, 'si', $novaSenhaHash, $id_user);
    mysqli_stmt_execute($stmt);
}

#endregion
// ==========================================
// ==========================================
#region CARRINHO


function adicionarAoCarrinho($id_usuario, $id_produto, $quantidade = 1)
{
    global $pdo;

    // Verifica se o item já está no carrinho do usuário
    $sql = "SELECT * FROM carrinho WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Atualiza a quantidade caso o item já exista
        $sql = "UPDATE carrinho SET quantidade = quantidade + :quantidade WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['quantidade' => $quantidade, 'id_usuario' => $id_usuario, 'id_produto' => $id_produto]);
    } else {
        // Insere um novo item no carrinho
        $sql = "INSERT INTO carrinho (id_usuario, id_produto, quantidade) VALUES (:id_usuario, :id_produto, :quantidade)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto, 'quantidade' => $quantidade]);
    }
}


// Função para remover um item do carrinho
function removerDoCarrinho($id_usuario, $id_produto)
{
    global $pdo;
    $sql = "DELETE FROM carrinho WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto]);

    // Retorna true se pelo menos 1 linha foi afetada
    return $stmt->rowCount() > 0;
}

// Função para buscar os itens do carrinho do usuário
function buscarItensCarrinho($id_usuario)
{
    global $pdo;

    $sql = "SELECT c.id_produto, c.quantidade, p.nome, p.preco, p.caminho_imagem, p.stock
            FROM carrinho c
            JOIN perfumes p ON c.id_produto = p.id_perfume
            WHERE c.id_usuario = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar se a quantidade no carrinho excede o stock disponível
    foreach ($itens as &$item) {
        if ($item['quantidade'] > $item['stock']) {
            $item['quantidade'] = $item['stock']; // Ajusta para o máximo disponível
        }
    }

    return $itens;
}


function contarItensCarrinho($id_usuario)
{
    global $pdo;

    $sql = "SELECT SUM(quantidade) AS total_itens FROM carrinho WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total_itens'] ?? 0; // Se não houver itens, retorna 0
}

function atualizarQuantidadeCarrinho($id_usuario, $id_produto, $quantidade)
{
    global $pdo;

    // Verifica se o produto existe no carrinho
    $sql = "SELECT quantidade FROM carrinho WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        $novaQuantidade = $item['quantidade'] + $quantidade;

        if ($novaQuantidade > 0) {
            // Atualiza a quantidade no carrinho
            $sql = "UPDATE carrinho SET quantidade = :quantidade WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['quantidade' => $novaQuantidade, 'id_usuario' => $id_usuario, 'id_produto' => $id_produto]);
        } else {
            // Remove o produto se a quantidade chegar a zero
            $sql = "DELETE FROM carrinho WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto]);
        }
    }
}

#endregion
// ==========================================
// ==========================================
#region FAVORITOS

function adicionarAosFavoritos($id_user, $id_produto)
{
    global $pdo;

    // Verifica se o produto já está na wishlist
    $sql = "SELECT * FROM wishlist WHERE id_user = :id_user AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);

    if (!$stmt->fetch()) {
        // Insere se não existir
        $sql = "INSERT INTO wishlist (id_user, id_produto) VALUES (:id_user, :id_produto)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);
    }
    return false;
}

// Remover um produto da wishlist
function removerDosFavoritos($id_user, $id_produto)
{
    global $pdo;
    $sql = "DELETE FROM wishlist WHERE id_user = :id_user AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);
}

// Verificar se um produto está na wishlist
function verificarFavorito($id_user, $id_produto)
{
    global $pdo;
    $sql = "SELECT * FROM wishlist WHERE id_user = :id_user AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);
    return $stmt->fetch() ? true : false;
}

function buscarWishlist($id_usuario)
{
    global $pdo;

    $sql = "SELECT w.id_produto, p.id_perfume, p.nome, p.preco, p.caminho_imagem, p.stock, m.nome AS marca
            FROM wishlist w
            JOIN perfumes p ON w.id_produto = p.id_perfume
            JOIN marcas m ON p.id_marca = m.id_marca
            WHERE w.id_user = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

#endregion
// ==========================================
// ==========================================
#region EMAIL & BASE64

function buscarEmailUsuario($id_user)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT email FROM tbl_user WHERE id_user = ?");
    $stmt->execute([$id_user]);
    return $stmt->fetchColumn();
}

function imgToBase64($path)
{
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}

#endregion
// ==========================================
// ==========================================
#region DASHBOARD

function contarPerfumes() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM perfumes")->fetchColumn();
}

function contarMarcas() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM marcas")->fetchColumn();
}

function contarEncomendas() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM encomendas")->fetchColumn();
}

function contarUtilizadores() {
    global $pdo;
    return $pdo->query("SELECT COUNT(*) FROM tbl_user")->fetchColumn();
}

function somarTotalVendas() {
    global $pdo;
    $total = $pdo->query("SELECT SUM(total) FROM encomendas")->fetchColumn();
    return $total ?? 0;
}

function encomendasUltimosMeses($limite = 6) {
    global $pdo;
    $sql = "
        SELECT DATE_FORMAT(data_encomenda, '%Y-%m') AS mes, COUNT(*) AS total
        FROM encomendas
        GROUP BY mes
        ORDER BY mes DESC
        LIMIT :limite
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

#endregion
// ==========================================


?>