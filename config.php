<?php
session_start();
// Configuracion para conectar a la base de datos MySQL en InfinityFree
$host = 'sql312.infinityfree.com';
$db   = 'if0_41874579_recetas_db';
$user = 'if0_41874579';
$pass = 'TdWKPf932rRFus';
$charset = 'utf8mb4';
// Configuracion para conexion local (XAMPP)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
// try y catch para manejar errores de conexion
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error conexión DB: " . $e->getMessage());
}
?>
