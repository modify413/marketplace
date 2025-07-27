<?php
session_start();

// Redirige si no hay sesión iniciada
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
</head>
<body class="bg-light">

<div class="container mt-3">
    <!-- Botón de logout en la esquina superior derecha -->
    <div class="d-flex justify-content-end">
        <a href="?logout" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <div class="text-center mt-5">
        <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_email']) ?></h1>
        <p>Estás en el panel principal.</p>
    </div>
</div>

</body>
</html>

