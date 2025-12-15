<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 'supervisor') {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$id = $_GET['id'] ?? 0;

$sql = "SELECT v.*, u.nombre as empleado_nombre, u.usuario as empleado_usuario
        FROM vacaciones v 
        JOIN usuarios u ON v.empleado_id = u.id 
        WHERE v.id = $id AND v.estado = 'pendiente'";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

if (!$solicitud) {
    die("Solicitud no encontrada o ya procesada");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $decision = $_POST['decision'];
    $comentarios = $_POST['comentarios'] ?? '';
    
    if ($decision == 'aprobar') {
        $estado = 'aprobado_supervisor';
        $motivo_rechazo = '';
    } else {
        $estado = 'rechazado_supervisor';
        $motivo_rechazo = $comentarios;
    }
    
    $sql_update = "UPDATE vacaciones SET 
                   estado = '$estado',
                   supervisor_id = " . $_SESSION["idusuario"] . ",
                   motivo_rechazo = '" . mysqli_real_escape_string($con, $motivo_rechazo) . "',
                   comentarios_supervisor = '" . mysqli_real_escape_string($con, $comentarios) . "',
                   fecha_aprobacion_supervisor = NOW()
                   WHERE id = $id";
    
    if (mysqli_query($con, $sql_update)) {
        $sql_fin = "UPDATE seguimiento SET fechafin = NOW() 
                   WHERE nrotramite = $id AND flujo = 'VAC' 
                   AND proceso = 'P4' AND fechafin IS NULL";
        mysqli_query($con, $sql_fin);
        
        if ($decision == 'aprobar') {
            $proceso_siguiente = 'P3';
        } else {
            $proceso_siguiente = 'P5';
        }
        
        $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
                   VALUES ($id, 'VAC', '$proceso_siguiente', NOW(), 'system')";
        mysqli_query($con, $sql_seg);
        
        header("Location: index.php?msg=Solicitud procesada correctamente");
        exit();
    } else {
        $error = "Error al procesar la solicitud: " . mysqli_error($con);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Revisar Solicitud de Vacaciones</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        .solicitud-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        select, textarea { width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Supervisor:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <a href="index.php">Volver al Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="form-container">
        <h2>Revisar Solicitud de Vacaciones</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="solicitud-info">
            <h3>Detalles de la Solicitud</h3>
            <p><strong>N° Trámite:</strong> <?php echo $solicitud['id']; ?></p>
            <p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?> (<?php echo $solicitud['empleado_usuario']; ?>)</p>
            <p><strong>Fecha Solicitud:</strong> <?php echo $solicitud['fecha_solicitud']; ?></p>
            <p><strong>Fecha Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
            <p><strong>Fecha Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
            <p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
            <p><strong>Días Disponibles:</strong> <?php echo $solicitud['dias_disponibles']; ?></p>
            <?php if (!empty($solicitud['motivo'])): ?>
            <p><strong>Motivo:</strong> <?php echo htmlspecialchars($solicitud['motivo']); ?></p>
            <?php endif; ?>
        </div>
        
        <form method="POST" action="">
            <label for="decision">Decisión:</label>
            <select id="decision" name="decision" required onchange="toggleComentarios()">
                <option value="">-- Seleccione una opción --</option>
                <option value="aprobar">✅ Aprobar solicitud</option>
                <option value="rechazar">❌ Rechazar solicitud</option>
            </select>
            
            <label for="comentarios">Comentarios:</label>
            <textarea id="comentarios" name="comentarios" rows="4" 
                      placeholder="Agregue comentarios sobre su decisión..."></textarea>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-success">Confirmar Decisión</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
    function toggleComentarios() {
        const decision = document.getElementById('decision').value;
        const comentarios = document.getElementById('comentarios');
        
        if (decision === 'rechazar') {
            comentarios.placeholder = 'Explique el motivo del rechazo... (requerido)';
            comentarios.required = true;
        } else {
            comentarios.placeholder = 'Agregue comentarios sobre su decisión... (opcional)';
            comentarios.required = false;
        }
    }
    </script>
</body>
</html>