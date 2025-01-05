<?php
session_start();
require_once 'conexion.php';

require_once 'partials/header.php'; // Header y apertura del <main>
require_once 'partials/aside.php';  // Menú lateral

$success = "";
$error = "";

// Validar sesión de usuario
if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sala = $_POST['id_sala'];
    $id_usuario = $_SESSION['correo'];

    try {
        $stmt = $conn->prepare("INSERT INTO participantes_sala (id_sala, id_usuario) VALUES (:id_sala, :id_usuario)");
        $stmt->bindParam(':id_sala', $id_sala);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        $success = "Te has unido a la sala exitosamente.";
    } catch (PDOException $e) {
        $error = "Error al unirse a la sala: " . $e->getMessage();
    }
}
?>

<!-- Formulario -->


            <form method="POST" action="unirse_sala.php">
                <label for="id_sala">ID de la Sala:</label>
                <input type="text" name="id_sala" id="id_sala" placeholder="Ingresa el ID de la sala" required>
                <button type="submit">Unirse</button>
            </form>
        </main>
    </body>
</html>
