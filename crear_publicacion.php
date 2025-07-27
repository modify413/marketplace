<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$conexion = new mysqli('localhost', 'root', '', 'marketplace');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Validar campos
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);
$precio = floatval($_POST['precio']);
$telefono = trim($_POST['telefono']);
$usuario_id = $_SESSION['usuario_id'];

if (empty($titulo) || empty($descripcion) || $precio <= 0 || empty($telefono)) {
    die("Todos los campos son obligatorios y deben ser válidos.");
}

// Procesar imágenes
$imagenes = [];
$directorio = 'uploads/';
for ($i = 1; $i <= 5; $i++) {
    if (isset($_FILES["imagen$i"]) && $_FILES["imagen$i"]["error"] === UPLOAD_ERR_OK) {
        $tmp = $_FILES["imagen$i"]["tmp_name"];
        $nombreArchivo = uniqid("img_") . "_" . basename($_FILES["imagen$i"]["name"]);
        $destino = $directorio . $nombreArchivo;

        if (move_uploaded_file($tmp, $destino)) {
            $imagenes[] = $destino;
        } else {
            $imagenes[] = null;
        }
    } else {
        $imagenes[] = null;
    }
}

// Insertar en base de datos
$stmt = $conexion->prepare("INSERT INTO publicaciones 
    (titulo, descripcion, precio, telefono, usuario_id, imagen1, imagen2, imagen3, imagen4, imagen5) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "ssdissssss",
    $titulo,
    $descripcion,
    $precio,
    $telefono,
    $usuario_id,
    $imagenes[0],
    $imagenes[1],
    $imagenes[2],
    $imagenes[3],
    $imagenes[4]
);

if ($stmt->execute()) {
    header("Location: dashboard.php?publicacion=ok");
    exit;
} else {
    echo "Error al guardar la publicación: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
