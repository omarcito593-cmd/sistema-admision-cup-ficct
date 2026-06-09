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
    <title>Nuevo Docente - FITCCT</title>
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
        <h1>Registrar Nuevo Docente</h1>
        <p>Ingrese los datos del docente.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar el docente. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/DocenteController.php?action=store" method="POST">
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
                    <input type="text" name="ci">
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono">
                </div>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo">
            </div>

            <button type="submit" class="btn">Guardar Docente</button>
        </form>
    </div>
</div>

</body>
</html>