<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$conn = new mysqli('sql309.infinityfree.com', 'if0_39580339', 'Worldof2000', 'if0_39580339_marketplace');
if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

$id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

// Verificamos que la publicación sea del usuario
$stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$pub = $result->fetch_assoc();

if ($pub) {
    // Obtener imágenes para borrarlas
    $img_stmt = $conn->prepare("SELECT ruta_imagen FROM imagenes WHERE publicacion_id = ?");
    $img_stmt->bind_param("i", $id);
    $img_stmt->execute();
    $img_res = $img_stmt->get_result();

    while ($img = $img_res->fetch_assoc()) {
        if (file_exists($img['ruta_imagen'])) {
            unlink($img['ruta_imagen']);
        }
    }

    // Borrar imágenes y publicación
    $conn->query("DELETE FROM imagenes WHERE publicacion_id = $id");
    $conn->query("DELETE FROM publicaciones WHERE id = $id");
}

$conn->close();
header("Location: dashboard.php");
exit;
?>
