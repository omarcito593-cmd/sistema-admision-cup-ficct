<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

/* 
   REGISTRAR GRUPO
 */
if ($action == "store") {

    $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $id_aula = $_POST['id_aula'] ?? '';
    $cupo_maximo = $_POST['cupo_maximo'] ?? 70;
    $estado = $_POST['estado'] ?? 'Activo';

    if ($nombre_grupo == '' || $turno == '' || $id_aula == '') {
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }

    try {

        $sql = "INSERT INTO grupos 
                (nombre_grupo, turno, id_aula, cupo_maximo, estado)
                VALUES 
                (:nombre_grupo, :turno, :id_aula, :cupo_maximo, :estado)";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_grupo", $nombre_grupo);
        $stmt->bindParam(":turno", $turno);
        $stmt->bindParam(":id_aula", $id_aula);
        $stmt->bindParam(":cupo_maximo", $cupo_maximo);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        header("Location: ../views/grupos/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }
}

/* 
   ACTUALIZAR GRUPO
*/
if ($action == "update") {

    $id_grupo = $_POST['id_grupo'] ?? '';
    $nombre_grupo = trim($_POST['nombre_grupo'] ?? '');
    $turno = trim($_POST['turno'] ?? '');
    $id_aula = $_POST['id_aula'] ?? '';
    $cupo_maximo = $_POST['cupo_maximo'] ?? 70;
    $estado = $_POST['estado'] ?? 'Activo';

    if ($id_grupo == '' || $nombre_grupo == '' || $turno == '' || $id_aula == '') {
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }

    try {

        $sql = "UPDATE grupos
                SET nombre_grupo = :nombre_grupo,
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
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }
}

/* 
   ELIMINAR GRUPO
   Solo elimina si no está usado
 */
if ($action == "delete") {

    $id_grupo = $_POST['id_grupo'] ?? '';

    if ($id_grupo == '') {
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }

    try {

        /* Verificar si el grupo tiene postulantes asignados */
        $sqlPostulantes = "
            SELECT 
                p.id_postulante,
                p.nombre,
                p.apellido
            FROM postulante_grupo pg
            INNER JOIN postulantes p ON pg.id_postulante = p.id_postulante
            WHERE pg.id_grupo = :id_grupo
            ORDER BY p.apellido, p.nombre
        ";

        $stmtPostulantes = $conexion->prepare($sqlPostulantes);
        $stmtPostulantes->bindParam(":id_grupo", $id_grupo);
        $stmtPostulantes->execute();

        $postulantesUso = $stmtPostulantes->fetchAll(PDO::FETCH_ASSOC);

        /* Verificar si el grupo tiene materias/docentes asignados */
        $sqlAsignaciones = "
    SELECT DISTINCT
        m.nombre_materia,
        d.nombre,
        d.apellido
    FROM asignaciones a
    INNER JOIN materias m ON a.id_materia = m.id_materia
    INNER JOIN docentes d ON a.id_docente = d.id_docente
    WHERE a.id_grupo = :id_grupo
    ORDER BY m.nombre_materia, d.apellido
";

        $stmtAsignaciones = $conexion->prepare($sqlAsignaciones);
        $stmtAsignaciones->bindParam(":id_grupo", $id_grupo);
        $stmtAsignaciones->execute();

        $asignacionesUso = $stmtAsignaciones->fetchAll(PDO::FETCH_ASSOC);

        $mensajes = [];

        /* Mensaje si tiene postulantes */
        if (count($postulantesUso) > 0) {

            if (count($postulantesUso) <= 3) {

                $nombresPostulantes = [];

                foreach ($postulantesUso as $postulante) {
                    $nombresPostulantes[] = $postulante['nombre'] . " " . $postulante['apellido'];
                }

                $mensajes[] = "El grupo tiene asignado(s) al/los postulante(s): " . implode(", ", $nombresPostulantes);

            } else {

                $mensajes[] = "El grupo tiene " . count($postulantesUso) . " postulantes asignados";
            }
        }

        /* Mensaje si tiene asignaciones de materias/docentes */
        if (count($asignacionesUso) > 0) {

            if (count($asignacionesUso) <= 3) {

                $detalles = [];

                foreach ($asignacionesUso as $asignacion) {
                    $detalles[] = $asignacion['nombre_materia'] . " con " . $asignacion['nombre'] . " " . $asignacion['apellido'];
                }

                $mensajes[] = "El grupo tiene asignaciones de materia/docente: " . implode(", ", $detalles);

            } else {

                $mensajes[] = "El grupo tiene " . count($asignacionesUso) . " asignaciones de materias y docentes";
            }
        }

        /* Si está usado, no se elimina */
        if (count($mensajes) > 0) {

            $mensajeFinal = implode(". ", $mensajes);

            header("Location: ../views/grupos/index.php?used=1&msg=" . urlencode($mensajeFinal));
            exit();
        }

        /* Si no está usado, eliminar grupo */
        $sqlEliminar = "DELETE FROM grupos 
                        WHERE id_grupo = :id_grupo";

        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bindParam(":id_grupo", $id_grupo);
        $stmtEliminar->execute();

        header("Location: ../views/grupos/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/grupos/index.php?error=1");
        exit();
    }
}

/* 
   SI NO EXISTE ACCIÓN
*/
header("Location: ../views/grupos/index.php");
exit();

?>