<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action == "store") {

    $id_grupo = $_POST['id_grupo'] ?? '';
    $id_materia = $_POST['id_materia'] ?? '';
    $id_docente = $_POST['id_docente'] ?? '';
    $horario = trim($_POST['horario'] ?? '');

    if ($id_grupo == '' || $id_materia == '' || $id_docente == '' || $horario == '') {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }

    try {
        $sqlVerificar = "
            SELECT COUNT(*)
            FROM asignaciones
            WHERE id_grupo = :id_grupo
            AND id_materia = :id_materia
        ";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":id_grupo", $id_grupo);
        $stmtVerificar->bindParam(":id_materia", $id_materia);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/asignar_materias/index.php?duplicado=1");
            exit();
        }

        $sql = "
            INSERT INTO asignaciones (id_grupo, id_materia, id_docente, horario)
            VALUES (:id_grupo, :id_materia, :id_docente, :horario)
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->bindParam(":id_materia", $id_materia);
        $stmt->bindParam(":id_docente", $id_docente);
        $stmt->bindParam(":horario", $horario);
        $stmt->execute();

        header("Location: ../views/asignar_materias/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }
}

if ($action == "update") {

    $id_asignacion = $_POST['id_asignacion'] ?? '';
    $id_grupo = $_POST['id_grupo'] ?? '';
    $id_materia = $_POST['id_materia'] ?? '';
    $id_docente = $_POST['id_docente'] ?? '';
    $horario = trim($_POST['horario'] ?? '');

    if ($id_asignacion == '' || $id_grupo == '' || $id_materia == '' || $id_docente == '' || $horario == '') {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }

    try {
        $sqlVerificar = "
            SELECT COUNT(*)
            FROM asignaciones
            WHERE id_grupo = :id_grupo
            AND id_materia = :id_materia
            AND id_asignacion <> :id_asignacion
        ";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":id_grupo", $id_grupo);
        $stmtVerificar->bindParam(":id_materia", $id_materia);
        $stmtVerificar->bindParam(":id_asignacion", $id_asignacion);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/asignar_materias/index.php?duplicado=1");
            exit();
        }

        $sql = "
            UPDATE asignaciones
            SET id_grupo = :id_grupo,
                id_materia = :id_materia,
                id_docente = :id_docente,
                horario = :horario
            WHERE id_asignacion = :id_asignacion
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->bindParam(":id_materia", $id_materia);
        $stmt->bindParam(":id_docente", $id_docente);
        $stmt->bindParam(":horario", $horario);
        $stmt->bindParam(":id_asignacion", $id_asignacion);
        $stmt->execute();

        header("Location: ../views/asignar_materias/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }
}

if ($action == "delete") {

    $id_asignacion = $_POST['id_asignacion'] ?? '';

    if ($id_asignacion == '') {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }

    try {
        $sqlAsignacion = "
            SELECT 
                a.id_asignacion,
                a.id_grupo,
                a.id_materia,
                g.nombre_grupo,
                m.nombre_materia
            FROM asignaciones a
            INNER JOIN grupos g ON a.id_grupo = g.id_grupo
            INNER JOIN materias m ON a.id_materia = m.id_materia
            WHERE a.id_asignacion = :id_asignacion
        ";

        $stmtAsignacion = $conexion->prepare($sqlAsignacion);
        $stmtAsignacion->bindParam(":id_asignacion", $id_asignacion);
        $stmtAsignacion->execute();

        $asignacion = $stmtAsignacion->fetch(PDO::FETCH_ASSOC);

        if (!$asignacion) {
            header("Location: ../views/asignar_materias/index.php?error=1");
            exit();
        }

        $id_grupo = $asignacion['id_grupo'];
        $id_materia = $asignacion['id_materia'];

        /*
            Verifica si la materia de esta asignación ya tiene notas
            de postulantes asignados a ese mismo grupo.
        */
        $sqlNotas = "
            SELECT p.nombre, p.apellido
            FROM notas n
            INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
            INNER JOIN postulante_grupo pg ON p.id_postulante = pg.id_postulante
            WHERE n.id_materia = :id_materia
            AND pg.id_grupo = :id_grupo
        ";

        $stmtNotas = $conexion->prepare($sqlNotas);
        $stmtNotas->bindParam(":id_materia", $id_materia);
        $stmtNotas->bindParam(":id_grupo", $id_grupo);
        $stmtNotas->execute();

        $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

        if (count($notas) > 0) {
            if (count($notas) <= 3) {
                $lista = [];
                foreach ($notas as $nota) {
                    $lista[] = $nota['nombre'] . " " . $nota['apellido'];
                }

                $msg = "No se puede eliminar la asignación porque la materia " .
                       $asignacion['nombre_materia'] .
                       " ya tiene notas registradas para: " .
                       implode(", ", $lista) . ".";
            } else {
                $msg = "No se puede eliminar la asignación porque la materia " .
                       $asignacion['nombre_materia'] .
                       " ya tiene notas registradas en " .
                       count($notas) . " postulantes del grupo.";
            }

            header("Location: ../views/asignar_materias/index.php?used=1&msg=" . urlencode($msg));
            exit();
        }

        $sqlDelete = "
            DELETE FROM asignaciones
            WHERE id_asignacion = :id_asignacion
        ";

        $stmtDelete = $conexion->prepare($sqlDelete);
        $stmtDelete->bindParam(":id_asignacion", $id_asignacion);
        $stmtDelete->execute();

        header("Location: ../views/asignar_materias/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_materias/index.php?error=1");
        exit();
    }
}

header("Location: ../views/asignar_materias/index.php");
exit();
?>
