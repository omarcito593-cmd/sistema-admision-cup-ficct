<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

/*
   FUNCIONES AUXILIARES
*/

function guardarDatosTemporales($datos) {
    $_SESSION['old_postulante'] = $datos;
}

function limpiarDatosTemporales() {
    if (isset($_SESSION['old_postulante'])) {
        unset($_SESSION['old_postulante']);
    }
}

function volverCreate($codigoError, $mensaje = '') {
    $url = "../views/postulantes/create.php?" . $codigoError . "=1";

    if ($mensaje != '') {
        $url .= "&msg=" . urlencode($mensaje);
    }

    header("Location: " . $url);
    exit();
}

function volverEdit($id_postulante, $codigoError, $mensaje = '') {
    $url = "../views/postulantes/edit.php?id=" . $id_postulante . "&" . $codigoError . "=1";

    if ($mensaje != '') {
        $url .= "&msg=" . urlencode($mensaje);
    }

    header("Location: " . $url);
    exit();
}

/* 
   REGISTRAR POSTULANTE
*/
if ($action == "store") {

    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $apellido = trim($apellido_paterno . " " . $apellido_materno);

    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    $id_carrera = $_POST['id_carrera'] ?? '';
    $id_carrera_segunda_opcion = $_POST['id_carrera_segunda_opcion'] ?? null;

    $sexo = trim($_POST['sexo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $colegio_procedencia = trim($_POST['colegio_procedencia'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $titulo_bachiller = trim($_POST['titulo_bachiller'] ?? '');
    $otros = trim($_POST['otros'] ?? '');

    if ($id_carrera_segunda_opcion === '') {
        $id_carrera_segunda_opcion = null;
    }

    guardarDatosTemporales($_POST);

    /* Campos obligatorios principales */
    if ($ci == '' || $nombre == '' || $apellido_paterno == '' || $apellido_materno == '' || $id_carrera == '') {
        volverCreate("error", "Debe completar CI, nombre completo, apellido paterno, apellido materno y primera opción de carrera.");
    }

    /* Debe tener al menos correo o teléfono */
    if ($correo == '' && $telefono == '') {
        volverCreate("contacto", "Debe registrar al menos un medio de contacto: correo o teléfono/celular.");
    }

    /* Segunda carrera diferente */
    if (!empty($id_carrera_segunda_opcion) && $id_carrera == $id_carrera_segunda_opcion) {
        volverCreate("carrera_igual", "Debe escoger una carrera diferente como segunda opción.");
    }

    try {

        /* Verificar CI duplicado */
        $sqlVerificarCI = "
            SELECT COUNT(*) 
            FROM postulantes 
            WHERE ci = :ci
        ";

        $stmtVerificarCI = $conexion->prepare($sqlVerificarCI);
        $stmtVerificarCI->bindParam(":ci", $ci);
        $stmtVerificarCI->execute();

        if ($stmtVerificarCI->fetchColumn() > 0) {
            volverCreate("duplicado", "Ya existe un postulante registrado con ese CI.");
        }

        /* Verificar nombre completo duplicado */
        $sqlVerificarNombre = "
            SELECT COUNT(*) 
            FROM postulantes 
            WHERE LOWER(nombre) = LOWER(:nombre)
            AND LOWER(apellido_paterno) = LOWER(:apellido_paterno)
            AND LOWER(apellido_materno) = LOWER(:apellido_materno)
        ";

        $stmtVerificarNombre = $conexion->prepare($sqlVerificarNombre);
        $stmtVerificarNombre->bindParam(":nombre", $nombre);
        $stmtVerificarNombre->bindParam(":apellido_paterno", $apellido_paterno);
        $stmtVerificarNombre->bindParam(":apellido_materno", $apellido_materno);
        $stmtVerificarNombre->execute();

        if ($stmtVerificarNombre->fetchColumn() > 0) {
            volverCreate("nombre_repetido", "Ya existe un postulante con el mismo nombre completo, apellido paterno y apellido materno.");
        }

        $sql = "
            INSERT INTO postulantes
            (
                ci, 
                nombre, 
                apellido,
                apellido_paterno,
                apellido_materno,
                telefono, 
                correo, 
                id_carrera,
                id_carrera_segunda_opcion,
                sexo,
                direccion,
                colegio_procedencia,
                ciudad,
                titulo_bachiller,
                otros
            )
            VALUES
            (
                :ci, 
                :nombre, 
                :apellido,
                :apellido_paterno,
                :apellido_materno,
                :telefono, 
                :correo, 
                :id_carrera,
                :id_carrera_segunda_opcion,
                :sexo,
                :direccion,
                :colegio_procedencia,
                :ciudad,
                :titulo_bachiller,
                :otros
            )
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":apellido_paterno", $apellido_paterno);
        $stmt->bindParam(":apellido_materno", $apellido_materno);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->bindParam(":id_carrera_segunda_opcion", $id_carrera_segunda_opcion);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":colegio_procedencia", $colegio_procedencia);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":titulo_bachiller", $titulo_bachiller);
        $stmt->bindParam(":otros", $otros);
        $stmt->execute();

        limpiarDatosTemporales();

        header("Location: ../views/postulantes/index.php?success=1");
        exit();

    } catch (PDOException $e) {
        volverCreate("error", "Ocurrió un error al registrar el postulante.");
    }
}

/* 
   ACTUALIZAR POSTULANTE
 */
if ($action == "update") {

    $id_postulante = $_POST['id_postulante'] ?? '';

    $ci = trim($_POST['ci'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $apellido = trim($apellido_paterno . " " . $apellido_materno);

    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    $id_carrera = $_POST['id_carrera'] ?? '';
    $id_carrera_segunda_opcion = $_POST['id_carrera_segunda_opcion'] ?? null;

    $sexo = trim($_POST['sexo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $colegio_procedencia = trim($_POST['colegio_procedencia'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $titulo_bachiller = trim($_POST['titulo_bachiller'] ?? '');
    $otros = trim($_POST['otros'] ?? '');

    if ($id_carrera_segunda_opcion === '') {
        $id_carrera_segunda_opcion = null;
    }

    if ($id_postulante == '') {
        header("Location: ../views/postulantes/index.php?error=1");
        exit();
    }

    guardarDatosTemporales($_POST);

    /* Campos obligatorios principales */
    if ($ci == '' || $nombre == '' || $apellido_paterno == '' || $apellido_materno == '' || $id_carrera == '') {
        volverEdit($id_postulante, "error", "Debe completar CI, nombre completo, apellido paterno, apellido materno y primera opción de carrera.");
    }

    /* Debe tener al menos correo o teléfono */
    if ($correo == '' && $telefono == '') {
        volverEdit($id_postulante, "contacto", "Debe registrar al menos un medio de contacto: correo o teléfono/celular.");
    }

    /* Segunda carrera diferente */
    if (!empty($id_carrera_segunda_opcion) && $id_carrera == $id_carrera_segunda_opcion) {
        volverEdit($id_postulante, "carrera_igual", "Debe escoger una carrera diferente como segunda opción.");
    }

    try {

        /* Verificar CI duplicado en otro postulante */
        $sqlVerificarCI = "
            SELECT COUNT(*) 
            FROM postulantes 
            WHERE ci = :ci
            AND id_postulante <> :id_postulante
        ";

        $stmtVerificarCI = $conexion->prepare($sqlVerificarCI);
        $stmtVerificarCI->bindParam(":ci", $ci);
        $stmtVerificarCI->bindParam(":id_postulante", $id_postulante);
        $stmtVerificarCI->execute();

        if ($stmtVerificarCI->fetchColumn() > 0) {
            volverEdit($id_postulante, "duplicado", "Ya existe otro postulante registrado con ese CI.");
        }

        /* Verificar nombre completo duplicado en otro postulante */
        $sqlVerificarNombre = "
            SELECT COUNT(*) 
            FROM postulantes 
            WHERE LOWER(nombre) = LOWER(:nombre)
            AND LOWER(apellido_paterno) = LOWER(:apellido_paterno)
            AND LOWER(apellido_materno) = LOWER(:apellido_materno)
            AND id_postulante <> :id_postulante
        ";

        $stmtVerificarNombre = $conexion->prepare($sqlVerificarNombre);
        $stmtVerificarNombre->bindParam(":nombre", $nombre);
        $stmtVerificarNombre->bindParam(":apellido_paterno", $apellido_paterno);
        $stmtVerificarNombre->bindParam(":apellido_materno", $apellido_materno);
        $stmtVerificarNombre->bindParam(":id_postulante", $id_postulante);
        $stmtVerificarNombre->execute();

        if ($stmtVerificarNombre->fetchColumn() > 0) {
            volverEdit($id_postulante, "nombre_repetido", "Ya existe otro postulante con el mismo nombre completo, apellido paterno y apellido materno.");
        }

        $sql = "
            UPDATE postulantes
            SET 
                ci = :ci,
                nombre = :nombre,
                apellido = :apellido,
                apellido_paterno = :apellido_paterno,
                apellido_materno = :apellido_materno,
                telefono = :telefono,
                correo = :correo,
                id_carrera = :id_carrera,
                id_carrera_segunda_opcion = :id_carrera_segunda_opcion,
                sexo = :sexo,
                direccion = :direccion,
                colegio_procedencia = :colegio_procedencia,
                ciudad = :ciudad,
                titulo_bachiller = :titulo_bachiller,
                otros = :otros
            WHERE id_postulante = :id_postulante
        ";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":ci", $ci);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":apellido_paterno", $apellido_paterno);
        $stmt->bindParam(":apellido_materno", $apellido_materno);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->bindParam(":id_carrera_segunda_opcion", $id_carrera_segunda_opcion);
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":colegio_procedencia", $colegio_procedencia);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":titulo_bachiller", $titulo_bachiller);
        $stmt->bindParam(":otros", $otros);
        $stmt->bindParam(":id_postulante", $id_postulante);
        $stmt->execute();

        limpiarDatosTemporales();

        header("Location: ../views/postulantes/index.php?updated=1");
        exit();

    } catch (PDOException $e) {
        volverEdit($id_postulante, "error", "Ocurrió un error al actualizar el postulante.");
    }
}

/* 
   ELIMINAR POSTULANTE
   Solo elimina si no está usado
*/
if ($action == "delete") {

    $id_postulante = $_POST['id_postulante'] ?? $_GET['id'] ?? '';

    if ($id_postulante == '') {
        header("Location: ../views/postulantes/index.php?error=1");
        exit();
    }

    try {

        /* Verificar si está asignado a un grupo */
        $sqlGrupo = "
            SELECT 
                g.nombre_grupo
            FROM postulante_grupo pg
            INNER JOIN grupos g ON pg.id_grupo = g.id_grupo
            WHERE pg.id_postulante = :id_postulante
        ";

        $stmtGrupo = $conexion->prepare($sqlGrupo);
        $stmtGrupo->bindParam(":id_postulante", $id_postulante);
        $stmtGrupo->execute();
        $grupoUso = $stmtGrupo->fetch(PDO::FETCH_ASSOC);

        if ($grupoUso) {
            $mensaje = "El postulante está asignado al grupo: " . $grupoUso['nombre_grupo'];
            header("Location: ../views/postulantes/index.php?used=1&msg=" . urlencode($mensaje));
            exit();
        }

        /* Verificar si tiene notas */
        $sqlNotas = "
            SELECT COUNT(*) 
            FROM notas 
            WHERE id_postulante = :id_postulante
        ";

        $stmtNotas = $conexion->prepare($sqlNotas);
        $stmtNotas->bindParam(":id_postulante", $id_postulante);
        $stmtNotas->execute();

        if ($stmtNotas->fetchColumn() > 0) {
            $mensaje = "El postulante tiene notas registradas.";
            header("Location: ../views/postulantes/index.php?used=1&msg=" . urlencode($mensaje));
            exit();
        }

        /* Si no está usado, eliminar */
        $sqlEliminar = "
            DELETE FROM postulantes
            WHERE id_postulante = :id_postulante
        ";

        $stmtEliminar = $conexion->prepare($sqlEliminar);
        $stmtEliminar->bindParam(":id_postulante", $id_postulante);
        $stmtEliminar->execute();

        header("Location: ../views/postulantes/index.php?deleted=1");
        exit();

    } catch (PDOException $e) {
        header("Location: ../views/postulantes/index.php?error=1");
        exit();
    }
}

/* 
   SI NO EXISTE ACCIÓN
 */
header("Location: ../views/postulantes/index.php");
exit();

?>
