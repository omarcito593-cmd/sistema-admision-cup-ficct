<?php
$host = "localhost";
$port = "5432";
$dbname = "bd_fitcct_postulantes";
$user = "postgres";
$password = "TU_PASSWORD";

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