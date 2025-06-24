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

$marcas = buscarMarcas();
$notas_gerais = buscarNotasOlfativas();
$produtos = listarPerfumes();

// Submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'preco' => $_POST['preco'] ?? 0,
        'stock' => $_POST['stock'] ?? 0,
        'id_marca' => $_POST['id_marca'] ?? null,
        'caminho_imagem' => null,
        'caminho_imagem_hover' => null,
        'id_familia' => null
    ];

    if (!empty($_FILES['imagem']['tmp_name'])) {
        $dados['caminho_imagem'] = guardarImagem($_FILES['imagem']);
    }
    if (!empty($_FILES['imagem_hover']['tmp_name'])) {
        $dados['caminho_imagem_hover'] = guardarImagem($_FILES['imagem_hover']);
    }

    $id_perfume = inserirPerfume($dados);

    if (!empty($_FILES['imagens_adicionais']['tmp_name'][0])) {
        inserirImagensAdicionais($id_perfume, $_FILES['imagens_adicionais']);
    }

    atualizarNotasPerfume($id_perfume, [
        'topo' => $_POST['notas_topo'] ?? [],
        'coracao' => $_POST['notas_coracao'] ?? [],
        'base' => $_POST['notas_base'] ?? []
    ]);

    atribuirFamiliaDominante();
    header("Location: produtos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Produto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include('admin_layout.php'); ?>

<div class="container-geral">
    <h1>Adicionar Produto</h1>

    <form method="post" enctype="multipart/form-data" class="form-container">

        <div class="linha-superior">

            <div class="form-left">
                <label>Nome:</label>
                <input type="text" name="nome" required>

                <label>Descrição:</label>
                <textarea name="descricao" required></textarea>

                <label>Preço:</label>
                <input type="number" step="0.01" name="preco" required>

                <label>Stock:</label>
                <input type="number" name="stock" required>

                <label>Marca:</label>
                <select name="id_marca" required>
                    <option value="">-- Escolha a Marca --</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?= $marca['id_marca'] ?>"><?= htmlspecialchars($marca['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-right">
                <fieldset>
                    <legend>Notas Olfativas</legend>

                    <div class="dropdown-notas">
                        <button type="button" onclick="toggleDropdown('topo')">Notas de Topo</button>
                        <div class="dropdown-list" id="dropdown-topo">
                            <?php foreach ($notas_gerais as $nota): ?>
                                <label>
                                    <input type="checkbox" name="notas_topo[]" value="<?= $nota['id_nota'] ?>">
                                    <?= htmlspecialchars($nota['nome_nota']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dropdown-notas">
                        <button type="button" onclick="toggleDropdown('coracao')">Notas de Coração</button>
                        <div class="dropdown-list" id="dropdown-coracao">
                            <?php foreach ($notas_gerais as $nota): ?>
                                <label>
                                    <input type="checkbox" name="notas_coracao[]" value="<?= $nota['id_nota'] ?>">
                                    <?= htmlspecialchars($nota['nome_nota']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dropdown-notas">
                        <button type="button" onclick="toggleDropdown('base')">Notas de Base</button>
                        <div class="dropdown-list" id="dropdown-base">
                            <?php foreach ($notas_gerais as $nota): ?>
                                <label>
                                    <input type="checkbox" name="notas_base[]" value="<?= $nota['id_nota'] ?>">
                                    <?= htmlspecialchars($nota['nome_nota']) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </fieldset>
            </div>

        </div>

        <div class="imagens-section">
            <div class="imagem-bloco">
                <label>Imagem Principal:</label>
                <input type="file" name="imagem" required>
            </div>

            <div class="imagem-bloco">
                <label>Imagem Hover:</label>
                <input type="file" name="imagem_hover" required>
            </div>

            <div class="imagem-bloco">
                <label>Imagens Adicionais (até 3):</label>
                <input type="file" name="imagens_adicionais[]" multiple accept="image/*">
            </div>
        </div>

        <button type="submit" class="botao-submit">Adicionar Produto</button>
    </form>

    <h2>Produtos Existentes</h2>
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
                    <a href="editar_produto.php?id=<?= $produto['id_perfume'] ?>" class="editar-link">Editar</a>
                    <a href="eliminar_produto.php?id=<?= $produto['id_perfume'] ?>" class="eliminar-link">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


</div>

<script>
    function toggleDropdown(tipo) {
        document.getElementById('dropdown-' + tipo).classList.toggle('active');
    }
</script>

</body>
</html>
