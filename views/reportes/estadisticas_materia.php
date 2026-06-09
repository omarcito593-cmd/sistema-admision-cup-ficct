<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "
        SELECT 
            m.id_materia,
            m.nombre_materia,
            COUNT(n.id_nota) AS total_notas,
            COUNT(CASE WHEN n.resultado = 'APROBADO' THEN 1 END) AS aprobados,
            COUNT(CASE WHEN n.resultado = 'REPROBADO' THEN 1 END) AS reprobados,
            ROUND(AVG(n.promedio_final), 2) AS promedio_materia
        FROM materias m
        LEFT JOIN notas n ON m.id_materia = n.id_materia
        GROUP BY m.id_materia, m.nombre_materia
        ORDER BY m.id_materia ASC
    ";

    $stmt = $conexion->query($sql);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $materias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas por Materia</title>
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
            max-width: 1100px;
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

        .aprobados {
            color: green;
            font-weight: bold;
        }

        .reprobados {
            color: red;
            font-weight: bold;
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
        <h1>Estadísticas por Materia</h1>
        <p>Resumen de notas, aprobados, reprobados y promedio por materia.</p>
    </div>

    <div class="acciones-superior">
        <a href="index.php" class="btn-volver">Volver a Reportes</a>
        <a href="exportar_csv.php?tipo=estadisticas_materia" class="btn-exportar">Exportar CSV</a>
    </div>

    <div class="tabla-card">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Materia</th>
                    <th>Total de notas</th>
                    <th>Aprobados</th>
                    <th>Reprobados</th>
                    <th>Promedio materia</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($materias) > 0): ?>
                    <?php $numero = 1; ?>
                    <?php foreach ($materias as $m): ?>
                        <tr>
                            <td><?php echo $numero++; ?></td>
                            <td><?php echo htmlspecialchars($m['nombre_materia']); ?></td>
                            <td><?php echo htmlspecialchars($m['total_notas']); ?></td>
                            <td><span class="aprobados"><?php echo htmlspecialchars($m['aprobados']); ?></span></td>
                            <td><span class="reprobados"><?php echo htmlspecialchars($m['reprobados']); ?></span></td>
                            <td>
                                <?php echo $m['promedio_materia'] !== null ? number_format($m['promedio_materia'], 2) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No hay datos disponibles.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
