<?php
session_start();
include('config.php');

$mostrarResumo = false; // <- evita warning se a página for acedida por GET


$id_sessao = $_SESSION['id_sessao'] ?? null;
$id_cliente = $id_sessao;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['clientname'] ?? 'Conta';

// Carrinho só para cliente
$itensCarrinho = buscarItensCarrinho($id_cliente);
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

$moradas = buscarMoradasCliente($id_cliente);
$totalCompra = 0;
foreach ($itensCarrinho as $item) {
    $totalCompra += $item['preco'] * $item['quantidade'];
}

$mensagem = '';

// Processamento de formulários

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['usar_existente'])) {
        $_SESSION['id_morada_checkout'] = $_POST['morada_id'];
        $moradaSelecionada = buscarMoradaPorId($_POST['morada_id']);
        $mostrarResumo = true;
    }

    if (isset($_POST['nova_morada'])) {
        $dados = [
            'endereco' => trim($_POST['endereco']),
            'andar' => trim($_POST['andar']),
            'porta' => trim($_POST['porta']),
            'codigo_postal' => trim($_POST['codigo_postal']),
            'cidade' => trim($_POST['cidade']),
            'pais' => trim($_POST['pais']),
        ];
        if (guardarMoradaCliente($id_cliente, $dados)) {
            $mensagem = "Nova morada adicionada.";
            $moradas = buscarMoradasCliente($id_cliente);
        } else {
            $mensagem = "Erro ao guardar morada.";
        }
    }

    if (isset($_POST['confirmar_pagamento'])) {
        unset($_SESSION['veio_do_carrinho']);
        header("Location: checkout.php");
        exit;
    }

    if (isset($_POST['trocar_morada'])) {
        $mostrarResumo = false;
        unset($_SESSION['id_morada_checkout']);
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Selecionar Morada</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include('menu.php'); ?>

    <div class="perfil-container">
        <h1>Selecionar Morada de Envio</h1>

        <?php if ($mensagem): ?>
            <p class="mensagem-perfil"><?= htmlspecialchars($mensagem); ?></p>
        <?php endif; ?>

        <?php if (!$mostrarResumo): ?>
            <?php if (!empty($moradas)): ?>
                <form method="POST">
                    <?php foreach ($moradas as $morada): ?>
                        <div class="morada-opcao">
                            <input type="radio" name="morada_id" value="<?= $morada['id_morada'] ?>" required>
                            <?= htmlspecialchars($morada['endereco']) ?>,
                            <?= htmlspecialchars($morada['codigo_postal']) ?>             <?= htmlspecialchars($morada['cidade']) ?>,
                            <?= htmlspecialchars($morada['pais']) ?>
                        </div>
                    <?php endforeach; ?>
                    <button class="botao-morada" type="submit" name="usar_existente">Usar Morada Selecionada</button>
                </form>
            <?php else: ?>
                <p>Não tem moradas registadas ainda.</p>
            <?php endif; ?>

            <h2>Adicionar Nova Morada</h2>
            <form method="POST">
                <div class="dropdown-nova-morada">
                    <div class="dropdown-header" onclick="toggleDropdown()">
                        + Adicionar Nova Morada
                    </div>
                    <div class="dropdown-content_morada" id="novaMoradaContent">
                        <input type="text" name="endereco" placeholder="Endereço" required>
                        <input type="text" name="andar" placeholder="Andar">
                        <input type="text" name="porta" placeholder="Porta">
                        <input type="text" name="codigo_postal" placeholder="Código Postal" required>
                        <input type="text" name="cidade" placeholder="Cidade" required>
                        <input type="text" name="pais" placeholder="País" required>
                        <button class="botao-morada" type="submit" name="nova_morada">Guardar Morada</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <h2>Resumo da Encomenda</h2>
            <div class="resumo-box">
                <h3>Morada de Envio:</h3>
                <p>
                    <?= htmlspecialchars($moradaSelecionada['endereco']) ?>,
                    <?= htmlspecialchars($moradaSelecionada['andar']) ?>
                    <?= htmlspecialchars($moradaSelecionada['porta']) ?><br>
                    <?= htmlspecialchars($moradaSelecionada['codigo_postal']) ?>
                    <?= htmlspecialchars($moradaSelecionada['cidade']) ?>,
                    <?= htmlspecialchars($moradaSelecionada['pais']) ?>
                </p>

                <h3>Produtos no Carrinho:</h3>
                <ul>
                    <?php foreach ($itensCarrinho as $item): ?>
                        <li><?= htmlspecialchars($item['nome']) ?> - <?= $item['quantidade'] ?> x
                            <?= number_format($item['preco'], 2, ',', ' ') ?> €
                        </li>
                    <?php endforeach; ?>
                </ul>

                <p><strong>Total: <?= number_format($totalCompra, 2, ',', ' ') ?> €</strong></p>

                <form method="POST" class="resumo-actions">
                    <button type="submit" name="confirmar_pagamento">Confirmar e Pagar</button>
                    <button type="submit" name="trocar_morada">Trocar Morada</button>
                </form>

            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleDropdown() {
            const content = document.getElementById("novaMoradaContent");
            content.style.display = content.style.display === "block" ? "none" : "block";
        }
    </script>

</body>

</html>