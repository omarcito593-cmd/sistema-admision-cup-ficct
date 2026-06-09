<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../../config/database.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Aula - FITCCT</title>
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
        <h1>Registrar Nueva Aula</h1>
        <p>Ingrese el aula y su capacidad máxima de alumnos.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar el aula. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/AulaController.php?action=store" method="POST">
            <div class="form-group">
                <label>Nombre del aula</label>
                <input type="text" name="nombre_aula" placeholder="Ej: Aula 5" required>
            </div>

            <div class="form-group">
                <label>Capacidad</label>
                <input type="number" name="capacidad" value="70" min="1" required>
            </div>

            <button type="submit" class="btn">Guardar Aula</button>
        </form>
    </div>
</div>

</body>
</html>