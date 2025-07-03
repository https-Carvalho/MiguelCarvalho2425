<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados e a função listarPerfumes

// Verifica o tipo do usuário
$id_usuario = $_SESSION['id_user'] ?? null;
$tipo_usuario = $id_usuario ? verificarTipoUsuario(id_usuario: $id_usuario) : 'visitante';



// Função de busca AJAX
if (isset($_GET['kit_ajax']) && $_GET['kit_ajax'] === '1') {
    $termo = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '';
    $perfumes = listarPerfumes($termo);

    // Gera os resultados como HTML
    if (!empty($perfumes)): ?>
        <?php foreach ($perfumes as $perfume): ?>
            <div class="result-item" data-nome="<?php echo htmlspecialchars($perfume['marca'] . ' ' . $perfume['nome']); ?>"
                data-id="<?php echo $perfume['id_perfume']; ?>" onclick="selectPerfume(this)">
                <img src="<?php echo htmlspecialchars($perfume['caminho_imagem']); ?>"
                    alt="<?php echo htmlspecialchars($perfume['nome']); ?>">
                <div class="info">
                    <h3><?php echo htmlspecialchars($perfume['marca']); ?>             <?php echo htmlspecialchars($perfume['nome']); ?></h3>
                    <p><?php echo number_format($perfume['preco'], 2, ',', ' ') . ' €'; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhum resultado encontrado.</p>
    <?php endif;

    exit; // Encerra a execução para evitar renderizar o restante do HTML
}

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




// Obtém os perfumes da base de dados
$perfumes = listarPerfumes();
$marcas = buscarMarcasAgrupadas();
$familias = buscarFamiliasOlfativas(); // Chama a função para buscar as famílias olfativas

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discovery Kit</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body class="<?php echo strtolower($tipo_usuario); ?>">
    <!-- Menu de Navegação -->
    <?php include('menu.php'); ?>

    <!-- Cabeçalho -->
    <main>
        <section class="discovery-kit">
            <div class="left-side">
                <img src="images/kit.jpg" alt="">
            </div>
            <header class="right-side">
                <div class="familia-header">
                    Discovery Kit - Escolha até 5 perfumes
                </div>
                <!-- 5 Campos para Escolher Perfumes -->
                <div class="search-field">
                    <input type="text" id="searchInput1" placeholder="Escolha o primeiro perfume...">
                    <div id="searchResults1" class="search-results"></div>
                </div>
                <div class="search-field">
                    <input type="text" id="searchInput2" placeholder="Escolha o segundo perfume...">
                    <div id="searchResults2" class="search-results"></div>
                </div>
                <div class="search-field">
                    <input type="text" id="searchInput3" placeholder="Escolha o terceiro perfume...">
                    <div id="searchResults3" class="search-results"></div>
                </div>

                <div class="search-field">
                    <input type="text" id="searchInput4" placeholder="Escolha o quarto perfume...">
                    <div id="searchResults4" class="search-results"></div>
                </div>
                <div class="search-field">
                    <input type="text" id="searchInput5" placeholder="Escolha o quinto perfume...">
                    <div id="searchResults5" class="search-results"></div>
                </div>
            </header>
        </section>
    </main>

    <script>
        // Função para configurar os campos de pesquisa e resultados
        function setupSearchField(inputId, resultsId) {
            const inputField = document.getElementById(inputId);
            const resultsContainer = document.getElementById(resultsId);

            // Lidar com a entrada no campo de pesquisa
            inputField.addEventListener('input', function () {
                const query = this.value.trim();

                if (query.length > 0) {
                    fetch(`?kit_ajax=1&q=${encodeURIComponent(query)}`)
                        .then(response => response.text())
                        .then(data => {
                            resultsContainer.innerHTML = data;
                        })
                        .catch(error => console.error('Erro na pesquisa:', error));
                } else {
                    resultsContainer.innerHTML = ''; // Limpa os resultados
                }
            });

            // Preencher o campo de pesquisa ao clicar em um item
            resultsContainer.addEventListener('click', function (event) {
                if (event.target && event.target.matches('.result-item')) {
                    // Pega o nome do perfume e da marca da sugestão
                    const perfumeName = event.target.getAttribute('data-nome');
                    const perfumeId = event.target.getAttribute('data-id'); // ID opcional, se necessário

                    // Preenche o campo com o nome do perfume e a marca
                    inputField.value = perfumeName;

                    // Limpa os resultados
                    resultsContainer.innerHTML = '';

                    // Opcional: Armazena o ID ou outras informações do perfume
                    // Exemplo: Armazenar o ID em um campo oculto (se necessário)
                    // document.getElementById('hiddenPerfumeId').value = perfumeId;
                }
            });
        }

        // Configura os campos de pesquisa para cada um dos 5 campos de escolha de perfume
        setupSearchField('searchInput1', 'searchResults1');
        setupSearchField('searchInput2', 'searchResults2');
        setupSearchField('searchInput3', 'searchResults3');
        setupSearchField('searchInput4', 'searchResults4');
        setupSearchField('searchInput5', 'searchResults5');
    </script>
</body>

</html>