<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once "config/database.php";

$totalPostulantes = $conexion->query("SELECT COUNT(*) FROM postulantes")->fetchColumn();
$totalCarreras = $conexion->query("SELECT COUNT(*) FROM carreras")->fetchColumn();
$totalAulas = $conexion->query("SELECT COUNT(*) FROM aulas")->fetchColumn();
$totalDocentes = $conexion->query("SELECT COUNT(*) FROM docentes")->fetchColumn();
$totalGrupos = $conexion->query("SELECT COUNT(*) FROM grupos")->fetchColumn();

$capacidadPorGrupo = 70;

if ($totalPostulantes > 0) {
    $gruposHabilitados = ceil($totalPostulantes / $capacidadPorGrupo);
} else {
    $gruposHabilitados = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - FITCCT</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div>
        <strong>Sistema FITCCT</strong>
    </div>
    <div>
        Usuario: <?php echo $_SESSION['nombre']; ?> |
        Rol: <?php echo $_SESSION['rol']; ?>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">
    <div class="dashboard-layout">

        <div class="sidebar-panel">
            <h3>Opciones del Sistema</h3>

            <a href="views/postulantes/index.php" class="sidebar-btn">Gestionar Postulantes</a>
            <a href="views/carreras/index.php" class="sidebar-btn">Gestionar Carreras</a>
            <a href="views/aulas/index.php" class="sidebar-btn">Gestionar Aulas</a>
            <a href="views/materias/index.php" class="sidebar-btn">Gestionar Materias</a>
            <a href="views/docentes/index.php" class="sidebar-btn">Gestionar Docentes</a>
            <a href="views/grupos/index.php" class="sidebar-btn">Gestionar Grupos</a>
        </div>

        <div class="content-panel">
            
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

                <div class="card">
                    <h3>Grupos Habilitados</h3>
                    <p><?php echo $gruposHabilitados; ?></p>
                </div>
            </div>

            <div class="info-box">
                <strong>Regla de cálculo:</strong>
                El sistema calcula automáticamente los grupos habilitados considerando un máximo de
                <?php echo $capacidadPorGrupo; ?> estudiantes por grupo.
            </div>
        </div>

    </div>
</div>
</body>
</html>