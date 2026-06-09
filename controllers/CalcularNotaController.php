<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "recalcular") {

    try {

        $sql = "
            UPDATE notas
            SET 
                promedio_final = ROUND(
                    ((examen1 * 0.30) + (examen2 * 0.30) + (examen3 * 0.40)), 
                    2
                ),
                resultado = CASE 
                    WHEN ROUND(((examen1 * 0.30) + (examen2 * 0.30) + (examen3 * 0.40)), 2) >= 60 
                    THEN 'APROBADO'
                    ELSE 'REPROBADO'
                END
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        header("Location: ../views/calcular_nota/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/calcular_nota/index.php?error=1");
        exit();
    }
}

header("Location: ../views/calcular_nota/index.php");
exit();