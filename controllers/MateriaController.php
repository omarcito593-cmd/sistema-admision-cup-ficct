<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

/* 
   REGISTRAR MATERIA
 */
if ($action == "store") {

    $nombre_materia = trim($_POST['nombre_materia'] ?? $_POST['nombre'] ?? '');
    $estado = $_POST['estado'] ?? 'Activo';

    if ($nombre_materia == '') {
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }

    try {

        $sql = "INSERT INTO materias (nombre_materia, estado)
                VALUES (:nombre_materia, :estado)";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre_materia", $nombre_materia);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();

        header("Location: ../views/materias/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }
}

/* 
   ACTUALIZAR MATERIA
 */
if ($action == "update") {

    $id_materia = $_POST['id_materia'] ?? '';
    $nombre_materia = trim($_POST['nombre_materia'] ?? $_POST['nombre'] ?? '');
    $estado = $_POST['estado'] ?? 'Activo';

    if ($id_materia == '' || $nombre_materia == '') {
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }

    try {

        $sql = "UPDATE materias
                SET nombre_materia = :nombre_materia,
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
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }
}

/* 
   ELIMINAR MATERIA
   Solo elimina si no está usada
 */
if ($action == "delete") {

    $id_materia = $_POST['id_materia'] ?? '';

    if ($id_materia == '') {
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }

    try {

        /* Verificar si la materia está usada en notas por postulantes */
        $sqlPostulantes = "
            SELECT DISTINCT 
                p.id_postulante,
                p.nombre,
                p.apellido
            FROM notas n
            INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
            WHERE n.id_materia = :id_materia
            ORDER BY p.apellido, p.nombre
        ";

        $stmtPostulantes = $conexion->prepare($sqlPostulantes);
        $stmtPostulantes->bindParam(":id_materia", $id_materia);
        $stmtPostulantes->execute();
        $postulantesUso = $stmtPostulantes->fetchAll(PDO::FETCH_ASSOC);

        /* Verificar si la materia está asignada a docentes */
        $sqlDocentes = "
            SELECT DISTINCT 
                d.id_docente,
                d.nombre,
                d.apellido
            FROM asignaciones a
            INNER JOIN docentes d ON a.id_docente = d.id_docente
            WHERE a.id_materia = :id_materia
            ORDER BY d.apellido, d.nombre
        ";

        $stmtDocentes = $conexion->prepare($sqlDocentes);
        $stmtDocentes->bindParam(":id_materia", $id_materia);
        $stmtDocentes->execute();
        $docentesUso = $stmtDocentes->fetchAll(PDO::FETCH_ASSOC);

        $mensajes = [];

        /* Mensaje si está usada por postulantes */
        if (count($postulantesUso) > 0) {

            if (count($postulantesUso) <= 3) {

                $nombresPostulantes = [];

                foreach ($postulantesUso as $postulante) {
                    $nombresPostulantes[] = $postulante['nombre'] . " " . $postulante['apellido'];
                }

                $mensajes[] = "La materia está usada por el/los postulante(s): " . implode(", ", $nombresPostulantes);

            } else {

                $mensajes[] = "La materia está usada por " . count($postulantesUso) . " postulantes";
            }
        }

        /* Mensaje si está asignada a docentes */
        if (count($docentesUso) > 0) {

            if (count($docentesUso) <= 3) {

                $nombresDocentes = [];

                foreach ($docentesUso as $docente) {
                    $nombresDocentes[] = $docente['nombre'] . " " . $docente['apellido'];
                }

                $mensajes[] = "La materia está asignada al/los docente(s): " . implode(", ", $nombresDocentes);

            } else {

                $mensajes[] = "La materia está asignada a " . count($docentesUso) . " docentes";
            }
        }

        /* Si está usada, no se elimina */
        if (count($mensajes) > 0) {

            $mensajeFinal = implode(". ", $mensajes);

            header("Location: ../views/materias/index.php?used=1&msg=" . urlencode($mensajeFinal));
            exit();
        }

        /* Si no está usada, se elimina realmente */
        $sqlEliminar = "DELETE FROM materias
                       WHERE id_materia = :id_materia";

        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bindParam(":id_materia", $id_materia);
        $stmtEliminar->execute();

        header("Location: ../views/materias/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/materias/index.php?error=1");
        exit();
    }
}

/* 
   SI NO EXISTE ACCIÓN
*/
header("Location: ../views/materias/index.php");
exit();

?>