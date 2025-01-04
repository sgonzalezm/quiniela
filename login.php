<?php
session_start();
require_once 'conexion.php'; // Archivo de conexión a la base de datos

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];

    // Validación de campos
    if (empty($correo) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Comprobar si el usuario existe
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Iniciar sesión
            $_SESSION['correo'] = $user['correo'];
            $_SESSION['nombre'] = $user['nombre']; // Puedes mostrarlo en el dashboard si lo deseas.
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Correo o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h2>Iniciar Sesión</h2>

            <!-- Mensaje de error -->
            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Formulario de Inicio de Sesión -->
            <form method="POST" action="login.php" id="form-login">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" name="correo" id="correo" placeholder="Ingresa tu correo" required>

                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" placeholder="Ingresa tu contraseña" required>

                <button type="submit">Iniciar Sesión</button>
            </form>

            <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>
