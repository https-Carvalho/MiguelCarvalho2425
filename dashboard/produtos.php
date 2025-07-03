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

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $termo = $_GET['q'] ?? '';
    $resultados = listarPerfumes($termo); // Aqui usa a função com lógica completa
    header('Content-Type: application/json');
    echo json_encode($resultados);
    exit;
}



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

    if ($id_perfume) {
        if (!empty($_FILES['imagens_adicionais']['tmp_name'][0])) {
            inserirImagensAdicionais($id_perfume, $_FILES['imagens_adicionais']);
        }

        atualizarNotasPerfume($id_perfume, [
            'topo' => $_POST['notas_topo'] ?? [],
            'coracao' => $_POST['notas_coracao'] ?? [],
            'base' => $_POST['notas_base'] ?? []
        ]);

        atribuirFamiliaDominante();

        $_SESSION['sucesso'] = "Produto inserido com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao inserir o produto.";
    }

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
    <title>Adicionar Produto</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include('admin_layout.php'); ?>

    <div class="main-content">
        <h1>Adicionar Produto</h1>
        <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="alerta sucesso"><?= $_SESSION['sucesso'] ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="alerta erro"><?= $_SESSION['erro'] ?></div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>

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
                    <div class="familia-info">
                        Família Olfativa Prevista: <span id="nome-familia">Desconhecida</span>
                    </div>
                </div>
            </div>

            <div class="imagens-section">
                <div class="imagem-bloco">
                    <label>Imagem Principal:</label>
                    <input type="file" name="imagem" accept="image/*">
                    <div class="galeria-item" style="display: none;">
                        <img src="" alt="Imagem principal">
                        <span class="tipo-imagem">Principal</span>
                        <a href="#" class="btn-remover-imagem">×</a>
                    </div>
                </div>
                <div class="imagem-bloco">
                    <label>Imagem Hover:</label>
                    <input type="file" name="imagem_hover" accept="image/*">
                    <div class="galeria-item" style="display: none;">
                        <img src="" alt="Imagem hover">
                        <span class="tipo-imagem">Hover</span>
                        <a href="#" class="btn-remover-imagem">×</a>
                    </div>
                </div>
                <div class="imagem-bloco-adicionais-wrapper">
                    <label>Imagens Adicionais (até 3):</label>
                    <div class="imagem-bloco-adicionais">
                        <?php for ($i = 0; $i < 3; $i++): ?>
                            <div class="slot">
                                <input type="file" name="imagens_adicionais[]" accept="image/*">
                                <div class="galeria-item" style="display: none;">
                                    <img src="" alt="Imagem adicional">
                                    <span class="tipo-imagem">Adicional</span>
                                    <a href="#" class="btn-remover-imagem">×</a>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="botao-submit">Adicionar Produto</button>
        </form>

        <div class="tabela-produtos">
            <h2>Produtos Existentes</h2>
            <input type="text" id="pesquisa-produtos" placeholder="Pesquisar produtos..." autocomplete="off"
                style="padding: 10px; width: 100%; max-width: 400px; border-radius: 6px; border: 1px solid #ccc;">
            <table>
                <thead>
                    <tr>
                        <th>Numero</th>
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
                                <a href="eliminar_produto.php?id=<?= $produto['id_perfume'] ?>"
                                    class="eliminar-link">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
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

        document.addEventListener("DOMContentLoaded", function () {
            // 🔍 PESQUISA DE PRODUTOS
            const input = document.getElementById("pesquisa-produtos");
            const tbody = document.querySelector(".tabela-produtos tbody");

            if (input && tbody) {
                input.addEventListener("input", function () {
                    const termo = input.value.trim();

                    fetch(`produtos.php?ajax=1&q=${encodeURIComponent(termo)}`)
                        .then(response => response.json())
                        .then(produtos => {
                            tbody.innerHTML = "";

                            if (produtos.length === 0) {
                                const tr = document.createElement("tr");
                                tr.innerHTML = `<td colspan="5" style="text-align: center;">Nenhum resultado encontrado.</td>`;
                                tbody.appendChild(tr);
                                return;
                            }

                            produtos.forEach(produto => {
                                const tr = document.createElement("tr");
                                tr.innerHTML = `
                            <td>${produto.id_perfume}</td>
                            <td>${produto.nome}</td>
                            <td>${parseFloat(produto.preco).toFixed(2)} €</td>
                            <td>${produto.stock}</td>
                            <td>
                                <a href="editar_produto.php?id=${produto.id_perfume}" class="editar-link">Editar</a>
                                <a href="eliminar_produto.php?id=${produto.id_perfume}" class="eliminar-link">Eliminar</a>
                            </td>`;
                                tbody.appendChild(tr);
                            });
                        });
                });
            }
        });

        document.querySelectorAll('.btn-remover-imagem').forEach(btn => {
            if (!btn.dataset.listener) {
                btn.addEventListener('click', function (e) {
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

        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function (event) {
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
    </script>

</body>

</html>