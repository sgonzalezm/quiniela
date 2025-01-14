<?php
session_start();
require_once 'conexion.php'; // Conexión a la base de datos

if (!isset($_SESSION['correo'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['correo'];
$sala_default = $_SESSION['id_sala'];

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

// Consulta para obtener las salas creadas por el usuario y cantidad de participantes
$stmt = $conn->prepare("SELECT s.nombre_sala, s.descripcion, s.fecha_creacion, COUNT(p.id_usuario) AS total_participantes
                        FROM salas s
                        LEFT JOIN participantes_sala p ON s.id_sala = p.id_sala
                        WHERE s.owner_id = :id_usuario
                        GROUP BY s.id_sala");
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$salas_creadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener las predicciones realizadas por el usuario
$stmt = $conn->prepare("SELECT p.id_evento, e.equipo_local, e.equipo_visitante, e.fecha_evento, p.prediccion_local, p.prediccion_visitante, e.marcador_local, e.marcador_visitante
                        FROM predicciones p
                        JOIN eventos e ON p.id_evento = e.id_evento
                        WHERE p.id_usuario = :id_usuario
                        ORDER BY e.fecha_evento ASC");
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$predicciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener fechas límite por vencer (próximos 3 días)
$stmt = $conn->prepare("SELECT nombre_sala, fecha_limite FROM salas WHERE fecha_limite > :fecha_actual ORDER BY fecha_limite ASC");
$stmt->bindParam(':fecha_actual', $fecha_actual);
$stmt->execute();
$fechas_por_vencer = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los puntos acumulados por jornada
$stmt = $conn->prepare('
    SELECT s.nombre_sala, e.fecha_evento, SUM(
        CASE
            WHEN p.prediccion_local = e.marcador_local AND p.prediccion_visitante = e.marcador_visitante THEN 3
            WHEN (p.prediccion_local > p.prediccion_visitante AND e.marcador_local > e.marcador_visitante) OR (p.prediccion_local < p.prediccion_visitante AND e.marcador_local < e.marcador_visitante) THEN 1
            ELSE 0
        END
    ) AS puntos
    FROM predicciones p
    JOIN eventos e ON p.id_evento = e.id_evento
    JOIN salas_ligas sl ON e.id_liga = sl.id_liga
    JOIN salas s ON sl.id_sala = s.id_sala
    WHERE p.id_usuario = :id_usuario
    GROUP BY s.nombre_sala, e.fecha_evento
');
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$puntos_jornadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'partials/header.php';
?>

<div class="dashboard-container">
    <h3>Bienvenido, <?= htmlspecialchars($_SESSION['correo']) ?>!</h3>
    <p><strong>Fecha actual:</strong> <?= $fecha_actual ?></p>
    <a href="predicciones.php"><button>Ingresar predicción</button></a>
    <a href="unirse_sala.php"><button>Unirte a una sala</button></a>
    <a href="crear_sala.php"><button>Agregar nueva sala</button></a>
    <a href="gestionar_salas.php"><button>Gestionar mis salas</button></a>

    <h2>Salas creadas por ti</h2>
    <?php if (!empty($salas_creadas)): ?>
        <table>
            <thead>
                <tr>
                    <th>Nombre de la Sala</th>
                    <th>Descripción</th>
                    <th>Fecha de Creación</th>
                    <th>Total de Participantes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salas_creadas as $sala): ?>
                    <tr>
                        <td><?= htmlspecialchars($sala['nombre_sala']) ?></td>
                        <td><?= htmlspecialchars($sala['descripcion']) ?></td>
                        <td><?= htmlspecialchars($sala['fecha_creacion']) ?></td>
                        <td><?= $sala['total_participantes'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No has creado ninguna sala aún.</p>
    <?php endif; ?>

    <h2>Predicciones realizadas</h2>
    <?php if (!empty($predicciones)): ?>
        <table>
            <thead>
                <tr>
                    <th>Evento</th>
                    <th>Fecha</th>
                    <th>Predicción</th>
                    <th>Resultado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($predicciones as $prediccion): ?>
                    <tr>
                        <td><?= htmlspecialchars($prediccion['equipo_local']) ?> vs <?= htmlspecialchars($prediccion['equipo_visitante']) ?></td>
                        <td><?= htmlspecialchars($prediccion['fecha_evento']) ?></td>
                        <td><?= $prediccion['prediccion_local'] ?> - <?= $prediccion['prediccion_visitante'] ?></td>
                        <td>
                            <?php if (is_null($prediccion['marcador_local']) || is_null($prediccion['marcador_visitante'])): ?>
                                Aún sin resultado
                            <?php else: ?>
                                <?= $prediccion['marcador_local'] ?> - <?= $prediccion['marcador_visitante'] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No has realizado predicciones aún.</p>
    <?php endif; ?>

    <h2>Fechas de predicciones por vencer</h2>
    <?php if (!empty($fechas_por_vencer)): ?>
        <ul>
            <?php foreach ($fechas_por_vencer as $fecha): ?>
                <li>
                    Sala: <strong><?= htmlspecialchars($fecha['nombre_sala']) ?></strong> - Fecha límite: <?= htmlspecialchars($fecha['fecha_limite']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay fechas de predicciones próximas a vencer.</p>
    <?php endif; ?>

    <h2>Puntos acumulados por jornada</h2>
    <?php if (!empty($puntos_jornadas)): ?>
        <table>
            <thead>
                <tr>
                    <th>Sala</th>
                    <th>Fecha de Evento</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($puntos_jornadas as $puntos): ?>
                    <tr>
                        <td><?= htmlspecialchars($puntos['nombre_sala']) ?></td>
                        <td><?= htmlspecialchars($puntos['fecha_evento']) ?></td>
                        <td><?= $puntos['puntos'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tienes puntos acumulados aún.</p>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
