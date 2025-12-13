<?php
// Pantalla P3 (RRHH) dentro de motor.php. Muestra rol de cada actor.
include "conexion.inc.php";

$nrotramite = $_GET['nrotramite'] ?? 0;
$sql = "SELECT v.*,
               u.nombre AS empleado_nombre, u.rol AS empleado_rol,
               s.nombre AS supervisor_nombre, s.rol AS supervisor_rol,
               r.nombre AS rrhh_nombre, r.rol AS rrhh_rol
        FROM vacaciones v
        JOIN usuarios u ON v.empleado_id = u.id
        LEFT JOIN usuarios s ON v.supervisor_id = s.id
        LEFT JOIN usuarios r ON v.rrhh_id = r.id
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);
?>
<h3>Verificación RRHH</h3>
<p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?> (<?php echo $solicitud['empleado_rol']; ?>)</p>
<?php if (!empty($solicitud['supervisor_nombre'])): ?>
<p><strong>Supervisor:</strong> <?php echo $solicitud['supervisor_nombre']; ?><?php echo $solicitud['supervisor_rol'] ? ' ('.$solicitud['supervisor_rol'].')' : ''; ?></p>
<?php endif; ?>
<p><strong>RRHH actual (tú):</strong> <?php echo $_SESSION['nombre']; ?></p>
<?php if (!empty($solicitud['rrhh_nombre'])): ?>
<p><strong>RRHH que procesó:</strong> <?php echo $solicitud['rrhh_nombre']; ?><?php echo $solicitud['rrhh_rol'] ? ' ('.$solicitud['rrhh_rol'].')' : ''; ?></p>
<?php endif; ?>
<p><strong>Fecha Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
<p><strong>Fecha Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
<p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
<p><strong>Días Disponibles:</strong> <?php echo $solicitud['dias_disponibles']; ?></p>
<p><strong>Estado actual:</strong> <?php echo $solicitud['estado']; ?></p>

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
