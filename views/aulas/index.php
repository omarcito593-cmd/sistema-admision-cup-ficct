<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT * FROM aulas ORDER BY id_aula DESC";
$stmt = $conexion->query($sql);
$aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aulas - FITCCT</title>
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
            <h1>Gestión de Aulas</h1>
            <p>Administración de aulas y capacidad máxima de alumnos.</p>
        </div>
        <a class="btn-link" href="create.php">Nueva Aula</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Aula registrada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Aula actualizada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Aula desactivada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error. Verifique los datos.</div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del aula</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($aulas) > 0): ?>
                    <?php foreach ($aulas as $aula): ?>
                        <tr>
                            <td><?php echo $aula['id_aula']; ?></td>
                            <td><?php echo $aula['nombre_aula']; ?></td>
                            <td><?php echo $aula['capacidad']; ?> alumnos</td>
                            <td><?php echo $aula['estado']; ?></td>
                            <td>
                                <a class="btn-small edit" href="edit.php?id=<?php echo $aula['id_aula']; ?>">Editar</a>

                                <a class="btn-small delete"
                                   href="../../controllers/AulaController.php?action=delete&id=<?php echo $aula['id_aula']; ?>"
                                   onclick="return confirm('¿Está seguro de desactivar esta aula?')">
                                   Desactivar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No hay aulas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>