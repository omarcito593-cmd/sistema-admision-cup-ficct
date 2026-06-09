<?php

$host = "127.0.0.1";
$port = "5432";
$dbname = "bd_fitcct_postulantes";
$user = "postgres";
$password = "123456";

try {
    $conexion = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

?>