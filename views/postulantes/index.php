<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

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
            p.sexo,
            p.ciudad,
            c1.nombre_carrera AS carrera_principal,
            c2.nombre_carrera AS carrera_segunda_opcion
        FROM postulantes p
        INNER JOIN carreras c1 ON p.id_carrera = c1.id_carrera
        LEFT JOIN carreras c2 ON p.id_carrera_segunda_opcion = c2.id_carrera
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
    <title>Gestionar Postulantes</title>
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
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: bold;
        }

        .success,
        .error {
            padding: 11px 13px;
            border-radius: 6px;
            margin-bottom: 12px;
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

        .tabla-postulantes {
            width: 100%;
            min-width: 1180px;
            border-collapse: collapse;
        }

        .tabla-postulantes th {
            background: #1f3a5f;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
        }

        .tabla-postulantes td {
            padding: 11px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .tabla-postulantes tr:nth-child(even) {
            background: #f2f2f2;
        }

        .acciones {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .acciones form {
            margin: 0;
        }

        .btn-small {
            display: inline-block;
            padding: 7px 11px;
            border-radius: 5px;
            border: none;
            color: #fff !important;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }

        .edit {
            background: #2f6f95;
        }

        .delete {
            background: #dc3545;
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

            .page-header {
                flex-direction: column;
                align-items: flex-start;
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


<div class="page">

    <div class="page-header">
        <div>
            <h1>Gestionar Postulantes</h1>
            <p>Administre los postulantes registrados en el sistema.</p>
        </div>

        <a href="create.php" class="btn-nuevo">Nuevo Postulante</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Postulante registrado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Postulante actualizado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Postulante eliminado correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['used'])): ?>
        <div class="error">
            No se puede eliminar el postulante.
            <?php if (isset($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error al procesar la operación.</div>
    <?php endif; ?>

    <div class="tabla-card">

        <div class="table-scroll">

            <table class="tabla-postulantes">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>CI</th>
                        <th>Postulante</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Sexo</th>
                        <th>Ciudad</th>
                        <th>Carrera principal</th>
                        <th>Segunda opción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($postulantes) > 0): ?>
                        <?php $numero = 1; ?>
                        <?php foreach ($postulantes as $postulante): ?>
                            <tr>
                                <td><?php echo $numero++; ?></td>
                                <td><?php echo htmlspecialchars($postulante['ci']); ?></td>
                                <td><?php echo htmlspecialchars(nombreCompleto($postulante)); ?></td>
                                <td><?php echo htmlspecialchars($postulante['telefono'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($postulante['correo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($postulante['sexo'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($postulante['ciudad'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($postulante['carrera_principal']); ?></td>
                                <td><?php echo htmlspecialchars($postulante['carrera_segunda_opcion'] ?? 'Sin segunda opción'); ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="edit.php?id=<?php echo $postulante['id_postulante']; ?>" class="btn-small edit">Editar</a>

                                        <form action="../../controllers/PostulanteController.php?action=delete" method="POST"
                                              onsubmit="return confirm('¿Está seguro de eliminar este postulante?');">
                                            <input type="hidden" name="id_postulante" value="<?php echo $postulante['id_postulante']; ?>">
                                            <button type="submit" class="btn-small delete">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align:center;">No hay postulantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>
