<?php
require 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    // Validaciones basicas
    if (strlen($username) < 3) {
        $errors[] = "El nombre de usuario debe tener al menos 3 caracteres.";
    }
    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }
    // Sin errores, se realiza el registro
    if (!$errors) {
        // Verificar si usuario existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "El nombre de usuario ya existe.";
        } else {
            // Insertar usuario
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            if ($stmt->execute([$username, $hash])) {
                header('Location: login.php');
                exit;
            } else {
                $errors[] = "Error al registrar usuario.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Registro - Recetas</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<h2>Registro</h2>
<?php if ($errors): ?>
    <div class="errors">
        <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
    </div>
<?php endif; ?>
<form method="post" action="register.php">
    <label>Nombre de usuario:<br><input type="text" name="username" required></label><br>
    <label>Contraseña:<br><input type="password" name="password" required></label><br>
    <button type="submit">Registrar</button>
    <button type="button" onclick="window.location.href='index.php'">Página principal</button>
</form>
<p>¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
</body>
</html>
