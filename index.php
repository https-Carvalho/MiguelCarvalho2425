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
    <link rel="stylesheet" href="styles.css">
    <style>
        .lista-fragrancias {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            justify-content: center;
        }

        .fragrancia-item {
            width: 200px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            padding: 10px;
            background-color: #fff;
        }

        .fragrancia-item img {
            width: 100%;
            border-radius: 8px;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .fragrancia-item:hover img {
            transform: scale(1.1); /* Aumenta ligeiramente a imagem */
            opacity: 0.8; /* Torna a imagem um pouco translúcida */
        }

        .informacoes-fragrancia {
            margin-top: 10px;
        }

        .informacoes-fragrancia h2 {
            font-size: 1em;
            color: #333;
            margin: 5px 0;
            font-weight: bold;
            text-transform: capitalize;
        }

        .informacoes-fragrancia h2:hover {
            text-decoration: underline; /* Sublinha ao passar o rato */
            color: #000; /* Fica preto ao passar o rato */
        }

        .informacoes-fragrancia p {
            margin: 5px 0;
            color: #888;
            font-size: 0.9em;
        }

        .informacoes-fragrancia .preco {
            font-size: 1.1em;
            color: #333;
            font-weight: bold;
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
            <div class="fragrancia-item">
                <a href="produto.php?id=<?php echo $perfume['id']; ?>"></a>
                    <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>" 
                        alt="<?php echo htmlspecialchars($perfume['nome']); ?>" 
                        onmouseover="this.src='<?php echo htmlspecialchars($perfume['caminho_imagem_hover']); ?>';">
                        onmouseout="this.src='<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>';">
                    <div class="informacoes-fragrancia">
                        <h2><?php echo htmlspecialchars($perfume['nome']); ?></h2>
                        <p><?php echo htmlspecialchars($perfume['marca']); ?></p>
                        <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>
