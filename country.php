<?php
require 'config.php';
// ID del país
$country_id = intval($_GET['id'] ?? 0);
if (!$country_id) {
    header('Location: index.php');
    exit;
}

// Obtener país y continente
$stmt = $pdo->prepare("SELECT co.name AS country_name, c.name AS continent_name FROM countries co JOIN continents c ON co.continent_id = c.id WHERE co.id = ?");
$stmt->execute([$country_id]);
$location = $stmt->fetch();
if (!$location) {
    header('Location: index.php');
    exit;
}

// Obtener receta del país
$stmt = $pdo->prepare("SELECT id, title FROM recipes WHERE country_id = ? LIMIT 1");
$stmt->execute([$country_id]);
$recipe = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Recetas de <?=htmlspecialchars($location['country_name'])?></title>
<link rel="stylesheet" href="style.css" />
<script src="nav.js" defer></script>
</head>
<body>
<header>
    <h1>Recetas del Mundo</h1>
    <nav>
        <a href="index.php">Inicio</a> &gt; 
        <?=htmlspecialchars($location['continent_name'])?> &gt; 
        <?=htmlspecialchars($location['country_name'])?>
    </nav>
</header>

<section>
    <h2>Receta destacada</h2>
    <?php if ($recipe): ?>
        <h3><a href="recipe.php?id=<?= $recipe['id'] ?>"><?=htmlspecialchars($recipe['title'])?></a></h3>
    <?php else: ?>
        <p>No hay recetas disponibles para este país.</p>
    <?php endif; ?>
</section>

</body>
</html>
