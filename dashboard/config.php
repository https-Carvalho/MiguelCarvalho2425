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

function editarPerfume($id, $dados) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE perfumes SET nome = ?, descricao = ?, preco = ?, caminho_imagem = ?, caminho_imagem_hover = ?, id_marca = ?, id_familia = ?, stock = ? WHERE id_perfume = ?");
    $stmt->execute([
        $dados['nome'],
        $dados['descricao'],
        $dados['preco'],
        $dados['caminho_imagem'],
        $dados['caminho_imagem_hover'],
        $dados['id_marca'],
        $dados['id_familia'],
        $dados['stock'],
        $id
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
