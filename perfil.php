<?php
session_start();
include('config.php');

if (!isset($_SESSION['id_sessao']) || $_SESSION['tipo_utilizador'] !== 'cliente') {
    header("Location: login.php");
    exit();
}

$id_sessao = $_SESSION['id_sessao'];
$tipo_utilizador = verificarTipoUsuario($id_sessao);
if ($tipo_utilizador !== 'cliente') {
    header("Location: index.php");
    exit();
}

$id_cliente = $id_sessao;
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['clientname'] ?? 'Conta';
$totalCarrinho = contarItensCarrinho($id_cliente);
$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

$mensagem = '';
$cliente = obterDadosCliente($id_cliente);

// Atualizar dados pessoais e morada existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_dados'])) {
    $novo_nome = trim($_POST['nome']);
    $novo_username = trim($_POST['username']);
    $novo_email = trim($_POST['email']);
    $novo_telefone = trim($_POST['telefone']);

    $morada_atual = [
        'endereco' => trim($_POST['endereco']),
        'andar' => trim($_POST['andar']),
        'porta' => trim($_POST['porta']),
        'codigo_postal' => trim($_POST['codigo_postal']),
        'cidade' => trim($_POST['cidade']),
        'pais' => trim($_POST['pais'])
    ];

    if (atualizarDadosCliente($id_cliente, $novo_nome, $novo_username, $novo_email, $novo_telefone, $morada_atual)) {
        $mensagem = "Dados atualizados com sucesso.";
        $cliente = obterDadosCliente($id_cliente);
    } else {
        $mensagem = "Erro ao atualizar dados.";
    }
}

// Adicionar nova morada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_morada'])) {
    $nova_morada = [
        'endereco' => trim($_POST['endereco']),
        'andar' => trim($_POST['andar']),
        'porta' => trim($_POST['porta']),
        'codigo_postal' => trim($_POST['codigo_postal']),
        'cidade' => trim($_POST['cidade']),
        'pais' => trim($_POST['pais'])
    ];

    if (guardarMoradaCliente($id_cliente, $nova_morada)) {
        $mensagem = "Nova morada adicionada.";
        $cliente = obterDadosCliente($id_cliente);
    } else {
        $mensagem = "Erro ao guardar nova morada.";
    }
}

// Eliminar morada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_morada_id'])) {
    $id_morada = (int) $_POST['eliminar_morada_id'];
    if (eliminarMoradaCliente($id_morada, $id_cliente)) {
        $mensagem = "Morada removida.";
        $cliente = obterDadosCliente($id_cliente);
    } else {
        $mensagem = "Erro ao remover morada.";
    }
}

$encomendas = obterEncomendasCliente($id_cliente);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('menu.php'); ?>

<div class="perfil-container">
    <h1>Meu Perfil</h1>

    <?php if ($mensagem): ?>
        <p class="mensagem-perfil"><?php echo htmlspecialchars($mensagem); ?></p>
    <?php endif; ?>

    <form method="POST" class="perfil-bloco">
        <input type="hidden" name="atualizar_dados" value="1">

        <label>Nome:</label>
        <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente['nome_completo']); ?>" required>

        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($cliente['username']); ?>" required>

        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>

        <label>Telefone:</label>
        <input type="text" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone']); ?>">

        <label>Endereço (editar 1ª morada):</label>
        <input type="text" name="endereco" value="<?php echo htmlspecialchars($cliente['moradas'][0]['endereco'] ?? ''); ?>">

        <label>Andar:</label>
        <input type="text" name="andar" value="<?php echo htmlspecialchars($cliente['moradas'][0]['andar'] ?? ''); ?>">

        <label>Porta:</label>
        <input type="text" name="porta" value="<?php echo htmlspecialchars($cliente['moradas'][0]['porta'] ?? ''); ?>">

        <label>Código Postal:</label>
        <input type="text" name="codigo_postal" value="<?php echo htmlspecialchars($cliente['moradas'][0]['codigo_postal'] ?? ''); ?>">

        <label>Cidade:</label>
        <input type="text" name="cidade" value="<?php echo htmlspecialchars($cliente['moradas'][0]['cidade'] ?? ''); ?>">

        <label>País:</label>
        <input type="text" name="pais" value="<?php echo htmlspecialchars($cliente['moradas'][0]['pais'] ?? ''); ?>">

        <button type="submit">Guardar Alterações</button>
    </form>

    <h2>Moradas Adicionais</h2>
    <?php foreach (array_slice($cliente['moradas'], 1) as $morada): ?>
        <div class="morada-bloco">
            <p><?php echo htmlspecialchars($morada['endereco'] . ', ' . $morada['andar'] . 'º ' . $morada['porta'] . ', ' . $morada['codigo_postal'] . ' ' . $morada['cidade'] . ', ' . $morada['pais']); ?></p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="eliminar_morada_id" value="<?php echo $morada['id_morada']; ?>">
                <button type="submit">Eliminar</button>
            </form>
        </div>
    <?php endforeach; ?>

    <h3>Adicionar nova morada</h3>
    <form method="POST" class="perfil-bloco">
        <input type="hidden" name="nova_morada" value="1">

        <label>Endereço:</label>
        <input type="text" name="endereco" required>

        <label>Andar:</label>
        <input type="text" name="andar">

        <label>Porta:</label>
        <input type="text" name="porta">

        <label>Código Postal:</label>
        <input type="text" name="codigo_postal" required>

        <label>Cidade:</label>
        <input type="text" name="cidade" required>

        <label>País:</label>
        <input type="text" name="pais" required>

        <button type="submit">Guardar Morada</button>
    </form>

    <h2>Minhas Encomendas</h2>
    <?php if (!empty($encomendas)): ?>
        <table class="perfil-encomendas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($encomendas as $enc): ?>
                    <tr>
                        <td><?php echo $enc['id_encomenda']; ?></td>
                        <td><?php echo $enc['data_encomenda']; ?></td>
                        <td><?php echo number_format($enc['total'], 2); ?>€</td>
                        <td><?php echo $enc['estado']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Ainda não fez nenhuma encomenda.</p>
    <?php endif; ?>
</div>
</body>
</html>
