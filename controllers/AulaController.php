<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {
    $nombre_aula = $_POST['nombre_aula'];
    $capacidad = $_POST['capacidad'];

    try {
        $sql = "INSERT INTO aulas (nombre_aula, capacidad, estado)
                VALUES (:nombre_aula, :capacidad, 'Disponible')";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_aula", $nombre_aula);
        $stmt->bindParam(":capacidad", $capacidad);
        $stmt->execute();

        header("Location: ../views/aulas/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/aulas/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_aula = $_POST['id_aula'];
    $nombre_aula = $_POST['nombre_aula'];
    $capacidad = $_POST['capacidad'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE aulas SET
                    nombre_aula = :nombre_aula,
                    capacidad = :capacidad,
                    estado = :estado
                WHERE id_aula = :id_aula";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_aula", $nombre_aula);
        $stmt->bindParam(":capacidad", $capacidad);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_aula", $id_aula);
        $stmt->execute();

        header("Location: ../views/aulas/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/aulas/edit.php?id=" . $id_aula . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "UPDATE aulas SET estado = 'Inactiva' WHERE id_aula = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/aulas/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/aulas/index.php?error=1");
            exit();
        }
    }
}

header("Location: ../views/aulas/index.php");
exit();

?>