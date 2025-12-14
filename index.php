<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Vacaciones</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .btn { padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-success { background: #28a745; }
        .card { border: 1px solid #ddd; padding: 20px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <strong>Rol:</strong> <?php echo $_SESSION["rol"]; ?> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <h2>Sistema de Gestión de Vacaciones</h2>
    
    <?php if ($_SESSION["rol"] == 'empleado'): ?>
    <div class="card">
        <h3>Nueva Solicitud</h3>
        <p>¿Desea solicitar vacaciones?</p>
        <a href="solicitud.php" class="btn btn-success">Crear Nueva Solicitud</a>
    </div>
    <?php endif; ?>
    
    <h3>Solicitudes Pendientes</h3>
    <?php
    // Consulta diferente según el rol
    if ($_SESSION["rol"] == 'empleado') {
        $sql = "SELECT v.* FROM vacaciones v 
                WHERE v.empleado_id = " . $_SESSION["idusuario"] . "
                AND v.estado IN ('pendiente', 'aprobado_supervisor')
                ORDER BY v.fecha_solicitud DESC";
    } elseif ($_SESSION["rol"] == 'supervisor') {
        $sql = "SELECT v.*, u.nombre as empleado_nombre 
                FROM vacaciones v 
                JOIN usuarios u ON v.empleado_id = u.id 
                WHERE v.estado = 'pendiente'
                ORDER BY v.fecha_solicitud DESC";
    } elseif ($_SESSION["rol"] == 'rrhh') {
        $sql = "SELECT v.*, u.nombre as empleado_nombre 
                FROM vacaciones v 
                JOIN usuarios u ON v.empleado_id = u.id 
                WHERE v.estado = 'aprobado_supervisor'
                ORDER BY v.fecha_solicitud DESC";
    }
    
    $resultado = mysqli_query($con, $sql);
    
    if (mysqli_num_rows($resultado) > 0):
    ?>
    <table>
        <tr>
            <th>N°</th>
            <?php if ($_SESSION["rol"] != 'empleado'): ?>
            <th>Empleado</th>
            <?php endif; ?>
            <th>Fecha Solicitud</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Días</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
        <?php while ($fila = mysqli_fetch_array($resultado)): 
            $estado_texto = [
                'pendiente' => 'Pendiente Supervisor',
                'aprobado_supervisor' => 'Pendiente RRHH',
                'aprobado_rrhh' => 'Aprobado',
                'rechazado_supervisor' => 'Rechazado por Supervisor',
                'rechazado_rrhh' => 'Rechazado por RRHH'
            ];
        ?>
        <tr>
            <td><?php echo $fila["id"]; ?></td>
            <?php if ($_SESSION["rol"] != 'empleado'): ?>
            <td><?php echo $fila["empleado_nombre"]; ?></td>
            <?php endif; ?>
            <td><?php echo $fila["fecha_solicitud"]; ?></td>
            <td><?php echo $fila["fecha_inicio"]; ?></td>
            <td><?php echo $fila["fecha_fin"]; ?></td>
            <td><?php echo $fila["dias_solicitados"]; ?></td>
            <td>
                <span style="
                    padding: 3px 8px;
                    border-radius: 3px;
                    background: <?php 
                        switch($fila["estado"]) {
                            case 'aprobado_rrhh': echo '#d4edda'; break;
                            case 'rechazado_supervisor':
                            case 'rechazado_rrhh': echo '#f8d7da'; break;
                            default: echo '#fff3cd';
                        }
                    ?>;
                    color: <?php 
                        switch($fila["estado"]) {
                            case 'aprobado_rrhh': echo '#155724'; break;
                            case 'rechazado_supervisor':
                            case 'rechazado_rrhh': echo '#721c24'; break;
                            default: echo '#856404';
                        }
                    ?>;
                ">
                    <?php echo $estado_texto[$fila["estado"]] ?? $fila["estado"]; ?>
                </span>
            </td>
            <td>
                <?php if ($_SESSION["rol"] == 'supervisor' && $fila["estado"] == 'pendiente'): ?>
                <a href="revisar.php?id=<?php echo $fila["id"]; ?>" class="btn">Revisar</a>
                <?php elseif ($_SESSION["rol"] == 'rrhh' && $fila["estado"] == 'aprobado_supervisor'): ?>
                <a href="verificar.php?id=<?php echo $fila["id"]; ?>" class="btn">Verificar</a>
                <?php else: ?>
                <a href="detalle.php?id=<?php echo $fila["id"]; ?>" class="btn">Ver Detalle</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No hay solicitudes pendientes.</p>
    <?php endif; ?>
    
    <h3>Historial de Solicitudes</h3>
    <?php
    $sql_historial = "SELECT v.* FROM vacaciones v 
                     WHERE v.empleado_id = " . $_SESSION["idusuario"] . "
                     ORDER BY v.fecha_solicitud DESC 
                     LIMIT 10";
    $resultado_historial = mysqli_query($con, $sql_historial);
    
    if (mysqli_num_rows($resultado_historial) > 0):
    ?>
    <table>
        <tr>
            <th>N°</th>
            <th>Fecha Solicitud</th>
            <th>Periodo</th>
            <th>Días</th>
            <th>Estado</th>
        </tr>
        <?php while ($fila = mysqli_fetch_array($resultado_historial)): ?>
        <tr>
            <td><?php echo $fila["id"]; ?></td>
            <td><?php echo $fila["fecha_solicitud"]; ?></td>
            <td><?php echo $fila["fecha_inicio"] . " al " . $fila["fecha_fin"]; ?></td>
            <td><?php echo $fila["dias_solicitados"]; ?></td>
            <td><?php echo $fila["estado"]; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No hay historial de solicitudes.</p>
    <?php endif; ?>
</body>
</html>
