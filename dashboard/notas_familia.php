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

// Verifica ID da família
$id_familia = $_GET['id'] ?? null;
if (!$id_familia) {
    die("Família não especificada.");
}

$familia = buscarDetalhesFamilia($id_familia);
$notas = buscarNotasDaFamilia($id_familia);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Notas da Família</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include('admin_layout.php'); ?>

<div class="main-content">
    <h1><?= htmlspecialchars($familia['nome_familia']) ?></h1>
    <p><?= nl2br(htmlspecialchars($familia['descricao'])) ?></p>

    <button class="btn-success" onclick="abrirModalNovaNota()">+ Adicionar Nota</button>

    <div class="notas-lista">
        <?php foreach ($notas as $nota): ?>
            <div class="nota-item">
                <span><?= htmlspecialchars($nota['nome_nota']) ?></span>
                <div>
                    <button class="btn-editar"
                            onclick="abrirModalEditarNota(<?= $nota['id_nota'] ?>, '<?= addslashes($nota['nome_nota']) ?>')">
                        Editar
                    </button>
                    <button class="btn-danger"
                            onclick="eliminarNota(<?= $id_familia ?>, <?= $nota['id_nota'] ?>)">
                        Eliminar
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal de Nota -->
<div id="modal-nota" class="modal" style="display: none;">
    <div class="modal-content">
        <h3 id="titulo-modal">Adicionar Nota</h3>
        <input type="text" id="input-nota" placeholder="Nome da nota">
        <div class="modal-buttons">
            <button class="btn-success" onclick="confirmarNota()">Guardar</button>
            <button class="btn-danger" onclick="fecharModalNota()">Cancelar</button>
        </div>
    </div>
</div>

<script>
    let modoEdicao = false;
    let idNotaEdicao = null;

    function abrirModalNovaNota() {
        modoEdicao = false;
        idNotaEdicao = null;
        document.getElementById('titulo-modal').innerText = "Adicionar Nota";
        document.getElementById('input-nota').value = "";
        document.getElementById('modal-nota').style.display = "flex";
    }

    function abrirModalEditarNota(id, nome) {
        modoEdicao = true;
        idNotaEdicao = id;
        document.getElementById('titulo-modal').innerText = "Editar Nota";
        document.getElementById('input-nota').value = nome;
        document.getElementById('modal-nota').style.display = "flex";
    }

    function fecharModalNota() {
        document.getElementById('modal-nota').style.display = "none";
    }

    function confirmarNota() {
        const nome = document.getElementById('input-nota').value.trim();
        if (!nome) return alert("Escreve um nome!");

        const body = new URLSearchParams({
            acao: modoEdicao ? 'editar_nota' : 'adicionar_nota',
            id_familia: <?= $id_familia ?>,
            nome_nota: nome
        });

        if (modoEdicao) body.append("id_nota", idNotaEdicao);

        fetch('ajax_familia.php', {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body
        }).then(() => location.reload());
    }

    function eliminarNota(idFamilia, idNota) {
        if (confirm("Eliminar esta nota?")) {
            fetch('ajax_familia.php', {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    acao: 'remover_nota',
                    id_familia: idFamilia,
                    id_nota: idNota
                })
            }).then(() => location.reload());
        }
    }
</script>
</body>
</html>
