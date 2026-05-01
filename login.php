<?php
require 'config.php';

$errors = [];
// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    // Verificacion 
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        header('Location: index.php');
        exit;
    } else {
        $errors[] = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Login - Recetas</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<h2>Iniciar sesión</h2>
<?php if ($errors): ?>
    <div class="errors">
        <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
    </div>
<?php endif; ?>
<form method="post" action="login.php">
    <label>Nombre de usuario:<br><input type="text" name="username" required></label><br>
    <label>Contraseña:<br><input type="password" name="password" required></label><br>
    <button type="submit">Entrar</button>
    <button type="button" onclick="window.location.href='index.php'">Página principal</button>
</form>
<p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
</body>
</html>
