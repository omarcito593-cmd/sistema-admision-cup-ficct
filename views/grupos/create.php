<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$stmt = $conexion->query("SELECT id_aula, nombre_aula, capacidad FROM aulas WHERE estado = 'Disponible'");
$aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Grupo - FITCCT</title>
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
        <h1>Registrar Nuevo Grupo</h1>
        <p>Ingrese los datos del grupo, turno, aula y cupo máximo.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo registrar el grupo. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/GrupoController.php?action=store" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del grupo</label>
                    <input type="text" name="nombre_grupo" placeholder="Ej: Grupo A" required>
                </div>

                <div class="form-group">
                    <label>Turno</label>
                    <select name="turno" required>
                        <option value="">Seleccione turno</option>
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                        <option value="Noche">Noche</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Aula</label>
                    <select name="id_aula" required>
                        <option value="">Seleccione aula</option>
                        <?php foreach ($aulas as $aula): ?>
                            <option value="<?php echo $aula['id_aula']; ?>">
                                <?php echo $aula['nombre_aula']; ?> - Capacidad: <?php echo $aula['capacidad']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cupo máximo del grupo</label>
                    <input type="number" name="cupo_maximo" value="70" min="1" max="70"required>
                </div>
            </div>

            <button type="submit" class="btn">Guardar Grupo</button>
        </form>
    </div>
</div>

</body>
</html>