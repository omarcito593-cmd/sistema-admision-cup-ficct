<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$stmt = $conexion->query("SELECT id_carrera, nombre_carrera FROM carreras WHERE estado = 'Activo'");
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Postulante - FITCCT</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div>
        <strong>Sistema FITCCT</strong>
    </div>
    <div>
        Usuario: <?php echo $_SESSION['nombre']; ?> |
        <a href="index.php">Volver</a>
        <a href="../../logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <h1>Registrar Nuevo Postulante</h1>
        <p>Ingrese los datos del alumno nuevo/postulante.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar el postulante. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/PostulanteController.php?action=store" method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Apellido</label>
            <input type="text" name="apellido" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>CI</label>
            <input type="text" name="ci" required>
        </div>

        <div class="form-group">
            <label>Sexo</label>
            <select name="sexo" required>
                <option value="">Seleccione sexo</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" required>
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" required>
        </div>
    </div>

    <div class="form-group">
        <label>Correo electrónico</label>
        <input type="email" name="correo" required>
    </div>

    <div class="form-group">
        <label>Dirección</label>
        <input type="text" name="direccion" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Colegio de procedencia</label>
            <input type="text" name="colegio_procedencia" required>
        </div>

        <div class="form-group">
            <label>Ciudad</label>
            <input type="text" name="ciudad" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Carrera a la que postula</label>
            <select name="id_carrera" required>
                <option value="">Seleccione una carrera</option>
                <?php foreach ($carreras as $carrera): ?>
                    <option value="<?php echo $carrera['id_carrera']; ?>">
                        <?php echo $carrera['nombre_carrera']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Segunda opción de carrera</label>
            <select name="id_carrera_segunda_opcion">
                <option value="">Seleccione una carrera</option>
                <?php foreach ($carreras as $carrera): ?>
                    <option value="<?php echo $carrera['id_carrera']; ?>">
                        <?php echo $carrera['nombre_carrera']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Título de bachiller</label>
        <select name="titulo_bachiller" required>
            <option value="">Seleccione una opción</option>
            <option value="Si">Sí</option>
            <option value="No">No</option>
        </select>
    </div>

    <div class="form-group">
        <label>Otros requisitos u observaciones</label>
        <textarea name="otros" rows="4" placeholder="Ej: Fotocopia de CI, certificado de nacimiento, otros documentos..."></textarea>
    </div>

    <button type="submit" class="btn">Guardar Postulante</button>
         </form>
    </div>
</div>

</body>
</html>