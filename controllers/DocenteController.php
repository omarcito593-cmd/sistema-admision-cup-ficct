<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $ci = $_POST['ci'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];

    try {
        $sql = "INSERT INTO docentes (nombre, apellido, ci, telefono, correo, estado)
                VALUES (:nombre, :apellido, :ci, :telefono, :correo, 'Activo')";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();

        header("Location: ../views/docentes/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/docentes/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_docente = $_POST['id_docente'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $ci = $_POST['ci'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE docentes SET
                    nombre = :nombre,
                    apellido = :apellido,
                    ci = :ci,
                    telefono = :telefono,
                    correo = :correo,
                    estado = :estado
                WHERE id_docente = :id_docente";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_docente", $id_docente);
        $stmt->execute();

        header("Location: ../views/docentes/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/docentes/edit.php?id=" . $id_docente . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "UPDATE docentes SET estado = 'Inactivo' WHERE id_docente = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/docentes/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/docentes/index.php?error=1");
            exit();
        }
    }
}

header("Location: ../views/docentes/index.php");
exit();

?>