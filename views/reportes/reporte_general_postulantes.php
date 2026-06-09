<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "
        SELECT 
            p.id_postulante,
            p.ci,
            p.nombre,
            p.apellido,
            p.apellido_paterno,
            p.apellido_materno,
            p.telefono,
            p.correo,
            c1.nombre_carrera AS carrera_principal,
            c2.nombre_carrera AS carrera_segunda_opcion,
            g.nombre_grupo,
            g.turno,
            COUNT(n.id_nota) AS cantidad_notas,
            ROUND(AVG(n.promedio_final), 2) AS promedio_general,
            CASE 
                WHEN COUNT(n.id_nota) = 0 THEN 'SIN NOTAS'
                WHEN AVG(n.promedio_final) >= 60 THEN 'APROBADO'
                ELSE 'REPROBADO'
            END AS resultado_general
        FROM postulantes p
        INNER JOIN carreras c1 ON p.id_carrera = c1.id_carrera
        LEFT JOIN carreras c2 ON p.id_carrera_segunda_opcion = c2.id_carrera
        LEFT JOIN postulante_grupo pg ON p.id_postulante = pg.id_postulante
        LEFT JOIN grupos g ON pg.id_grupo = g.id_grupo
        LEFT JOIN notas n ON p.id_postulante = n.id_postulante
        GROUP BY 
            p.id_postulante, p.ci, p.nombre, p.apellido, p.apellido_paterno, p.apellido_materno,
            p.telefono, p.correo, c1.nombre_carrera, c2.nombre_carrera, g.nombre_grupo, g.turno
        ORDER BY p.id_postulante ASC
    ";

    $stmt = $conexion->query($sql);
    $postulantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $postulantes = [];
}

function nombreCompleto($fila) {
    $nombre = $fila['nombre'] ?? '';
    $paterno = $fila['apellido_paterno'] ?? '';
    $materno = $fila['apellido_materno'] ?? '';

    if ($paterno != '' || $materno != '') {
        return trim($nombre . " " . $paterno . " " . $materno);
    }

    return trim($nombre . " " . ($fila['apellido'] ?? ''));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte General de Postulantes</title>
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
            max-width: 1280px;
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

        .table-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1250px;
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

        .aprobado {
            color: green;
            font-weight: bold;
        }

        .reprobado {
            color: red;
            font-weight: bold;
        }

        .sin-notas {
            color: #777;
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
        <h1>Reporte General de Postulantes</h1>
        <p>Listado general de postulantes con carrera, grupo y resultado general.</p>
    </div>

    <div class="acciones-superior">
        <a href="index.php" class="btn-volver">Volver a Reportes</a>
        <a href="exportar_csv.php?tipo=general_postulantes" class="btn-exportar">Exportar CSV</a>
    </div>

    <div class="tabla-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>CI</th>
                        <th>Postulante</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Carrera principal</th>
                        <th>Segunda opción</th>
                        <th>Grupo</th>
                        <th>Notas</th>
                        <th>Promedio general</th>
                        <th>Resultado general</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($postulantes) > 0): ?>
                        <?php $numero = 1; ?>
                        <?php foreach ($postulantes as $p): ?>
                            <tr>
                                <td><?php echo $numero++; ?></td>
                                <td><?php echo htmlspecialchars($p['ci']); ?></td>
                                <td><?php echo htmlspecialchars(nombreCompleto($p)); ?></td>
                                <td><?php echo htmlspecialchars($p['telefono'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['correo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['carrera_principal']); ?></td>
                                <td><?php echo htmlspecialchars($p['carrera_segunda_opcion'] ?? 'Sin segunda opción'); ?></td>
                                <td>
                                    <?php 
                                        echo htmlspecialchars(
                                            ($p['nombre_grupo'] ?? 'Sin grupo') . 
                                            (isset($p['turno']) ? ' - ' . $p['turno'] : '')
                                        ); 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['cantidad_notas']); ?></td>
                                <td>
                                    <?php echo $p['promedio_general'] !== null ? number_format($p['promedio_general'], 2) : '-'; ?>
                                </td>
                                <td>
                                    <?php if ($p['resultado_general'] == 'APROBADO'): ?>
                                        <span class="aprobado">APROBADO</span>
                                    <?php elseif ($p['resultado_general'] == 'REPROBADO'): ?>
                                        <span class="reprobado">REPROBADO</span>
                                    <?php else: ?>
                                        <span class="sin-notas">SIN NOTAS</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" style="text-align:center;">No hay postulantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
