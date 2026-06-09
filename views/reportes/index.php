<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2, 3]);

require_once __DIR__ . "/../../config/database.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
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


        .reportes-page {
            max-width: 1180px;
            margin: 30px auto;
            padding: 0 25px;
        }

        .reportes-header {
            margin-bottom: 25px;
        }

        .reportes-header h1 {
            margin: 0;
            font-size: 32px;
        }

        .reportes-header p {
            margin: 5px 0 0 0;
            color: #333;
        }

        .reportes-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .reporte-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
            text-decoration: none;
            color: #000;
            border-left: 6px solid #1f3a5f;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .reporte-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 13px rgba(0,0,0,0.15);
        }

        .reporte-card h2 {
            margin: 0 0 8px 0;
            color: #1f3a5f;
            font-size: 22px;
        }

        .reporte-card p {
            margin: 0;
            color: #555;
            line-height: 1.4;
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

            .reportes-grid {
                grid-template-columns: 1fr;
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


<div class="reportes-page">

    <div class="reportes-header">
        <h1>Reportes</h1>
        <p>Seleccione el reporte que desea consultar.</p>
    </div>

    <div class="reportes-grid">

        <a href="aprobados.php" class="reporte-card">
            <h2>Reporte de aprobados</h2>
            <p>Muestra los postulantes que aprobaron según su promedio final.</p>
        </a>

        <a href="reprobados.php" class="reporte-card">
            <h2>Reporte de reprobados</h2>
            <p>Muestra los postulantes que no alcanzaron la nota mínima de aprobación.</p>
        </a>

        <a href="grupo_mas_aprobados.php" class="reporte-card">
            <h2>Grupo con más aprobados</h2>
            <p>Identifica el grupo con mayor cantidad de postulantes aprobados.</p>
        </a>

        <a href="grupo_mas_reprobados.php" class="reporte-card">
            <h2>Grupo con más reprobados</h2>
            <p>Identifica el grupo con mayor cantidad de postulantes reprobados.</p>
        </a>

        <a href="reporte_general_postulantes.php" class="reporte-card">
            <h2>Reporte general de postulantes</h2>
            <p>Muestra datos completos del postulante, carrera, grupo y estado de notas.</p>
        </a>

        <a href="estadisticas_materia.php" class="reporte-card">
            <h2>Estadísticas por materia</h2>
            <p>Muestra cantidad de notas, aprobados, reprobados y promedio por materia.</p>
        </a>

        <a href="docentes_por_grupo.php" class="reporte-card">
            <h2>Docentes por grupo</h2>
            <p>Muestra las materias asignadas, docentes y horarios por grupo.</p>
        </a>

        <a href="exportar_reportes.php" class="reporte-card">
            <h2>Exportar reportes</h2>
            <p>Permite descargar reportes en formato CSV para abrirlos en Excel.</p>
        </a>

    </div>

</div>

</body>
</html>
