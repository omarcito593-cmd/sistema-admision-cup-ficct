<?php

session_start();
require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST["usuario"];
    $contrasena = $_POST["contrasena"];

    $sql = "SELECT u.id_usuario, u.nombre, u.usuario, r.nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.usuario = :usuario
            AND u.contrasena = :contrasena
            AND u.estado = 'Activo'";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":usuario", $usuario);
    $stmt->bindParam(":contrasena", $contrasena);
    $stmt->execute();

    $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuarioEncontrado) {
        $_SESSION["id_usuario"] = $usuarioEncontrado["id_usuario"];
        $_SESSION["nombre"] = $usuarioEncontrado["nombre"];
        $_SESSION["usuario"] = $usuarioEncontrado["usuario"];
        $_SESSION["rol"] = $usuarioEncontrado["nombre_rol"];

        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../login.php?error=1");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}