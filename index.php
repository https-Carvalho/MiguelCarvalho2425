<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados

// Obtém a quantidade de itens no carrinho do usuário logado
$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario($id_usuario) : 'visitante';
$totalCarrinho = $id_usuario ? contarItensCarrinho($id_usuario) : 0;

//funcao de busca
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    $termo = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
    $perfumes = listarPerfumes($termo);

    // Gera os resultados como HTML
    if (!empty($perfumes)): ?>
        <?php foreach ($perfumes as $perfume): ?>
            <a href="produto.php?id=<?php echo $perfume['id_perfume']; ?>" class="result-item">
                <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>"
                    alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                <div class="info">
                    <h3><?php echo htmlspecialchars($perfume['nome']); ?></h3>
                    <p><?php echo htmlspecialchars($perfume['marca']); ?></p>
                    <p><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum resultado encontrado.</p>
    <?php endif;

    exit; // Encerra a execução para evitar renderizar o restante do HTML
}


// Define a variável de controle
$mostrar_carrinho = true;

// Verifica se o usuário está logado
if (isset($_SESSION['id_user'])) {
    $tipo_usuario = verificarTipoUsuario($_SESSION['id_user']); // Obtém o tipo do usuário

    // Se for admin ou trabalhador, oculta o carrinho
    if ($tipo_usuario === "admin" || $tipo_usuario === "trabalhador") {
        $mostrar_carrinho = false;
    }
}

// Captura os filtros do GET
$termo = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
$precoMin = $_GET['preco_min'] ?? null;
$precoMax = $_GET['preco_max'] ?? null;
$filtroMarcas = isset($_GET['marca']) ? $_GET['marca'] : [];
$filtroFamilias = isset($_GET['familia']) ? $_GET['familia'] : [];
$disponibilidade = $_GET['disponibilidade'] ?? null;

//paginacao
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$limite = 10; // Número de perfumes por página

// 🔹 CHAMADA ÚNICA DA FUNÇÃO LISTAR PERFUMES (Pesquisa + Filtros)
$perfumes = listarPerfumes($termo, $precoMin, $precoMax, $filtroMarcas, $filtroFamilias, $disponibilidade, $pagina, $limite);

//paginacao
$totalPerfumes = contarTotalPerfumes($termo, $precoMin, $precoMax, $filtroMarcas, $filtroFamilias, $disponibilidade);
$totalPaginas = ceil($totalPerfumes / $limite);

$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fragrâncias Nicho</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>

    <form method="GET" action="index.php" class="filtro-form">
        <div class="filtro-dropdown">
            <span>Filtrar por:</span>

            <!-- Filtro de Preço -->
            <div class="dropdown-filtro">
                <button type="button" class="dropbtn">Preço ▼</button>
                <div class="dropdown-content-filtro">
                    <label>Mínimo (€):</label>
                    <input type="number" name="preco_min" placeholder="€"
                        value="<?php echo htmlspecialchars($_GET['preco_min'] ?? ''); ?>">
                    <label>Máximo (€):</label>
                    <input type="number" name="preco_max" placeholder="€"
                        value="<?php echo htmlspecialchars($_GET['preco_max'] ?? ''); ?>">
                </div>
            </div>

            <!-- Filtro de Marcas -->
            <div class="dropdown-filtro">
                <button type="button" class="dropbtn">Marca ▼</button>
                <div class="dropdown-content-filtro">
                    <label><input type="checkbox" id="selecionar_tudo_marcas"> Selecionar Todas</label>
                    <?php
                    $marcas = buscarMarcas();
                    $marcasSelecionadas = $_GET['marca'] ?? [];
                    foreach ($marcas as $marca):
                        $checked = in_array($marca['id_marca'], $marcasSelecionadas) ? 'checked' : '';
                        ?>
                        <label>
                            <input type="checkbox" name="marca[]"
                                value="<?php echo htmlspecialchars($marca['id_marca']); ?>" <?php echo $checked; ?>>
                            <?php echo htmlspecialchars($marca['nome']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filtro de Famílias Olfativas -->
            <div class="dropdown-filtro">
                <button type="button" class="dropbtn">Famílias Olfativas ▼</button>
                <div class="dropdown-content-filtro">
                    <label><input type="checkbox" id="selecionar_tudo_familias"> Selecionar Todas</label>
                    <?php
                    $familias = buscarFamiliasOlfativas();
                    $familiasSelecionadas = $_GET['familia'] ?? [];
                    foreach ($familias as $familia):
                        $checked = in_array($familia['id_familia'], $familiasSelecionadas) ? 'checked' : '';
                        ?>
                        <label>
                            <input type="checkbox" name="familia[]"
                                value="<?php echo htmlspecialchars($familia['id_familia']); ?>" <?php echo $checked; ?>>
                            <?php echo htmlspecialchars($familia['nome_familia']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Filtro de Disponibilidade -->
            <div class="dropdown-filtro">
                <button type="button" class="dropbtn">Disponibilidade ▼</button>
                <div class="dropdown-content-filtro">
                    <label>
                        <input type="radio" name="disponibilidade" value="" <?php echo ($_GET['disponibilidade'] ?? '') === '' ? 'checked' : ''; ?>>
                        Todas
                    </label>
                    <label>
                        <input type="radio" name="disponibilidade" value="1" <?php echo ($_GET['disponibilidade'] ?? '') === '1' ? 'checked' : ''; ?>>
                        Em Estoque
                    </label>
                    <label>
                        <input type="radio" name="disponibilidade" value="0" <?php echo ($_GET['disponibilidade'] ?? '') === '0' ? 'checked' : ''; ?>>
                        Esgotado
                    </label>
                </div>
            </div>

            <!-- Botão de Aplicar Filtros -->
            <button type="submit">Aplicar Filtros</button>
        </div>

        <div class="ordenar-wrapper">
            <label for="ordenar">Ordenar por:</label>
            <select id="ordenar">
                <option value="">Padrão</option>
                <option value="az">Alfabeticamente, A-Z</option>
                <option value="za">Alfabeticamente, Z-A</option>
                <option value="preco_menor">Preço, mais baratos</option>
                <option value="preco_maior">Preço, mais caros</option>
            </select>
        </div>

    </form>



    <!-- Lista de Fragrâncias -->
    <section class="lista-fragrancias">
        <?php foreach ($perfumes as $perfume): ?>
            <div class="fragrancia-item"
                caminho_imagem_hover="<?php echo htmlspecialchars($perfume['caminho_imagem_hover']); ?>">

                <!-- Rótulo "Esgotado" (único e fora do link) -->
                <?php if ($perfume['stock'] == 0): ?>
                    <div class="esgotado-label">Esgotado</div>
                <?php endif; ?>

                <a href="produto.php?id=<?php echo $perfume['id_perfume']; ?>">
                    <div class="imagem-fragrancia">
                        <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>"
                            alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                    </div>
                    <div class="informacoes-fragrancia">
                        <h2><?php echo htmlspecialchars($perfume['nome']); ?></h2>
                        <p class="marca"><?php echo htmlspecialchars($perfume['marca']); ?></p>
                        <p class="preco"><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </section>

    <div class="paginacao">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?php echo $pagina - 1; ?>" class="btn-paginacao">Anterior</a>
        <?php endif; ?>

        <span>Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>

        <?php if ($pagina < $totalPaginas): ?>
            <a href="?pagina=<?php echo $pagina + 1; ?>" class="btn-paginacao">Próximo</a>
        <?php endif; ?>
    </div>



    <!-- script para a troca de imagens -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.fragrancia-item');
            items.forEach(item => {
                const img = item.querySelector('img'); // Imagem base
                const caminhoImagemOriginal = img.src;
                const caminhoImagemAlternativa = item.getAttribute('caminho_imagem_hover');

                // Alternar para imagem hover no mouseover
                item.addEventListener('mouseover', () => {
                    if (caminhoImagemAlternativa) {
                        img.src = caminhoImagemAlternativa;
                    }
                });

                // Retornar à imagem original no mouseout
                item.addEventListener('mouseout', () => {
                    img.src = caminhoImagemOriginal;
                });
            });
        });
    </script>

    <!-- script selecionar varios filtros -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let selectAllMarcas = document.getElementById("selecionar_tudo_marcas");
            let checkboxesMarcas = document.querySelectorAll("input[name='marca[]']");

            if (selectAllMarcas) {
                selectAllMarcas.addEventListener("change", function () {
                    checkboxesMarcas.forEach(checkbox => {
                        checkbox.checked = selectAllMarcas.checked;
                    });
                });
            }

            let selectAllFamilias = document.getElementById("selecionar_tudo_familias");
            let checkboxesFamilias = document.querySelectorAll("input[name='familia[]']");

            if (selectAllFamilias) {
                selectAllFamilias.addEventListener("change", function () {
                    checkboxesFamilias.forEach(checkbox => {
                        checkbox.checked = selectAllFamilias.checked;
                    });
                });
            }
        });

    </script>

    <!-- script para ordenar Alfabeticamente e por preco -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectOrdenar = document.getElementById("ordenar");
            const listaFragrancias = document.querySelector(".lista-fragrancias");
            let itensOriginais = Array.from(listaFragrancias.children); // Guarda a ordem original

            selectOrdenar.addEventListener("change", function () {
                let ordem = selectOrdenar.value;
                let itens = [...itensOriginais]; // Restaura a ordem original

                if (ordem === "az") {
                    itens.sort((a, b) => a.querySelector("h2").innerText.toLowerCase().localeCompare(b.querySelector("h2").innerText.toLowerCase()));
                } else if (ordem === "za") {
                    itens.sort((a, b) => b.querySelector("h2").innerText.toLowerCase().localeCompare(a.querySelector("h2").innerText.toLowerCase()));
                } else if (ordem === "preco_menor") {
                    itens.sort((a, b) => parseFloat(a.querySelector(".preco").innerText.replace(" €", "").replace(",", ".")) -
                        parseFloat(b.querySelector(".preco").innerText.replace(" €", "").replace(",", ".")));
                } else if (ordem === "preco_maior") {
                    itens.sort((a, b) => parseFloat(b.querySelector(".preco").innerText.replace(" €", "").replace(",", ".")) -
                        parseFloat(a.querySelector(".preco").innerText.replace(" €", "").replace(",", ".")));
                }

                // Atualiza a lista na tela
                itens.forEach(item => listaFragrancias.appendChild(item));
            });
        });
    </script>




</body>

</html>