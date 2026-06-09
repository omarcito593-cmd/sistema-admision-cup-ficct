<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$sqlNotas = "
    SELECT 
        n.id_nota,
        p.ci,
        p.nombre,
        p.apellido,
        m.nombre_materia,
        n.examen1,
        n.examen2,
        n.examen3,
        n.promedio_final,
        n.resultado
    FROM notas n
    INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
    INNER JOIN materias m ON n.id_materia = m.id_materia
    ORDER BY p.apellido, p.nombre, m.nombre_materia
";

$stmtNotas = $conexion->query($sqlNotas);
$notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calcular Nota Final</title>
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

    <h1>Calcular Nota Final</h1>
    <p>
        El sistema calcula la nota final considerando:
        Examen 1 = 30%, Examen 2 = 30% y Examen 3 = 40%.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Notas recalculadas correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">No se pudieron recalcular las notas.</div>
    <?php endif; ?>

    <div class="form-container">
        <form action="../../controllers/CalcularNotaController.php?action=recalcular" method="POST">
            <button type="submit" class="btn">Recalcular Notas Finales</button>
        </form>
    </div>

    <h2>Resultados de notas finales</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>CI</th>
                <th>Postulante</th>
                <th>Materia</th>
                <th>Examen 1</th>
                <th>Examen 2</th>
                <th>Examen 3</th>
                <th>Nota Final</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($notas) > 0): ?>
                <?php foreach ($notas as $nota): ?>
                    <tr>
                        <td><?php echo $nota['id_nota']; ?></td>
                        <td><?php echo $nota['ci']; ?></td>
                        <td><?php echo $nota['nombre'] . " " . $nota['apellido']; ?></td>
                        <td><?php echo $nota['nombre_materia']; ?></td>
                        <td><?php echo $nota['examen1']; ?></td>
                        <td><?php echo $nota['examen2']; ?></td>
                        <td><?php echo $nota['examen3']; ?></td>
                        <td><?php echo $nota['promedio_final']; ?></td>
                        <td>
                            <?php if ($nota['resultado'] == 'APROBADO'): ?>
                                <strong style="color: green;">APROBADO</strong>
                            <?php else: ?>
                                <strong style="color: red;">REPROBADO</strong>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No existen notas registradas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>