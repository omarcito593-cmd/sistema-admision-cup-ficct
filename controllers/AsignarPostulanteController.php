<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

/* =========================
   REGISTRAR ASIGNACIÓN
========================= */
if ($action == "store") {

    $id_postulante = $_POST['id_postulante'];
    $id_grupo = $_POST['id_grupo'];

    try {

        $sqlVerificar = "SELECT COUNT(*) 
                         FROM postulante_grupo 
                         WHERE id_postulante = :id_postulante";

        $stmtVerificar = $conexion->prepare($sqlVerificar);
        $stmtVerificar->bindParam(":id_postulante", $id_postulante);
        $stmtVerificar->execute();

        if ($stmtVerificar->fetchColumn() > 0) {
            header("Location: ../views/asignar_postulantes/index.php?duplicado=1");
            exit();
        }

        $sqlCupo = "
            SELECT 
                g.cupo_maximo,
                COUNT(pg.id_postulante) AS inscritos
            FROM grupos g
            LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
            WHERE g.id_grupo = :id_grupo
            GROUP BY g.id_grupo, g.cupo_maximo
        ";

        $stmtCupo = $conexion->prepare($sqlCupo);
        $stmtCupo->bindParam(":id_grupo", $id_grupo);
        $stmtCupo->execute();

        $grupo = $stmtCupo->fetch(PDO::FETCH_ASSOC);

        if (!$grupo) {
            header("Location: ../views/asignar_postulantes/index.php?error=1");
            exit();
        }

        if ($grupo['inscritos'] >= $grupo['cupo_maximo']) {
            header("Location: ../views/asignar_postulantes/index.php?cupo=1");
            exit();
        }

        $sql = "INSERT INTO postulante_grupo (id_postulante, id_grupo)
                VALUES (:id_postulante, :id_grupo)";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_postulante", $id_postulante);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->execute();

        header("Location: ../views/asignar_postulantes/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_postulantes/index.php?error=1");
        exit();
    }
}

/* =========================
   CAMBIAR DE GRUPO
========================= */
if ($action == "update") {

    $id_postulante_grupo = $_POST['id_postulante_grupo'];
    $id_grupo = $_POST['id_grupo'];

    try {

        // Verificar cupo del nuevo grupo
        $sqlCupo = "
            SELECT 
                g.cupo_maximo,
                COUNT(pg.id_postulante) AS inscritos
            FROM grupos g
            LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
            WHERE g.id_grupo = :id_grupo
            GROUP BY g.id_grupo, g.cupo_maximo
        ";

        $stmtCupo = $conexion->prepare($sqlCupo);
        $stmtCupo->bindParam(":id_grupo", $id_grupo);
        $stmtCupo->execute();

        $grupo = $stmtCupo->fetch(PDO::FETCH_ASSOC);

        if (!$grupo) {
            header("Location: ../views/asignar_postulantes/index.php?error=1");
            exit();
        }

        if ($grupo['inscritos'] >= $grupo['cupo_maximo']) {
            header("Location: ../views/asignar_postulantes/index.php?cupo=1");
            exit();
        }

        $sql = "UPDATE postulante_grupo
                SET id_grupo = :id_grupo
                WHERE id_postulante_grupo = :id_postulante_grupo";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_grupo", $id_grupo);
        $stmt->bindParam(":id_postulante_grupo", $id_postulante_grupo);
        $stmt->execute();

        header("Location: ../views/asignar_postulantes/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_postulantes/index.php?error=1");
        exit();
    }
}

/* =========================
   ELIMINAR ASIGNACIÓN
========================= */
if ($action == "delete") {

    $id_postulante_grupo = $_POST['id_postulante_grupo'];

    try {

        $sql = "DELETE FROM postulante_grupo
                WHERE id_postulante_grupo = :id_postulante_grupo";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_postulante_grupo", $id_postulante_grupo);
        $stmt->execute();

        header("Location: ../views/asignar_postulantes/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/asignar_postulantes/index.php?error=1");
        exit();
    }
}

header("Location: ../views/asignar_postulantes/index.php");
exit();