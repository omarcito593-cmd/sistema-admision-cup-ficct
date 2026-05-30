<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {
    $nombre_grupo = $_POST['nombre_grupo'];
    $turno = $_POST['turno'];
    $id_aula = $_POST['id_aula'];
    $cupo_maximo = $_POST['cupo_maximo'];

    try {
        $sql = "INSERT INTO grupos (nombre_grupo, turno, id_aula, cupo_maximo, estado)
                VALUES (:nombre_grupo, :turno, :id_aula, :cupo_maximo, 'Activo')";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_grupo", $nombre_grupo);
        $stmt->bindParam(":turno", $turno);
        $stmt->bindParam(":id_aula", $id_aula);
        $stmt->bindParam(":cupo_maximo", $cupo_maximo);
        $stmt->execute();

        header("Location: ../views/grupos/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/grupos/create.php?error=1");
        exit();
    }
}

if ($action == "update") {
    $id_grupo = $_POST['id_grupo'];
    $nombre_grupo = $_POST['nombre_grupo'];
    $turno = $_POST['turno'];
    $id_aula = $_POST['id_aula'];
    $cupo_maximo = $_POST['cupo_maximo'];
    $estado = $_POST['estado'];

    try {
        $sql = "UPDATE grupos SET
                    nombre_grupo = :nombre_grupo,
                    turno = :turno,
                    id_aula = :id_aula,
                    cupo_maximo = :cupo_maximo,
                    estado = :estado
                WHERE id_grupo = :id_grupo";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_grupo", $nombre_grupo);
        $stmt->bindParam(":turno", $turno);
        $stmt->bindParam(":id_aula", $id_aula);
        $stmt->bindParam(":cupo_maximo", $cupo_maximo);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->execute();

        header("Location: ../views/grupos/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/grupos/edit.php?id=" . $id_grupo . "&error=1");
        exit();
    }
}

if ($action == "delete") {
    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "UPDATE grupos SET estado = 'Inactivo' WHERE id_grupo = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();

            header("Location: ../views/grupos/index.php?deleted=1");
            exit();

        } catch (PDOException $e) {
            header("Location: ../views/grupos/index.php?error=1");
            exit();
        }
    }
}

header("Location: ../views/grupos/index.php");
exit();

?>