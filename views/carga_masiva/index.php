<?php
session_start();

require_once __DIR__ . "/../../config/validar_rol.php";
validarRol([1, 2]);

$resultado = $_SESSION['resultado_carga'] ?? null;

if (isset($_SESSION['resultado_carga'])) {
    unset($_SESSION['resultado_carga']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carga Masiva de Postulantes</title>
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


        .carga-page {
            max-width: 1050px;
            margin: 30px auto;
            padding: 0 25px;
        }

        .carga-header h1 {
            margin: 0;
            font-size: 30px;
        }

        .carga-header p {
            margin: 5px 0 20px 0;
            color: #333;
        }

        .form-card {
            background: #fff;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
            max-width: 750px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .btn-cargar {
            background: #1f3a5f;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-cargar:hover {
            background: #162b46;
        }

        .info-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
            margin-bottom: 25px;
        }

        .info-card code {
            background: #f1f1f1;
            padding: 3px 6px;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .resultado-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 9px rgba(0,0,0,0.10);
        }

        .errores-lista {
            margin-top: 10px;
        }

        .errores-lista li {
            margin-bottom: 6px;
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


<div class="carga-page">

    <div class="carga-header">
        <h1>Carga Masiva de Postulantes</h1>
        <p>Importe postulantes desde un archivo CSV.</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">No se pudo procesar el archivo. Verifique el formato.</div>
    <?php endif; ?>

    <div class="info-card">
        <h3>Formato del archivo CSV</h3>
        <p>El archivo debe estar separado por punto y coma <code>;</code> o coma <code>,</code>.</p>
        <p>La primera fila debe tener estos encabezados:</p>
        <code>ci;nombre;apellido_paterno;apellido_materno;telefono;correo;sexo;direccion;colegio_procedencia;ciudad;titulo_bachiller;id_carrera;id_carrera_segunda_opcion;otros</code>
        <p><strong>Reglas:</strong></p>
        <p>CI, nombre, apellido paterno, apellido materno e id_carrera son obligatorios. Debe existir al menos correo o teléfono. La segunda carrera debe ser diferente a la primera.</p>
    </div>

    <div class="form-card">
        <form action="../../controllers/CargaMasivaController.php?action=postulantes" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Archivo CSV</label>
                <input type="file" name="archivo_csv" accept=".csv,text/csv" required>
            </div>

            <button type="submit" class="btn-cargar">Cargar Postulantes</button>
        </form>
    </div>

    <?php if ($resultado): ?>
        <div class="resultado-card">
            <h3>Resultado de la carga</h3>

            <div class="success">
                Insertados correctamente: <?php echo htmlspecialchars($resultado['insertados']); ?>
            </div>

            <?php if (count($resultado['errores']) > 0): ?>
                <div class="error">
                    Registros con error: <?php echo count($resultado['errores']); ?>
                </div>

                <ul class="errores-lista">
                    <?php foreach ($resultado['errores'] as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
