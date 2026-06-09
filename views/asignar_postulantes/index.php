<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../../config/database.php";

/* Postulantes que todavía no tienen grupo */
$sqlPostulantes = "
    SELECT p.id_postulante, p.ci, p.nombre, p.apellido, c.nombre_carrera
    FROM postulantes p
    INNER JOIN carreras c ON p.id_carrera = c.id_carrera
    WHERE NOT EXISTS (
        SELECT 1 
        FROM postulante_grupo pg 
        WHERE pg.id_postulante = p.id_postulante
    )
    ORDER BY p.apellido, p.nombre
";

$stmtPostulantes = $conexion->query($sqlPostulantes);
$postulantes = $stmtPostulantes->fetchAll(PDO::FETCH_ASSOC);

/* Grupos disponibles */
$sqlGrupos = "
    SELECT 
        g.id_grupo,
        g.nombre_grupo,
        g.turno,
        g.cupo_maximo,
        a.nombre_aula,
        COUNT(pg.id_postulante) AS inscritos
    FROM grupos g
    INNER JOIN aulas a ON g.id_aula = a.id_aula
    LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
    WHERE g.estado = 'Activo'
    GROUP BY g.id_grupo, g.nombre_grupo, g.turno, g.cupo_maximo, a.nombre_aula
    ORDER BY g.nombre_grupo
";

$stmtGrupos = $conexion->query($sqlGrupos);
$grupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

/* Lista de asignaciones */
$sqlAsignaciones = "
    SELECT 
        pg.id_postulante_grupo,
        p.ci,
        p.nombre,
        p.apellido,
        g.nombre_grupo,
        g.turno,
        a.nombre_aula,
        pg.fecha_asignacion
    FROM postulante_grupo pg
    INNER JOIN postulantes p ON pg.id_postulante = p.id_postulante
    INNER JOIN grupos g ON pg.id_grupo = g.id_grupo
    INNER JOIN aulas a ON g.id_aula = a.id_aula
    ORDER BY pg.id_postulante_grupo DESC
";

$stmtAsignaciones = $conexion->query($sqlAsignaciones);
$asignaciones = $stmtAsignaciones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Postulantes a Grupos</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div>
        <strong>Sistema FITCCT</strong>
    </div>
    <div>
        Usuario: <?php echo $_SESSION['nombre']; ?> |
        <a href="../../dashboard.php">Panel principal</a> |
        <a href="../../logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">

    <h1>Asignar Postulantes a Grupos</h1>
    <p>Seleccione un postulante y asígnelo a un grupo disponible.</p>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Postulante asignado correctamente.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
    <div class="success">Asignación actualizada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="success">Asignación eliminada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="error">
        No se pudo realizar la asignación.
        <?php if (isset($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

    <?php if (isset($_GET['duplicado'])): ?>
        <div class="error">El postulante ya está asignado a un grupo.</div>
    <?php endif; ?>

    <?php if (isset($_GET['cupo'])): ?>
        <div class="error">El grupo ya alcanzó el cupo máximo permitido.</div>
    <?php endif; ?>

    <div class="form-container">
    <h2>Nueva asignación</h2>

    <form action="../../controllers/AsignarPostulanteController.php?action=store" method="POST">

        <div class="form-group">
            <label>Postulante</label>
            <select name="id_postulante" required>
                <option value="">Seleccione un postulante</option>

                <?php foreach ($postulantes as $postulante): ?>
                    <option value="<?php echo $postulante['id_postulante']; ?>">
                        <?php 
                            echo $postulante['ci'] . " - " . 
                                 $postulante['nombre'] . " " . 
                                 $postulante['apellido'] . " - " . 
                                 $postulante['nombre_carrera']; 
                        ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label>Grupo</label>
            <select name="id_grupo" required>
                <option value="">Seleccione un grupo</option>

                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?php echo $grupo['id_grupo']; ?>">
                        <?php 
                            echo $grupo['nombre_grupo'] . " - " . 
                                 $grupo['turno'] . " - " . 
                                 $grupo['nombre_aula'] . " | " . 
                                 $grupo['inscritos'] . "/" . 
                                 $grupo['cupo_maximo']; 
                        ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <button type="submit" class="btn">Asignar Postulante</button>

    </form>
</div>

 <h2>Postulantes asignados</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>CI</th>
            <th>Postulante</th>
            <th>Grupo actual</th>
            <th>Turno</th>
            <th>Aula</th>
            <th>Fecha</th>
            <th>Cambiar grupo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($asignaciones) > 0): ?>
            <?php $numero = 1; ?>
            <?php foreach ($asignaciones as $asignacion): ?>
                <tr>
                    <td><?php echo $numero++; ?></td>
                    <td><?php echo $asignacion['ci']; ?></td>
                    <td><?php echo $asignacion['nombre'] . " " . $asignacion['apellido']; ?></td>
                    <td><?php echo $asignacion['nombre_grupo']; ?></td>
                    <td><?php echo $asignacion['turno']; ?></td>
                    <td><?php echo $asignacion['nombre_aula']; ?></td>
                    <td><?php echo date("d/m/Y H:i", strtotime($asignacion['fecha_asignacion'])); ?></td>

                    <td>
                        <form action="../../controllers/AsignarPostulanteController.php?action=update" method="POST">
                            <input type="hidden" name="id_postulante_grupo" value="<?php echo $asignacion['id_postulante_grupo']; ?>">

                            <select name="id_grupo" required>
                                <?php foreach ($grupos as $grupo): ?>
                                    <option value="<?php echo $grupo['id_grupo']; ?>">
                                        <?php 
                                            echo $grupo['nombre_grupo'] . " - " .
                                                 $grupo['turno'] . " - " .
                                                 $grupo['nombre_aula'] . " | " .
                                                 $grupo['inscritos'] . "/" .
                                                 $grupo['cupo_maximo'];
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" class="btn-cambiar">Cambiar</button>
                        </form>
                    </td>

                    <td>
                        <form action="../../controllers/AsignarPostulanteController.php?action=delete" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta asignación?');">
                            <input type="hidden" name="id_postulante_grupo" value="<?php echo $asignacion['id_postulante_grupo']; ?>">
                            <button type="submit" class="btn-eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No existen postulantes asignados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</div>

</body>
</html>