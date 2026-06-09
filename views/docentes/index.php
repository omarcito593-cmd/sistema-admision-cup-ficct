<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../../config/database.php";

try {
    $sql = "
        SELECT 
            id_docente,
            ci,
            nombre,
            apellido,
            telefono,
            correo,
            profesion,
            maestria,
            diplomado,
            estado
        FROM docentes
        ORDER BY id_docente ASC
    ";

    $stmt = $conexion->query($sql);
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $docentes = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Docentes - FITCCT</title>
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

        .topbar-separador {
            color: #fff;
            font-weight: bold;
        }

        .page {
            max-width: 1280px;
            margin: 30px auto;
            padding: 0 25px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 18px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .btn-nuevo {
            background: #1f3a5f;
            color: #fff !important;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
            font-weight: bold;
        }

        .success,
        .error {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            min-width: 1150px;
            border-collapse: collapse;
        }

        th {
            background: #1f3a5f;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        .acciones {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .acciones form {
            margin: 0;
        }

        .btn-small {
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            color: #fff !important;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
        }

        .edit {
            background: #2f6f95;
        }

        .delete {
            background: #dc3545;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-title">Sistema FITCCT</div>

    <div class="topbar-right">
        <span>
            Usuario: <?php echo htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']); ?>
        </span>

        <span class="topbar-separador">|</span>

        <a href="../../dashboard.php" class="topbar-btn">Panel</a>

        <span class="topbar-separador">|</span>

        <a href="../../logout.php" class="topbar-btn">Cerrar sesión</a>
    </div>
</div>

<div class="page">

    <div class="page-header">
        <div>
            <h1>Gestión de Docentes</h1>
            <p>Administración de docentes para materias y grupos.</p>
        </div>

        <a href="create.php" class="btn-nuevo">Nuevo Docente</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Docente registrado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Docente actualizado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Docente eliminado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['duplicado'])): ?>
        <div class="error">Ya existe un docente registrado con ese mismo CI.</div>
    <?php endif; ?>

    <?php if (isset($_GET['used'])): ?>
        <div class="error">
            <?php 
                echo isset($_GET['msg'])
                    ? htmlspecialchars($_GET['msg'])
                    : "No se puede eliminar el docente porque está siendo usado.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error al procesar la operación.</div>
    <?php endif; ?>

    <div class="tabla-card">

        <div class="table-scroll">

            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Nombre completo</th>
                        <th>CI</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Profesión</th>
                        <th>Maestría</th>
                        <th>Diplomado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($docentes) > 0): ?>
                        <?php $numero = 1; ?>
                        <?php foreach ($docentes as $docente): ?>
                            <tr>
                                <td><?php echo $numero++; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($docente['nombre'] . " " . $docente['apellido']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($docente['ci']); ?></td>
                                <td><?php echo htmlspecialchars($docente['telefono'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($docente['correo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($docente['profesion'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($docente['maestria'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($docente['diplomado'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($docente['estado'] ?? ''); ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="edit.php?id=<?php echo $docente['id_docente']; ?>" class="btn-small edit">Editar</a>

                                        <form action="../../controllers/DocenteController.php?action=delete" method="POST"
                                              onsubmit="return confirm('¿Está seguro de eliminar este docente?');">
                                            <input type="hidden" name="id_docente" value="<?php echo $docente['id_docente']; ?>">
                                            <button type="submit" class="btn-small delete">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No hay docentes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>
