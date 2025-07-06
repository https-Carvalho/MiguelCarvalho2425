<?php
session_start();
include('config.php'); // Conexão com a base de dados

// Autenticação e identificação do utilizador
$id_sessao = $_SESSION['id_sessao'] ?? null;
$tipo_utilizador = $id_sessao ? verificarTipoUsuario($id_sessao) : 'visitante';
$nome_utilizador = $_SESSION['username'] ?? $_SESSION['nome_cliente'] ?? 'Conta';

// Carrinho só para cliente
$totalCarrinho = ($tipo_utilizador === 'cliente' && $id_sessao)
    ? contarItensCarrinho($id_sessao)
    : 0;

$mostrar_carrinho = !in_array($tipo_utilizador, ['Admin', 'trabalhador']);

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
$ordenacao = $_GET['ordenar'] ?? '';
$perfumes = listarPerfumes($termo, $precoMin, $precoMax, $filtroMarcas, $filtroFamilias, $disponibilidade, $ordenacao);



//paginacao
$totalPerfumes = contarTotalPerfumes($termo, $precoMin, $precoMax, $filtroMarcas, $filtroFamilias, $disponibilidade);
//marcas e familias
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
            <button class="aplicar-filtros" type="submit">Aplicar Filtros</button>
        </div>

        <div class="ordenar-wrapper">
            <label for="ordenar">Ordenar por:</label>
            <select id="ordenar" name="ordenar" onchange="this.form.submit()">
                <option value="">Padrão</option>
                <option value="az" <?= $ordenacao === 'az' ? 'selected' : '' ?>>Alfabeticamente, A-Z</option>
                <option value="za" <?= $ordenacao === 'za' ? 'selected' : '' ?>>Alfabeticamente, Z-A</option>
                <option value="preco_menor" <?= $ordenacao === 'preco_menor' ? 'selected' : '' ?>>Preço, mais baratos
                </option>
                <option value="preco_maior" <?= $ordenacao === 'preco_maior' ? 'selected' : '' ?>>Preço, mais caros
                </option>
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

    <!-- script para filtracao -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 🔹 Paginação
            const perfumesPorPagina = 12;
            const lista = document.querySelectorAll(".lista-fragrancias .fragrancia-item");
            const container = document.querySelector(".lista-fragrancias");

            let paginaAtual = 1;
            const totalPaginas = Math.ceil(lista.length / perfumesPorPagina);

            function mostrarPagina(pagina) {
                lista.forEach((item, index) => {
                    item.style.display = (index >= (pagina - 1) * perfumesPorPagina && index < pagina * perfumesPorPagina) ? "block" : "none";
                });
            }

            function criarPaginacao() {
                const paginacaoExistente = document.querySelector(".paginacao");
                if (paginacaoExistente) paginacaoExistente.remove();

                const paginacao = document.createElement("div");
                paginacao.className = "paginacao";
                let html = '';

                if (paginaAtual > 1) {
                    html += `<a href="#" class="btn-paginacao" data-pagina="${paginaAtual - 1}">Anterior</a>`;
                }

                html += `<span>Página ${paginaAtual} de ${totalPaginas}</span>`;

                if (paginaAtual < totalPaginas) {
                    html += `<a href="#" class="btn-paginacao" data-pagina="${paginaAtual + 1}">Próximo</a>`;
                }

                paginacao.innerHTML = html;
                container.parentNode.insertBefore(paginacao, container.nextSibling);

                paginacao.addEventListener("click", function (e) {
                    if (e.target.dataset.pagina) {
                        e.preventDefault();
                        paginaAtual = parseInt(e.target.dataset.pagina);
                        mostrarPagina(paginaAtual);
                        criarPaginacao();
                    }
                });
            }

            mostrarPagina(paginaAtual);
            criarPaginacao();

            // 🔹 Filtros ativos
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            const filtrosAtivosContainer = document.createElement("div");
            filtrosAtivosContainer.className = "filtros-ativos";
            filtrosAtivosContainer.innerHTML = "<strong>Filtros ativos:</strong> ";
            let temFiltros = false;

            const mapaMarcas = {};
            document.querySelectorAll("input[name='marca[]']").forEach(cb => {
                mapaMarcas[cb.value] = cb.parentElement.textContent.trim();
            });

            const mapaFamilias = {};
            document.querySelectorAll("input[name='familia[]']").forEach(cb => {
                mapaFamilias[cb.value] = cb.parentElement.textContent.trim();
            });

            for (const [key, value] of params.entries()) {
                if (key === 'pagina' || value === '') continue;

                let label = "";

                if (key === "preco_min") {
                    label = "Preço mínimo: " + parseFloat(value).toFixed(2) + " €";
                } else if (key === "preco_max") {
                    label = "Preço máximo: " + parseFloat(value).toFixed(2) + " €";
                } else if (key === "disponibilidade") {
                    label = value === "1" ? "Em estoque" : "Esgotado";
                } else if (key === "marca[]") {
                    label = mapaMarcas[value] ?? "Marca #" + value;
                } else if (key === "familia[]") {
                    label = mapaFamilias[value] ?? "Família #" + value;
                } else {
                    label = `${key}: ${value}`;
                }

                if (label) {
                    const badge = document.createElement("span");
                    badge.className = "filtro-badge";
                    badge.innerHTML = `${label} <a href="#" data-key="${key}" data-value="${value}" class="remover-filtro">×</a>`;
                    filtrosAtivosContainer.appendChild(badge);
                    temFiltros = true;
                }
            }

            if (temFiltros) {
                const clearAll = document.createElement("a");
                clearAll.href = window.location.pathname;
                clearAll.className = "btn-clear-filtros";
                clearAll.textContent = "Limpar todos";
                filtrosAtivosContainer.appendChild(clearAll);

                document.querySelector(".filtro-form").after(filtrosAtivosContainer);

                document.querySelectorAll(".remover-filtro").forEach(link => {
                    link.addEventListener("click", function (e) {
                        e.preventDefault();
                        const key = this.dataset.key;
                        const value = this.dataset.value;

                        const newParams = new URLSearchParams(window.location.search);

                        if (key.endsWith("[]")) {
                            let values = newParams.getAll(key);
                            values = values.filter(v => v !== value);
                            newParams.delete(key);
                            values.forEach(v => newParams.append(key, v));
                        } else {
                            newParams.delete(key);
                        }

                        window.location.search = newParams.toString();
                    });
                });
            }
        });
    </script>



</body>

</html>