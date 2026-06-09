<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit();
}

$usuario = trim($_POST['usuario'] ?? '');
$contrasena = trim($_POST['contrasena'] ?? '');

if ($usuario == '' || $contrasena == '') {
    header("Location: ../login.php?error=1");
    exit();
}

try {
    $sql = "
        SELECT 
            u.id_usuario,
            u.nombre,
            u.usuario,
            u.contrasena,
            u.id_rol,
            u.estado,
            r.nombre_rol
        FROM usuarios u
        INNER JOIN roles r ON u.id_rol = r.id_rol
        WHERE u.usuario = :usuario
        AND u.contrasena = :contrasena
        AND u.estado = 'Activo'
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":usuario", $usuario);
    $stmt->bindParam(":contrasena", $contrasena);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        $_SESSION['id_usuario'] = $datos['id_usuario'];
        $_SESSION['nombre'] = $datos['nombre'];
        $_SESSION['usuario'] = $datos['usuario'];
        $_SESSION['id_rol'] = $datos['id_rol'];
        $_SESSION['nombre_rol'] = $datos['nombre_rol'];

        header("Location: ../dashboard.php");
        exit();
    }

    header("Location: ../login.php?error=1");
    exit();

} catch (PDOException $e) {
    header("Location: ../login.php?error=1");
    exit();
}
?>
