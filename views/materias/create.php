<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../../config/database.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Materia - FITCCT</title>
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
        <h1>Registrar Nueva Materia</h1>
        <p>Ingrese el nombre de la materia.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar la materia. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/MateriaController.php?action=store" method="POST">
            <div class="form-group">
                <label>Nombre de materia</label>
                <input type="text" name="nombre_materia" placeholder="Ej: Computación" required>
            </div>

            <button type="submit" class="btn">Guardar Materia</button>
        </form>
    </div>
</div>

</body>
</html>