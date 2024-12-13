<?php
include 'config.php';

// Obtém as marcas agrupadas em ordem alfabética
$marcas = buscarMarcasAgrupadas();
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marcas - Perfumes Nicho</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Menu de Navegação -->
    <nav class="menu">
    <div class="logo">
        <a href="index.php">LuxFragrance</a>
    </div>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="#">Discovery Kit</a></li>
            <li class="dropdown">
                <a href="#">Marcas </a>
                <div class="dropdown-content">
                    <?php foreach ($marcas as $inicial => $grupoMarcas): ?>
                        <div class="column">
                            <h3><?php echo htmlspecialchars($inicial); ?></h3>
                            <?php foreach ($grupoMarcas as $marca): ?>
                                <p>
                                    <a href="marca.php?id=<?php echo htmlspecialchars($marca['id_marca']); ?>">
                                        <?php echo htmlspecialchars($marca['nome']); ?>
                                    </a>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="view-all">
                        <a href="todas_marcas.php">Ver todas as marcas</a>
                    </div>
                </div>
            </li>
            <li><a href="#">Família Olfativa </a></li>
            <li><a href="#">Categorias </a></li>
            <li><a href="#">Sobre Nós </a></li>
            <li><a href="#">Contactos</a></li>
        </ul>
    </nav>

    <!-- Cabeçalho -->
    <header class="marca-header">
        Marca
    </header>

    <!-- Conteúdo da página -->
    <main>
        <section class="marcas-lista">
            <div class="marcas-container">
                <?php
                // Exibe todas as marcas sem agrupar por inicial
                foreach ($marcas as $inicial => $nomes): ?>
                    <?php foreach ($nomes as $marca): ?>
                        <div class="marca-item">
                            <a href="marca.php?id=<?php echo $marca['id_marca']; ?>">
                                <div class="marca-box">
                                    <div class="marca-image">
                                        <img src="<?php echo htmlspecialchars($marca['caminho_imagem']); ?>"
                                            alt="<?php echo htmlspecialchars($marca['nome']); ?>">
                                    </div>
                                    <h4 class="marca-nome"><?php echo htmlspecialchars($marca['nome']); ?></h4>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>

</html>