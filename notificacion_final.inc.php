<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
$sql = "SELECT v.*, u.nombre as empleado_nombre 
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

$estado = $solicitud['estado'] ?? 'pendiente';

// Determinar estilo y mensaje según estado
if ($estado == 'aprobado_rrhh') {
    $estilo = 'background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 6px solid #28a745;';
    $icono = '✅';
    $titulo = '¡Felicidades! Solicitud APROBADA';
    $mensaje = 'Su solicitud de vacaciones ha sido aprobada definitivamente por RRHH.';
} elseif ($estado == 'rechazado_rrhh') {
    $estilo = 'background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); border-left: 6px solid #dc3545;';
    $icono = '❌';
    $titulo = 'Solicitud RECHAZADA por RRHH';
    $mensaje = 'Su solicitud de vacaciones ha sido rechazada por el departamento de RRHH.';
} elseif ($estado == 'rechazado_supervisor') {
    $estilo = 'background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border-left: 6px solid #ffc107;';
    $icono = '⚠️';
    $titulo = 'Solicitud RECHAZADA por Supervisor';
    $mensaje = 'Su solicitud de vacaciones ha sido rechazada por su supervisor.';
} else {
    $estilo = 'background: #e7f3fe; border-left: 6px solid #17a2b8;';
    $icono = '⏳';
    $titulo = 'Procesando Solicitud';
    $mensaje = 'Su solicitud está siendo procesada.';
}
?>
<h2><?php echo $icono; ?> Notificación Final (P5)</h2>

<div style="<?php echo $estilo; ?> padding: 25px; border-radius: 8px; margin-bottom: 20px;">
    <h3 style="margin-top: 0; color: #155724;"><?php echo $titulo; ?></h3>
    <p style="font-size: 16px;"><?php echo $mensaje; ?></p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <strong>📋 N° Trámite:</strong><br>
        <span style="font-size: 24px; font-weight: bold; color: #007bff;">#<?php echo $solicitud['id']; ?></span>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <strong>👤 Empleado:</strong><br>
        <?php echo $solicitud['empleado_nombre']; ?>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <strong>📊 Estado Final:</strong><br>
        <span style="padding: 5px 10px; border-radius: 3px; background: <?php 
            echo ($estado == 'aprobado_rrhh') ? '#d4edda' : 
                 (strpos($estado, 'rechazado') !== false ? '#f8d7da' : '#fff3cd');
        ?>;">
            <?php echo $estado; ?>
        </span>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
        <strong>📅 Fecha Solicitud:</strong><br>
        <?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?>
    </div>
</div>

<?php if ($estado == 'aprobado_rrhh'): ?>
<div style="background: #e8f4fd; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
    <h4>📊 Resumen de Vacaciones</h4>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div>
            <strong>Días solicitados:</strong><br>
            <?php echo $solicitud['dias_solicitados']; ?> días
        </div>
        <div>
            <strong>Días descontados:</strong><br>
            <?php echo $solicitud['dias_descontar'] ?? $solicitud['dias_solicitados']; ?> días
        </div>
        <div>
            <strong>Días disponibles antes:</strong><br>
            <?php echo ($solicitud['dias_disponibles'] + ($solicitud['dias_descontar'] ?? $solicitud['dias_solicitados'])); ?> días
        </div>
        <div>
            <strong>Días disponibles ahora:</strong><br>
            <?php echo $solicitud['dias_disponibles']; ?> días
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($solicitud['motivo_rechazo'])): ?>
<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
    <h4>📝 Motivo:</h4>
    <p><?php echo nl2br(htmlspecialchars($solicitud['motivo_rechazo'])); ?></p>
</div>
<?php endif; ?>

<div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin-top: 20px;">
    <p><strong>ℹ️ Información:</strong> Este proceso ha finalizado. Puede ver el detalle completo en el historial de solicitudes.</p>
</div>