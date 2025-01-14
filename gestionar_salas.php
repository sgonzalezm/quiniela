<?php
session_start();
require_once 'conexion.php'; // Conexión a la base de datos

if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}
// Obtener las salas del usuario actual
$id_usuario = $_SESSION['correo']; // Asegúrate de que la sesión esté iniciada y tenga el ID del usuario
$stmt = $conn->prepare("
    SELECT s.id_sala, s.nombre_sala, GROUP_CONCAT(l.nombre_liga SEPARATOR ', ') AS ligas
    FROM salas s
    JOIN salas_ligas sl ON s.id_sala = sl.id_sala
    JOIN ligas l ON sl.id_liga = l.id_liga
    WHERE s.owner_id = :owner_id
    GROUP BY s.id_sala, s.nombre_sala
");
$stmt->bindParam(':owner_id', $id_usuario);
$stmt->execute();
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar la inserción de una nueva sala
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_sala = $_POST['nombre_sala'];
    $ligas_seleccionadas = $_POST['ligas'];

    $stmt = $conn->prepare("INSERT INTO salas (nombre_sala, owner_id) VALUES (:nombre_sala, :id_usuario)");
    $stmt->bindParam(':nombre_sala', $nombre_sala);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $id_sala = $conn->lastInsertId();

    foreach ($ligas_seleccionadas as $id_liga) {
        $stmt_liga = $conn->prepare("INSERT INTO salas_ligas (id_sala, id_liga) VALUES (:id_sala, :id_liga)");
        $stmt_liga->bindParam(':id_sala', $id_sala);
        $stmt_liga->bindParam(':id_liga', $id_liga);
        $stmt_liga->execute();
    }

    header("Location: gestionar_salas.php"); // Redireccionar después de agregar la sala
    exit;
}

// Obtener todas las ligas disponibles para seleccionar al crear una sala
$stmt_ligas = $conn->query("SELECT id_liga, nombre_liga, temporada FROM ligas");
$ligas = $stmt_ligas->fetchAll(PDO::FETCH_ASSOC);

require_once 'partials/header.php';
?>



    <h1>Gestionar Salas de Quiniela</h1>

    <!-- Listado de salas del usuario -->
    <h2>Salas creadas</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre de la Sala</th>
                <th>Ligas Asociadas</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salas as $sala): ?>
                <tr>
                    <td><?= htmlspecialchars($sala['nombre_sala']) ?></td>
                    <td><?= htmlspecialchars($sala['ligas']) ?></td>
                    <td>
                        <a href="editar_sala.php?id_sala=<?= $sala['id_sala'] ?>">Editar</a> |
                        <a href="eliminar_sala.php?id_sala=<?= $sala['id_sala'] ?>" onclick="return confirm('¿Estás seguro de eliminar esta sala?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulario para agregar una nueva sala -->
    <h2>Crear una nueva sala</h2>
    <form method="POST">
        <label for="nombre_sala">Nombre de la Sala:</label>
        <input type="text" id="nombre_sala" name="nombre_sala" required>

        <label for="ligas">Ligas asociadas:</label>
        <select id="ligas" name="ligas[]" multiple required>
            <?php foreach ($ligas as $liga): ?>
                <option value="<?= $liga['id_liga'] ?>"><?= htmlspecialchars($liga['nombre_liga']) ?> (<?= $liga['temporada'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Crear Sala</button>
    </form>
    </main>
</body>
</html>

