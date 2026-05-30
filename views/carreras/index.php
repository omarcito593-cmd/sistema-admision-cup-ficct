<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT * FROM carreras ORDER BY id_carrera DESC";
$stmt = $conexion->query($sql);
$carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carreras - FITCCT</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div>
        <strong>Sistema FITCCT</strong>
    </div>
    <div>
        Usuario: <?php echo $_SESSION['nombre']; ?> |
        <a href="../../dashboard.php">Panel</a>
        <a href="../../logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Gestión de Carreras</h1>
            <p>Administración de carreras disponibles para los postulantes.</p>
        </div>
        <a class="btn-link" href="create.php">Nueva Carrera</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Carrera registrada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Carrera actualizada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Carrera desactivada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error. Verifique los datos.</div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de carrera</th>
                    <th>Sigla</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($carreras) > 0): ?>
                    <?php foreach ($carreras as $carrera): ?>
                        <tr>
                            <td><?php echo $carrera['id_carrera']; ?></td>
                            <td><?php echo $carrera['nombre_carrera']; ?></td>
                            <td><?php echo $carrera['sigla']; ?></td>
                            <td><?php echo $carrera['estado']; ?></td>
                            <td>
                                <a class="btn-small edit" href="edit.php?id=<?php echo $carrera['id_carrera']; ?>">Editar</a>

                                <a class="btn-small delete"
                                   href="../../controllers/CarreraController.php?action=delete&id=<?php echo $carrera['id_carrera']; ?>"
                                   onclick="return confirm('¿Está seguro de desactivar esta carrera?')">
                                   Desactivar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No hay carreras registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>