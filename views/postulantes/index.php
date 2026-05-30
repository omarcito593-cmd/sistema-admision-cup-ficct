<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT p.id_postulante, p.nombre, p.apellido, p.ci, p.sexo, p.telefono, p.correo,
               p.ciudad, p.colegio_procedencia, p.titulo_bachiller,
               c.nombre_carrera, p.estado, p.fecha_registro
        FROM postulantes p
        INNER JOIN carreras c ON p.id_carrera = c.id_carrera
        ORDER BY p.id_postulante DESC";

$stmt = $conexion->query($sql);
$postulantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulantes - FITCCT</title>
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
            <h1>Gestión de Postulantes</h1>
            <p>Administración de alumnos nuevos/postulantes de la FITCCT.</p>
        </div>
        <a class="btn-link" href="create.php">Nuevo Postulante</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Postulante registrado correctamente.</div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
    <div class="success">Postulante actualizado correctamente.</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
    <div class="success">Postulante eliminado correctamente.</div>
<?php endif; ?>

<?php if (isset($_GET['error_delete'])): ?>
    <div class="error">No se pudo eliminar el postulante.</div>
<?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
    <th>ID</th>
    <th>Nombre completo</th>
    <th>CI</th>
    <th>Sexo</th>
    <th>Teléfono</th>
    <th>Correo</th>
    <th>Ciudad</th>
    <th>Colegio</th>
    <th>Carrera</th>
    <th>Título bachiller</th>
    <th>Estado</th>
    <th>Fecha registro</th>
    <th>Acciones</th>
</tr>
            </thead>
            <tbody>
                <?php if (count($postulantes) > 0): ?>
                    <?php foreach ($postulantes as $postulante): ?>
                        <tr>
                            <td><?php echo $postulante['id_postulante']; ?></td>
<td><?php echo $postulante['nombre'] . " " . $postulante['apellido']; ?></td>
<td><?php echo $postulante['ci']; ?></td>
<td><?php echo $postulante['sexo']; ?></td>
<td><?php echo $postulante['telefono']; ?></td>
<td><?php echo $postulante['correo']; ?></td>
<td><?php echo $postulante['ciudad']; ?></td>
<td><?php echo $postulante['colegio_procedencia']; ?></td>
<td><?php echo $postulante['nombre_carrera']; ?></td>
<td><?php echo $postulante['titulo_bachiller']; ?></td>
<td><?php echo $postulante['estado']; ?></td>
<td><?php echo date("d/m/Y", strtotime($postulante['fecha_registro'])); ?></td>
                            <td>
    <a class="btn-small edit" href="edit.php?id=<?php echo $postulante['id_postulante']; ?>">Editar</a>

    <a class="btn-small delete" 
       href="../../controllers/PostulanteController.php?action=delete&id=<?php echo $postulante['id_postulante']; ?>"
       onclick="return confirm('¿Está seguro de eliminar este postulante?')">
       Eliminar
    </a>
</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13">No hay postulantes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>