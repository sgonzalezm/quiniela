<?php
session_start();
require_once 'conexion.php'; // Archivo de conexión a la base de datos

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($correo) || empty($password) || empty($confirm_password)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Comprobar si el correo ya está registrado
        $stmt = $conn->prepare("SELECT correo FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userExists) {
            $error = "El correo ya está registrado. <a href='login.php'>Inicia sesión aquí</a>.";
        } else {
            // Hashear contraseña e insertar
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $conn->prepare("INSERT INTO usuarios (correo, password) VALUES (:correo, :password)");
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':password', $password_hash);
                $stmt->execute();
                $success = "Registro exitoso.";
            } catch (PDOException $e) {
                $error = "Error al registrar el usuario: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="form-container">
        <div class="form-card">
            <h2>Registro de Usuario</h2>

            <!-- Mensajes de éxito o error -->
            <?php if (!empty($success)): ?>
                <div class="success"><?= htmlspecialchars($success); ?></div>
            <?php elseif (!empty($error)): ?>
                <div class="error"><?= $error; ?></div>
            <?php endif; ?>

            <!-- Formulario de Registro -->
            <form method="POST" action="registro.php" id="form-register">
                
                <label for="correo">Correo Electrónico:</label>
                <input type="email" name="correo" id="correo" placeholder="Ingresa tu correo" required>

                <label for="password">Contraseña:</label>
                <input type="password" name="password" id="password" placeholder="Crea una contraseña" required>

                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirma la contraseña" required>

                <button type="submit">Registrarse</button>
            </form>

            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
