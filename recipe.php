<?php
require 'config.php';
// ID de la receta
$recipe_id = intval($_GET['id'] ?? 0);
if (!$recipe_id) {
    header('Location: index.php');
    exit;
}

// Obtener receta con país y continente
$stmt = $pdo->prepare("SELECT r.*, co.name AS country_name, c.name AS continent_name FROM recipes r JOIN countries co ON r.country_id = co.id JOIN continents c ON co.continent_id = c.id WHERE r.id = ?");
$stmt->execute([$recipe_id]);
$recipe = $stmt->fetch();
if (!$recipe) {
    header('Location: index.php');
    exit;
}

// Guardar última receta visitada si usuario logueado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO user_last_recipe (user_id, recipe_id, last_visited_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE recipe_id = VALUES(recipe_id), last_visited_at = NOW()");
    $stmt->execute([$user_id, $recipe_id]);
} else {
    $user_id = null;
}

// Obtener curiosidad
$stmt = $pdo->prepare("SELECT content FROM curiosities WHERE country_id = ?");
$stmt->execute([$recipe['country_id']]);
$curiosity = $stmt->fetchColumn();

// Manejo de comentarios: añadir, editar, reportar con validaciones básicas

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    if (isset($_POST['add_comment'])) {
        $content = trim($_POST['comment_content'] ?? '');
        if ($content === '') {
            $errors[] = "El comentario no puede estar vacío.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, recipe_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $recipe_id, $content]);
            header("Location: recipe.php?id=$recipe_id");
            exit;
        }
    } elseif (isset($_POST['edit_comment'])) {
        $comment_id = intval($_POST['comment_id']);
        $content = trim($_POST['comment_content'] ?? '');
        // Verificar que el comentario pertenece al usuario
        $stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $owner = $stmt->fetchColumn();
        if ($owner != $user_id) {
            $errors[] = "No puedes editar este comentario.";
        } elseif ($content === '') {
            $errors[] = "El comentario no puede estar vacío.";
        } else {
            $stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$content, $comment_id]);
            header("Location: recipe.php?id=$recipe_id");
            exit;
        }
    } elseif (isset($_POST['report_comment'])) {
        $comment_id = intval($_POST['comment_id']);
        $reason = trim($_POST['report_reason'] ?? 'No especificado');
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, comment_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $comment_id, $reason]);
        // Marcar comentario como reportado
        $stmt = $pdo->prepare("UPDATE comments SET reported = TRUE WHERE id = ?");
        $stmt->execute([$comment_id]);
        header("Location: recipe.php?id=$recipe_id");
        exit;
    }
}

// Obtener comentarios ordenados por fecha descendente
$stmt = $pdo->prepare("SELECT cm.*, u.username, 
    (SELECT COUNT(*) FROM likes WHERE comment_id = cm.id) AS likes_count,
    (SELECT COUNT(*) FROM likes WHERE comment_id = cm.id AND user_id = ?) AS user_liked
    FROM comments cm JOIN users u ON cm.user_id = u.id WHERE cm.recipe_id = ? ORDER BY cm.created_at DESC");
$stmt->execute([$user_id ?? 0, $recipe_id]);
$comments = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title><?=htmlspecialchars($recipe['title'])?> - Recetas</title>
<link rel="stylesheet" href="style.css" />
<script src="nav.js" defer></script>
<script>
    <!-- funciones para toggle de edicion y manejo de likes -->
function toggleEdit(commentId) {
    var displayDiv = document.getElementById('comment-display-' + commentId);
    var editDiv = document.getElementById('comment-edit-' + commentId);
    if (displayDiv.style.display === 'none') {
        displayDiv.style.display = 'block';
        editDiv.style.display = 'none';
    } else {
        displayDiv.style.display = 'none';
        editDiv.style.display = 'block';
    }
}

function likeComment(commentId) {
    fetch('like.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_id=' + commentId
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              var countSpan = document.getElementById('likes-count-' + commentId);
              countSpan.textContent = data.likes_count;
          } else {
              alert(data.message);
          }
      });
}
</script>
</head>
<body>
<header>
    <h1>Recetas del Mundo</h1>
    <nav>
        <a href="index.php">Inicio</a> &gt; 
        <?=htmlspecialchars($recipe['continent_name'])?> &gt; 
        <?=htmlspecialchars($recipe['country_name'])?> &gt; 
        <?=htmlspecialchars($recipe['title'])?>
    </nav>
    <div>
        <?php if ($user_id): ?>
            <span>Hola, <?=htmlspecialchars($_SESSION['username'])?></span> | <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Iniciar sesión</a> | <a href="register.php">Registrarse</a>
        <?php endif; ?>
    </div>
</header>

<main>
    <h2><?=htmlspecialchars($recipe['title'])?></h2>
 
    <div class="recipe">
        <div class="recipe-image">
            <?php if ($recipe['image_url']): ?>
                <img src="<?=htmlspecialchars($recipe['image_url'])?>" alt="<?=htmlspecialchars($recipe['title'])?>">
            <?php endif; ?>
        </div>
 
        <div class="recipe-info">
            <h3>Ingredientes</h3>
            <pre><?=htmlspecialchars($recipe['ingredients'])?></pre>
            <h3>Pasos</h3>
            <pre><?=htmlspecialchars($recipe['steps'])?></pre>
        </div>
    </div>
 
    <section>
        <h3>Curiosidad</h3>
        <p><?=htmlspecialchars($curiosity)?></p>
    </section>
    
    <section>
        <h3>Comentarios</h3>
        <?php if ($errors): ?>
            <div class="errors">
                <ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
            </div>
        <?php endif; ?>

        <?php if ($user_id): ?>
        <form method="post" action="recipe.php?id=<?= $recipe_id ?>">
            <textarea name="comment_content" rows="3" placeholder="Escribe tu comentario aquí..." required></textarea><br>
            <button type="submit" name="add_comment">Añadir comentario</button>
        </form>
        <?php else: ?>
            <p><a href="login.php">Inicia sesión</a> para comentar.</p>
        <?php endif; ?>

        <?php foreach ($comments as $comment): ?>
            <div class="comment" id="comment-<?= $comment['id'] ?>">
                <div id="comment-display-<?= $comment['id'] ?>">
                    <strong><?=htmlspecialchars($comment['username'])?></strong> 
                    <small>(<?=htmlspecialchars($comment['created_at'])?>)</small>
                    <?php if ($comment['reported']): ?>
                        <em style="color:red;">[Reportado]</em>
                    <?php endif; ?>
                    <p><?=nl2br(htmlspecialchars($comment['content']))?></p>
                    <button onclick="likeComment(<?= $comment['id'] ?>)">Like (<span id="likes-count-<?= $comment['id'] ?>"><?= $comment['likes_count'] ?></span>)</button>
                    <?php if ($user_id === $comment['user_id']): ?>
                        <button onclick="toggleEdit(<?= $comment['id'] ?>)">Editar</button>
                    <?php endif; ?>
                    <?php if ($user_id && $user_id !== $comment['user_id'] && !$comment['reported']): ?>
                        <form method="post" action="recipe.php?id=<?= $recipe_id ?>" style="display:inline;">
                            <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                            <input type="hidden" name="report_reason" value="Comentario inapropiado">
                            <button type="submit" name="report_comment" onclick="return confirm('¿Reportar este comentario?')">Reportar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php if ($user_id === $comment['user_id']): ?>
                <div id="comment-edit-<?= $comment['id'] ?>" style="display:none;">
                    <form method="post" action="recipe.php?id=<?= $recipe_id ?>">
                        <textarea name="comment_content" rows="3" required><?=htmlspecialchars($comment['content'])?></textarea><br>
                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                        <button type="submit" name="edit_comment">Guardar</button>
                        <button type="button" onclick="toggleEdit(<?= $comment['id'] ?>)">Cancelar</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <hr>
        <?php endforeach; ?>
    </section>
</main>

</body>
</html>
