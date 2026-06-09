<?php
/*
    Archivo: config/validar_rol.php

    Roles:
    1 = Administrador
    2 = Secretaria
    3 = Docente
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function validarRol($rolesPermitidos = []) {

    if (!isset($_SESSION['usuario'])) {
        header("Location: /fitcct_postulantes/login.php");
        exit();
    }

    if (!isset($_SESSION['id_rol'])) {
        header("Location: /fitcct_postulantes/login.php");
        exit();
    }

    $rolUsuario = (int) $_SESSION['id_rol'];
    $rolesPermitidos = array_map('intval', $rolesPermitidos);

    if (!in_array($rolUsuario, $rolesPermitidos)) {
        header("Location: /fitcct_postulantes/dashboard.php?sin_permiso=1");
        exit();
    }
}
?>
