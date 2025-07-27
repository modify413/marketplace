<?php
session_start();

// Si ya está logueado, redirige al dashboard o panel
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marketplace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f4f4f4;
        }
        .landing-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="landing-container">
    <div class="card text-center">
        <h1 class="mb-4">Bienvenido al Marketplace</h1>
        <p class="mb-4">Compra y vende productos fácilmente</p>
        <div class="d-grid gap-2">
            <a href="login.php" class="btn btn-primary btn-lg">Iniciar sesión</a>
            <a href="register.php" class="btn btn-outline-primary btn-lg">Registrarse</a>
        </div>
    </div>
</div>

</body>
</html>
