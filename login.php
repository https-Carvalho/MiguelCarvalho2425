<?php
session_start();
include('config.php'); // Inclui a configuração da base de dados

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']); // Obtém o email
    $password = trim($_POST['password']); // Obtém a senha

    // Chama a função de login
    $user = logarUtilizador($email, $password);

    if ($user) {
        // Login bem-sucedido, cria a sessão
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['tipo'] = $user['tipo'];

        // Redireciona para a página inicial
        header("Location: index.php");
        exit();
    } else {
        $erro = "Email ou senha inválidos.";
    }
}
?>


<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        fieldset {
            border: none;
        }
        legend {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }
        input[type="email"],
        input[type="password"],
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <form method="POST" action="login.php">
        <fieldset>
            <legend>Login</legend>
            <?php if (!empty($erro)): ?>
                <p class="error"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Entrar">
        </fieldset>
    </form>
</body>
</html>
