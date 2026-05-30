<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../login.php");
    exit();
}

require_once __DIR__ . "/../../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM postulantes WHERE id_postulante = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();
$postulante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$postulante) {
    header("Location: index.php");
    exit();
}

$stmtCarreras = $conexion->query("SELECT id_carrera, nombre_carrera FROM carreras WHERE estado = 'Activo'");
$carreras = $stmtCarreras->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Postulante - FITCCT</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <div>
        <strong>Sistema FITCCT</strong>
    </div>
    <div>
        Usuario: <?php echo $_SESSION['nombre']; ?> |
        <a href="index.php">Volver</a>
        <a href="../../logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">
    <div class="form-container">
        <h1>Editar Postulante</h1>
        <p>Modifique los datos del alumno nuevo/postulante.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">No se pudo actualizar el postulante. Verifique los datos.</div>
        <?php endif; ?>

        <form action="../../controllers/PostulanteController.php?action=update" method="POST">
            <input type="hidden" name="id_postulante" value="<?php echo $postulante['id_postulante']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo $postulante['nombre']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo $postulante['apellido']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>CI</label>
                    <input type="text" name="ci" value="<?php echo $postulante['ci']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Sexo</label>
                    <select name="sexo" required>
                        <option value="">Seleccione sexo</option>
                        <option value="Masculino" <?php echo ($postulante['sexo'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Femenino" <?php echo ($postulante['sexo'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="<?php echo $postulante['fecha_nacimiento']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo $postulante['telefono']; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="correo" value="<?php echo $postulante['correo']; ?>" required>
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?php echo $postulante['direccion']; ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Colegio de procedencia</label>
                    <input type="text" name="colegio_procedencia" value="<?php echo $postulante['colegio_procedencia']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" value="<?php echo $postulante['ciudad']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Carrera a la que postula</label>
                    <select name="id_carrera" required>
                        <option value="">Seleccione una carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>"
                                <?php echo ($carrera['id_carrera'] == $postulante['id_carrera']) ? 'selected' : ''; ?>>
                                <?php echo $carrera['nombre_carrera']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Segunda opción de carrera</label>
                    <select name="id_carrera_segunda_opcion">
                        <option value="">Seleccione una carrera</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>"
                                <?php echo ($carrera['id_carrera'] == $postulante['id_carrera_segunda_opcion']) ? 'selected' : ''; ?>>
                                <?php echo $carrera['nombre_carrera']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Título de bachiller</label>
                <select name="titulo_bachiller" required>
                    <option value="">Seleccione una opción</option>
                    <option value="Si" <?php echo ($postulante['titulo_bachiller'] == 'Si') ? 'selected' : ''; ?>>Sí</option>
                    <option value="No" <?php echo ($postulante['titulo_bachiller'] == 'No') ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="form-group">
                <label>Otros requisitos u observaciones</label>
                <textarea name="otros" rows="4"><?php echo $postulante['otros']; ?></textarea>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="estado" required>
                    <option value="Postulante" <?php echo ($postulante['estado'] == 'Postulante') ? 'selected' : ''; ?>>Postulante</option>
                    <option value="Admitido" <?php echo ($postulante['estado'] == 'Admitido') ? 'selected' : ''; ?>>Admitido</option>
                    <option value="Rechazado" <?php echo ($postulante['estado'] == 'Rechazado') ? 'selected' : ''; ?>>Rechazado</option>
                </select>
            </div>

            <button type="submit" class="btn">Actualizar Postulante</button>
        </form>
    </div>
</div>

</body>
</html>