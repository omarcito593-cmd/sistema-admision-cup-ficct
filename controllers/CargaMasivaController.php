<?php
session_start();

require_once __DIR__ . "/../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../config/database.php";

$action = $_GET['action'] ?? '';

if ($action != "postulantes") {
    header("Location: ../views/carga_masiva/index.php?error=1");
    exit();
}

if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] != UPLOAD_ERR_OK) {
    header("Location: ../views/carga_masiva/index.php?error=1");
    exit();
}

$archivoTmp = $_FILES['archivo_csv']['tmp_name'];

$handle = fopen($archivoTmp, "r");

if (!$handle) {
    header("Location: ../views/carga_masiva/index.php?error=1");
    exit();
}

/* Detectar delimitador */
$primeraLinea = fgets($handle);
rewind($handle);

$delimitador = (substr_count($primeraLinea, ";") >= substr_count($primeraLinea, ",")) ? ";" : ",";

/* Leer encabezados */
$headers = fgetcsv($handle, 0, $delimitador);

if (!$headers) {
    fclose($handle);
    header("Location: ../views/carga_masiva/index.php?error=1");
    exit();
}

$headers = array_map(function($h) {
    return strtolower(trim($h));
}, $headers);

$esperados = [
    "ci",
    "nombre",
    "apellido_paterno",
    "apellido_materno",
    "telefono",
    "correo",
    "sexo",
    "direccion",
    "colegio_procedencia",
    "ciudad",
    "titulo_bachiller",
    "id_carrera",
    "id_carrera_segunda_opcion",
    "otros"
];

foreach ($esperados as $campo) {
    if (!in_array($campo, $headers)) {
        fclose($handle);
        $_SESSION['resultado_carga'] = [
            "insertados" => 0,
            "errores" => ["Falta el encabezado obligatorio: " . $campo]
        ];
        header("Location: ../views/carga_masiva/index.php");
        exit();
    }
}

$insertados = 0;
$errores = [];
$filaNumero = 1;
$cisArchivo = [];

try {

    while (($data = fgetcsv($handle, 0, $delimitador)) !== false) {

        $filaNumero++;

        if (count(array_filter($data)) == 0) {
            continue;
        }

        $fila = array_combine($headers, array_pad($data, count($headers), ''));

        $ci = trim($fila['ci'] ?? '');
        $nombre = trim($fila['nombre'] ?? '');
        $apellido_paterno = trim($fila['apellido_paterno'] ?? '');
        $apellido_materno = trim($fila['apellido_materno'] ?? '');
        $apellido = trim($apellido_paterno . " " . $apellido_materno);

        $telefono = trim($fila['telefono'] ?? '');
        $correo = trim($fila['correo'] ?? '');
        $sexo = trim($fila['sexo'] ?? '');
        $direccion = trim($fila['direccion'] ?? '');
        $colegio_procedencia = trim($fila['colegio_procedencia'] ?? '');
        $ciudad = trim($fila['ciudad'] ?? '');
        $titulo_bachiller = trim($fila['titulo_bachiller'] ?? '');
        $id_carrera = trim($fila['id_carrera'] ?? '');
        $id_carrera_segunda_opcion = trim($fila['id_carrera_segunda_opcion'] ?? '');
        $otros = trim($fila['otros'] ?? '');

        if ($id_carrera_segunda_opcion == '') {
            $id_carrera_segunda_opcion = null;
        }

        if ($ci == '' || $nombre == '' || $apellido_paterno == '' || $apellido_materno == '' || $id_carrera == '') {
            $errores[] = "Fila $filaNumero: CI, nombre, apellido paterno, apellido materno e id_carrera son obligatorios.";
            continue;
        }

        if ($telefono == '' && $correo == '') {
            $errores[] = "Fila $filaNumero: debe registrar correo o teléfono/celular.";
            continue;
        }

        if (!empty($id_carrera_segunda_opcion) && $id_carrera == $id_carrera_segunda_opcion) {
            $errores[] = "Fila $filaNumero: la segunda opción de carrera debe ser diferente.";
            continue;
        }

        if (in_array($ci, $cisArchivo)) {
            $errores[] = "Fila $filaNumero: CI duplicado dentro del archivo CSV.";
            continue;
        }

        $cisArchivo[] = $ci;

        /* Verificar que la carrera principal exista */
        $stmtCarrera = $conexion->prepare("SELECT COUNT(*) FROM carreras WHERE id_carrera = :id_carrera");
        $stmtCarrera->bindParam(":id_carrera", $id_carrera);
        $stmtCarrera->execute();

        if ($stmtCarrera->fetchColumn() == 0) {
            $errores[] = "Fila $filaNumero: la carrera principal no existe.";
            continue;
        }

        /* Verificar que la segunda carrera exista si fue enviada */
        if (!empty($id_carrera_segunda_opcion)) {
            $stmtCarrera2 = $conexion->prepare("SELECT COUNT(*) FROM carreras WHERE id_carrera = :id_carrera");
            $stmtCarrera2->bindParam(":id_carrera", $id_carrera_segunda_opcion);
            $stmtCarrera2->execute();

            if ($stmtCarrera2->fetchColumn() == 0) {
                $errores[] = "Fila $filaNumero: la segunda opción de carrera no existe.";
                continue;
            }
        }

        /* Verificar CI duplicado en BD */
        $stmtCI = $conexion->prepare("SELECT COUNT(*) FROM postulantes WHERE ci = :ci");
        $stmtCI->bindParam(":ci", $ci);
        $stmtCI->execute();

        if ($stmtCI->fetchColumn() > 0) {
            $errores[] = "Fila $filaNumero: ya existe un postulante con ese CI.";
            continue;
        }

        /* Verificar nombre completo duplicado en BD */
        $stmtNombre = $conexion->prepare("
            SELECT COUNT(*) 
            FROM postulantes 
            WHERE LOWER(nombre) = LOWER(:nombre)
            AND LOWER(apellido_paterno) = LOWER(:apellido_paterno)
            AND LOWER(apellido_materno) = LOWER(:apellido_materno)
        ");
        $stmtNombre->bindParam(":nombre", $nombre);
        $stmtNombre->bindParam(":apellido_paterno", $apellido_paterno);
        $stmtNombre->bindParam(":apellido_materno", $apellido_materno);
        $stmtNombre->execute();

        if ($stmtNombre->fetchColumn() > 0) {
            $errores[] = "Fila $filaNumero: ya existe un postulante con el mismo nombre completo.";
            continue;
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
                sexo,
                direccion,
                colegio_procedencia,
                ciudad,
                titulo_bachiller,
                id_carrera,
                id_carrera_segunda_opcion,
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
                :sexo,
                :direccion,
                :colegio_procedencia,
                :ciudad,
                :titulo_bachiller,
                :id_carrera,
                :id_carrera_segunda_opcion,
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
        $stmt->bindParam(":sexo", $sexo);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":colegio_procedencia", $colegio_procedencia);
        $stmt->bindParam(":ciudad", $ciudad);
        $stmt->bindParam(":titulo_bachiller", $titulo_bachiller);
        $stmt->bindParam(":id_carrera", $id_carrera);
        $stmt->bindParam(":id_carrera_segunda_opcion", $id_carrera_segunda_opcion);
        $stmt->bindParam(":otros", $otros);
        $stmt->execute();

        $insertados++;
    }

    fclose($handle);

    $_SESSION['resultado_carga'] = [
        "insertados" => $insertados,
        "errores" => $errores
    ];

    header("Location: ../views/carga_masiva/index.php");
    exit();

} catch (PDOException $e) {

    fclose($handle);

    $_SESSION['resultado_carga'] = [
        "insertados" => $insertados,
        "errores" => ["Error general: " . $e->getMessage()]
    ];

    header("Location: ../views/carga_masiva/index.php");
    exit();
}

?>
