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
    <title>Nueva Carrera - FITCCT</title>
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
        <h1>Registrar Nueva Carrera</h1>
        <p>Ingrese los datos de la carrera.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar la carrera. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/CarreraController.php?action=store" method="POST">
            <div class="form-group">
                <label>Nombre de carrera</label>
                <input type="text" name="nombre_carrera" required>
            </div>

            <div class="form-group">
                <label>Sigla</label>
                <input type="text" name="sigla" required>
            </div>

            <button type="submit" class="btn">Guardar Carrera</button>
        </form>
    </div>
</div>

</body>
</html>