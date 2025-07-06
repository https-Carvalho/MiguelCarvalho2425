<?php
ini_set("SMTP", "localhost");
ini_set("smtp_port", "25");
ini_set("sendmail_from", "noreply@luxfragrance.com");

session_start();
include('config.php');

if ($_SESSION['tipo_login'] !== 'cliente') {
    header("Location: login.php");
    exit;
}

$id_cliente = $_SESSION['id_cliente'];
$userEmail = buscarEmailUsuario($id_cliente); // Usa função para clientes
$itensCarrinho = buscarItensCarrinho($id_cliente);
$total = 0;

// 1. Calcula o total antes de criar a encomenda
foreach ($itensCarrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

// 2. Agora sim cria a encomenda com o total já certo
$id_encomenda = criarEncomenda($id_cliente, $total);

// 3. Regista os produtos e atualiza stock
foreach ($itensCarrinho as $item) {
    adicionarProdutoEncomenda($id_encomenda, $item['id_produto'], $item['quantidade'], $item['preco']);
    atualizarStock($item['id_produto'], $item['quantidade']);
}

// Limpa o carrinho
limparCarrinho($id_cliente);

// Envia email
// Conteúdo HTML do email
$mensagem = '
<html>
<head>
  <meta charset="UTF-8">
  <style>
    .produto { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #ddd; }
    .produto img { width: 100px; height: auto; border: 1px solid #ccc; }
    .info { flex: 1; }
    .nome { font-size: 16px; font-weight: bold; color: #000; margin: 0; }
    .preco { font-size: 14px; font-weight: bold; margin: 5px 0; }
    .qtd { font-size: 13px; color: #555; }
    .total-final { font-size: 16px; font-weight: bold; margin-top: 20px; text-align: right; }
  </style>
</head>
<body>
<div class="resumo-box">
  <h2>Detalhe da Compra</h2>';


  foreach ($itensCarrinho as $item) {
    $imagemPath = __DIR__ . '/' . $item['caminho_imagem'];
    $imagemBase64 = '';

    if (file_exists($imagemPath)) {
        $imagemData = file_get_contents($imagemPath);
        $tipoImagem = pathinfo($imagemPath, PATHINFO_EXTENSION);
        $imagemBase64 = 'data:image/' . $tipoImagem . ';base64,' . base64_encode($imagemData);
    }

    $mensagem .= '
    <div class="produto">
        <img src="' . $imagemBase64 . '" alt="' . htmlspecialchars($item['nome']) . '">
        <div class="info">
            <p class="nome">' . htmlspecialchars($item['nome']) . '</p>
            <p class="preco">' . number_format($item['preco'], 2, ',', ' ') . ' €</p>
            <p class="qtd">QTD: ' . $item['quantidade'] . ' x ' . number_format($item['preco'], 2, ',', ' ') . ' €</p>
        </div>
    </div>';
}
$mensagem .= '
    <p class="total-final">TOTAL: ' . number_format($total, 2, ',', ' ') . ' €</p>
</div>
</body>
</html>';

// Cabeçalhos para email HTML
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: LuxFragrance <noreply@luxfragrance.com>\r\n";

$assunto = "=?UTF-8?B?" . base64_encode("Confirmação da sua encomenda #$id_encomenda") . "?=";
mail($userEmail, $assunto, $mensagem, $headers);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Encomenda Concluída</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include('menu.php'); ?>
    <div class="sucesso-container">
        <h1>Encomenda realizada com sucesso!</h1>
        <p>Obrigado pela sua compra. Um email de confirmação foi enviado para <strong><?php echo $userEmail; ?></strong>.</p>
        <p>Número da encomenda: <strong>#<?php echo $id_encomenda; ?></strong></p>
        <a href="index.php" class="checkout-button">Voltar à página inicial</a>
    </div>
</body>
</html>
