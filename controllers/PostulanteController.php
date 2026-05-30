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
    $sexo = $_POST['sexo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $colegio_procedencia = $_POST['colegio_procedencia'];
    $ciudad = $_POST['ciudad'];
    $id_carrera = $_POST['id_carrera'];
    $id_carrera_segunda_opcion = !empty($_POST['id_carrera_segunda_opcion']) ? $_POST['id_carrera_segunda_opcion'] : null;
    $titulo_bachiller = $_POST['titulo_bachiller'];
    $otros = $_POST['otros'];

    try {
        $sql = "INSERT INTO postulantes 
                (nombre, apellido, ci, sexo, telefono, correo, fecha_nacimiento, direccion, colegio_procedencia, ciudad, id_carrera, id_carrera_segunda_opcion, titulo_bachiller, otros)
                VALUES 
                (:nombre, :apellido, :ci, :sexo, :telefono, :correo, :fecha_nacimiento, :direccion, :colegio_procedencia, :ciudad, :id_carrera, :id_carrera_segunda_opcion, :titulo_bachiller, :otros)";

        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":colegio_procedencia", $colegio_procedencia);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->bindParam(":id_carrera_segunda_opcion", $id_carrera_segunda_opcion);
        $stmt->bindParam(":titulo_bachiller", $titulo_bachiller);
        $stmt->bindParam(":otros", $otros);

        $stmt->execute();

        header("Location: ../views/postulantes/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/postulantes/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_postulante = $_POST['id_postulante'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $ci = $_POST['ci'];
    $sexo = $_POST['sexo'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion = $_POST['direccion'];
    $colegio_procedencia = $_POST['colegio_procedencia'];
    $ciudad = $_POST['ciudad'];
    $id_carrera = $_POST['id_carrera'];
    $id_carrera_segunda_opcion = !empty($_POST['id_carrera_segunda_opcion']) ? $_POST['id_carrera_segunda_opcion'] : null;
    $titulo_bachiller = $_POST['titulo_bachiller'];
    $otros = $_POST['otros'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE postulantes SET
                    nombre = :nombre,
                    apellido = :apellido,
                    ci = :ci,
                    sexo = :sexo,
                    telefono = :telefono,
                    correo = :correo,
                    fecha_nacimiento = :fecha_nacimiento,
                    direccion = :direccion,
                    colegio_procedencia = :colegio_procedencia,
                    ciudad = :ciudad,
                    id_carrera = :id_carrera,
                    id_carrera_segunda_opcion = :id_carrera_segunda_opcion,
                    titulo_bachiller = :titulo_bachiller,
                    otros = :otros,
                    estado = :estado
                WHERE id_postulante = :id_postulante";

        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":colegio_procedencia", $colegio_procedencia);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->bindParam(":id_carrera_segunda_opcion", $id_carrera_segunda_opcion);
        $stmt->bindParam(":titulo_bachiller", $titulo_bachiller);
        $stmt->bindParam(":otros", $otros);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_postulante", $id_postulante);

        $stmt->execute();

        header("Location: ../views/postulantes/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/postulantes/edit.php?id=" . $id_postulante . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "DELETE FROM postulantes WHERE id_postulante = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/postulantes/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/postulantes/index.php?error_delete=1");
            exit();
        }
    }
}

header("Location: ../views/postulantes/index.php");
exit();