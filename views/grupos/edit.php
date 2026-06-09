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

$sql = "SELECT * FROM grupos WHERE id_grupo = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$grupo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grupo) {
    header("Location: index.php");
    exit();
}

$stmtAulas = $conexion->query("SELECT id_aula, nombre_aula, capacidad FROM aulas WHERE estado != 'Inactiva'");
$aulas = $stmtAulas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo - FITCCT</title>
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
        <h1>Editar Grupo</h1>
        <p>Modifique los datos del grupo seleccionado.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar el grupo.</div>
        <?php endif; ?>

        <form action="../../controllers/GrupoController.php?action=update" method="POST">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del grupo</label>
                    <input type="text" name="nombre_grupo" value="<?php echo $grupo['nombre_grupo']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Turno</label>
                    <select name="turno" required>
                        <option value="Mañana" <?php echo ($grupo['turno'] == 'Mañana') ? 'selected' : ''; ?>>Mañana</option>
                        <option value="Tarde" <?php echo ($grupo['turno'] == 'Tarde') ? 'selected' : ''; ?>>Tarde</option>
                        <option value="Noche" <?php echo ($grupo['turno'] == 'Noche') ? 'selected' : ''; ?>>Noche</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Aula</label>
                    <select name="id_aula" required>
                        <?php foreach ($aulas as $aula): ?>
                            <option value="<?php echo $aula['id_aula']; ?>"
                                <?php echo ($aula['id_aula'] == $grupo['id_aula']) ? 'selected' : ''; ?>>
                                <?php echo $aula['nombre_aula']; ?> - Capacidad: <?php echo $aula['capacidad']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cupo máximo del grupo</label>
                    <input type="number" name="cupo_maximo" value="<?php echo $grupo['cupo_maximo']; ?>" min="1" max="70" required>
                </div>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Activo" <?php echo ($grupo['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="Inactivo" <?php echo ($grupo['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Grupo</button>
        </form>
    </div>
</div>

</body>
</html>