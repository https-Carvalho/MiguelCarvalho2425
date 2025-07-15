<?php
session_start();
include('config.php');

$erro = '';
$sucesso = '';
$mostrarFormulario = true;

// 🟢 CONFIRMAÇÃO POR TOKEN
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $dadosTemp = validarTokenClienteTemp($token);

    if ($dadosTemp) {
        if (confirmarClienteDefinitivo($dadosTemp)) {
            eliminarClienteTemporario($token);
            $sucesso = "Conta confirmada com sucesso! Pode fazer login.";
        } else {
            $erro = "Erro ao confirmar a conta. Tente novamente.";
        }
    } else {
        $erro = "Token inválido ou expirado.";
    }

    $mostrarFormulario = false;
}

// 🟡 SUBMISSÃO DO FORMULÁRIO DE REGISTO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['password']);

    if (empty($nome) || empty($username) || empty($email) || empty($senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $erro = "Username inválido. Deve ter entre 3 e 20 caracteres e conter apenas letras, números e underscores.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } elseif (strlen($senha) < 8 || !preg_match('/[a-z]/', $senha) || !preg_match('/[A-Z]/', $senha) || !preg_match('/\d/', $senha)) {
        $erro = "A palavra-passe deve conter letras maiúsculas, minúsculas e números.";
    } elseif (verificarEmailExistente($email)) {
        $erro = "Este email já está registado.";
    } else {
        $token = bin2hex(random_bytes(16));
        $expira = date('Y-m-d H:i:s', time() + 900); // 15 minutos
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        if (guardarClienteTemporario($nome, $username, $email, $senhaHash, $token, $expira)) {
            $link = "http://localhost/MiguelCarvalho2425/registo.php?token=$token";
            mail($email, "Confirme o seu registo", "Clique para confirmar a sua conta: $link");
            $sucesso = "Foi enviado um email de confirmação. Verifique a sua caixa de entrada.";
            $mostrarFormulario = false;
        } else {
            $erro = "Erro ao registar. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php if (!empty($erro)): ?>
    <p class="login-error"><?php echo htmlspecialchars($erro); ?></p>
<?php elseif (!empty($sucesso)): ?>
    <p class="login-sucesso"><?php echo htmlspecialchars($sucesso); ?></p>
    <?php if (!$mostrarFormulario): ?>
        <div class="login-voltar">
            <a href="login.php">Ir para o Login</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($mostrarFormulario): ?>
    <form method="POST" action="registo.php" class="form-login">
        <fieldset class="login-box">
            <legend class="login-title">Criar Conta</legend>

            <label for="nome">Nome completo</label>
            <input type="text" name="nome" id="nome" required class="login-input">

            <label for="username">Username</label>
            <input type="text" name="username" id="username" required class="login-input">

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required class="login-input">

            <label for="password">Palavra-passe</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" class="login-input" placeholder="Password">
                <img src="icones/eye-close.png" alt="Ver senha" id="togglePassword" class="ver-senha">
            </div>

            <ul class="password-requirements">
                <li id="lower">• Lowercase & Uppercase</li>
                <li id="number">• Number (0-9)</li>
                <li id="special">• Special Characters (!@#$%)</li>
                <li id="length">• 8 Characters</li>
            </ul>

            <input type="submit" value="Registar" class="login-button">

            <p class="login-recovery">
                Já tem conta? <a href="login.php">Entrar</a>
            </p>
        </fieldset>
    </form>
<?php endif; ?>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const lower = document.getElementById('lower');
    const number = document.getElementById('number');
    const special = document.getElementById('special');
    const length = document.getElementById('length');

    passwordInput.addEventListener('input', () => {
        const val = passwordInput.value;
        toggleClass(lower, /[a-z]/.test(val) && /[A-Z]/.test(val));
        toggleClass(number, /[0-9]/.test(val));
        toggleClass(special, /[!@#$%^&*(),.?":{}|<>]/.test(val));
        toggleClass(length, val.length >= 8);
    });

    function toggleClass(el, valid) {
        el.classList.toggle('valid', valid);
    }

    togglePassword.addEventListener('click', () => {
        const isHidden = passwordInput.type === 'password';
        passwordInput.type = isHidden ? 'text' : 'password';
        togglePassword.src = isHidden ? 'icones/eye-open.png' : 'icones/eye-close.png';
    });
</script>
</body>
</html>
