<?php
require 'config.php';

header('Content-Type: application/json');
// Verifica si el usuario está logeado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para dar like.']);
    exit;
}
// Obtener datos
$user_id = $_SESSION['user_id'];
$comment_id = intval($_POST['comment_id'] ?? 0);
// Validar comentario
if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'Comentario inválido.']);
    exit;
}

// Verificar si ya dio like
$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND comment_id = ?");
$stmt->execute([$user_id, $comment_id]);
$exists = $stmt->fetch();
// Si existe
if ($exists) {
    // Quitar like
    $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND comment_id = ?");
    $stmt->execute([$user_id, $comment_id]);
} else {
    // Añadir like
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, comment_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $comment_id]);
}

// Contar likes actualizados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE comment_id = ?");
$stmt->execute([$comment_id]);
$count = $stmt->fetchColumn();
// Devolver resultado
echo json_encode(['success' => true, 'likes_count' => $count]);
