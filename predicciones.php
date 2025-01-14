<?php
session_start();
require_once 'conexion.php';

require_once 'partials/header.php'; // Header y apertura del <main>
require_once 'partials/aside.php';  // Menú lateral

if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['correo'];
$id_sala = $_SESSION['id_sala'] ?? null; // ID de la sala desde la URL

// Validar que se haya pasado un ID de sala
if (is_null($id_sala)) {
    die("Error: No se ha seleccionado una sala.");
}

// Verificar la fecha límite de la sala
$stmt = $conn->prepare("SELECT fecha_limite FROM salas WHERE id_sala = :id_sala");
$stmt->bindParam(':id_sala', $id_sala);
$stmt->execute();
$sala = $stmt->fetch(PDO::FETCH_ASSOC);

// Validar si la consulta devolvió un resultado
if (!$sala) {
    die("Error: La sala con ID " . htmlspecialchars($id_sala) . " no existe.");
}

$fecha_limite = $sala['fecha_limite'];

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Obtener los eventos disponibles de la sala
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id_sala = :id_sala ORDER BY fecha_evento ASC");
$stmt->bindParam(':id_sala', $id_sala);
$stmt->execute();
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de predicciones solo si está dentro de la fecha límite
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $fecha_actual <= $fecha_limite) {
    foreach ($_POST['prediccion'] as $id_evento => $resultado) {
        $stmt = $conn->prepare("INSERT INTO predicciones (id_sala, id_usuario, id_evento, prediccion_local, prediccion_visitante) 
                                VALUES (:id_sala, :id_usuario, :id_evento, :local, :visitante)");
        $stmt->bindParam(':id_sala', $id_sala);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->bindParam(':id_evento', $id_evento);

        if ($resultado === 'local') {
            $stmt->bindValue(':local', 1);
            $stmt->bindValue(':visitante', 0);
        } elseif ($resultado === 'visitante') {
            $stmt->bindValue(':local', 0);
            $stmt->bindValue(':visitante', 1);
        } else {
            $stmt->bindValue(':local', 1);
            $stmt->bindValue(':visitante', 1);
        }
        $stmt->execute();
    }
    echo "<p>Predicciones guardadas correctamente.</p>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Predicciones de Quiniela</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="form-container">
        <h2>Haz tus Predicciones</h2>

        <!-- Mostrar aviso si ha pasado la fecha límite -->
        <?php if ($fecha_actual > $fecha_limite): ?>
            <div class="alert">La fecha límite ha pasado. Ya no es posible modificar predicciones.</div>
        <?php elseif (empty($eventos)): ?>
            <p>No hay eventos disponibles para esta sala.</p>
        <?php else: ?>
            <form method="POST" action="predicciones.php?id_sala=<?= $id_sala ?>">
                <?php foreach ($eventos as $evento): ?>
                    <fieldset>
                        <legend>
                            <?= htmlspecialchars($evento['equipo_local']) ?> vs <?= htmlspecialchars($evento['equipo_visitante']) ?>
                            (Fecha: <?= htmlspecialchars($evento['fecha_evento']) ?>)
                        </legend>
                        <label>
                            <input type="radio" name="prediccion[<?= $evento['id_evento'] ?>]" value="local" required>
                            Gana <?= htmlspecialchars($evento['equipo_local']) ?>
                        </label>
                        <label>
                            <input type="radio" name="prediccion[<?= $evento['id_evento'] ?>]" value="visitante" required>
                            Gana <?= htmlspecialchars($evento['equipo_visitante']) ?>
                        </label>
                        <label>
                            <input type="radio" name="prediccion[<?= $evento['id_evento'] ?>]" value="empate" required>
                            Empate
                        </label>
                    </fieldset>
                <?php endforeach; ?>
                <button type="submit">Guardar Predicciones</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

