<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM docentes WHERE id_docente = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$docente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$docente) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Docente - FITCCT</title>
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
        <h1>Editar Docente</h1>
        <p>Modifique los datos del docente seleccionado.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar el docente.</div>
        <?php endif; ?>

        <form action="../../controllers/DocenteController.php?action=update" method="POST">
            <input type="hidden" name="id_docente" value="<?php echo $docente['id_docente']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo $docente['nombre']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo $docente['apellido']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>CI</label>
                    <input type="text" name="ci" value="<?php echo $docente['ci']; ?>">
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo $docente['telefono']; ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="correo" value="<?php echo $docente['correo']; ?>">
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Activo" <?php echo ($docente['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo ($docente['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Docente</button>
        </form>
    </div>
</div>

</body>
</html>