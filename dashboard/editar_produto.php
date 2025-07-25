<?php
session_start();
include('../config.php');

$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';

if ($tipo_utilizador !== 'Admin') {
    header('Location: ../index.php');
    exit();
}

$id_perfume = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_perfume <= 0) {
    header('Location: produtos.php');
    exit();
}

$perfume = buscarInformacoesComNotas($id_perfume, true);
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
<script>
    const familiaNotas = <?= json_encode(buscarFamiliaPorNota()) ?>;
</script>

<head>
    <meta charset="UTF-8">
    <title>Editar Perfume</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="main-content">
        <h1>Editar Perfume: <?= htmlspecialchars($perfume['nome']) ?></h1>

        <form method="post" enctype="multipart/form-data" class="form-container">

            <!-- LINHA SUPERIOR COM INFO E NOTAS -->

            <div class="linha-superior">

                <!-- COLUNA ESQUERDA: INFO GERAL -->
                <div class="form-left">
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
                </div>

                <!-- COLUNA DIREITA: NOTAS OLFACTIVAS -->
                <div class="form-right">

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
                        Família Olfativa Atual: <span
                            id="nome-familia"><?= htmlspecialchars($perfume['nome_familia'] ?? 'Desconhecida') ?></span>
                    </div>

                </div>
            </div>

            <!-- ÁREA DE IMAGENS (ABAIXO DE TUDO) -->
            <div class="imagens-section">

                <!-- IMAGEM PRINCIPAL -->
                <div class="imagem-bloco">
                    <label>Imagem Principal:</label>

                    <?php if (!empty($perfume['caminho_imagem'])): ?>
                        <div class="galeria-item">
                            <img src="<?= '../' . htmlspecialchars($perfume['caminho_imagem']) ?>" alt="Imagem principal">
                            <span class="tipo-imagem">Principal</span>
                            <a href="#" class="btn-remover-imagem">×</a>
                        </div>
                        <input type="file" name="imagem" style="display: none;" accept="image/*">
                    <?php else: ?>
                        <input type="file" name="imagem" accept="image/*">
                        <div class="galeria-item" style="display: none;">
                            <img src="" alt="Imagem principal">
                            <span class="tipo-imagem">Principal</span>
                            <a href="#" class="btn-remover-imagem">×</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- IMAGEM HOVER -->
                <div class="imagem-bloco">
                    <label>Imagem Hover:</label>

                    <?php if (!empty($perfume['caminho_imagem_hover'])): ?>
                        <div class="galeria-item">
                            <img src="<?= '../' . htmlspecialchars($perfume['caminho_imagem_hover']) ?>" alt="Imagem hover">
                            <span class="tipo-imagem">Hover</span>
                            <a href="#" class="btn-remover-imagem">×</a>
                        </div>
                        <input type="file" name="imagem_hover" style="display: none;" accept="image/*">
                    <?php else: ?>
                        <input type="file" name="imagem_hover" accept="image/*">
                        <div class="galeria-item" style="display: none;">
                            <img src="" alt="Imagem hover">
                            <span class="tipo-imagem">Hover</span>
                            <a href="#" class="btn-remover-imagem">×</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="imagem-bloco-adicionais-wrapper">
                    <label>Imagens Adicionais (até 3):</label>
                    <div class="imagem-bloco-adicionais">
                        <div class="imagem-bloco-adicionais">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="slot">
                                    <?php if (!empty($imagens[$i]['caminho_imagem'])): ?>
                                        <div class="galeria-item">
                                            <img src="<?= '../' . htmlspecialchars($imagens[$i]['caminho_imagem']) ?>"
                                                alt="Imagem adicional">
                                            <span class="tipo-imagem">Adicional</span>
                                            <a href="#" class="btn-remover-imagem">×</a>
                                        </div>
                                        <input type="file" name="imagens_adicionais[]" style="display: none;" accept="image/*">
                                    <?php else: ?>
                                        <input type="file" name="imagens_adicionais[]" accept="image/*">
                                        <div class="galeria-item" style="display: none;">
                                            <img src="" alt="Imagem adicional">
                                            <span class="tipo-imagem">Adicional</span>
                                            <a href="#" class="btn-remover-imagem">×</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit">Guardar Alterações</button>
        </form>
    </div>
    <script>
        function toggleDropdown(tipo) {
            document.getElementById('dropdown-' + tipo).classList.toggle('active');
        }

        document.querySelectorAll('input[type="checkbox"][name^="notas_"]').forEach(cb => {
            cb.addEventListener('change', atualizarFamiliaPrevista);
        });

        function atualizarFamiliaPrevista() {
            const selecionadas = [...document.querySelectorAll('input[type="checkbox"][name^="notas_"]:checked')];
            const contagem = {};
            const nomes = {};

            selecionadas.forEach(cb => {
                const idNota = cb.value;
                const familia = familiaNotas[idNota];
                if (familia) {
                    const id = familia.id_familia;
                    contagem[id] = (contagem[id] || 0) + 1;
                    nomes[id] = familia.nome_familia;
                }
            });

            let familiaDominante = 'Desconhecida';
            let max = 0;
            for (const id in contagem) {
                if (contagem[id] > max) {
                    max = contagem[id];
                    familiaDominante = nomes[id];
                }
            }

            document.getElementById('nome-familia').textContent = familiaDominante;
        }

        // -------------------------------
        // GESTÃO DAS IMAGENS ADICIONAIS
        // -------------------------------
        document.addEventListener('DOMContentLoaded', () => {
            // REMOVER imagem
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

            // PREVIEW ao selecionar nova imagem
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
</body>

</html>