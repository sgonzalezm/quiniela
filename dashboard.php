<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}

echo "<h1>Bienvenido, " . htmlspecialchars($_SESSION['correo']) . "!</h1>";
echo "<a href='logout.php'>Cerrar sesión</a>";
?>
