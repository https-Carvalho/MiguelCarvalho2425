<?php
session_start();
include('config.php');

$mensagem = '';
$erro = '';
$etapa = 'solicitar';

// Guardar email em sessão para reusar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Etapa 1: envio de email com código
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $utilizador = obterUsuarioPorEmail($email);

        if ($utilizador) {
            $codigo = rand(100000, 999999);
            $_SESSION['recuperacao_email'] = $email;
            $_SESSION['recuperacao_codigo'] = $codigo;
            $_SESSION['recuperacao_id'] = $utilizador['id_user'];
            $_SESSION['recuperacao_expira'] = time() + 600; // 10 min

            $mensagemEmail = "O seu código de recuperação é: <strong>$codigo</strong><br>Expira em 10 minutos.";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: LuxFragrance <noreply@luxfragrance.com>\r\n";

            mail($email, "Código de Recuperação", $mensagemEmail, $headers);

            $mensagem = "Foi enviado um código para <strong>$email</strong>.";
            $etapa = 'codigo';
        } else {
            $erro = "Email não encontrado.";
        }
    }

    // Etapa 2: validação do código
    elseif (isset($_POST['codigo'])) {
        $codigoDigitado = trim($_POST['codigo']);

        if (!isset($_SESSION['recuperacao_codigo']) || time() > $_SESSION['recuperacao_expira']) {
            $erro = "O código expirou. Solicite novamente.";
            session_unset();
        } elseif ($codigoDigitado != $_SESSION['recuperacao_codigo']) {
            $erro = "Código incorreto.";
            $etapa = 'codigo';
        } else {
            $etapa = 'redefinir';
        }
    }

    // Etapa 3: redefinição da senha
    elseif (isset($_POST['nova_senha'], $_POST['confirmar_senha'])) {
        $nova = $_POST['nova_senha'];
        $confirma = $_POST['confirmar_senha'];

        if ($nova !== $confirma) {
            $erro = "As palavras-passe não coincidem.";
            $etapa = 'redefinir';
        } elseif (!preg_match('/^(?=.*[a-z]).{8,}$/', $nova)) {
            $erro = "A senha deve ter no mínimo 8 caracteres e pelo menos uma letra minúscula.";
            $etapa = 'redefinir';
        } else {
            $senhaHash = password_hash($nova, PASSWORD_DEFAULT);
            atualizarSenhaPorId($_SESSION['recuperacao_id'], $senhaHash);
            session_unset();
            $mensagem = "Palavra-passe alterada com sucesso. <a href='login.php'>Clique aqui para entrar</a>";
            $etapa = 'completo';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Palavra-passe</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <form method="POST" class="form-recuperacao">
        <fieldset class="recuperacao-box">
            <?php if ($etapa === 'solicitar'): ?>
                <legend class="recuperacao-title">Recuperar Palavra-passe</legend>
                <?php if ($erro): ?>
                    <p class="recuperacao-erro"><?= $erro ?></p><?php endif; ?>
                <?php if ($mensagem): ?>
                    <p class="recuperacao-sucesso"><?= $mensagem ?></p><?php endif; ?>
                <label>Email:</label>
                <input type="email" name="email" required class="recuperacao-input">
                <input type="submit" value="Enviar Código" class="recuperacao-button">

            <?php elseif ($etapa === 'codigo'): ?>
                <legend class="recuperacao-title">Introduza o Código</legend>
                <?php if ($erro): ?>
                    <p class="recuperacao-erro"><?= $erro ?></p><?php endif; ?>
                <?php if ($mensagem): ?>
                    <p class="recuperacao-sucesso"><?= $mensagem ?></p><?php endif; ?>
                <label>Código recebido por email:</label>
                <input type="text" name="codigo" required class="recuperacao-input">
                <input type="submit" value="Validar Código" class="recuperacao-button">

            <?php elseif ($etapa === 'redefinir'): ?>
                <legend class="recuperacao-title">Nova Palavra-passe</legend>
                <?php if ($erro): ?>
                    <p class="recuperacao-erro"><?= $erro ?></p>
                <?php endif; ?>

                <label>Nova senha:</label>
                <div class="senha-container">
                    <input type="password" name="nova_senha" id="nova_senha" required class="recuperacao-input">
                    <button type="button" onclick="toggleSenha('nova_senha', this)" class="toggle-btn">👁️</button>
                </div>

                <label>Confirmar senha:</label>
                <div class="senha-container">
                    <input type="password" name="confirmar_senha" id="confirmar_senha" required class="recuperacao-input">
                    <button type="button" onclick="toggleSenha('confirmar_senha', this)" class="toggle-btn">👁️</button>
                </div>

                <input type="submit" value="Alterar Senha" class="recuperacao-button">



            <?php elseif ($etapa === 'completo'): ?>
                <p class="recuperacao-sucesso"><?= $mensagem ?></p>
            <?php endif; ?>
        </fieldset>
    </form>
</body>
<script>
    function toggleSenha(id, botao) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            botao.textContent = "🙈";
        } else {
            input.type = "password";
            botao.textContent = "👁️";
        }
    }
</script>

</html>