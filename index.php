<?php
require 'config.php';
// Datos del usuario
$user_id = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

// Obtener continentes y países de la base de datos para menú
$stmt = $pdo->query("SELECT c.id AS continent_id, c.name AS continent_name, co.id AS country_id, co.name AS country_name
                     FROM continents c
                     LEFT JOIN countries co ON co.continent_id = c.id
                     ORDER BY c.name, co.name");
$menu = [];
while ($row = $stmt->fetch()) {
    $menu[$row['continent_name']][] = ['id' => $row['country_id'], 'name' => $row['country_name']];
}

// Obtener última receta visitada
$last_recipe = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT r.id, r.title FROM user_last_recipe ulr JOIN recipes r ON ulr.recipe_id = r.id WHERE ulr.user_id = ?");
    $stmt->execute([$user_id]);
    $last_recipe = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Inicio - Recetas</title>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<header>
    <h1>Recetas del Mundo</h1>
    <!-- Menú de navegación -->
    <nav>
        <?php if ($username): ?>
            <span>Hola, <?=htmlspecialchars($username)?></span> | <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a> | <a href="register.php">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>

<section>
    <h2>Continentes y Países</h2>
    <ul>
        <?php foreach ($menu as $continent => $countries): ?>
            <li><strong><?=htmlspecialchars($continent)?></strong>
                <ul>
                    <?php foreach ($countries as $country): ?>
                        <?php if ($country['id']): ?>
                        <li><a href="country.php?id=<?= $country['id'] ?>"><?=htmlspecialchars($country['name'])?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php if ($last_recipe): ?>
<section>
    <h2>Última receta visitada</h2>
    <a href="recipe.php?id=<?= $last_recipe['id'] ?>"><?=htmlspecialchars($last_recipe['title'])?></a>
</section>
<?php endif; ?>

</body>
</html>
