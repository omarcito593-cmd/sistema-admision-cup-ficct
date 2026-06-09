<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1, 3]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

/* 
   FUNCIONES
 */
function calcularPromedio($examen1, $examen2, $examen3) {
    return round(($examen1 * 0.30) + ($examen2 * 0.30) + ($examen3 * 0.40), 2);
}

function calcularResultado($promedio) {
    return ($promedio >= 60) ? "APROBADO" : "REPROBADO";
}

/* 
   REGISTRAR NOTA
 */
if ($action == "store") {

    $id_postulante = $_POST['id_postulante'] ?? '';
    $id_materia = $_POST['id_materia'] ?? '';
    $examen1 = $_POST['examen1'] ?? '';
    $examen2 = $_POST['examen2'] ?? '';
    $examen3 = $_POST['examen3'] ?? '';

    if ($id_postulante == '' || $id_materia == '' || $examen1 === '' || $examen2 === '' || $examen3 === '') {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }

    if ($examen1 < 0 || $examen1 > 100 || $examen2 < 0 || $examen2 > 100 || $examen3 < 0 || $examen3 > 100) {
        header("Location: ../views/notas/index.php?rango=1");
        exit();
    }

    try {

        /* Verificar si ya existe nota para ese postulante y materia */
        $sqlVerificar = "
            SELECT COUNT(*) 
            FROM notas 
            WHERE id_postulante = :id_postulante
            AND id_materia = :id_materia
        ";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":id_postulante", $id_postulante);
        $stmtVerificar->bindParam(":id_materia", $id_materia);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/notas/index.php?duplicado=1");
            exit();
        }

        $promedio_final = calcularPromedio($examen1, $examen2, $examen3);
        $resultado = calcularResultado($promedio_final);

        $sql = "
            INSERT INTO notas
            (
                id_postulante,
                id_materia,
                examen1,
                examen2,
                examen3,
                promedio_final,
                resultado
            )
            VALUES
            (
                :id_postulante,
                :id_materia,
                :examen1,
                :examen2,
                :examen3,
                :promedio_final,
                :resultado
            )
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_postulante", $id_postulante);
        $stmt->bindParam(":id_materia", $id_materia);
        $stmt->bindParam(":examen1", $examen1);
        $stmt->bindParam(":examen2", $examen2);
        $stmt->bindParam(":examen3", $examen3);
        $stmt->bindParam(":promedio_final", $promedio_final);
        $stmt->bindParam(":resultado", $resultado);
        $stmt->execute();

        header("Location: ../views/notas/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }
}

/* 
   ACTUALIZAR NOTA
 */
if ($action == "update") {

    $id_nota = $_POST['id_nota'] ?? '';
    $examen1 = $_POST['examen1'] ?? '';
    $examen2 = $_POST['examen2'] ?? '';
    $examen3 = $_POST['examen3'] ?? '';

    if ($id_nota == '' || $examen1 === '' || $examen2 === '' || $examen3 === '') {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }

    if ($examen1 < 0 || $examen1 > 100 || $examen2 < 0 || $examen2 > 100 || $examen3 < 0 || $examen3 > 100) {
        header("Location: ../views/notas/index.php?rango=1");
        exit();
    }

    try {

        $promedio_final = calcularPromedio($examen1, $examen2, $examen3);
        $resultado = calcularResultado($promedio_final);

        $sql = "
            UPDATE notas
            SET
                examen1 = :examen1,
                examen2 = :examen2,
                examen3 = :examen3,
                promedio_final = :promedio_final,
                resultado = :resultado
            WHERE id_nota = :id_nota
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":examen1", $examen1);
        $stmt->bindParam(":examen2", $examen2);
        $stmt->bindParam(":examen3", $examen3);
        $stmt->bindParam(":promedio_final", $promedio_final);
        $stmt->bindParam(":resultado", $resultado);
        $stmt->bindParam(":id_nota", $id_nota);
        $stmt->execute();

        header("Location: ../views/notas/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }
}

/* 
   ELIMINAR NOTA
   Solo elimina la nota
 */
if ($action == "delete") {

    $id_nota = $_POST['id_nota'] ?? '';

    if ($id_nota == '') {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }

    try {

        $sql = "
            DELETE FROM notas
            WHERE id_nota = :id_nota
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_nota", $id_nota);
        $stmt->execute();

        header("Location: ../views/notas/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/notas/index.php?error=1");
        exit();
    }
}

/* 
   SI NO EXISTE ACCIÓN
*/
header("Location: ../views/notas/index.php");
exit();

?>
