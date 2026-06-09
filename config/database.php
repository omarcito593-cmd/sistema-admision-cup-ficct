<?php
$host = getenv("DB_HOST") ?: "localhost";
$port = getenv("DB_PORT") ?: "5432";
$dbname = getenv("DB_NAME") ?: "bd_fitcct_postulantes";
$user = getenv("DB_USER") ?: "postgres";
$password = getenv("DB_PASSWORD") ?: "";

try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>