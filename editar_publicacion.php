<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id = intval($_GET['id'] ?? 0);

// Obtener publicación y verificar que pertenece al usuario logueado
$stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$publicacion = $result->fetch_assoc();
$stmt->close();

if (!$publicacion) {
    echo "Publicación no encontrada o acceso denegado.";
    exit;
}

// Obtener imágenes actuales
$stmt = $conn->prepare("SELECT id, ruta_imagen FROM imagenes WHERE publicacion_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$imagenes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $telefono = $_POST['telefono'];

    $stmt = $conn->prepare("UPDATE publicaciones SET titulo = ?, descripcion = ?, precio = ?, telefono = ? WHERE id = ?");
    $stmt->bind_param("ssdsi", $titulo, $descripcion, $precio, $telefono, $id);
    $stmt->execute();
    $stmt->close();

    // Subida de nuevas imágenes (hasta 5 en total incluyendo existentes)
    $totalActual = count($imagenes);
    $maxSubidas = 5 - $totalActual;

    for ($i = 1; $i <= $maxSubidas; $i++) {
        if (isset($_FILES["imagen$i"]) && $_FILES["imagen$i"]["error"] === UPLOAD_ERR_OK) {
            $nombreTmp = $_FILES["imagen$i"]["tmp_name"];
            $nombreArchivo = uniqid("img_") . "." . pathinfo($_FILES["imagen$i"]["name"], PATHINFO_EXTENSION);
            $rutaDestino = "uploads/" . $nombreArchivo;

            if (move_uploaded_file($nombreTmp, $rutaDestino)) {
                $stmt = $conn->prepare("INSERT INTO imagenes (publicacion_id, ruta_imagen) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $rutaDestino);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar publicación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Editar publicación</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3"><label class="form-label">Título</label><input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($publicacion['titulo']) ?>"></div>
        <div class="mb-3"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-control" required><?= htmlspecialchars($publicacion['descripcion']) ?></textarea></div>
        <div class="mb-3"><label class="form-label">Precio</label><input type="number" name="precio" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($publicacion['precio']) ?>"></div>
        <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($publicacion['telefono']) ?>"></div>

    <div class="mb-3">
        <label class="form-label">Imágenes actuales</label><br>
        <div class="d-flex flex-wrap gap-3">
            <?php foreach ($imagenes as $img): ?>
                <div class="text-center">
                    <img src="<?= htmlspecialchars($img['ruta_imagen']) ?>" width="100" class="mb-1 rounded shadow-sm d-block">
                    <a href="eliminar_imagen.php?id=<?= $img['id'] ?>&publicacion_id=<?= $id ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('¿Seguro que deseas eliminar esta imagen?')">Eliminar</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


        <div class="mb-3">
            <label class="form-label">Agregar más imágenes (máx <?= 5 - count($imagenes) ?>)</label>
            <?php for ($i = 1; $i <= 5 - count($imagenes); $i++): ?>
                <input type="file" name="imagen<?= $i ?>" class="form-control mb-2" accept="image/*">
            <?php endfor; ?>
        </div>

        <div class="d-flex justify-content-between">
            <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
    </form>
</div>
</body>
</html>
