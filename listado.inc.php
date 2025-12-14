<?php
include "conexion.inc.php";

$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
$sql = "SELECT v.*, u.* 
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        WHERE v.id = $nrotramite";
$resultado = mysqli_query($con, $sql);
$fila = mysqli_fetch_array($resultado);
?>
<h2>👤 Datos del Empleado (P2)</h2>
<p>Verifique los datos del solicitante:</p>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
    <div>
        <label>👤 Usuario:</label>
        <input type="text" value="<?php echo $fila['usuario']; ?>" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>📛 Nombre Completo:</label>
        <input type="text" value="<?php echo $fila['nombre']; ?>" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>🎭 Rol:</label>
        <input type="text" value="<?php echo $fila['rol']; ?>" readonly style="background: #f8f9fa;">
    </div>
    
    <div>
        <label>🆔 ID Empleado:</label>
        <input type="text" value="<?php echo $fila['empleado_id']; ?>" readonly style="background: #f8f9fa;">
    </div>
</div>

<h3>📅 Detalles de la Solicitud</h3>
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
    <div>
        <label>📅 Fecha Inicio:</label>
        <input type="text" value="<?php echo $fila['fecha_inicio']; ?>" readonly style="background: #e8f4fd;">
    </div>
    
    <div>
        <label>📅 Fecha Fin:</label>
        <input type="text" value="<?php echo $fila['fecha_fin']; ?>" readonly style="background: #e8f4fd;">
    </div>
    
    <div>
        <label>📊 Días Solicitados:</label>
        <input type="text" value="<?php echo $fila['dias_solicitados']; ?> días" readonly style="background: #e8f4fd;">
    </div>
    
    <div>
        <label>📈 Días Disponibles:</label>
        <input type="text" value="<?php echo $fila['dias_disponibles']; ?> días" readonly style="background: #e8f4fd;">
    </div>
</div>

<?php if (!empty($fila['motivo'])): ?>
<div style="margin-top: 15px;">
    <label>📝 Motivo del empleado:</label>
    <textarea readonly style="width: 100%; background: #f8f9fa; padding: 10px; border-radius: 5px;" rows="3"><?php echo htmlspecialchars($fila['motivo']); ?></textarea>
</div>
<?php endif; ?>

<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
    <strong>⚠️ Atención Supervisor:</strong> Revise cuidadosamente los datos antes de proceder a la decisión.
</div>