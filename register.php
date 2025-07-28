<?php
session_start();
$errores = "";
$exito = "";

// Conexión a la base de datos
$conexion = new mysqli('sql309.infinityfree.com', 'if0_39580339', 'Worldof2000', 'if0_39580339_marketplace');

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['email']);
    $contrasena = $_POST['password'];
    $telefono = trim($_POST['telefono']);

    // Validaciones del lado del servidor
    if (empty($correo) || empty($contrasena) || empty($telefono)) {
        $errores = "Por favor, completá todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores = "El correo electrónico no es válido.";
    } elseif (strlen($contrasena) < 8 ||
              !preg_match('/[A-Z]/', $contrasena) ||
              !preg_match('/[0-9]/', $contrasena) ||
              !preg_match('/[\W]/', $contrasena)) {
        $errores = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errores = "Ese correo ya está registrado.";
        } else {
            // Insertar nuevo usuario
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO usuarios (email, password, telefono) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $correo, $hash, $telefono);
            if ($stmt->execute()) {
                $exito = "Registro exitoso. Ya podés iniciar sesión.";
            } else {
                $errores = "Error al registrar: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4">
                <h2 class="text-center mb-4">Crear cuenta</h2>

                <?php if ($errores): ?>
                    <div class="alert alert-danger"><?= $errores ?></div>
                <?php endif; ?>

                <?php if ($exito): ?>
                    <div class="alert alert-success"><?= $exito ?></div>
                    <div class="text-center"><a href="login.php" class="btn btn-success">Iniciar sesión</a></div>
                <?php else: ?>
                    <form method="post" action="" id="formRegistro" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control" id="password" required>
                            <small id="ayudaContrasena" class="form-text"></small>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" id="telefono" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                        </div>
                    </form>
                <?php endif; ?>

                <p class="mt-3 text-center">¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Validación JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputContrasena = document.getElementById('password');
    const textoAyuda = document.getElementById('ayudaContrasena');
    const form = document.getElementById('formRegistro');

    function validarContrasena(contrasena) {
        const errores = [];

        if (contrasena.length < 8) errores.push("al menos 8 caracteres");
        if (!/[A-Z]/.test(contrasena)) errores.push("una letra mayúscula");
        if (!/[0-9]/.test(contrasena)) errores.push("un número");
        if (!/[\W_]/.test(contrasena)) errores.push("un símbolo");

        return errores;
    }

    inputContrasena.addEventListener('input', () => {
        const errores = validarContrasena(inputContrasena.value);
        if (errores.length > 0) {
            textoAyuda.innerText = "La contraseña debe tener: " + errores.join(", ");
            textoAyuda.className = "form-text text-danger";
        } else {
            textoAyuda.innerText = "Contraseña segura.";
            textoAyuda.className = "form-text text-success";
        }
    });

    form.addEventListener('submit', (e) => {
        const errores = validarContrasena(inputContrasena.value);
        if (errores.length > 0) {
            e.preventDefault();
            textoAyuda.innerText = "La contraseña debe tener: " + errores.join(", ");
            textoAyuda.className = "form-text text-danger";
        }
    });
});
</script>

</body>
</html>
