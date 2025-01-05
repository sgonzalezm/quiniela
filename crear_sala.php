<?php
session_start();
require_once 'conexion.php';

require_once 'partials/header.php'; // Header y apertura del <main>
require_once 'partials/aside.php';  // Menú lateral

$success = "";
$error = "";

// Validar si el usuario es el "Owner"
if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_sala = trim($_POST['nombre_sala']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_limite = $_POST['fecha_limite'];
    $owner_id = $_SESSION['correo']; // ID del "Owner"

    if (empty($nombre_sala) || empty($fecha_limite)) {
        $error = "El nombre de la sala y la fecha límite son obligatorios.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO salas (nombre_sala, descripcion, owner_id, fecha_limite) VALUES (:nombre_sala, :descripcion, :owner_id, :fecha_limite)");
            $stmt->bindParam(':nombre_sala', $nombre_sala);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':owner_id', $owner_id);
            $stmt->bindParam(':fecha_limite', $fecha_limite);
            $stmt->execute();

            $success = "Sala de quiniela creada exitosamente.";
        } catch (PDOException $e) {
            $error = "Error al crear la sala: " . $e->getMessage();
        }
    }
}
?>
    <div class="form-container">
        <div class="form-card">
            <h2>Crear Sala de Quiniela</h2>

            <!-- Mensajes de éxito o error -->
            <?php if (!empty($success)): ?>
                <div class="success"><?= htmlspecialchars($success); ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="crear_sala.php">
                <label for="nombre_sala">Nombre de la Sala:</label>
                <input type="text" name="nombre_sala" id="nombre_sala" placeholder="Nombre de la sala" required>

                <label for="descripcion">Descripción (opcional):</label>
                <textarea name="descripcion" id="descripcion" placeholder="Descripción de la sala"></textarea>

                <label for="fecha_limite">Fecha Límite de Predicciones:</label>
                <input type="date" name="fecha_limite" id="fecha_limite" required>

                <button type="submit">Crear Sala</button>
            </form>
        </div>
    </div>
    </main>
</body>
</html>
