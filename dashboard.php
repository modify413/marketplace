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

// Conexión a base de datos
$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener publicaciones
$stmt = $conn->prepare("SELECT p.*, GROUP_CONCAT(f.ruta_imagen) AS imagenes FROM publicaciones p LEFT JOIN imagenes f ON p.id = f.publicacion_id GROUP BY p.id ORDER BY p.id DESC");
$stmt->execute();
$resultado = $stmt->get_result();
$publicaciones = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        .card-img-top {
            object-fit: cover;
            height: 200px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-3">
    <div class="d-flex justify-content-end">
        <a href="?logout" class="btn btn-danger">Cerrar sesión</a>
    </div>

    <div class="text-center mt-5">
        <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario_email']) ?></h1>
        <p>Estás en el panel principal.</p>
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#publicacionModal">Crear publicación</button>
    </div>

    <div class="row mt-5">
        <?php foreach ($publicaciones as $pub): 
            $imagenes = $pub['imagenes'] ? explode(',', $pub['imagenes']) : [];
            $imagen_principal = $imagenes[0] ?? 'https://via.placeholder.com/300x200?text=Sin+imagen';
        ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100" onclick='mostrarDetalle(<?= json_encode($pub, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>, <?= json_encode($imagenes) ?>)'>
                    <img src="<?= htmlspecialchars($imagen_principal) ?>" class="card-img-top" alt="Imagen">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($pub['titulo']) ?></h5>
                        <p class="card-text">$<?= number_format($pub['precio'], 2) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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

<!-- Modal: Detalle publicación -->
<div class="modal fade" id="detalleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleTitulo"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body d-flex">
        <div class="me-4">
            <img id="detalleImagen" src="" class="img-fluid rounded" style="max-height: 300px;">
            <div class="text-center mt-2">
                <button class="btn btn-outline-secondary btn-sm me-2" onclick="cambiarImagen(-1)">&lt;</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="cambiarImagen(1)">&gt;</button>
            </div>
        </div>
        <div>
            <p id="detalleDescripcion"></p>
            <p><strong>Precio:</strong> $<span id="detallePrecio"></span></p>
            <p><strong>Teléfono:</strong> <span id="detalleTelefono"></span></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let imagenesActuales = [];
let indiceActual = 0;

function mostrarDetalle(pub, imagenes) {
    document.getElementById('detalleTitulo').textContent = pub.titulo;
    document.getElementById('detalleDescripcion').textContent = pub.descripcion;
    document.getElementById('detallePrecio').textContent = parseFloat(pub.precio).toFixed(2);
    document.getElementById('detalleTelefono').textContent = pub.telefono;

    imagenesActuales = imagenes.length ? imagenes : ['https://via.placeholder.com/300x200?text=Sin+imagen'];
    indiceActual = 0;
    actualizarImagen();

    new bootstrap.Modal(document.getElementById('detalleModal')).show();
}

function actualizarImagen() {
    document.getElementById('detalleImagen').src = imagenesActuales[indiceActual];
}

function cambiarImagen(dir) {
    indiceActual = (indiceActual + dir + imagenesActuales.length) % imagenesActuales.length;
    actualizarImagen();
}

// Validación de precio
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