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
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
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
        <!-- Botón para abrir modal -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#publicacionModal">Crear publicación</button>
    </div>
</div>

<!-- Modal: Crear publicación -->
<div class="modal fade" id="publicacionModal" tabindex="-1" aria-labelledby="publicacionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="crear_publicacion.php" enctype="multipart/form-data" id="formPublicacion">
      <div class="modal-header">
        <h5 class="modal-title" id="publicacionModalLabel">Nueva publicación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
            <label for="titulo" class="form-label">Título</label>
            <input type="text" name="titulo" id="titulo" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" name="precio" id="precio" class="form-control" required step="0.01" min="0">
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="telefono" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Imágenes (hasta 5)</label>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <input type="file" name="imagen<?= $i ?>" class="form-control mb-2" accept="image/*">
            <?php endfor; ?>
        </div>

      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Publicar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<!-- Validación JS opcional -->
<script>
document.getElementById('formPublicacion').addEventListener('submit', function(e) {
    const precio = parseFloat(document.getElementById('precio').value);
    if (precio < 0) {
        alert("El precio no puede ser negativo.");
        e.preventDefault();
    }
});
</script>

</body>
</html>
