<?php
session_start();
include('../config.php');

// Verifica permissões
$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
if ($tipo_usuario !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// Verifica ID da encomenda
$id_encomenda = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_encomenda <= 0) {
    header('Location: encomendas.php');
    exit();
}

// Atualizar estado se enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_estado'])) {
    $novo_estado = $_POST['novo_estado'];
    alterarEstadoEncomenda($id_encomenda, $novo_estado);
    header("Location: ver_encomenda.php?id=" . $id_encomenda);
    exit();
}

// Reutilizar função existente para buscar encomenda
$encomendas = listarEncomendas(); // já traz JOIN com username
$encomenda = null;
foreach ($encomendas as $e) {
    if ($e['id_encomenda'] == $id_encomenda) {
        $encomenda = $e;
        break;
    }
}
if (!$encomenda) {
    header('Location: encomendas.php');
    exit();
}

$produtos = detalhesEncomenda($id_encomenda);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Encomenda</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('admin_layout.php'); ?>

<div class="main-content">
    <h1>Encomenda #<?= $encomenda['id_encomenda'] ?></h1>

    <p><strong>Cliente:</strong> <?= htmlspecialchars($encomenda['username']) ?></p>
    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($encomenda['data_encomenda'])) ?></p>
    <p><strong>Total:</strong> <?= number_format($encomenda['total'], 2) ?> €</p>

    <form method="post" style="margin-top: 20px;">
        <label><strong>Estado:</strong></label>
        <select name="novo_estado" onchange="this.form.submit()">
            <?php
            $estados = ['Pendente', 'Processada', 'Enviada', 'Cancelada', 'Concluída'];
            foreach ($estados as $estado):
            ?>
                <option value="<?= $estado ?>" <?= ($encomenda['status'] === $estado ? 'selected' : '') ?>>
                    <?= $estado ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <h2 style="margin-top: 40px;">Produtos Encomendados</h2>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($produtos as $p): ?>
                <?php $subtotal = $p['quantidade'] * $p['preco_unitario']; $total += $subtotal; ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome_produto']) ?></td>
                    <td><?= $p['quantidade'] ?></td>
                    <td><?= number_format($p['preco_unitario'], 2) ?> €</td>
                    <td><?= number_format($subtotal, 2) ?> €</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        <a href="encomendas.php" class="editar-link">← Voltar às Encomendas</a>
    </p>
</div>
</body>
</html>
