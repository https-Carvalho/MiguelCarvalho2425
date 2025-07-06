<?php
session_start();
include('../config.php');

// Verifica se é admin
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';

if ($tipo_utilizador !== 'Admin') {
    header('Location: ../index.php');
    exit();
}
// Busca todas as famílias
$familias = buscarFamiliasOlfativas();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão Olfativa</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include('admin_layout.php'); ?>

    <div class="main-content">
        <h1>Gestão Olfativa</h1>
        <button class="btn-success" onclick="abrirModalAdicionarFamilia()">+ Nova Família</button>

        <div id="familias">
            <?php foreach ($familias as $familia): ?>
                <div class="familia-box"
                    onclick="window.location.href='notas_familia.php?id=<?= $familia['id_familia'] ?>'">
                    <div class="familia-header">
                        <h3 onclick="mostrarNotas(<?= $familia['id_familia'] ?>)">
                            <?= htmlspecialchars($familia['nome_familia']) ?>
                        </h3>
                        <div class="btns">
                            <button class="btn-editar"
                                onclick="event.stopPropagation(); abrirEditarFamilia(<?= $familia['id_familia'] ?>, '<?= htmlspecialchars($familia['nome_familia']) ?>')">Editar</button>
                            <button class="btn-danger"
                                onclick="event.stopPropagation(); eliminarFamilia(<?= $familia['id_familia'] ?>)">Eliminar</button>
                        </div>

                    </div>

                    <div class="notas-container" id="notas-<?= $familia['id_familia'] ?>" style="display: none;">
                        <!-- Notas serão carregadas via JS -->
                        <p><em>A carregar notas...</em></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Família -->
    <div class="modal-olfativa" id="modalFamilia">
        <div class="modal-conteudo">
            <h3 id="titulo-modal-familia">Nova Família</h3>
            <input type="hidden" id="idFamiliaEditar">
            <input type="text" id="nomeFamilia" placeholder="Nome da Família">
            <div class="modal-botoes">
                <button class="btn-success" onclick="submeterFamilia()">Guardar</button>
                <button class="btn-danger" onclick="fecharModalFamilia()">Fechar</button>
            </div>
        </div>
    </div>

    <!-- Modal Nota -->
    <div class="modal-olfativa" id="modalNota">
        <div class="modal-conteudo">
            <h3>Nova Nota</h3>
            <input type="hidden" id="idFamiliaNota">
            <input type="text" id="nomeNota" placeholder="Nome da Nota">
            <div class="modal-botoes">
                <button class="btn-success" onclick="submeterNota()">Adicionar</button>
                <button class="btn-danger" onclick="fecharModalNota()">Fechar</button>
            </div>
        </div>
    </div>

    <script>
        function mostrarNotas(idFamilia) {
            const container = document.getElementById('notas-' + idFamilia);
            if (container.style.display === 'none') {
                fetch('ajax_notas_familia.php?id=' + idFamilia)
                    .then(resp => resp.json())
                    .then(notas => {
                        container.innerHTML = '';
                        if (notas.length === 0) {
                            container.innerHTML = '<p><em>Sem notas associadas.</em></p>';
                        } else {
                            notas.forEach(nota => {
                                const div = document.createElement('div');
                                div.classList.add('nota-item');
                                div.innerHTML = `
                            <span>${nota.nome_nota}</span>
                            <button class="btn-danger" onclick="eliminarNota(${idFamilia}, ${nota.id_nota})">×</button>
                        `;
                                container.appendChild(div);
                            });
                        }
                        container.style.display = 'block';
                    });
            } else {
                container.style.display = 'none';
            }
        }

        function eliminarFamilia(id) {
            if (confirm("Eliminar a família e todas as suas notas?")) {
                fetch('ajax_familia.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'acao=eliminar_familia&id=' + id
                }).then(() => location.reload());
            }
        }

        function eliminarNota(idFamilia, idNota) {
            if (confirm("Eliminar esta nota da família?")) {
                fetch('ajax_familia.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        acao: 'remover_nota',
                        id_familia: idFamilia,
                        id_nota: idNota
                    })
                }).then(() => mostrarNotas(idFamilia));
            }
        }

        function abrirModalAdicionarFamilia() {
            document.getElementById("titulo-modal-familia").textContent = "Nova Família";
            document.getElementById("idFamiliaEditar").value = '';
            document.getElementById("nomeFamilia").value = '';
            document.getElementById("modalFamilia").style.display = "flex";
        }

        function abrirEditarFamilia(id, nome) {
            document.getElementById("titulo-modal-familia").textContent = "Editar Família";
            document.getElementById("idFamiliaEditar").value = id;
            document.getElementById("nomeFamilia").value = nome;
            document.getElementById("modalFamilia").style.display = "flex";
        }

        function submeterFamilia() {
            const id = document.getElementById("idFamiliaEditar").value;
            const nome = document.getElementById("nomeFamilia").value.trim();
            if (!nome) return alert("Insere o nome.");

            const acao = id ? 'editar_familia' : 'adicionar_familia';
            const dados = new URLSearchParams({ acao, nome });
            if (id) dados.append('id', id);

            fetch('ajax_familia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: dados
            }).then(() => location.reload());
        }

        function fecharModalFamilia() {
            document.getElementById("modalFamilia").style.display = "none";
        }

        function abrirModalAdicionarNota(idFamilia) {
            document.getElementById("idFamiliaNota").value = idFamilia;
            document.getElementById("nomeNota").value = '';
            document.getElementById("modalNota").style.display = "flex";
        }

        function fecharModalNota() {
            document.getElementById("modalNota").style.display = "none";
        }

        function submeterNota() {
            const idFamilia = document.getElementById("idFamiliaNota").value;
            const nome = document.getElementById("nomeNota").value.trim();
            if (!nome) return alert("Insere o nome da nota.");

            fetch('ajax_familia.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    acao: 'adicionar_nota',
                    id_familia: idFamilia,
                    nome_nota: nome
                })
            }).then(() => {
                fecharModalNota();
                mostrarNotas(idFamilia);
            });
        }
    </script>
</body>

</html>