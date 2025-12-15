<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
$solicitud = [];

if ($nrotramite) {
    $sql = "SELECT * FROM vacaciones WHERE id = $nrotramite";
    $resultado = mysqli_query($con, $sql);
    $solicitud = mysqli_fetch_array($resultado);
}
?>
<h2>📝 Solicitud de Vacaciones (P1)</h2>
<p>Complete el formulario para solicitar sus vacaciones:</p>

<div style="background: #e8f4fd; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <strong>ℹ️ Información importante:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li>Tiene <strong>30 días disponibles</strong> para vacaciones</li>
        <li>La solicitud será revisada por su supervisor</li>
        <li>Recibirá notificaciones en cada paso del proceso</li>
    </ul>
</div>

<label for="fecha_inicio">📅 Fecha de Inicio:</label>
<input type="date" id="fecha_inicio" name="fecha_inicio" required 
       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
       value="<?php echo $solicitud['fecha_inicio'] ?? date('Y-m-d', strtotime('+1 day')); ?>"
       onchange="calcularDias()">

<label for="fecha_fin">📅 Fecha de Fin:</label>
<input type="date" id="fecha_fin" name="fecha_fin" required 
       min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>"
       value="<?php echo $solicitud['fecha_fin'] ?? date('Y-m-d', strtotime('+2 days')); ?>"
       onchange="calcularDias()">

<label for="dias_solicitados">📊 Días Solicitados:</label>
<input type="number" id="dias_solicitados" name="dias_solicitados" 
       min="1" max="30" required readonly
       value="<?php echo $solicitud['dias_solicitados'] ?? 1; ?>">

<label for="motivo">📝 Motivo (opcional):</label>
<textarea id="motivo" name="motivo" rows="4" 
          placeholder="Explique brevemente el motivo de su solicitud..."><?php echo $solicitud['motivo'] ?? ''; ?></textarea>

<script>
function calcularDias() {
    const inicio = document.getElementById('fecha_inicio');
    const fin = document.getElementById('fecha_fin');
    const dias = document.getElementById('dias_solicitados');
    
    if (inicio.value && fin.value) {
        const fechaInicio = new Date(inicio.value);
        const fechaFin = new Date(fin.value);
        
        if (fechaFin < fechaInicio) {
            alert('❌ La fecha de fin debe ser posterior a la fecha de inicio');
            fin.value = '';
            dias.value = '';
            return;
        }
        
        const diffTime = fechaFin.getTime() - fechaInicio.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 30) {
            alert('⚠️ No puede solicitar más de 30 días de vacaciones consecutivos');
            fin.value = '';
            dias.value = '';
            return;
        }
        
        dias.value = diffDays;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calcularDias();
    
    const inicio = document.getElementById('fecha_inicio');
    const fin = document.getElementById('fecha_fin');
    
    inicio.addEventListener('change', function() {
        if (inicio.value) {
            const fechaMin = new Date(inicio.value);
            fechaMin.setDate(fechaMin.getDate() + 1);
            fin.min = fechaMin.toISOString().split('T')[0];
        }
    });
});
</script>