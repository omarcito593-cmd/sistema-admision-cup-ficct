<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {
    $nombre_materia = $_POST['nombre_materia'];

    try {
        $sql = "INSERT INTO materias (nombre_materia, estado)
                VALUES (:nombre_materia, 'Activo')";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_materia", $nombre_materia);
        $stmt->execute();

        header("Location: ../views/materias/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/materias/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_materia = $_POST['id_materia'];
    $nombre_materia = $_POST['nombre_materia'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE materias SET
                    nombre_materia = :nombre_materia,
                    estado = :estado
                WHERE id_materia = :id_materia";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_materia", $nombre_materia);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_materia", $id_materia);
        $stmt->execute();

        header("Location: ../views/materias/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/materias/edit.php?id=" . $id_materia . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "UPDATE materias SET estado = 'Inactivo' WHERE id_materia = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/materias/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/materias/index.php?error=1");
            exit();
        }
    }
}

header("Location: ../views/materias/index.php");
exit();

?>  