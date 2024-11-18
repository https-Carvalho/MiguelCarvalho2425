<?php
include('config.php'); // Inclui a configuração da base de dados e a função listarPerfumes

// Obtém os perfumes da base de dados
$perfumes = listarPerfumes();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrâncias Nicho</title>
    <style>
        /* Estilo do Menu */
        .menu ul {
            display: flex;
            justify-content: space-around;
            list-style-type: none;
            padding: 10px;
            background-color: #333;
            color: #fff;
        }

        .menu ul li {
            cursor: pointer;
        }

        /* Estilo da Lista de Fragrâncias */
        .lista-fragrancias {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        /* Estilos do Link */
        .fragrancia-link {
            text-decoration: none;
            color: inherit;
            display: inline-block;
            width: 100%;
        }

        .fragrancia-item {
            width: 200px;
            padding: 10px;
            text-align: center;
            transition: transform 0.3s ease, background-color 0.3s ease;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            color: inherit;
        }

        .fragrancia-item img {
            width: 100%;
            border-radius: 8px;
        }

        .fragrancia-item:hover {
            transform: scale(1.05);
        }

        .fragrancia-item[data-estacao="verao"]:hover {
            background-color: #FFD700; /* Amarelo para Verão */
        }

        .fragrancia-item[data-estacao="inverno"]:hover {
            background-color: #87CEEB; /* Azul Claro para Inverno */
        }

        .fragrancia-item[data-estacao="primavera"]:hover {
            background-color: #98FB98; /* Verde Claro para Primavera */
        }

        .fragrancia-item[data-estacao="outono"]:hover {
            background-color: #FF8C00; /* Laranja para Outono */
        }

        /* Estilos de Texto */
        .fragrancia-item h2 {
            color: #333;
            font-size: 1.1em;
            margin: 10px 0;
            font-weight: bold;
        }

        .preco {
            font-size: 1em;
            color: #333;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Menu de Navegação -->
    <nav class="menu">
        <ul>
            <li>Início</li>
            <li>Discovery Kit</li>
            <li>Marcas ▼</li>
            <li>Família Olfativa ▼</li>
            <li>Categorias ▼</li>
            <li>Sobre Nós ▼</li>
            <li>Contactos</li>
        </ul>
    </nav>

    <!-- Lista de Fragrâncias -->
    <section class="lista-fragrancias">
        <?php foreach ($perfumes as $perfume): ?>
            <a href="produto.php?id=<?php echo $perfume['id']; ?>" class="fragrancia-link">
                <div class="fragrancia-item" data-estacao="<?php echo htmlspecialchars($perfume['estacao']); ?>">
                    <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>" alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                    <h2><?php echo htmlspecialchars($perfume['nome']); ?></h2>
                    <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </section>

    <script src="animacoes.js"></script>
</body>
</html>

