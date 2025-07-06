<?php
session_start();
include('../config.php');

// Verifica permissões
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';

if ($tipo_utilizador !== 'Admin') {
    header('Location: ../index.php');
    exit();
}
$encomendas = listarEncomendas(); // usa a função existente no config.php
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Encomendas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('admin_layout.php'); ?>

<div class="main-content">
    <h1>Encomendas</h1>
    <p>Gestão das encomendas feitas pelos utilizadores.</p>

    <div class="tabela-produtos">
        <table>
            <thead>
                <tr>
                    <th>Numero</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($encomendas as $enc): ?>
                    <tr>
                        <td><?= $enc['id_encomenda'] ?></td>
                        <td><?= htmlspecialchars($enc['username']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($enc['data_encomenda'])) ?></td>
                        <td><?= number_format($enc['total'], 2) ?> €</td>
                        <td><?= htmlspecialchars($enc['status']) ?></td>
                        <td>
                            <a href="ver_encomenda.php?id=<?= $enc['id_encomenda'] ?>" class="editar-link">Ver Detalhes</a>
                            <a href="alterar_estado.php?id=<?= $enc['id_encomenda'] ?>" class="eliminar-link">Alterar Estado</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($encomendas)): ?>
                    <tr><td colspan="6" style="text-align: center;">Nenhuma encomenda encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
