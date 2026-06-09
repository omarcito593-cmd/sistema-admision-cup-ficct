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

$sql = "SELECT * FROM materias WHERE id_materia = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$materia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Materia - FITCCT</title>
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
        <h1>Editar Materia</h1>
        <p>Modifique los datos de la materia seleccionada.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar la materia.</div>
        <?php endif; ?>

        <form action="../../controllers/MateriaController.php?action=update" method="POST">
            <input type="hidden" name="id_materia" value="<?php echo $materia['id_materia']; ?>">

            <div class="form-group">
                <label>Nombre de materia</label>
                <input type="text" name="nombre_materia" value="<?php echo $materia['nombre_materia']; ?>" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Activo" <?php echo ($materia['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo ($materia['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Materia</button>
        </form>
    </div>
</div>

</body>
</html>