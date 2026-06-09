<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

require_once __DIR__ . "/../../config/database.php";

$id_postulante = $_GET['id'] ?? '';

if ($id_postulante == '') {
    header("Location: index.php?error=1");
    exit();
}

/* Datos temporales para que no se borren los campos cuando hay error */
$old = $_SESSION['old_postulante'] ?? [];

function old($campo, $default = '') {
    global $old;
    return htmlspecialchars($old[$campo] ?? $default);
}

try {
    /* Cargar datos del postulante */
    $sqlPostulante = "
        SELECT *
        FROM postulantes
        WHERE id_postulante = :id_postulante
    ";

    $stmtPostulante = $conexion->prepare($sqlPostulante);
    $stmtPostulante->bindParam(":id_postulante", $id_postulante);
    $stmtPostulante->execute();

    $postulante = $stmtPostulante->fetch(PDO::FETCH_ASSOC);

    if (!$postulante) {
        header("Location: index.php?error=1");
        exit();
    }

    /* Cargar carreras activas */
    $sqlCarreras = "
        SELECT id_carrera, nombre_carrera
        FROM carreras
        WHERE estado = 'Activo'
        ORDER BY nombre_carrera ASC
    ";
    $stmtCarreras = $conexion->query($sqlCarreras);
    $carreras = $stmtCarreras->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header("Location: index.php?error=1");
    exit();
}

/* Si el postulante antiguo no tiene los campos nuevos llenos, separar desde apellido */
$apellidoPaternoBD = $postulante['apellido_paterno'] ?? '';
$apellidoMaternoBD = $postulante['apellido_materno'] ?? '';

if ($apellidoPaternoBD == '' && isset($postulante['apellido'])) {
    $partesApellido = explode(" ", trim($postulante['apellido']), 2);
    $apellidoPaternoBD = $partesApellido[0] ?? '';
    $apellidoMaternoBD = $partesApellido[1] ?? '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Postulante</title>
    <link rel="stylesheet" href="../../assets/css/style.css">

<style>
    .postulante-page {
        max-width: 1100px;
        margin: 25px auto;
        padding: 0 20px;
    }

    .postulante-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 18px;
    }

    .postulante-header h1 {
        margin: 0;
    }

    .btn-volver {
        display: inline-block;
        padding: 9px 16px;
        border-radius: 6px;
        text-decoration: none;
        background: #1f3a5f;
        color: #fff;
        font-weight: bold;
        white-space: nowrap;
    }

    .btn-volver:hover {
        background: #162b46;
    }

    .form-container {
        background: #fff;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: bold;
        margin-bottom: 6px;
        color: #1f3a5f;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group small {
        margin-top: 4px;
        font-size: 12px;
        color: #666;
    }

    .full-row {
        grid-column: 1 / 3;
    }

    .form-actions {
        grid-column: 1 / 3;
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    .form-actions .btn {
        min-width: 190px;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        background: #1f3a5f;
        color: white;
        font-weight: bold;
        cursor: pointer;
    }

    .form-actions .btn:hover {
        background: #162b46;
    }

    .error {
        margin-bottom: 15px;
        padding: 12px;
        background: #f8d7da;
        color: #721c24;
        border-radius: 6px;
        border: 1px solid #f5c6cb;
    }

    .success {
        margin-bottom: 15px;
        padding: 12px;
        background: #d4edda;
        color: #155724;
        border-radius: 6px;
        border: 1px solid #c3e6cb;
    }

    @media (max-width: 768px) {
        .postulante-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .full-row,
        .form-actions {
            grid-column: 1;
        }
    }
</style>

</head>
<body>

<div class="postulante-page">

    <div class="postulante-header">
        <h1>Editar Postulante</h1>
        <a href="index.php" class="btn-volver">Volver atrás</a>
    </div>

    <!-- MENSAJES -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            Ocurrió un error.
            <?php if (isset($_GET['msg'])) echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['duplicado'])): ?>
        <div class="error">
            Ya existe otro postulante registrado con ese CI.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['carrera_igual'])): ?>
        <div class="error">
            Debe escoger una carrera diferente como segunda opción.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['contacto'])): ?>
        <div class="error">
            Debe registrar al menos un medio de contacto: correo o teléfono/celular.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['nombre_repetido'])): ?>
        <div class="error">
            Ya existe otro postulante con el mismo nombre completo, apellido paterno y apellido materno.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && !isset($_GET['error'])): ?>
        <div class="error">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="form-container">

        <form action="../../controllers/PostulanteController.php?action=update" method="POST">

            <input type="hidden" name="id_postulante" value="<?php echo htmlspecialchars($postulante['id_postulante']); ?>">

            <div class="form-grid">

                <div class="form-group">
                    <label>CI</label>
                    <input type="text" name="ci" value="<?php echo old('ci', $postulante['ci'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?php echo old('nombre', $postulante['nombre'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido paterno</label>
                    <input type="text" name="apellido_paterno" value="<?php echo old('apellido_paterno', $apellidoPaternoBD); ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido materno</label>
                    <input type="text" name="apellido_materno" value="<?php echo old('apellido_materno', $apellidoMaternoBD); ?>" required>
                </div>

                <div class="form-group">
                    <label>Teléfono / Celular</label>
                    <input type="text" name="telefono" value="<?php echo old('telefono', $postulante['telefono'] ?? ''); ?>">
                    <small>Opcional si registra correo. Obligatorio si no registra correo.</small>
                </div>

                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" name="correo" value="<?php echo old('correo', $postulante['correo'] ?? ''); ?>">
                    <small>Opcional si registra teléfono/celular.</small>
                </div>

                <div class="form-group">
                    <label>Sexo</label>
                    <select name="sexo">
                        <option value="">Seleccione sexo</option>

                        <?php $sexoSeleccionado = old('sexo', $postulante['sexo'] ?? ''); ?>

                        <option value="Masculino" <?php echo $sexoSeleccionado == 'Masculino' ? 'selected' : ''; ?>>
                            Masculino
                        </option>

                        <option value="Femenino" <?php echo $sexoSeleccionado == 'Femenino' ? 'selected' : ''; ?>>
                            Femenino
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" value="<?php echo old('ciudad', $postulante['ciudad'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?php echo old('direccion', $postulante['direccion'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Colegio de procedencia</label>
                    <input type="text" name="colegio_procedencia" value="<?php echo old('colegio_procedencia', $postulante['colegio_procedencia'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Título de bachiller</label>
                    <input type="text" name="titulo_bachiller" value="<?php echo old('titulo_bachiller', $postulante['titulo_bachiller'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Primera opción de carrera</label>
                    <select name="id_carrera" id="id_carrera" required>
                        <option value="">Seleccione una carrera</option>

                        <?php 
                            $carreraSeleccionada = old('id_carrera', $postulante['id_carrera'] ?? '');
                        ?>

                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>"
                                <?php echo $carreraSeleccionada == $carrera['id_carrera'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Segunda opción de carrera</label>
                    <select name="id_carrera_segunda_opcion" id="id_carrera_segunda_opcion">
                        <option value="">Sin segunda opción</option>

                        <?php 
                            $segundaCarreraSeleccionada = old('id_carrera_segunda_opcion', $postulante['id_carrera_segunda_opcion'] ?? '');
                        ?>

                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>"
                                <?php echo $segundaCarreraSeleccionada == $carrera['id_carrera'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($carrera['nombre_carrera']); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                    <small>Debe ser diferente a la primera opción.</small>
                </div>

                <div class="form-group full-row">
                    <label>Otros</label>
                    <textarea name="otros" rows="3"><?php echo old('otros', $postulante['otros'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Actualizar Postulante</button>
                </div>

            </div>

        </form>

    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const carrera1 = document.getElementById("id_carrera");
    const carrera2 = document.getElementById("id_carrera_segunda_opcion");

    function validarCarreras() {
        if (carrera1 && carrera2 && carrera2.value !== "" && carrera1.value === carrera2.value) {
            carrera2.setCustomValidity("Debe escoger una carrera diferente como segunda opción.");
        } else {
            carrera2.setCustomValidity("");
        }
    }

    if (carrera1 && carrera2) {
        carrera1.addEventListener("change", validarCarreras);
        carrera2.addEventListener("change", validarCarreras);
    }
});
</script>

</body>
</html>
