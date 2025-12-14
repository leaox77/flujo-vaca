<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
$sql = "SELECT v.*, u.nombre as empleado_nombre, s.nombre as supervisor_nombre
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        LEFT JOIN usuarios s ON v.supervisor_id = s.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);
?>
<h2>🏢 Verificación RRHH (P3)</h2>

<div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <p><strong>📋 Solicitud #<?php echo $nrotramite; ?></strong></p>
    <p><strong>👤 Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
    <p><strong>👨‍💼 Supervisor:</strong> <?php echo $solicitud['supervisor_nombre'] ?? 'No asignado'; ?></p>
    <p><strong>📊 Estado:</strong> 
        <span style="background: #d4edda; padding: 3px 8px; border-radius: 3px;">
            <?php echo $solicitud['estado']; ?>
        </span>
    </p>
</div>

<h3>📅 Detalles de la Solicitud</h3>
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

<?php if (!empty($solicitud['comentarios_supervisor'])): ?>
<div style="margin-bottom: 20px;">
    <label>💬 Comentarios del Supervisor:</label>
    <div style="background: #fff3cd; padding: 10px; border-radius: 5px; border-left: 4px solid #ffc107;">
        <?php echo nl2br(htmlspecialchars($solicitud['comentarios_supervisor'])); ?>
    </div>
</div>
<?php endif; ?>

<hr style="margin: 25px 0;">

<h3>✅❌ Decisión Final de RRHH</h3>

<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>ℹ️ Información:</strong> Esta es la decisión final del proceso. Los días descontados se restarán del saldo disponible del empleado.
</div>

<label for="decision_rrhh">🎯 Decisión Final:</label>
<select id="decision_rrhh" name="decision_rrhh" required onchange="toggleComentarios()" style="padding: 10px; font-size: 16px;">
    <option value="">-- Seleccione una opción --</option>
    <option value="aprobar">✅ Aprobar definitivamente</option>
    <option value="rechazar">❌ Rechazar solicitud</option>
</select>

<label for="dias_descontar">📊 Días a descontar:</label>
<input type="number" id="dias_descontar" name="dias_descontar" 
       value="<?php echo $solicitud['dias_solicitados']; ?>" 
       min="1" max="<?php echo $solicitud['dias_disponibles']; ?>" required
       style="padding: 10px;">

<label for="comentarios_rrhh">💬 Comentarios RRHH:</label>
<textarea id="comentarios_rrhh" name="comentarios_rrhh" rows="4" 
          placeholder="Agregue comentarios de RRHH..."></textarea>

<script>
function toggleComentarios() {
    const decision = document.getElementById('decision_rrhh').value;
    const comentarios = document.getElementById('comentarios_rrhh');
    
    if (decision === 'rechazar') {
        comentarios.placeholder = '⚠️ Explique detalladamente el motivo del rechazo por parte de RRHH... (requerido)';
        comentarios.required = true;
        comentarios.style.border = '2px solid #dc3545';
    } else {
        comentarios.placeholder = '💬 Agregue comentarios de RRHH... (opcional)';
        comentarios.required = false;
        comentarios.style.border = '1px solid #ddd';
    }
}

// Validar que los días a descontar no superen los disponibles
document.getElementById('dias_descontar').addEventListener('change', function() {
    const max = <?php echo $solicitud['dias_disponibles']; ?>;
    const valor = parseInt(this.value);
    
    if (valor > max) {
        alert('⚠️ No puede descontar más días de los disponibles (' + max + ' días)');
        this.value = max;
    }
    
    if (valor < 1) {
        this.value = 1;
    }
});
</script>