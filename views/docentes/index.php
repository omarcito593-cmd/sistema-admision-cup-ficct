<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT * FROM docentes ORDER BY id_docente DESC";
$stmt = $conexion->query($sql);
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docentes - FITCCT</title>
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
            <h1>Gestión de Docentes</h1>
            <p>Administración de docentes para materias y grupos.</p>
        </div>
        <a class="btn-link" href="create.php">Nuevo Docente</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Docente registrado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Docente actualizado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Docente desactivado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error. Verifique los datos.</div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre completo</th>
                    <th>CI</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($docentes) > 0): ?>
                    <?php foreach ($docentes as $docente): ?>
                        <tr>
                            <td><?php echo $docente['id_docente']; ?></td>
                            <td><?php echo $docente['nombre'] . " " . $docente['apellido']; ?></td>
                            <td><?php echo $docente['ci']; ?></td>
                            <td><?php echo $docente['telefono']; ?></td>
                            <td><?php echo $docente['correo']; ?></td>
                            <td><?php echo $docente['estado']; ?></td>
                            <td>
                                <a class="btn-small edit" href="edit.php?id=<?php echo $docente['id_docente']; ?>">Editar</a>

                                <a class="btn-small delete"
                                   href="../../controllers/DocenteController.php?action=delete&id=<?php echo $docente['id_docente']; ?>"
                                   onclick="return confirm('¿Está seguro de desactivar este docente?')">
                                   Desactivar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No hay docentes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>