<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int) $_GET['nrotramite'] : 0;
$sql = "SELECT v.*, u.nombre AS empleado_nombre, s.nombre AS supervisor_nombre
        FROM vacaciones v
        JOIN usuarios u ON v.empleado_id = u.id
        LEFT JOIN usuarios s ON v.supervisor_id = s.id
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

if (!$solicitud) {
    echo "<p>No se encontró la solicitud.</p>";
    return;
}

$estado = $solicitud['estado'];
$estilo = 'background:#fff3cd; color:#856404;';
$titulo = 'Pendiente';
$rechazado = ($estado === 'rechazado_supervisor' || $estado === 'rechazado_rrhh');

if ($estado === 'aprobado_rrhh') {
    $estilo = 'background:#d4edda; color:#155724;';
    $titulo = 'Solicitud aprobada';
} elseif ($rechazado) {
    $estilo = 'background:#f8d7da; color:#721c24;';
    $titulo = 'Solicitud rechazada';
}
?>
<h3>Notificación final</h3>
<div style="<?php echo $estilo; ?> padding:15px; border-radius:6px;">
    <h4><?php echo $titulo; ?></h4>
    <p><strong>N° Trámite:</strong> <?php echo $solicitud['id']; ?></p>
    <p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
    <p><strong>Supervisor:</strong> <?php echo $solicitud['supervisor_nombre'] ?? 'No asignado'; ?></p>
    <p><strong>Estado:</strong> <?php echo $estado; ?></p>
    <?php if ($estado === 'aprobado_rrhh'): ?>
        <p><strong>Días descontados:</strong> <?php echo $solicitud['dias_descontar'] ?? $solicitud['dias_solicitados']; ?></p>
    <?php endif; ?>
    <?php if ($rechazado && !empty($solicitud['motivo_rechazo'])): ?>
        <p><strong>Motivo:</strong> <?php echo htmlspecialchars($solicitud['motivo_rechazo']); ?></p>
    <?php endif; ?>
</div>

<div style="margin-top: 15px;">
    <a href="index.php" class="btn">Volver al inicio</a>
</div>
