<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT * FROM materias ORDER BY id_materia DESC";
$stmt = $conexion->query($sql);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Materias - FITCCT</title>
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
            <h1>Gestión de Materias</h1>
            <p>Administración de materias para los postulantes.</p>
        </div>
        <a class="btn-link" href="create.php">Nueva Materia</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Materia registrada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Materia actualizada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Materia desactivada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error. Verifique los datos.</div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de materia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($materias) > 0): ?>
                    <?php foreach ($materias as $materia): ?>
                        <tr>
                            <td><?php echo $materia['id_materia']; ?></td>
                            <td><?php echo $materia['nombre_materia']; ?></td>
                            <td><?php echo $materia['estado']; ?></td>
                            <td>
                                <a class="btn-small edit" href="edit.php?id=<?php echo $materia['id_materia']; ?>">Editar</a>

                                <a class="btn-small delete"
                                   href="../../controllers/MateriaController.php?action=delete&id=<?php echo $materia['id_materia']; ?>"
                                   onclick="return confirm('¿Está seguro de desactivar esta materia?')">
                                   Desactivar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No hay materias registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>