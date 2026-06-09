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
    <title>Exportar Reportes</title>
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


        .exportar-page {
            max-width: 1050px;
            margin: 30px auto;
            padding: 0 25px;
        }

        .exportar-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .exportar-header p {
            margin: 5px 0 22px 0;
            color: #333;
        }

        .acciones-superior {
            margin-bottom: 18px;
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

        .export-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .export-card {
            background: #fff;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
            text-decoration: none;
            color: #000;
            border-left: 6px solid #198754;
        }

        .export-card h2 {
            margin: 0 0 8px 0;
            color: #198754;
            font-size: 21px;
        }

        .export-card p {
            margin: 0;
            color: #555;
        }

        @media (max-width: 760px) {
            .export-grid {
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


<div class="exportar-page">

    <div class="exportar-header">
        <h1>Exportar Reportes</h1>
        <p>Seleccione el reporte que desea descargar en formato CSV.</p>
    </div>

    <div class="acciones-superior">
        <a href="index.php" class="btn-volver">Volver a Reportes</a>
    </div>

    <div class="export-grid">

        <a href="exportar_csv.php?tipo=aprobados" class="export-card">
            <h2>Aprobados</h2>
            <p>Descargar reporte de postulantes aprobados.</p>
        </a>

        <a href="exportar_csv.php?tipo=reprobados" class="export-card">
            <h2>Reprobados</h2>
            <p>Descargar reporte de postulantes reprobados.</p>
        </a>

        <a href="exportar_csv.php?tipo=grupo_mas_aprobados" class="export-card">
            <h2>Grupo con más aprobados</h2>
            <p>Descargar el grupo con mayor cantidad de aprobados.</p>
        </a>

        <a href="exportar_csv.php?tipo=grupo_mas_reprobados" class="export-card">
            <h2>Grupo con más reprobados</h2>
            <p>Descargar el grupo con mayor cantidad de reprobados.</p>
        </a>

        <a href="exportar_csv.php?tipo=general_postulantes" class="export-card">
            <h2>General de postulantes</h2>
            <p>Descargar listado general de postulantes.</p>
        </a>

        <a href="exportar_csv.php?tipo=estadisticas_materia" class="export-card">
            <h2>Estadísticas por materia</h2>
            <p>Descargar resumen de aprobados y reprobados por materia.</p>
        </a>

        <a href="exportar_csv.php?tipo=docentes_por_grupo" class="export-card">
            <h2>Docentes por grupo</h2>
            <p>Descargar listado de docentes asignados por grupo.</p>
        </a>

    </div>

</div>

</body>
</html>
