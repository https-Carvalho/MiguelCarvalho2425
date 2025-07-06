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

// ✅ Usa funções da region DASHBOARD
$total_perfumes = contarPerfumes();
$total_marcas = contarMarcas();
$total_encomendas = contarEncomendas();
$total_utilizadores = contarUtilizadores();
$total_vendas = somarTotalVendas();

$encomendasPorMes = encomendasUltimosMeses();
$labels = array_reverse(array_column($encomendasPorMes, 'mes'));
$valores = array_reverse(array_column($encomendasPorMes, 'total'));
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php include('admin_layout.php'); ?>


    <div class="main-content">
        <h1>Bem-vindo ao Painel</h1>
        <p>Utilize o menu lateral para gerir a loja.</p>

        <div class="estatisticas">
            <div class="box"><strong><?= $total_perfumes ?></strong><br>Perfumes</div>
            <div class="box"><strong><?= $total_marcas ?></strong><br>Marcas</div>
            <div class="box"><strong><?= $total_utilizadores ?></strong><br>Utilizadores</div>
            <div class="box"><strong><?= $total_encomendas ?></strong><br>Encomendas</div>
            <div class="box"><strong><?= number_format($total_vendas, 2) ?> €</strong><br>Total Vendido</div>
        </div>

        <div class="grafico-container">
            <canvas id="graficoEncomendas"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('graficoEncomendas').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Encomendas por Mês',
                        data: <?= json_encode($valores) ?>,
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }
                }
            });
        </script>
    </div>
</body>

</html>