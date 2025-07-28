<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$conexion = new mysqli('sql309.infinityfree.com', 'if0_39580339', 'Worldof2000', 'if0_39580339_marketplace');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recoger datos del formulario
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = $_POST['precio'] ?? 0;
$telefono = $_POST['telefono'] ?? '';
$usuario_id = $_SESSION['usuario_id'];

$imagenes = [];

// Crear carpeta si no existe
$carpeta = 'uploads/';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Subir hasta 5 imágenes
for ($i = 1; $i <= 5; $i++) {
    $inputName = "imagen$i";
    if (!empty($_FILES[$inputName]['name'])) {
        $nombreTmp = $_FILES[$inputName]['tmp_name'];
        $nombreArchivo = basename($_FILES[$inputName]['name']);
        $rutaDestino = $carpeta . time() . "_$i" . "_" . $nombreArchivo;

        if (move_uploaded_file($nombreTmp, $rutaDestino)) {
            $imagenes[] = $rutaDestino;
        }
    }
}

// Insertar publicación
$stmt = $conexion->prepare("INSERT INTO publicaciones (titulo, descripcion, precio, telefono, usuario_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsi", $titulo, $descripcion, $precio, $telefono, $usuario_id);
$stmt->execute();

$publicacion_id = $stmt->insert_id;
$stmt->close();

// Insertar imágenes en tabla 'imagenes'
if (!empty($imagenes)) {
    $stmtImg = $conexion->prepare("INSERT INTO imagenes (publicacion_id, ruta_imagen) VALUES (?, ?)");
    foreach ($imagenes as $ruta) {
        $stmtImg->bind_param("is", $publicacion_id, $ruta);
        $stmtImg->execute();
    }
    $stmtImg->close();
}

$conexion->close();
header("Location: dashboard.php");
exit;
?>
