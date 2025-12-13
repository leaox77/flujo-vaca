<?php
include "conexion.inc.php";

$nrotramite = $_GET['nrotramite'] ?? 0;
$sql = "SELECT v.*, u.nombre as supervisor_nombre 
        FROM vacaciones v 
        JOIN usuarios u ON v.supervisor_id = u.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);
?>
<h3>Notificación</h3>
<?php if ($solicitud['estado'] == 'aprobado_rrhh'): ?>
    <div style="background: #d4edda; padding: 15px; border-radius: 5px;">
        <h4>¡Solicitud Aprobada!</h4>
        <p>Su solicitud de vacaciones ha sido aprobada.</p>
        <p><strong>Estado final:</strong> Aprobado por RRHH</p>
        <p><strong>Días descontados:</strong> <?php echo $solicitud['dias_descontar'] ?? $solicitud['dias_solicitados']; ?></p>
    </div>
<?php elseif ($solicitud['estado'] == 'rechazado_supervisor'): ?>
    <div style="background: #f8d7da; padding: 15px; border-radius: 5px;">
        <h4>Solicitud Rechazada por Supervisor</h4>
        <p><strong>Motivo:</strong> <?php echo $solicitud['motivo_rechazo']; ?></p>
    </div>
<?php elseif ($solicitud['estado'] == 'rechazado_rrhh'): ?>
    <div style="background: #f8d7da; padding: 15px; border-radius: 5px;">
        <h4>Solicitud Rechazada por RRHH</h4>
        <p><strong>Motivo:</strong> <?php echo $solicitud['motivo_rechazo']; ?></p>
    </div>
<?php endif; ?>

<label>Confirmar recepción:</label>
<input type="checkbox" name="confirmar" required> He leído la notificación
