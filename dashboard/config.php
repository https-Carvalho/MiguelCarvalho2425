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






function inserirPerfume($dados) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO perfumes (nome, descricao, preco, caminho_imagem, caminho_imagem_hover, id_marca, id_familia, stock) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $dados['nome'],
        $dados['descricao'],
        $dados['preco'],
        $dados['caminho_imagem'],
        $dados['caminho_imagem_hover'],
        $dados['id_marca'],
        $dados['id_familia'],
        $dados['stock']
    ]);
}


function editarPerfume($id_perfume, $dados) {
    global $pdo;
    $sql = "UPDATE perfumes SET nome = ?, descricao = ?, preco = ?, stock = ?, id_marca = ?, caminho_imagem = ?, caminho_imagem_hover = ?, id_familia = ? WHERE id_perfume = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $dados['nome'],
        $dados['descricao'],
        $dados['preco'],
        $dados['stock'],
        $dados['id_marca'],
        $dados['caminho_imagem'],
        $dados['caminho_imagem_hover'],
        $dados['id_familia'],
        $id_perfume
    ]);
}


function eliminarPerfume($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM perfumes WHERE id_perfume = ?");
    $stmt->execute([$id]);
}


//marcas
function listarMarcasDashboard() {
    global $pdo;
    return $pdo->query("SELECT * FROM marcas ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
}

function inserirMarca($nome, $descricao, $imagem) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO marcas (nome, descricao, caminho_imagem) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $descricao, $imagem]);
}

function editarMarca($id, $nome, $descricao, $imagem) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE marcas SET nome = ?, descricao = ?, caminho_imagem = ? WHERE id_marca = ?");
    $stmt->execute([$nome, $descricao, $imagem, $id]);
}

function eliminarMarca($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM marcas WHERE id_marca = ?");
    $stmt->execute([$id]);
}

//encomendas
function listarEncomendas($estado = '') {
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

function detalhesEncomenda($id_encomenda) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ep.*, p.nome AS nome_produto 
                           FROM encomenda_produtos ep 
                           JOIN perfumes p ON ep.id_produto = p.id_perfume 
                           WHERE ep.id_encomenda = ?");
    $stmt->execute([$id_encomenda]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function alterarEstadoEncomenda($id_encomenda, $novo_estado) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE encomendas SET estado = ? WHERE id_encomenda = ?");
    $stmt->execute([$novo_estado, $id_encomenda]);
}


//contas
function listarUtilizadores() {
    global $pdo;
    return $pdo->query("SELECT id_user, username, email, tipo, criado_em FROM tbl_user ORDER BY criado_em DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function alterarTipoUtilizador($id_user, $novo_tipo) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tbl_user SET tipo = ? WHERE id_user = ?");
    $stmt->execute([$novo_tipo, $id_user]);
}

function eliminarUtilizador($id_user) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM tbl_user WHERE id_user = ?");
    $stmt->execute([$id_user]);
}


function buscarMarcas() {
    global $liga;
    $sql = "SELECT id_marca, nome FROM marcas ORDER BY nome ASC";
    $result = mysqli_query($liga, $sql);

    $marcasLista = [];
    while ($marca = mysqli_fetch_assoc($result)) {
        $marcasLista[] = $marca;
    }
    return $marcasLista;
}

function buscarInformacoesComNotas($id_perfume) {
    global $liga;

    // Busca os dados do perfume, incluindo nome da família
    $sql = "SELECT p.*, f.nome_familia 
            FROM perfumes p 
            LEFT JOIN familias_olfativas f ON p.id_familia = f.id_familia
            WHERE p.id_perfume = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_perfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $perfume = mysqli_fetch_assoc($result);

    $perfume['notas'] = ['topo' => [], 'coracao' => [], 'base' => []];

    // Busca notas ligadas ao perfume
    $sql_notas = "SELECT pn.id_nota, pn.tipo_nota, ng.nome_nota
                  FROM perfume_notas pn
                  JOIN notas_geral ng ON pn.id_nota = ng.id_nota
                  WHERE pn.id_perfume = ?";
    $stmt_notas = mysqli_prepare($liga, $sql_notas);
    mysqli_stmt_bind_param($stmt_notas, 'i', $id_perfume);
    mysqli_stmt_execute($stmt_notas);
    $res_notas = mysqli_stmt_get_result($stmt_notas);

    while ($nota = mysqli_fetch_assoc($res_notas)) {
        $perfume['notas'][$nota['tipo_nota']][] = [
            'id_nota' => $nota['id_nota'],
            'nome_nota' => $nota['nome_nota']
        ];
    }

    return $perfume;
}

function buscarImagensPerfume($id_perfume) {
    global $liga;
    $sql = "SELECT id, caminho_imagem FROM imagens_perfume WHERE perfume_id = ?";
    $stmt = mysqli_prepare($liga, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id_perfume);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $imagens = [];
    while ($img = mysqli_fetch_assoc($result)) {
        $imagens[] = $img;
    }
    return $imagens;
}

function atribuirFamiliaDominante() {
    global $liga;

    $sqlPerfumes = "SELECT id_perfume FROM perfumes";
    $resultPerfumes = mysqli_query($liga, $sqlPerfumes);

    while ($row = mysqli_fetch_assoc($resultPerfumes)) {
        $id_perfume = $row['id_perfume'];

        $sql = "
            SELECT fn.id_familia, COUNT(*) as total
            FROM familia_notas fn
            JOIN perfume_notas pn ON fn.id_nota = pn.id_nota
            WHERE pn.id_perfume = ?
            GROUP BY fn.id_familia
            ORDER BY total DESC LIMIT 1
        ";
        $stmt = mysqli_prepare($liga, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id_perfume);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if ($familia = mysqli_fetch_assoc($res)) {
            $stmtUpdate = mysqli_prepare($liga, "UPDATE perfumes SET id_familia = ? WHERE id_perfume = ?");
            mysqli_stmt_bind_param($stmtUpdate, 'ii', $familia['id_familia'], $id_perfume);
            mysqli_stmt_execute($stmtUpdate);
        }
    }
}

function guardarImagem($file) {
    $pasta = 'uploads/';
    $nomeFinal = $pasta . uniqid() . '_' . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $nomeFinal);
    return $nomeFinal;
}

function inserirImagensAdicionais($id_perfume, $files) {
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

function atualizarNotasPerfume($id_perfume, $notas) {
    global $pdo;
    $pdo->prepare("DELETE FROM perfume_notas WHERE id_perfume = ?")->execute([$id_perfume]);

    foreach ($notas as $tipo => $lista) {
        foreach ($lista as $id_nota) {
            $stmt = $pdo->prepare("INSERT INTO perfume_notas (id_perfume, id_nota, tipo_nota) VALUES (?, ?, ?)");
            $stmt->execute([$id_perfume, $id_nota, $tipo]);
        }
    }
}



function buscarNotasOlfativas() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM notas_geral ORDER BY nome_nota ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function verificarTipoUsuario($id_usuario) {
    global $pdo;

    $sql = "SELECT tipo FROM tbl_user WHERE id_user = :id_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario['tipo'] ?? null; // Retorna null se não encontrar o usuário
}
