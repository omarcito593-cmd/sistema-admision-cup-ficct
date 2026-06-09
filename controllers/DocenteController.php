<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {

    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $profesion = trim($_POST['profesion'] ?? '');
    $maestria = trim($_POST['maestria'] ?? '');
    $diplomado = trim($_POST['diplomado'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Activo');

    if ($ci == '' || $nombre == '' || $apellido == '') {
        header("Location: ../views/docentes/create.php?error=1");
        exit();
    }

    try {

        $sqlVerificar = "
            SELECT COUNT(*)
            FROM docentes
            WHERE ci = :ci
        ";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":ci", $ci);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/docentes/create.php?duplicado=1");
            exit();
        }

        $sql = "
            INSERT INTO docentes
            (
                ci,
                nombre,
                apellido,
                telefono,
                correo,
                profesion,
                maestria,
                diplomado,
                estado
            )
            VALUES
            (
                :ci,
                :nombre,
                :apellido,
                :telefono,
                :correo,
                :profesion,
                :maestria,
                :diplomado,
                :estado
            )
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":profesion", $profesion);
        $stmt->bindParam(":maestria", $maestria);
        $stmt->bindParam(":diplomado", $diplomado);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        header("Location: ../views/docentes/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/docentes/create.php?error=1");
        exit();
    }
}

if ($action == "update") {

    $id_docente = $_POST['id_docente'] ?? '';
    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $profesion = trim($_POST['profesion'] ?? '');
    $maestria = trim($_POST['maestria'] ?? '');
    $diplomado = trim($_POST['diplomado'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Activo');

    if ($id_docente == '' || $ci == '' || $nombre == '' || $apellido == '') {
        header("Location: ../views/docentes/index.php?error=1");
        exit();
    }

    try {

        $sqlVerificar = "
            SELECT COUNT(*)
            FROM docentes
            WHERE ci = :ci
            AND id_docente <> :id_docente
        ";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":ci", $ci);
        $stmtVerificar->bindParam(":id_docente", $id_docente);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/docentes/edit.php?id=" . $id_docente . "&duplicado=1");
            exit();
        }

        $sql = "
            UPDATE docentes
            SET
                ci = :ci,
                nombre = :nombre,
                apellido = :apellido,
                telefono = :telefono,
                correo = :correo,
                profesion = :profesion,
                maestria = :maestria,
                diplomado = :diplomado,
                estado = :estado
            WHERE id_docente = :id_docente
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":profesion", $profesion);
        $stmt->bindParam(":maestria", $maestria);
        $stmt->bindParam(":diplomado", $diplomado);
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

    $id_docente = $_POST['id_docente'] ?? '';

    if ($id_docente == '') {
        header("Location: ../views/docentes/index.php?error=1");
        exit();
    }

    try {

        $sqlUso = "
            SELECT 
                m.nombre_materia,
                g.nombre_grupo
            FROM asignaciones a
            INNER JOIN materias m ON a.id_materia = m.id_materia
            INNER JOIN grupos g ON a.id_grupo = g.id_grupo
            WHERE a.id_docente = :id_docente
        ";

        $stmtUso = $conexion->prepare($sqlUso);
        $stmtUso->bindParam(":id_docente", $id_docente);
        $stmtUso->execute();

        $usos = $stmtUso->fetchAll(PDO::FETCH_ASSOC);

        if (count($usos) > 0) {

            if (count($usos) <= 3) {
                $detalle = [];

                foreach ($usos as $uso) {
                    $detalle[] = $uso['nombre_materia'] . " en " . $uso['nombre_grupo'];
                }

                $msg = "No se puede eliminar porque está asignado a: " . implode(", ", $detalle);
            } else {
                $msg = "No se puede eliminar porque está asignado en " . count($usos) . " registros.";
            }

            header("Location: ../views/docentes/index.php?used=1&msg=" . urlencode($msg));
            exit();
        }

        $sql = "
            DELETE FROM docentes
            WHERE id_docente = :id_docente
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_docente", $id_docente);
        $stmt->execute();

        header("Location: ../views/docentes/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/docentes/index.php?error=1");
        exit();
    }
}

header("Location: ../views/docentes/index.php");
exit();
?>
