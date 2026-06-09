<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 3]);

require_once __DIR__ . "/../../config/database.php";

/* 
   CARGAR DATOS*/
try {

    /* Postulantes */
    $sqlPostulantes = "
        SELECT 
            id_postulante,
            ci,
            nombre,
            apellido,
            apellido_paterno,
            apellido_materno
        FROM postulantes
        ORDER BY id_postulante ASC
    ";
    $stmtPostulantes = $conexion->query($sqlPostulantes);
    $postulantes = $stmtPostulantes->fetchAll(PDO::FETCH_ASSOC);

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

    /* Notas registradas */
    $sqlNotas = "
        SELECT 
            n.id_nota,
            n.examen1,
            n.examen2,
            n.examen3,
            n.promedio_final,
            n.resultado,
            p.ci,
            p.nombre,
            p.apellido,
            p.apellido_paterno,
            p.apellido_materno,
            m.nombre_materia
        FROM notas n
        INNER JOIN postulantes p ON n.id_postulante = p.id_postulante
        INNER JOIN materias m ON n.id_materia = m.id_materia
        ORDER BY n.id_nota ASC
    ";
    $stmtNotas = $conexion->query($sqlNotas);
    $notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $postulantes = [];
    $materias = [];
    $notas = [];
}

/* Función para mostrar el nombre completo */
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
    <title>Registrar Notas</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

    <style>
        .notas-page {
            max-width: 1280px;
            margin: 25px auto;
            padding: 0 25px;
        }

        .notas-header {
            margin-bottom: 15px;
        }

        .notas-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .notas-header p {
            margin: 4px 0 0 0;
            color: #333;
        }

        .form-nota-card {
            max-width: 850px;
            margin: 0 auto 28px auto;
            background: #fff;
            padding: 22px 26px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
        }

        .form-nota-card h2 {
            margin: 0 0 16px 0;
            color: #000;
        }

        .nota-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px 24px;
        }

        .bloque-nota {
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            padding: 16px;
            background: #fafafa;
        }

        .bloque-nota h3 {
            margin: 0 0 12px 0;
            font-size: 18px;
            color: #1f3a5f;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 8px 9px;
            border: 1px solid #cfcfcf;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .btn-registrar-nota {
            width: 100%;
            margin-top: 14px;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #1f3a5f;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-registrar-nota:hover {
            background: #162b46;
        }

        .mensaje-info {
            max-width: 850px;
            margin: 0 auto 14px auto;
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

        .tabla-card h2 {
            margin-top: 0;
        }

        .table-scroll {
            overflow-x: auto;
        }

        .tabla-notas {
            width: 100%;
            min-width: 1250px;
            border-collapse: collapse;
        }

        .tabla-notas th {
            background: #1f3a5f;
            color: #fff;
            padding: 12px 10px;
            text-align: left;
        }

        .tabla-notas td {
            padding: 11px 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .tabla-notas tr:nth-child(even) {
            background: #f2f2f2;
        }

        .tabla-notas input {
            width: 80px;
            padding: 5px;
        }

        .resultado-aprobado {
            color: green;
            font-weight: bold;
        }

        .resultado-reprobado {
            color: red;
            font-weight: bold;
        }

        .acciones-nota {
            display: flex;
            gap: 7px;
            align-items: center;
        }

        .acciones-nota form {
            margin: 0;
        }

        .btn-actualizar {
            padding: 7px 12px;
            border: none;
            border-radius: 5px;
            background: #2f6f95;
            color: #fff;
            cursor: pointer;
        }

        .btn-actualizar:hover {
            background: #245a79;
        }

        .btn-eliminar {
            padding: 7px 12px;
            border: none;
            border-radius: 5px;
            background: #dc3545;
            color: #fff;
            cursor: pointer;
        }

        .btn-eliminar:hover {
            background: #b02a37;
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
        @media (max-width: 850px) {
            .nota-grid {
                grid-template-columns: 1fr;
            }

            .form-nota-card {
                max-width: 100%;
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

<div class="notas-page">

    <div class="notas-header">
        <h1>Registrar Notas</h1>
        <p>Registre o corrija las notas de los tres exámenes del postulante.</p>
    </div>

    <div class="mensaje-info">

        <?php if (isset($_GET['success'])): ?>
            <div class="success">Nota registrada correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="success">Nota actualizada correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="success">Nota eliminada correctamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['duplicado'])): ?>
            <div class="error">Este postulante ya tiene una nota registrada para esa materia.</div>
        <?php endif; ?>

        <?php if (isset($_GET['rango'])): ?>
            <div class="error">Las notas deben estar entre 0 y 100.</div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">Ocurrió un error al registrar, actualizar o eliminar la nota.</div>
        <?php endif; ?>

    </div>

    <div class="form-nota-card">

        <h2>Nueva nota</h2>

        <form action="../../controllers/NotaController.php?action=store" method="POST">

            <div class="nota-grid">

                <div class="bloque-nota">
                    <h3>Datos del postulante</h3>

                    <div class="form-group">
                        <label>Postulante</label>
                        <select name="id_postulante" required>
                            <option value="">Seleccione un postulante</option>

                            <?php foreach ($postulantes as $postulante): ?>
                                <option value="<?php echo $postulante['id_postulante']; ?>">
                                    <?php 
                                        echo htmlspecialchars(
                                            $postulante['ci'] . " - " . nombreCompleto($postulante)
                                        ); 
                                    ?>
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
                </div>

                <div class="bloque-nota">
                    <h3>Notas de exámenes</h3>

                    <div class="form-group">
                        <label>Examen 1</label>
                        <input type="number" name="examen1" min="0" max="100" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Examen 2</label>
                        <input type="number" name="examen2" min="0" max="100" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Examen 3</label>
                        <input type="number" name="examen3" min="0" max="100" step="0.01" required>
                    </div>
                </div>

            </div>

            <button type="submit" class="btn-registrar-nota">Registrar Nota</button>

        </form>

    </div>

    <div class="tabla-card">

        <h2>Notas registradas</h2>

        <div class="table-scroll">

            <table class="tabla-notas">

                <thead>
                    <tr>
                        <th>N°</th>
                        <th>CI</th>
                        <th>Postulante</th>
                        <th>Materia</th>
                        <th>Examen 1</th>
                        <th>Examen 2</th>
                        <th>Examen 3</th>
                        <th>Promedio</th>
                        <th>Resultado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($notas) > 0): ?>

                        <?php $numero = 1; ?>
                        <?php foreach ($notas as $nota): ?>

                            <?php $formId = "form_nota_" . $nota['id_nota']; ?>

                            <tr>
                                <td><?php echo $numero++; ?></td>
                                <td><?php echo htmlspecialchars($nota['ci']); ?></td>
                                <td><?php echo htmlspecialchars(nombreCompleto($nota)); ?></td>
                                <td><?php echo htmlspecialchars($nota['nombre_materia']); ?></td>

                                <form id="<?php echo $formId; ?>" action="../../controllers/NotaController.php?action=update" method="POST"></form>

                                <input type="hidden" name="id_nota" value="<?php echo $nota['id_nota']; ?>" form="<?php echo $formId; ?>">

                                <td>
                                    <input type="number" name="examen1" min="0" max="100" step="0.01"
                                           value="<?php echo htmlspecialchars($nota['examen1']); ?>"
                                           form="<?php echo $formId; ?>" required>
                                </td>

                                <td>
                                    <input type="number" name="examen2" min="0" max="100" step="0.01"
                                           value="<?php echo htmlspecialchars($nota['examen2']); ?>"
                                           form="<?php echo $formId; ?>" required>
                                </td>

                                <td>
                                    <input type="number" name="examen3" min="0" max="100" step="0.01"
                                           value="<?php echo htmlspecialchars($nota['examen3']); ?>"
                                           form="<?php echo $formId; ?>" required>
                                </td>

                                <td><?php echo number_format($nota['promedio_final'], 2); ?></td>

                                <td>
                                    <?php if ($nota['resultado'] == 'APROBADO'): ?>
                                        <span class="resultado-aprobado">APROBADO</span>
                                    <?php else: ?>
                                        <span class="resultado-reprobado">REPROBADO</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="acciones-nota">

                                        <button type="submit" class="btn-actualizar" form="<?php echo $formId; ?>">
                                            Actualizar
                                        </button>

                                        <form action="../../controllers/NotaController.php?action=delete" method="POST"
                                              onsubmit="return confirm('¿Está seguro de eliminar esta nota? Solo se eliminará la nota, no el postulante.');">

                                            <input type="hidden" name="id_nota" value="<?php echo $nota['id_nota']; ?>">

                                            <button type="submit" class="btn-eliminar">
                                                Eliminar
                                            </button>

                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" style="text-align:center;">No hay notas registradas.</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>
