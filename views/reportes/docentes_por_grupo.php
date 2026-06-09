<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "
        SELECT
            g.nombre_grupo,
            g.turno,
            aul.nombre_aula,
            m.nombre_materia,
            d.nombre,
            d.apellido,
            d.profesion,
            a.horario
        FROM asignaciones a
        INNER JOIN grupos g ON a.id_grupo = g.id_grupo
        INNER JOIN aulas aul ON g.id_aula = aul.id_aula
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN docentes d ON a.id_docente = d.id_docente
        ORDER BY g.id_grupo ASC, m.nombre_materia ASC
    ";

    $stmt = $conexion->query($sql);
    $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $asignaciones = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Docentes por Grupo</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>

        .topbar {
            width: 100%;
            background: #1f3a5f;
            color: #fff;
            padding: 10px 30px;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar-title {
            font-weight: bold;
            font-size: 16px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-user {
            font-size: 16px;
            color: #fff;
        }

        .topbar-btn,
        .topbar-btn:visited,
        .topbar-btn:link {
            background: #f02f4a;
            color: #fff !important;
            text-decoration: none !important;
            padding: 9px 15px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
        }

        .topbar-btn:hover {
            background: #c8233b;
            color: #fff !important;
            text-decoration: none !important;
        }

        .topbar-separador {
            color: #fff;
            font-weight: bold;
        }


        .reporte-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 25px;
        }

        .reporte-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .reporte-header p {
            margin: 5px 0 18px 0;
            color: #333;
        }

        .acciones-superior {
            margin-bottom: 18px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-volver {
            display: inline-block;
            background: #1f3a5f;
            color: #fff !important;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn-exportar {
            display: inline-block;
            background: #198754;
            color: #fff !important;
            text-decoration: none;
            padding: 9px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .tabla-card {
            background: #fff;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1f3a5f;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
        }

        td {
            padding: 11px 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>


<div class="topbar">
    <div class="topbar-title">Sistema FITCCT</div>

    <div class="topbar-right">
        <span class="topbar-user">
            Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?>
        </span>

        <span class="topbar-separador">|</span>

        <a href="../../dashboard.php" class="topbar-btn">Panel principal</a>

        <span class="topbar-separador">|</span>

        <a href="../../logout.php" class="topbar-btn">Cerrar sesión</a>
    </div>
</div>


<div class="reporte-page">

    <div class="reporte-header">
        <h1>Docentes por Grupo</h1>
        <p>Listado de materias, docentes y horarios asignados a cada grupo.</p>
    </div>

    <div class="acciones-superior">
        <a href="index.php" class="btn-volver">Volver a Reportes</a>
        <a href="exportar_csv.php?tipo=docentes_por_grupo" class="btn-exportar">Exportar CSV</a>
    </div>

    <div class="tabla-card">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Grupo</th>
                    <th>Turno</th>
                    <th>Aula</th>
                    <th>Materia</th>
                    <th>Docente</th>
                    <th>Profesión</th>
                    <th>Horario</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($asignaciones) > 0): ?>
                    <?php $numero = 1; ?>
                    <?php foreach ($asignaciones as $a): ?>
                        <tr>
                            <td><?php echo $numero++; ?></td>
                            <td><?php echo htmlspecialchars($a['nombre_grupo']); ?></td>
                            <td><?php echo htmlspecialchars($a['turno']); ?></td>
                            <td><?php echo htmlspecialchars($a['nombre_aula']); ?></td>
                            <td><?php echo htmlspecialchars($a['nombre_materia']); ?></td>
                            <td><?php echo htmlspecialchars($a['nombre'] . " " . $a['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($a['profesion'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($a['horario']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No hay asignaciones registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
