<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {
    $nombre_carrera = $_POST['nombre_carrera'];
    $sigla = $_POST['sigla'];

    try {
        $sql = "INSERT INTO carreras (nombre_carrera, sigla, estado)
                VALUES (:nombre_carrera, :sigla, 'Activo')";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_carrera", $nombre_carrera);
        $stmt->bindParam(":sigla", $sigla);
        $stmt->execute();

        header("Location: ../views/carreras/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/carreras/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_carrera = $_POST['id_carrera'];
    $nombre_carrera = $_POST['nombre_carrera'];
    $sigla = $_POST['sigla'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE carreras SET
                    nombre_carrera = :nombre_carrera,
                    sigla = :sigla,
                    estado = :estado
                WHERE id_carrera = :id_carrera";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_carrera", $nombre_carrera);
        $stmt->bindParam(":sigla", $sigla);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->execute();

        header("Location: ../views/carreras/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/carreras/edit.php?id=" . $id_carrera . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "UPDATE carreras SET estado = 'Inactivo' WHERE id_carrera = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/carreras/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/carreras/index.php?error=1");
            exit();
        }
    }
}

header("Location: ../views/carreras/index.php");
exit();

?>