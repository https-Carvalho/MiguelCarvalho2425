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
$marcas = listarMarcasDashboard();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'caminho_imagem' => null
    ];

    if (!empty($_FILES['imagem']['tmp_name'])) {
        $dados['caminho_imagem'] = guardarImagem($_FILES['imagem']);
    }

    $sucesso = inserirMarca($dados);

    if ($sucesso) {
        $_SESSION['sucesso'] = "Marca adicionada com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao adicionar marca.";
    }

    header("Location: marcas.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Marcas</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include('admin_layout.php'); ?>

    <div class="container-geral">
        <div class="main-content">
            <h1>Adicionar Marca</h1>

            <?php if (!empty($_SESSION['sucesso'])): ?>
                <div class="alerta sucesso"><?= $_SESSION['sucesso'] ?></div>
                <?php unset($_SESSION['sucesso']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['erro'])): ?>
                <div class="alerta erro"><?= $_SESSION['erro'] ?></div>
                <?php unset($_SESSION['erro']); ?>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="form-container">
                <label>Nome da Marca:</label>
                <input type="text" name="nome" required>

                <label>Descrição:</label>
                <textarea name="descricao" required></textarea>

                <div class="imagens-section">
                    <div class="imagem-bloco">
                        <label>Imagem da Marca:</label>
                        <input type="file" name="imagem" accept="image/*">
                        <div class="galeria-item" style="display: none;">
                            <img src="" alt="Imagem da Marca">
                            <span class="tipo-imagem">Marca</span>
                            <a href="#" class="btn-remover-imagem">×</a>
                        </div>
                    </div>
                </div>

                <button type="submit" class="botao-submit">Adicionar Marca</button>
            </form>

            <div class="tabela-produtos">
                <h2>Marcas Existentes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($marcas as $marca): ?>
                            <tr>
                                <td><?= $marca['id_marca'] ?></td>
                                <td><?= htmlspecialchars($marca['nome']) ?></td>
                                <td>
                                    <a href="editar_marca.php?id=<?= $marca['id_marca'] ?>" class="editar-link">Editar</a>
                                    <a href="eliminar_marca.php?id=<?= $marca['id_marca'] ?>"
                                        class="eliminar-link">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Remover imagem
        document.querySelectorAll('.btn-remover-imagem').forEach(btn => {
            if (!btn.dataset.listener) {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    if (confirm("Tem a certeza que deseja remover esta imagem?")) {
                        const galeriaItem = e.target.closest('.galeria-item');
                        const blocoImagem = galeriaItem.closest('.slot') || galeriaItem.closest('.imagem-bloco');
                        galeriaItem.style.display = 'none';

                        const inputFile = blocoImagem.querySelector('input[type="file"]');
                        if (inputFile) {
                            inputFile.style.display = 'block';
                            inputFile.value = '';
                        }
                    }
                });
                btn.dataset.listener = 'true';
            }
        });

        // Preview da imagem ao selecionar
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', event => {
                const file = event.target.files[0];
                if (file) {
                    const blocoImagem = input.closest('.slot') || input.closest('.imagem-bloco');
                    const galeriaItem = blocoImagem.querySelector('.galeria-item');
                    const imgPreview = galeriaItem?.querySelector('img');

                    if (imgPreview) {
                        imgPreview.src = URL.createObjectURL(file);
                        galeriaItem.style.display = 'block';
                        input.style.display = 'none';
                    }
                }
            });
        });
    });
</script>


</html>