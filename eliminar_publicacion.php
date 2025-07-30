<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$es_admin = isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = intval($_POST["id"]);

    $conn = new mysqli("localhost", "root", "", "marketplace");
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    if ($es_admin) {
        // Admin puede borrar cualquier publicación
        $stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id = ?");
        $stmt->bind_param("i", $id);
    } else {
        // Usuario común solo puede borrar las suyas
        $stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $id, $usuario_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $pub = $result->fetch_assoc();
    $stmt->close();

    if ($pub) {
        // Borramos imágenes si hay
        $stmtImg = $conn->prepare("DELETE FROM imagenes WHERE publicacion_id = ?");
        $stmtImg->bind_param("i", $id);
        $stmtImg->execute();
        $stmtImg->close();

        // Borramos la publicación
        $stmtDel = $conn->prepare("DELETE FROM publicaciones WHERE id = ?");
        $stmtDel->bind_param("i", $id);
        $stmtDel->execute();
        $stmtDel->close();
    }

    $conn->close();
    header("Location: dashboard.php");
    exit;
}

?>
