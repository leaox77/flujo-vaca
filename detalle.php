<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$id = $_GET['id'] ?? 0;

// Obtener datos de la solicitud (RRHH solo si el usuario es rol rrhh)
$sql = "SELECT v.*, u.nombre AS empleado_nombre, u.usuario AS empleado_usuario,
               s.nombre AS supervisor_nombre,
               r.nombre AS rrhh_nombre
        FROM vacaciones v
        JOIN usuarios u ON v.empleado_id = u.id
        LEFT JOIN usuarios s ON v.supervisor_id = s.id
        LEFT JOIN usuarios r ON v.rrhh_id = r.id AND r.rol = 'rrhh'
        WHERE v.id = $id";
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

if (!$solicitud) {
    die("Solicitud no encontrada");
}

// Verificar permisos (solo el empleado dueño, su supervisor o RRHH pueden ver)
$puede_ver = false;
if ($_SESSION["rol"] == 'empleado' && $solicitud['empleado_id'] == $_SESSION["idusuario"]) {
    $puede_ver = true;
} elseif ($_SESSION["rol"] == 'supervisor') {
    $puede_ver = true;
} elseif ($_SESSION["rol"] == 'rrhh') {
    $puede_ver = true;
}

if (!$puede_ver) {
    die("No tiene permisos para ver esta solicitud");
}
echo '<div style="margin-top: 20px;">';
echo '<a href="index.php" class="btn">Volver al Inicio</a> ';
echo '<a href="ver_workflow.php?id=' . $id . '" class="btn" style="background: #28a745;">Ver en Workflow</a>';
echo '</div>';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detalle de Solicitud</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .detail-container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .status-aprobado { background: #d4edda; color: #155724; }
        .status-rechazado { background: #f8d7da; color: #721c24; }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .btn { padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <strong>Rol:</strong> <?php echo $_SESSION["rol"]; ?> |
        <a href="index.php">Volver al Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="detail-container">
        <h2>Detalle de Solicitud #<?php echo $solicitud['id']; ?></h2>
        
        <div class="card">
            <h3>Información de la Solicitud</h3>
            <p><strong>Estado:</strong> 
                <span class="status-<?php 
                    if (strpos($solicitud['estado'], 'aprobado') !== false) echo 'aprobado';
                    elseif (strpos($solicitud['estado'], 'rechazado') !== false) echo 'rechazado';
                    else echo 'pendiente';
                ?>">
                    <?php 
                        $estados = [
                            'pendiente' => '⏳ Pendiente de revisión por supervisor',
                            'aprobado_supervisor' => '✅ Aprobado por supervisor (pendiente RRHH)',
                            'rechazado_supervisor' => '❌ Rechazado por supervisor',
                            'aprobado_rrhh' => '✅✅ Aprobado definitivamente por RRHH',
                            'rechazado_rrhh' => '❌❌ Rechazado por RRHH',
                            'finalizado' => '🏁 Proceso finalizado'
                        ];
                        echo $estados[$solicitud['estado']] ?? $solicitud['estado'];
                    ?>
                </span>
            </p>
            <p><strong>Fecha de Solicitud:</strong> <?php echo $solicitud['fecha_solicitud']; ?></p>
            <p><strong>Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?></p>
        </div>
        
        <div class="card">
            <h3>Detalles de las Vacaciones</h3>
            <p><strong>Fecha Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
            <p><strong>Fecha Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
            <p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
            <p><strong>Días Disponibles:</strong> <?php echo $solicitud['dias_disponibles']; ?></p>
            <?php if ($solicitud['dias_descontar']): ?>
            <p><strong>Días Descontados:</strong> <?php echo $solicitud['dias_descontar']; ?></p>
            <?php endif; ?>
            <?php if (!empty($solicitud['motivo'])): ?>
            <p><strong>Motivo del empleado:</strong> <?php echo htmlspecialchars($solicitud['motivo']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($solicitud['supervisor_nombre'])): ?>
        <div class="card">
            <h3>Decisión del Supervisor</h3>
            <p><strong>Supervisor:</strong> <?php echo $solicitud['supervisor_nombre']; ?></p>
            <p><strong>Fecha de aprobación:</strong> <?php echo $solicitud['fecha_aprobacion_supervisor']; ?></p>
            <?php if (!empty($solicitud['comentarios_supervisor'])): ?>
            <p><strong>Comentarios:</strong> <?php echo htmlspecialchars($solicitud['comentarios_supervisor']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($solicitud['rrhh_nombre'])): ?>
        <div class="card">
            <h3>Decisión de RRHH</h3>
            <p><strong>Responsable RRHH:</strong> <?php echo $solicitud['rrhh_nombre']; ?></p>
            <p><strong>Fecha de verificación:</strong> <?php echo $solicitud['fecha_aprobacion_rrhh']; ?></p>
            <?php if (!empty($solicitud['comentarios_rrhh'])): ?>
            <p><strong>Comentarios RRHH:</strong> <?php echo htmlspecialchars($solicitud['comentarios_rrhh']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($solicitud['motivo_rechazo'])): ?>
        <div class="card status-rechazado">
            <h3>Motivo de Rechazo</h3>
            <p><?php echo htmlspecialchars($solicitud['motivo_rechazo']); ?></p>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="btn">Volver al Inicio</a>
        </div>
    </div>
    
</body>
</html>
