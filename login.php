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
            $_SESSION['nome_cliente'] = $login['nome_cliente'];
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

            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required class="login-input">

            <input type="submit" value="Entrar" class="login-button">

            <p class="login-recovery">
                <a href="recuperar_password.php">Esqueceu a palavra-passe?</a>
            </p>
        </fieldset>
    </form>

</body>

</html>