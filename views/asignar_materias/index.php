<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1]);

require_once __DIR__ . "/../../config/database.php";

try {
    /* Grupos activos */
    $sqlGrupos = "
        SELECT 
            g.id_grupo,
            g.nombre_grupo,
            g.turno,
            g.cupo_maximo,
            a.nombre_aula
        FROM grupos g
        INNER JOIN aulas a ON g.id_aula = a.id_aula
        WHERE g.estado = 'Activo'
        ORDER BY g.id_grupo ASC
    ";
    $stmtGrupos = $conexion->query($sqlGrupos);
    $grupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

    /* Materias activas */
    $sqlMaterias = "
        SELECT 
            id_materia,
            nombre_materia
        FROM materias
        WHERE estado = 'Activo'
        ORDER BY id_materia ASC
    ";
    $stmtMaterias = $conexion->query($sqlMaterias);
    $materias = $stmtMaterias->fetchAll(PDO::FETCH_ASSOC);

    /* Docentes activos */
    $sqlDocentes = "
        SELECT 
            id_docente,
            nombre,
            apellido,
            profesion
        FROM docentes
        WHERE estado = 'Activo'
        ORDER BY id_docente ASC
    ";
    $stmtDocentes = $conexion->query($sqlDocentes);
    $docentes = $stmtDocentes->fetchAll(PDO::FETCH_ASSOC);

    /* Asignaciones registradas */
    $sqlAsignaciones = "
        SELECT 
            a.id_asignacion,
            a.id_grupo,
            a.id_materia,
            a.id_docente,
            a.horario,
            g.nombre_grupo,
            g.turno,
            au.nombre_aula,
            m.nombre_materia,
            d.nombre,
            d.apellido
        FROM asignaciones a
        INNER JOIN grupos g ON a.id_grupo = g.id_grupo
        INNER JOIN aulas au ON g.id_aula = au.id_aula
        INNER JOIN materias m ON a.id_materia = m.id_materia
        INNER JOIN docentes d ON a.id_docente = d.id_docente
        ORDER BY a.id_asignacion ASC
    ";
    $stmtAsignaciones = $conexion->query($sqlAsignaciones);
    $asignaciones = $stmtAsignaciones->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $grupos = [];
    $materias = [];
    $docentes = [];
    $asignaciones = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Materias y Docentes</title>
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

        .topbar-btn:hover {
            background: #c8233b;
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
            margin-bottom: 15px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .page-header p {
            margin: 4px 0 0 0;
            color: #333;
        }

        .form-card {
            max-width: 800px;
            margin: 0 auto 28px auto;
            background: #fff;
            padding: 24px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
        }

        .form-card h2 {
            margin: 0 0 14px 0;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 9px;
            border: 1px solid #cfcfcf;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .btn-guardar {
            width: 100%;
            padding: 10px;
            background: #1f3a5f;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-guardar:hover {
            background: #162b46;
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

        .tabla-asignaciones {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
        }

        .tabla-asignaciones th {
            background: #1f3a5f;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
        }

        .tabla-asignaciones td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .tabla-asignaciones tr:nth-child(even) {
            background: #f2f2f2;
        }

        .tabla-asignaciones select,
        .tabla-asignaciones input {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
            font-size: 13px;
        }

        .acciones-asignacion {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .acciones-asignacion form {
            margin: 0;
        }

        .btn-small {
            border: none;
            border-radius: 5px;
            padding: 8px 11px;
            color: #fff !important;
            text-decoration: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
            white-space: nowrap;
        }

        .edit {
            background: #2f6f95;
        }

        .delete {
            background: #dc3545;
        }

        .edit:hover {
            background: #245a79;
        }

        .delete:hover {
            background: #b02a37;
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

        <a href="../../dashboard.php" class="topbar-btn">Panel principal</a>

        <span class="topbar-separador">|</span>

        <a href="../../logout.php" class="topbar-btn">Cerrar sesión</a>
    </div>
</div>

<div class="page">

    <div class="page-header">
        <h1>Asignar Materias y Docentes</h1>
        <p>Seleccione un grupo, una materia, un docente y registre el horario correspondiente.</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">Asignación registrada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="success">Asignación actualizada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="success">Asignación eliminada correctamente.</div>
    <?php endif; ?>

    <?php if (isset($_GET['duplicado'])): ?>
        <div class="error">Esa materia ya está asignada a ese grupo.</div>
    <?php endif; ?>

    <?php if (isset($_GET['used'])): ?>
        <div class="error">
            <?php 
                echo isset($_GET['msg'])
                    ? htmlspecialchars($_GET['msg'])
                    : "No se puede eliminar la asignación porque está siendo usada.";
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">Ocurrió un error al procesar la operación.</div>
    <?php endif; ?>

    <div class="form-card">
        <h2>Nueva asignación</h2>

        <form action="../../controllers/AsignarMateriaController.php?action=store" method="POST">

            <div class="form-group">
                <label>Grupo</label>
                <select name="id_grupo" required>
                    <option value="">Seleccione un grupo</option>

                    <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id_grupo']; ?>">
                            <?php echo htmlspecialchars($grupo['nombre_grupo'] . " - " . $grupo['turno'] . " - " . $grupo['nombre_aula']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Materia</label>
                <select name="id_materia" required>
                    <option value="">Seleccione una materia</option>

                    <?php foreach ($materias as $materia): ?>
                        <option value="<?php echo $materia['id_materia']; ?>">
                            <?php echo htmlspecialchars($materia['nombre_materia']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Docente</label>
                <select name="id_docente" required>
                    <option value="">Seleccione un docente</option>

                    <?php foreach ($docentes as $docente): ?>
                        <option value="<?php echo $docente['id_docente']; ?>">
                            <?php 
                                echo htmlspecialchars(
                                    $docente['nombre'] . " " . $docente['apellido'] .
                                    ($docente['profesion'] ? " - " . $docente['profesion'] : "")
                                ); 
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Horario</label>
                <input type="text" name="horario" placeholder="Ej: Lunes 08:00 - 10:00" required>
            </div>

            <button type="submit" class="btn-guardar">Guardar Asignación</button>

        </form>
    </div>

    <div class="tabla-card">
        <h2>Asignaciones registradas</h2>

        <div class="table-scroll">

            <table class="tabla-asignaciones">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Horario</th>
                        <th>Actualizar / Eliminar</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (count($asignaciones) > 0): ?>
                        <?php $numero = 1; ?>

                        <?php foreach ($asignaciones as $asignacion): ?>
                            <?php $formId = "form_actualizar_" . $asignacion['id_asignacion']; ?>

                            <tr>
                                <td><?php echo $numero++; ?></td>

                                <td>
                                    <form id="<?php echo $formId; ?>" action="../../controllers/AsignarMateriaController.php?action=update" method="POST"></form>

                                    <input type="hidden" name="id_asignacion"
                                           value="<?php echo $asignacion['id_asignacion']; ?>"
                                           form="<?php echo $formId; ?>">

                                    <select name="id_grupo" required form="<?php echo $formId; ?>">
                                        <?php foreach ($grupos as $grupo): ?>
                                            <option value="<?php echo $grupo['id_grupo']; ?>"
                                                <?php echo ($grupo['id_grupo'] == $asignacion['id_grupo']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($grupo['nombre_grupo'] . " - " . $grupo['turno'] . " - " . $grupo['nombre_aula']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <select name="id_materia" required form="<?php echo $formId; ?>">
                                        <?php foreach ($materias as $materia): ?>
                                            <option value="<?php echo $materia['id_materia']; ?>"
                                                <?php echo ($materia['id_materia'] == $asignacion['id_materia']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($materia['nombre_materia']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <select name="id_docente" required form="<?php echo $formId; ?>">
                                        <?php foreach ($docentes as $docente): ?>
                                            <option value="<?php echo $docente['id_docente']; ?>"
                                                <?php echo ($docente['id_docente'] == $asignacion['id_docente']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($docente['nombre'] . " " . $docente['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <td>
                                    <input type="text" name="horario"
                                           value="<?php echo htmlspecialchars($asignacion['horario']); ?>"
                                           required form="<?php echo $formId; ?>">
                                </td>

                                <td>
                                    <div class="acciones-asignacion">

                                        <button type="submit" class="btn-small edit" form="<?php echo $formId; ?>">
                                            Actualizar
                                        </button>

                                        <form action="../../controllers/AsignarMateriaController.php?action=delete" method="POST"
                                              onsubmit="return confirm('¿Está seguro de eliminar esta asignación? Solo se eliminará la asignación, no la materia.');">

                                            <input type="hidden" name="id_asignacion"
                                                   value="<?php echo $asignacion['id_asignacion']; ?>">

                                            <button type="submit" class="btn-small delete">
                                                Eliminar
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">No hay asignaciones registradas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

</div>

</body>
</html>
