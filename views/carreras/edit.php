<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM carreras WHERE id_carrera = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$carrera = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carrera) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Carrera - FITCCT</title>
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
        <h1>Editar Carrera</h1>
        <p>Modifique los datos de la carrera.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar la carrera.</div>
        <?php endif; ?>

        <form action="../../controllers/CarreraController.php?action=update" method="POST">
            <input type="hidden" name="id_carrera" value="<?php echo $carrera['id_carrera']; ?>">

            <div class="form-group">
                <label>Nombre de carrera</label>
                <input type="text" name="nombre_carrera" value="<?php echo $carrera['nombre_carrera']; ?>" required>
            </div>

            <div class="form-group">
                <label>Sigla</label>
                <input type="text" name="sigla" value="<?php echo $carrera['sigla']; ?>" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Activo" <?php echo ($carrera['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo ($carrera['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Carrera</button>
        </form>
    </div>
</div>

</body>
</html>