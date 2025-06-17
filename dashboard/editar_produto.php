<?php
session_start();
include('../config.php');

$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
if ($tipo_usuario !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

$id_perfume = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_perfume <= 0) {
    header('Location: produtos.php');
    exit();
}

$perfume = buscarInformacoesComNotas($id_perfume);
$imagens = buscarImagensPerfumeComId($id_perfume); // com ID
$notas_gerais = buscarNotasOlfativas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'nome' => $_POST['nome'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'preco' => $_POST['preco'] ?? 0,
        'stock' => $_POST['stock'] ?? 0,
        'id_marca' => $_POST['id_marca'] ?? null,
        'caminho_imagem' => $perfume['caminho_imagem'],
        'caminho_imagem_hover' => $perfume['caminho_imagem_hover'],
        'id_familia' => $perfume['id_familia']
    ];

    if (!empty($_FILES['imagem']['tmp_name'])) {
        $dados['caminho_imagem'] = guardarImagem($_FILES['imagem']);
    }
    if (!empty($_FILES['imagem_hover']['tmp_name'])) {
        $dados['caminho_imagem_hover'] = guardarImagem($_FILES['imagem_hover']);
    }

    editarPerfume($id_perfume, $dados);

    if (!empty($_FILES['imagens_adicionais']['tmp_name'])) {
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
    <title>Editar Perfume</title>
    <link rel="stylesheet" href="styles.css">   
</head>
<body>

    <div class="main-content">
        <h1>Editar Perfume: <?= htmlspecialchars($perfume['nome']) ?></h1>

        <form method="post" enctype="multipart/form-data">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($perfume['nome']) ?>" required>

            <label>Descrição:</label>
            <textarea name="descricao" required><?= htmlspecialchars($perfume['descricao']) ?></textarea>

            <label>Preço:</label>
            <input type="number" step="0.01" name="preco" value="<?= $perfume['preco'] ?>" required>

            <label>Stock:</label>
            <input type="number" name="stock" value="<?= $perfume['stock'] ?>" required>

            <label>Marca:</label>
            <select name="id_marca" required>
                <?php foreach (buscarMarcas() as $marca): ?>
                    <option value="<?= $marca['id_marca'] ?>" <?= $marca['id_marca'] == $perfume['id_marca'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($marca['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="galeria-container">
                <!-- Galeria de Imagens -->
                <label>Imagem Principal:</label>
                <?php if (!empty($perfume['caminho_imagem'])): ?>
                    <div class="galeria-item principal">
                        <img src="<?= htmlspecialchars($perfume['caminho_imagem']) ?>" alt="Imagem principal">
                        <span class="tipo-imagem">Principal</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagem">

                <label>Imagem Hover:</label>
                <?php if (!empty($perfume['caminho_imagem_hover'])): ?>
                    <div class="galeria-item hover">
                        <img src="<?= htmlspecialchars($perfume['caminho_imagem_hover']) ?>" alt="Imagem hover">
                        <span class="tipo-imagem">Hover</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagem_hover">

                <label>Imagens Adicionais (até 3):</label>
                <div class="galeria-container">
                    <?php foreach (array_slice($imagens, 0, 3) as $img): ?>
                        <label><?php print($img ) ?></label>
                        <div class="galeria-item adicional">
                            <img src="<?= htmlspecialchars($img['caminho_imagem']) ?>" alt="Imagem adicional">
                            <a href="remover_imagem.php?id=<?= $img['id'] ?>&id_perfume=<?= $id_perfume ?>" class="btn-remover-imagem" onclick="return confirm('Remover esta imagem?')">×</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($imagens) < 3): ?>
                    <input type="file" name="imagens_adicionais[]" multiple accept="image/*">
                <?php endif; ?>
            </div>

            <fieldset>
                <legend>Notas Olfativas</legend>

                <div class="dropdown-notas">
                    <button type="button" onclick="toggleDropdown('topo')">Notas de Topo</button>
                    <div class="dropdown-list" id="dropdown-topo">
                        <?php foreach ($notas_gerais as $nota): ?>
                            <label>
                                <input type="checkbox" name="notas_topo[]" value="<?= $nota['id_nota'] ?>" 
                                <?= in_array($nota['id_nota'], array_column($perfume['notas']['topo'], 'id_nota')) ? 'checked' : '' ?>>
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
                                <input type="checkbox" name="notas_coracao[]" value="<?= $nota['id_nota'] ?>" 
                                <?= in_array($nota['id_nota'], array_column($perfume['notas']['coracao'], 'id_nota')) ? 'checked' : '' ?>>
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
                                <input type="checkbox" name="notas_base[]" value="<?= $nota['id_nota'] ?>" 
                                <?= in_array($nota['id_nota'], array_column($perfume['notas']['base'], 'id_nota')) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($nota['nome_nota']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </fieldset>

            <div class="familia-info">
                Família Olfativa Atual: <?= htmlspecialchars($perfume['nome_familia'] ?? 'Desconhecida') ?>
            </div>

            <button type="submit">Guardar Alterações</button>
        </form>
    </div>

    <script>
    function toggleDropdown(tipo) {
        document.getElementById('dropdown-' + tipo).classList.toggle('active');
    }
    </script>

</body>
</html>
