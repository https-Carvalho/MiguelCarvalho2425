<?php
// Configurações de sessão 24 horas     

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
function listarPerfumes($termo = '', $precoMin = null, $precoMax = null, $filtroMarcas = [], $familias = [], $disponibilidade = null, $ordenacao = '') {
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
                $condicoes[] = "(marcas.nome LIKE :termo$index OR perfumes.nome LIKE :termo$index)";
                $params[":termo$index"] = $palavra . "%"; // Começa com o termo digitado
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
        }
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



//contar perfumes pra paginacao
function contarTotalPerfumes($termo = '', $precoMin = null, $precoMax = null, $filtroMarcas = [], $familias = [], $disponibilidade = null) {
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

function  buscarInformacoesMarca($id_marca)
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


//login
function logarUtilizador($email, $password) {
    global $pdo; // Usar a conexão PDO global

    try {
        // Busca o usuário pelo email e senha
        $sql = "SELECT * FROM tbl_user WHERE email = :email AND password = :password LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        $stmt->execute();

        // Retorna os dados do usuário se encontrado
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Lida com erros no banco de dados
        error_log("Erro no login: " . $e->getMessage());
        return false;
    }
}




//carrinho
function adicionarAoCarrinho($id_usuario, $id_produto, $quantidade = 1) {
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
function removerDoCarrinho($id_usuario, $id_produto) {
    global $pdo;
    $sql = "DELETE FROM carrinho WHERE id_usuario = :id_usuario AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario, 'id_produto' => $id_produto]);

    // Retorna true se pelo menos 1 linha foi afetada
    return $stmt->rowCount() > 0;
}

// Função para buscar os itens do carrinho do usuário
function buscarItensCarrinho($id_usuario) {
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


function contarItensCarrinho($id_usuario) {
    global $pdo;

    $sql = "SELECT SUM(quantidade) AS total_itens FROM carrinho WHERE id_usuario = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result['total_itens'] ?? 0; // Se não houver itens, retorna 0
}

function atualizarQuantidadeCarrinho($id_usuario, $id_produto, $quantidade) {
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

//verifica tipo de user
function verificarTipoUsuario($id_usuario) {
    global $pdo;

    $sql = "SELECT tipo FROM tbl_user WHERE id_user = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario['tipo'] ?? null; // Retorna null se não encontrar o usuário
}


//favoritos
// Adicionar um produto à wishlist
function adicionarAosFavoritos($id_user, $id_produto) {
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
function removerDosFavoritos($id_user, $id_produto) {
    global $pdo;
    $sql = "DELETE FROM wishlist WHERE id_user = :id_user AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);
}

// Verificar se um produto está na wishlist
function verificarFavorito($id_user, $id_produto) {
    global $pdo;
    $sql = "SELECT * FROM wishlist WHERE id_user = :id_user AND id_produto = :id_produto";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_user' => $id_user, 'id_produto' => $id_produto]);
    return $stmt->fetch() ? true : false;
}




    
function buscarWishlist($id_usuario) {
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


//funcoes pra compra

function buscarEmailUsuario($id_user) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT email FROM tbl_user WHERE id_user = ?");
    $stmt->execute([$id_user]);
    return $stmt->fetchColumn();
}
function criarEncomenda($id_user, $total) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO encomendas (id_user, total, data_encomenda) VALUES (?, ?, NOW())");
    $stmt->execute([$id_user, $total]);
    return $pdo->lastInsertId();
}

function adicionarProdutoEncomenda($id_encomenda, $id_produto, $quantidade, $preco_unitario) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO encomenda_produtos (id_encomenda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_encomenda, $id_produto, $quantidade, $preco_unitario]);
}


function limparCarrinho($id_user) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id_usuario = ?");
    $stmt->execute([$id_user]);
}

function atualizarStock($id_produto, $quantidadeVendida) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE perfumes SET stock = stock - ? WHERE id_perfume = ?");
    $stmt->execute([$quantidadeVendida, $id_produto]);
}

//imagem pros emails
function imgToBase64($path) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
}
