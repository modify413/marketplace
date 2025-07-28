<?php
session_start();
$errores = "";

// Conexión a la base de datos
$conexion = new mysqli('sql309.infinityfree.com', 'if0_39580339', 'Worldof2000', 'if0_39580339_marketplace');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Si ya está logueado, redirige al panel (dashboard.php)
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email']);
    $contrasena = $_POST['password'];

    if (empty($correo) || empty($contrasena)) {
        $errores = "Por favor, completá todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores = "El correo electrónico no es válido.";
    } else {
        // Buscar usuario por correo
        $stmt = $conexion->prepare("SELECT id, password, es_admin FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            // Verificar contraseña
            if (password_verify($contrasena, $usuario['password'])) {
                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_email'] = $correo;
                $_SESSION['es_admin'] = $usuario['es_admin'];

                // Redirigir a dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $errores = "Contraseña incorrecta.";
            }
        } else {
            $errores = "No existe una cuenta con ese correo.";
        }

        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow p-4">
                <h2 class="text-center mb-4">Iniciar sesión</h2>

                <?php if ($errores): ?>
                    <div class="alert alert-danger"><?= $errores ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" id="email" required />
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" id="password" required />
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    ¿No tenés cuenta? <a href="register.php">Registrate aquí</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
