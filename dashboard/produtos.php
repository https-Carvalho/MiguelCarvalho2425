<?php
// ================== [INÍCIO SESSÃO & VERIFICAÇÃO DE PERMISSÕES] ==================
session_start();
include('../config.php');

$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
$nome = $_SESSION['username'] ?? 'Desconhecido';

if ($tipo_usuario !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

// ================== [OBTENÇÃO DOS PRODUTOS] ==================
$produtos = listarPerfumes(); // Função definida em config.php
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Produtos</title>
    <link rel="stylesheet" href="./styles.css">
</head>

<body>
    <!-- ================== [SIDEBAR ADMIN] ================== -->
    <div class="sidebar">
        <h2>Admin - <?= htmlspecialchars($nome) ?></h2>
        <ul>
            <li><a href="dashboard.php">📊 Painel</a></li>
            <li><a href="produtos.php">📦 Produtos</a></li>
            <li><a href="marcas.php">🏷️ Marcas</a></li>
            <li><a href="encomendas.php">📑 Encomendas</a></li>
            <li><a href="contas.php">👤 Contas</a></li>
            <li><a href="../index.php">← Voltar à Loja</a></li>
        </ul>
    </div>

    <!-- ================== [CONTEÚDO PRINCIPAL] ================== -->
    <div class="main-content">
        <h1>Produtos</h1>
        <a href="adicionar_produto.php" class="botao-adicionar">+ Adicionar Produto</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Stock</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><?= $produto['id_perfume'] ?></td>
                    <td><?= htmlspecialchars($produto['nome']) ?></td>
                    <td><?= number_format($produto['preco'], 2) ?> €</td>
                    <td><?= $produto['stock'] ?></td>
                    <td>
                        <a href="editar_produto.php?id=<?= $produto['id_perfume'] ?>">Editar</a>
                        <a href="eliminar_produto.php?id=<?= $produto['id_perfume'] ?>" class="eliminar-link">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ================== [MODAL DE CONFIRMAÇÃO] ================== -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <p>Tem a certeza que deseja eliminar este produto?</p>
            <div class="modal-buttons">
                <button id="confirmYes">Sim</button>
                <button id="confirmNo">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- ================== [SCRIPT DO MODAL] ================== -->
    <script>
        const modal = document.getElementById('confirmModal');
        let currentLink = null;

        document.querySelectorAll('a.eliminar-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                currentLink = this;
                modal.style.display = 'flex';
            });
        });

        document.getElementById('confirmYes').onclick = function () {
            if (currentLink) {
                window.location.href = currentLink.href;
            }
        };

        document.getElementById('confirmNo').onclick = function () {
            modal.style.display = 'none';
            currentLink = null;
        };

        // Fecha modal se clicar fora
        window.onclick = function (event) {
            if (event.target === modal) {
                modal.style.display = "none";
                currentLink = null;
            }
        };
    </script>

</body>
</html>
