<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM aulas WHERE id_aula = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$aula = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aula) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aula - FITCCT</title>
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
        <h1>Editar Aula</h1>
        <p>Modifique los datos del aula seleccionada.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar el aula.</div>
        <?php endif; ?>

        <form action="../../controllers/AulaController.php?action=update" method="POST">
            <input type="hidden" name="id_aula" value="<?php echo $aula['id_aula']; ?>">

            <div class="form-group">
                <label>Nombre del aula</label>
                <input type="text" name="nombre_aula" value="<?php echo $aula['nombre_aula']; ?>" required>
            </div>

            <div class="form-group">
                <label>Capacidad</label>
                <input type="number" name="capacidad" value="<?php echo $aula['capacidad']; ?>" min="1" required>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Disponible" <?php echo ($aula['estado'] == 'Disponible') ? 'selected' : ''; ?>>Disponible</option>
                    <option value="Ocupada" <?php echo ($aula['estado'] == 'Ocupada') ? 'selected' : ''; ?>>Ocupada</option>
                    <option value="Inactiva" <?php echo ($aula['estado'] == 'Inactiva') ? 'selected' : ''; ?>>Inactiva</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Aula</button>
        </form>
    </div>
</div>

</body>
</html>