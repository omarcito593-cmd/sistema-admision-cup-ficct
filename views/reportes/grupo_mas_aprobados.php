<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "
        WITH conteo AS (
            SELECT 
                g.id_grupo,
                g.nombre_grupo,
                g.turno,
                a.nombre_aula,
                COUNT(DISTINCT CASE WHEN n.resultado = 'APROBADO' THEN p.id_postulante END) AS cantidad_aprobados
            FROM grupos g
            INNER JOIN aulas a ON g.id_aula = a.id_aula
            LEFT JOIN postulante_grupo pg ON g.id_grupo = pg.id_grupo
            LEFT JOIN postulantes p ON pg.id_postulante = p.id_postulante
            LEFT JOIN notas n ON p.id_postulante = n.id_postulante
            GROUP BY g.id_grupo, g.nombre_grupo, g.turno, a.nombre_aula
        )
        SELECT *
        FROM conteo
        WHERE cantidad_aprobados = (
            SELECT MAX(cantidad_aprobados)
            FROM conteo
        )
        ORDER BY nombre_grupo ASC
    ";

    $stmt = $conexion->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $datos = [];
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
    <title>Grupo con Más Aprobados</title>
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
            max-width: 1250px;
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
            min-width: 1050px;
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

        @media (max-width: 760px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .topbar-right {
                flex-wrap: wrap;
            }
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
        <h1>Grupo con Más Aprobados</h1>
        <p>Grupo o grupos con mayor cantidad de postulantes aprobados.</p>
    </div>

    <div class="acciones-superior">
        <a href="index.php" class="btn-volver">Volver a Reportes</a>
        <a href="exportar_csv.php?tipo=grupo_mas_aprobados" class="btn-exportar">Exportar CSV</a>
    </div>

    <div class="tabla-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        
                        <th>N°</th>
                        <th>Grupo</th>
                        <th>Turno</th>
                        <th>Aula</th>
                        <th>Cantidad</th>

                    </tr>
                </thead>

                <tbody>
                    <?php if (count($datos) > 0): ?>
                        <?php $numero = 1; ?>
                        <?php foreach ($datos as $fila): ?>
                            
                            <tr>
                                <td><?php echo $numero++; ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_grupo']); ?></td>
                                <td><?php echo htmlspecialchars($fila['turno']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_aula']); ?></td>
                                <td><strong><?php echo htmlspecialchars($fila['cantidad_aprobados']); ?></strong></td>
                            </tr>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">No hay datos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
