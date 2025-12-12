<?php
include "conexion.inc.php";

$nrotramite = $_GET['nrotramite'] ?? 0;
$sql = "SELECT v.*, u.nombre as empleado_nombre 
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);
?>
<h3>Verificación RRHH</h3>
<p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
<p><strong>Fecha Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
<p><strong>Fecha Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
<p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
<p><strong>Días Disponibles:</strong> <?php echo $solicitud['dias_disponibles']; ?></p>
<p><strong>Estado Supervisor:</strong> <?php echo $solicitud['estado']; ?></p>

<hr>
<label>Verificación:</label>
<select name="decision_rrhh" required>
    <option value="">-- Seleccione --</option>
    <option value="aprobar">Aprobar</option>
    <option value="rechazar">Rechazar</option>
</select>

<label>Días a descontar:</label>
<input type="number" name="dias_descontar" value="<?php echo $solicitud['dias_solicitados']; ?>" min="1">

<label>Comentarios:</label>
<textarea name="comentarios_rrhh" rows="3"></textarea>