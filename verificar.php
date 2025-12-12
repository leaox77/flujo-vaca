<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 'rrhh') {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$id = $_GET['id'] ?? 0;

// Obtener datos de la solicitud
$sql = "SELECT v.*, u.nombre as empleado_nombre, u.usuario as empleado_usuario,
               s.nombre as supervisor_nombre
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        LEFT JOIN usuarios s ON v.supervisor_id = s.id
        WHERE v.id = $id AND v.estado = 'aprobado_supervisor'";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

if (!$solicitud) {
    die("Solicitud no encontrada o no está lista para verificación RRHH");
}

// Procesar la decisión
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $dias_descontar = $_POST['dias_descontar'];
    $comentarios = $_POST['comentarios'] ?? '';
    
    if ($decision == 'aprobar') {
        $estado = 'aprobado_rrhh';
        $motivo_rechazo = '';
    } else {
        $estado = 'rechazado_rrhh';
        $motivo_rechazo = $comentarios;
    }
    
    // Actualizar la solicitud
    $sql_update = "UPDATE vacaciones SET 
                   estado = '$estado',
                   dias_descontar = $dias_descontar,
                   motivo_rechazo = '" . mysqli_real_escape_string($con, $motivo_rechazo) . "',
                   comentarios_rrhh = '" . mysqli_real_escape_string($con, $comentarios) . "',
                   fecha_aprobacion_rrhh = NOW()
                   WHERE id = $id";
    
    $sql_fin = "UPDATE seguimiento SET fechafin = NOW() 
           WHERE nrotramite = $id AND flujo = 'VAC' 
           AND proceso = 'P3' AND fechafin IS NULL";
mysqli_query($con, $sql_fin);

// Luego, registrar P5 (notificación final)
$sql_seg = "UPDATE seguimiento SET 
            fechainicio = NOW(),
            usuario = 'system',
            fechafin = NULL
            WHERE nrotramite = $id AND flujo = 'VAC' AND proceso = 'P5'";
    
    if (mysqli_query($con, $sql_update)) {
        // Registrar en seguimiento para notificar al empleado
        $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
                   VALUES ($id, 'VAC', 'P5', NOW(), 'system')";
        mysqli_query($con, $sql_seg);
        
        header("Location: index.php?msg=Solicitud verificada correctamente");
        exit();
    } else {
        $error = "Error al procesar la solicitud: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verificación RRHH</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .solicitud-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>RRHH:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <a href="index.php">Volver al Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="form-container">
        <h2>Verificación RRHH - Solicitud de Vacaciones</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="solicitud-info">
            <h3>Detalles de la Solicitud</h3>
            <p><strong>N° Trámite:</strong> <?php echo $solicitud['id']; ?></p>
            <p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
            <p><strong>Supervisor que aprobó:</strong> <?php echo $solicitud['supervisor_nombre'] ?? 'No asignado'; ?></p>
            <p><strong>Fecha Solicitud:</strong> <?php echo $solicitud['fecha_solicitud']; ?></p>
            <p><strong>Fecha Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
            <p><strong>Fecha Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
            <p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
            <p><strong>Días Disponibles:</strong> <?php echo $solicitud['dias_disponibles']; ?></p>
            <?php if (!empty($solicitud['comentarios_supervisor'])): ?>
            <p><strong>Comentarios del Supervisor:</strong> <?php echo htmlspecialchars($solicitud['comentarios_supervisor']); ?></p>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="">
            <label for="decision">Decisión Final:</label>
            <select id="decision" name="decision" required onchange="toggleComentarios()">
                <option value="">-- Seleccione una opción --</option>
                <option value="aprobar">✅ Aprobar definitivamente</option>
                <option value="rechazar">❌ Rechazar solicitud</option>
            </select>
            
            <label for="dias_descontar">Días a descontar:</label>
            <input type="number" id="dias_descontar" name="dias_descontar" 
                   value="<?php echo $solicitud['dias_solicitados']; ?>"
                   min="1" max="<?php echo $solicitud['dias_disponibles']; ?>" required>
            
            <label for="comentarios">Comentarios RRHH:</label>
            <textarea id="comentarios" name="comentarios" rows="4" 
                      placeholder="Agregue comentarios de RRHH..."></textarea>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Confirmar Decisión Final</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
    function toggleComentarios() {
        const decision = document.getElementById('decision').value;
        const comentarios = document.getElementById('comentarios');
        
        if (decision === 'rechazar') {
            comentarios.placeholder = 'Explique el motivo del rechazo por parte de RRHH... (requerido)';
            comentarios.required = true;
        } else {
            comentarios.placeholder = 'Agregue comentarios de RRHH... (opcional)';
            comentarios.required = false;
        }
    }
    </script>
</body>
</html>