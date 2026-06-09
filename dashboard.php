<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/config/database.php";

$rol = (int) ($_SESSION['id_rol'] ?? 0);
$nombreRol = $_SESSION['nombre_rol'] ?? 'Sin rol';

try {
    $totalPostulantes = $conexion->query("SELECT COUNT(*) FROM postulantes")->fetchColumn();
    $totalCarreras = $conexion->query("SELECT COUNT(*) FROM carreras")->fetchColumn();
    $totalAulas = $conexion->query("SELECT COUNT(*) FROM aulas")->fetchColumn();
    $totalDocentes = $conexion->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
    $totalGrupos = $conexion->query("SELECT COUNT(*) FROM grupos")->fetchColumn();
} catch (PDOException $e) {
    $totalPostulantes = 0;
    $totalCarreras = 0;
    $totalAulas = 0;
    $totalDocentes = 0;
    $totalGrupos = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal - Sistema FITCCT</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #eef1f5;
        }

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
        }

        .container {
            max-width: 1280px;
            margin: 25px auto;
            padding: 0 25px;
        }

        .dashboard-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 25px;
        }

        .sidebar-panel {
            background: #fff;
            padding: 18px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sidebar-panel h3 {
            margin: 0 0 10px 0;
            color: #1f3a5f;
        }

        .sidebar-btn,
        .sidebar-btn:visited,
        .sidebar-btn:link {
            background: #1f3a5f;
            color: #fff !important;
            text-decoration: none !important;
            padding: 10px 12px;
            border-radius: 6px;
            font-weight: bold;
            display: block;
        }

        .sidebar-btn:hover {
            background: #162b46;
        }

        .content-panel {
            background: #fff;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
        }

        .content-panel h1 {
            margin-top: 0;
            color: #1f3a5f;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 20px;
        }

        .card {
            background: #f8f9fa;
            padding: 22px;
            border-radius: 10px;
            border-left: 6px solid #1f3a5f;
        }

        .card h3 {
            margin: 0 0 8px 0;
            color: #1f3a5f;
        }

        .card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        @media (max-width: 900px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

            .cards {
                grid-template-columns: 1fr;
            }

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
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?>
            | Rol: <?php echo htmlspecialchars($nombreRol); ?>
        </span>

        <a href="logout.php" class="topbar-btn">Cerrar sesión</a>
    </div>
</div>

<div class="container">

    <?php if (isset($_GET['sin_permiso'])): ?>
        <div class="error">
            No tiene permiso para acceder a ese apartado.
        </div>
    <?php endif; ?>

    <div class="dashboard-layout">

        <div class="sidebar-panel">
            <h3>Menú principal</h3>

            <?php if ($rol == 1): ?>
                <!-- ADMINISTRADOR: acceso completo -->
                <a href="views/carreras/index.php" class="sidebar-btn">Gestionar Carreras</a>
                <a href="views/aulas/index.php" class="sidebar-btn">Gestionar Aulas</a>
                <a href="views/materias/index.php" class="sidebar-btn">Gestionar Materias</a>
                <a href="views/docentes/index.php" class="sidebar-btn">Gestionar Docentes</a>
                <a href="views/grupos/index.php" class="sidebar-btn">Gestionar Grupos</a>

                <a href="views/postulantes/index.php" class="sidebar-btn">Gestionar Postulantes</a>
                <a href="views/asignar_postulantes/index.php" class="sidebar-btn">Asignar Postulantes a Grupos</a>
                <a href="views/asignar_materias/index.php" class="sidebar-btn">Asignar Materias y Docentes</a>
                <a href="views/notas/index.php" class="sidebar-btn">Registrar Notas</a>

                <a href="views/reportes/index.php" class="sidebar-btn">Reportes</a>
                <a href="views/carga_masiva/index.php" class="sidebar-btn">Carga Masiva de Postulantes</a>

            <?php elseif ($rol == 2): ?>
                <!-- SECRETARIA -->
                <a href="views/postulantes/index.php" class="sidebar-btn">Gestionar Postulantes</a>
                <a href="views/carreras/index.php" class="sidebar-btn">Gestionar Carreras</a>
                <a href="views/aulas/index.php" class="sidebar-btn">Gestionar Aulas</a>
                <a href="views/asignar_postulantes/index.php" class="sidebar-btn">Asignar Postulantes a Grupos</a>
                <a href="views/reportes/index.php" class="sidebar-btn">Reportes</a>
                <a href="views/carga_masiva/index.php" class="sidebar-btn">Carga Masiva de Postulantes</a>

            <?php elseif ($rol == 3): ?>
                <!-- DOCENTE -->
                <a href="views/notas/index.php" class="sidebar-btn">Registrar Notas</a>
                <a href="views/reportes/index.php" class="sidebar-btn">Reportes</a>

            <?php else: ?>
                <p>No tiene un rol válido asignado.</p>
            <?php endif; ?>

        </div>

        <div class="content-panel">
            <h1>Panel Principal</h1>
            <p>Bienvenido al Sistema de Postulantes de la FITCCT.</p>

            <div class="cards">
                <div class="card">
                    <h3>Postulantes</h3>
                    <p><?php echo $totalPostulantes; ?></p>
                </div>

                <div class="card">
                    <h3>Carreras</h3>
                    <p><?php echo $totalCarreras; ?></p>
                </div>

                <div class="card">
                    <h3>Aulas</h3>
                    <p><?php echo $totalAulas; ?></p>
                </div>

                <div class="card">
                    <h3>Docentes</h3>
                    <p><?php echo $totalDocentes; ?></p>
                </div>

                <div class="card">
                    <h3>Grupos</h3>
                    <p><?php echo $totalGrupos; ?></p>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
