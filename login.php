<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_GET['error'])) {
    $error = "Usuario o contraseña incorrectos";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema FITCCT</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1>FITCCT</h1>
        <p>Sistema de Administración de Postulantes</p>

        <?php if ($error != ""): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="controllers/LoginController.php" method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="contrasena" required>
            </div>

            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
    </div>
</div>

</body>
</html>