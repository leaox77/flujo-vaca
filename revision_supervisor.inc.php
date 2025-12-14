<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
$sql = "SELECT v.*, u.nombre as empleado_nombre 
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);
?>
<h2>👨‍💼 Revisión Supervisor (P2a)</h2>

<div style="background: #f0f7ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <p><strong>📋 Solicitud #<?php echo $nrotramite; ?></strong></p>
    <p><strong>👤 Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
    <p><strong>📊 Estado Actual:</strong> 
        <span style="background: #fff3cd; padding: 3px 8px; border-radius: 3px;">
            <?php echo $solicitud['estado']; ?>
        </span>
    </p>
</div>

<h3>📅 Detalles de las Vacaciones</h3>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
    <div>
        <label>Fecha Inicio:</label>
        <input type="text" value="<?php echo $solicitud['fecha_inicio']; ?>" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>Fecha Fin:</label>
        <input type="text" value="<?php echo $solicitud['fecha_fin']; ?>" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>Días Solicitados:</label>
        <input type="text" value="<?php echo $solicitud['dias_solicitados']; ?> días" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>Días Disponibles:</label>
        <input type="text" value="<?php echo $solicitud['dias_disponibles']; ?> días" readonly style="background: #f8f9fa;">
    </div>
</div>

<?php if (!empty($solicitud['motivo'])): ?>
<div style="margin-bottom: 20px;">
    <label>📝 Motivo del empleado:</label>
    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #007bff;">
        <?php echo nl2br(htmlspecialchars($solicitud['motivo'])); ?>
    </div>
</div>
<?php endif; ?>

<hr style="margin: 25px 0;">

<h3>✅❌ Decisión del Supervisor</h3>

<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>ℹ️ Instrucciones:</strong> Seleccione su decisión y agregue comentarios si es necesario.
</div>

<label for="decision_supervisor">🎯 Decisión:</label>
<select id="decision_supervisor" name="decision_supervisor" required onchange="toggleComentarios()" style="padding: 10px; font-size: 16px;">
    <option value="">-- Seleccione una opción --</option>
    <option value="aprobar">✅ Aprobar solicitud</option>
    <option value="rechazar">❌ Rechazar solicitud</option>
</select>

<label for="comentarios_supervisor">💬 Comentarios:</label>
<textarea id="comentarios_supervisor" name="comentarios_supervisor" rows="4" 
          placeholder="Agregue sus comentarios sobre esta solicitud..."></textarea>

<script>
function toggleComentarios() {
    const decision = document.getElementById('decision_supervisor').value;
    const comentarios = document.getElementById('comentarios_supervisor');
    
    if (decision === 'rechazar') {
        comentarios.placeholder = '⚠️ Explique detalladamente el motivo del rechazo... (requerido)';
        comentarios.required = true;
        comentarios.style.border = '2px solid #dc3545';
    } else {
        comentarios.placeholder = '💬 Agregue comentarios sobre su decisión... (opcional)';
        comentarios.required = false;
        comentarios.style.border = '1px solid #ddd';
    }
}
</script>