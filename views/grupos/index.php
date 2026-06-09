<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../../config/database.php";

$sql = "SELECT 
            g.id_grupo, 
            g.nombre_grupo, 
            g.turno, 
            g.cupo_maximo, 
            g.estado,
            a.nombre_aula, 
            a.capacidad
        FROM grupos g
        INNER JOIN aulas a ON g.id_aula = a.id_aula
        ORDER BY g.id_grupo ASC";

$stmt = $conexion->query($sql);
$grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupos - FITCCT</title>
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
            <h1>Gestión de Grupos</h1>
            <p>Administración de grupos, turnos, aulas y cupos.</p>
        </div>

        <a class="btn-link" href="create.php">Nuevo Grupo</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Grupo registrado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Grupo actualizado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Grupo eliminado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['used'])): ?>
        <div class="error">
            No se puede eliminar el grupo.
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error al procesar la solicitud.</div>
    <?php endif; ?>

    <div class="table-container">
        <table class="tabla-grupos">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Grupo</th>
                    <th>Turno</th>
                    <th>Aula</th>
                    <th>Capacidad aula</th>
                    <th>Cupo grupo</th>
                    <th>Estado</th>
                    <th class="acciones-col">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($grupos) > 0): ?>
                    <?php $numero = 1; ?>

                    <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td><?php echo $numero++; ?></td>
                            <td><?php echo $grupo['nombre_grupo']; ?></td>
                            <td><?php echo $grupo['turno']; ?></td>
                            <td><?php echo $grupo['nombre_aula']; ?></td>
                            <td><?php echo $grupo['capacidad']; ?> alumnos</td>
                            <td><?php echo $grupo['cupo_maximo']; ?> alumnos</td>
                            <td><?php echo $grupo['estado']; ?></td>

                            <td class="acciones-col">
                                <div class="acciones-grupos">
                                    <a class="btn-small edit" href="edit.php?id=<?php echo $grupo['id_grupo']; ?>">
                                        Editar
                                    </a>

                                    <form action="../../controllers/GrupoController.php?action=delete" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este grupo?');">
                                        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                                        <button type="submit" class="btn-small delete">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="8">No hay grupos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>