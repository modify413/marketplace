<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli('sql309.infinityfree.com', 'if0_39580339', 'Worldof2000', 'if0_39580339_marketplace');
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$id_imagen = intval($_GET['id'] ?? 0);
$publicacion_id = intval($_GET['publicacion_id'] ?? 0);

// Verificar que la imagen le pertenece al usuario
$stmt = $conn->prepare("
    SELECT i.ruta_imagen, p.usuario_id 
    FROM imagenes i 
    JOIN publicaciones p ON i.publicacion_id = p.id 
    WHERE i.id = ? AND p.usuario_id = ?
");
$stmt->bind_param("ii", $id_imagen, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$imagen = $result->fetch_assoc();
$stmt->close();

if ($imagen) {
    // Borrar archivo físico si existe
    if (file_exists($imagen['ruta_imagen'])) {
        unlink($imagen['ruta_imagen']);
    }

    // Borrar de la base de datos
    $stmt = $conn->prepare("DELETE FROM imagenes WHERE id = ?");
    $stmt->bind_param("i", $id_imagen);
    $stmt->execute();
    $stmt->close();
}

// Volver al formulario de edición
header("Location: editar_publicacion.php?id=$publicacion_id");
exit;
