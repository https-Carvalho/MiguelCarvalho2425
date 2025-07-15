<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $login = logarUtilizador($email, $password);

    if ($login) {
        $_SESSION['id_sessao'] = $login['id_sessao'];
        $_SESSION['tipo_utilizador'] = $login['tipo_utilizador'];
        $_SESSION['email'] = $login['email'];

        if ($login['tipo_utilizador'] === 'cliente') {
            $_SESSION['clientname'] = $login['clientname'];
        } else {
            $_SESSION['username'] = $login['username']; // Admin ou trabalhador
        }

        header("Location: index.php");
        exit;
    } else {
        $erro = "Email ou palavra-passe inválidos.";
    }
}

?>


<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">

</head>

<body>
    <form method="POST" action="login.php" class="form-login">
        <fieldset class="login-box">
            <legend class="login-title">Login</legend>

            <?php if (!empty($erro)): ?>
                <p class="login-error"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required class="login-input">

            <label for="password">Palavra-passe</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" required class="login-input">
                <img src="icones/eye-close.png" alt="Ver senha" id="togglePassword" class="ver-senha">
            </div>
            <input type="submit" value="Entrar" class="login-button">

            <div class="login-links">
                <a href="registo.php">Não tem conta? Criar conta</a>
                <a href="recuperar_password.php">Esqueceu a palavra-passe?</a>
            </div>
        </fieldset>
    </form>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            togglePassword.src = isHidden ? 'icones/eye-open.png' : 'icones/eye-close.png';
        });
    </script>

</body>

</html>