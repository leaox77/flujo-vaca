<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$result_pendientes = null;

if ($_SESSION["rol"] == 'empleado') {
    $sql_pendientes = "SELECT DISTINCT s.*, v.*, f.pantalla
                      FROM seguimiento s
                      JOIN vacaciones v ON s.nrotramite = v.id
                      JOIN flujo f ON s.flujo = f.codflujo AND s.proceso = f.codproceso
                      WHERE v.empleado_id = " . $_SESSION["idusuario"] . "
                      AND s.fechafin IS NULL
                      AND s.usuario = '" . $_SESSION["usuario"] . "'
                      ORDER BY s.fechainicio DESC";
    
} elseif ($_SESSION["rol"] == 'supervisor') {
    $sql_pendientes = "SELECT DISTINCT s.*, v.*, f.pantalla, f.rol, u.nombre as empleado_nombre
                      FROM seguimiento s
                      JOIN vacaciones v ON s.nrotramite = v.id
                      JOIN flujo f ON s.flujo = f.codflujo AND s.proceso = f.codproceso
                      JOIN usuarios u ON v.empleado_id = u.id
                      WHERE s.fechafin IS NULL
                      AND f.rol = 'supervisor'
                      AND v.estado IN ('pendiente', 'aprobado_supervisor')
                      AND s.proceso IN ('P2', 'P2a')
                      ORDER BY s.fechainicio DESC";
    
} elseif ($_SESSION["rol"] == 'rrhh') {
    $sql_pendientes = "SELECT DISTINCT s.*, v.*, f.pantalla, f.rol, u.nombre as empleado_nombre
                      FROM seguimiento s
                      JOIN vacaciones v ON s.nrotramite = v.id
                      JOIN flujo f ON s.flujo = f.codflujo AND s.proceso = f.codproceso
                      JOIN usuarios u ON v.empleado_id = u.id
                      WHERE s.fechafin IS NULL
                      AND f.rol = 'rrhh'
                      AND v.estado IN ('aprobado_supervisor', 'aprobado_rrhh', 'rechazado_rrhh')
                      AND s.proceso = 'P3'
                      ORDER BY s.fechainicio DESC";
}

$result_pendientes = mysqli_query($con, $sql_pendientes);

if ($_SESSION["rol"] != 'empleado' && mysqli_num_rows($result_pendientes) == 0) {
    if ($_SESSION["rol"] == 'supervisor') {
        $sql_alternativa = "SELECT v.*, 'P2' as proceso, 'VAC' as flujo, 'listado' as pantalla,
                           u.nombre as empleado_nombre, NOW() as fechainicio
                           FROM vacaciones v
                           JOIN usuarios u ON v.empleado_id = u.id
                           WHERE v.estado = 'pendiente'
                           AND NOT EXISTS (
                               SELECT 1 FROM seguimiento s2 
                               WHERE s2.nrotramite = v.id 
                               AND s2.proceso IN ('P2', 'P2a')
                               AND s2.fechafin IS NULL
                           )
                           ORDER BY v.fecha_solicitud DESC";
    } else { // RRHH
        $sql_alternativa = "SELECT v.*, 'P3' as proceso, 'VAC' as flujo, 'verificacion_rrhh' as pantalla,
                           u.nombre as empleado_nombre, NOW() as fechainicio
                           FROM vacaciones v
                           JOIN usuarios u ON v.empleado_id = u.id
                           WHERE v.estado = 'aprobado_supervisor'
                           AND NOT EXISTS (
                               SELECT 1 FROM seguimiento s2 
                               WHERE s2.nrotramite = v.id 
                               AND s2.proceso = 'P3'
                               AND s2.fechafin IS NULL
                           )
                           ORDER BY v.fecha_solicitud DESC";
    }
    
    $result_alternativa = mysqli_query($con, $sql_alternativa);
    $result_pendientes = $result_alternativa;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Vacaciones - Workflow</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 15px; margin-bottom: 20px; 
            border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table { 
            border-collapse: collapse; 
            width: 100%; 
            margin: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background: linear-gradient(135deg, #4a6ee0 0%, #6a11cb 100%);
            color: white; 
            position: sticky;
            top: 0;
        }
        tr:hover { background-color: #f5f5f5; }
        .btn { 
            padding: 8px 15px; 
            background: #007bff; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            display: inline-block;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-success { 
            background: #28a745; 
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .btn-warning { 
            background: #ffc107; 
            color: #212529; 
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .card { 
            border: 1px solid #ddd; 
            padding: 25px; 
            margin: 15px 0; 
            border-radius: 8px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .status { 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-size: 0.85em;
            font-weight: bold;
            display: inline-block;
        }
        .status-pendiente { background: #fff3cd; color: #856404; }
        .status-aprobado { background: #d4edda; color: #155724; }
        .status-rechazado { background: #f8d7da; color: #721c24; }
        .status-finalizado { background: #cce5ff; color: #004085; }
        .section { 
            margin: 25px 0; 
            padding: 20px; 
            border: 1px solid #e9ecef; 
            border-radius: 8px;
            background: #f8f9fa;
        }
        .section h3 {
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }
        .rol-badge {
            background: #6f42c1;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>👤 Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> | 
        <strong>🎭 Rol:</strong> <span class="rol-badge"><?php echo $_SESSION["rol"]; ?></span> |
        <a href="index.php" style="color: white; text-decoration: underline; margin-left: 20px;">🏠 Inicio</a> |
        <a href="logout.php" style="color: white; text-decoration: underline; margin-left: 10px;">🚪 Salir</a>
    </div>
    
    <h2 style="color: #343a40;">🏢 Sistema de Workflow - Vacaciones</h2>
    
    <?php if ($_SESSION["rol"] == 'empleado'): ?>
    <div class="card">
        <h3 style="color: #28a745; margin-top: 0;">📋 Nueva Solicitud</h3>
        <p>Iniciar nuevo proceso de solicitud de vacaciones</p>
        <a href="motor.php?cod_flujo=VAC&cod_proceso=P1" class="btn btn-success">
            ➕ Iniciar Nueva Solicitud
        </a>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h3>
            🔄 Procesos Pendientes 
            <span style="font-size: 0.8em; color: #6c757d;">
                (<?php echo mysqli_num_rows($result_pendientes); ?> encontrados)
            </span>
        </h3>
        
        <?php if (mysqli_num_rows($result_pendientes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>N° Trámite</th>
                    <?php if ($_SESSION["rol"] != 'empleado'): ?>
                    <th>Empleado</th>
                    <?php endif; ?>
                    <th>Proceso</th>
                    <th>Pantalla</th>
                    <th>Inicio</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_array($result_pendientes)): 
                    $estado = $fila["estado"] ?? 'pendiente';
                    $clase = 'status-pendiente';
                    if (strpos($estado, 'aprobado') !== false) $clase = 'status-aprobado';
                    elseif (strpos($estado, 'rechazado') !== false) $clase = 'status-rechazado';
                    elseif ($estado == 'finalizado') $clase = 'status-finalizado';
                    
                    $proceso_actual = $fila["proceso"] ?? 'P1';
                ?>
                <tr>
                    <td>
                        <strong>#<?php echo $fila["nrotramite"] ?? $fila["id"]; ?></strong>
                    </td>
                    <?php if ($_SESSION["rol"] != 'empleado'): ?>
                    <td><?php echo $fila["empleado_nombre"] ?? 'N/A'; ?></td>
                    <?php endif; ?>
                    <td>
                        <strong><?php echo $proceso_actual; ?></strong> 
                        <span style="color: #6c757d; font-size: 0.9em;">
                            (<?php echo $fila["flujo"] ?? 'VAC'; ?>)
                        </span>
                    </td>
                    <td><?php echo $fila["pantalla"] ?? 'solicitud'; ?></td>
                    <td>
                        <?php 
                        if (isset($fila["fechainicio"]) && $fila["fechainicio"] != '0000-00-00 00:00:00') {
                            echo date('d/m/Y H:i', strtotime($fila["fechainicio"]));
                        } else {
                            echo 'Recién creado';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status <?php echo $clase; ?>">
                            <?php 
                            $estados_display = [
                                'pendiente' => '⏳ Pendiente',
                                'aprobado_supervisor' => '✅ Aprobado Supervisor',
                                'rechazado_supervisor' => '❌ Rechazado Supervisor',
                                'aprobado_rrhh' => '✅✅ Aprobado RRHH',
                                'rechazado_rrhh' => '❌❌ Rechazado RRHH',
                                'finalizado' => '🏁 Finalizado'
                            ];
                            echo $estados_display[$estado] ?? $estado;
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $url = "motor.php?cod_flujo=" . ($fila["flujo"] ?? 'VAC') . 
                               "&cod_proceso=" . $proceso_actual . 
                               "&nrotramite=" . ($fila["nrotramite"] ?? $fila["id"]);
                        ?>
                        <a href="<?php echo $url; ?>" class="btn">
                            ▶️ Continuar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
            <h4 style="color: #6c757d;">No hay procesos pendientes</h4>
            <p>No se encontraron solicitudes que requieran su atención en este momento.</p>
            <?php if ($_SESSION["rol"] == 'empleado'): ?>
            <a href="motor.php?cod_flujo=VAC&cod_proceso=P1" class="btn btn-success" style="margin-top: 15px;">
                Crear mi primera solicitud
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h3>📊 Historial de Solicitudes</h3>
        <?php
        // Historial para el usuario actual
        if ($_SESSION["rol"] == 'empleado') {
            $sql_historial = "SELECT v.*, 
                             (SELECT proceso FROM seguimiento 
                              WHERE nrotramite = v.id 
                              ORDER BY fechainicio DESC LIMIT 1) as ultimo_proceso,
                             u.nombre as empleado_nombre
                             FROM vacaciones v 
                             JOIN usuarios u ON v.empleado_id = u.id
                             WHERE v.empleado_id = " . $_SESSION["idusuario"] . "
                             ORDER BY v.fecha_solicitud DESC 
                             LIMIT 10";
        } else {
            $sql_historial = "SELECT v.*, 
                             (SELECT proceso FROM seguimiento 
                              WHERE nrotramite = v.id 
                              ORDER BY fechainicio DESC LIMIT 1) as ultimo_proceso,
                             u.nombre as empleado_nombre
                             FROM vacaciones v 
                             JOIN usuarios u ON v.empleado_id = u.id
                             ORDER BY v.fecha_solicitud DESC 
                             LIMIT 15";
        }
        
        $result_historial = mysqli_query($con, $sql_historial);
        
        if (mysqli_num_rows($result_historial) > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Periodo</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Último Proceso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = mysqli_fetch_array($result_historial)): 
                    $estado = $fila["estado"];
                    $clase = 'status-pendiente';
                    if (strpos($estado, 'aprobado') !== false) $clase = 'status-aprobado';
                    elseif (strpos($estado, 'rechazado') !== false) $clase = 'status-rechazado';
                    elseif ($estado == 'finalizado') $clase = 'status-finalizado';
                ?>
                <tr>
                    <td><strong>#<?php echo $fila["id"]; ?></strong></td>
                    <td><?php echo date('d/m/Y', strtotime($fila["fecha_solicitud"])); ?></td>
                    <td><?php echo $fila["empleado_nombre"]; ?></td>
                    <td>
                        <?php 
                        echo date('d/m/Y', strtotime($fila["fecha_inicio"])) . " - " . 
                             date('d/m/Y', strtotime($fila["fecha_fin"])); 
                        ?>
                    </td>
                    <td><?php echo $fila["dias_solicitados"]; ?> días</td>
                    <td>
                        <span class="status <?php echo $clase; ?>">
                            <?php 
                            $estados_display = [
                                'pendiente' => '⏳ Pendiente',
                                'aprobado_supervisor' => '✅ Aprobado Supervisor',
                                'rechazado_supervisor' => '❌ Rechazado Supervisor',
                                'aprobado_rrhh' => '✅✅ Aprobado RRHH',
                                'rechazado_rrhh' => '❌❌ Rechazado RRHH',
                                'finalizado' => '🏁 Finalizado'
                            ];
                            echo $estados_display[$estado] ?? $estado;
                            ?>
                        </span>
                    </td>
                    <td>
                        <span style="background: #e9ecef; padding: 3px 8px; border-radius: 3px; font-size: 0.9em;">
                            <?php echo $fila["ultimo_proceso"] ?? 'N/A'; ?>
                        </span>
                    </td>
                    <td>
                        <a href="detalle.php?id=<?php echo $fila["id"]; ?>" class="btn" style="padding: 6px 12px; margin-right: 5px;">
                            👁️ Ver
                        </a>
                        <a href="ver_workflow.php?id=<?php echo $fila["id"]; ?>" class="btn btn-warning" style="padding: 6px 12px;">
                            🔄 Workflow
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 48px; margin-bottom: 20px;">📋</div>
            <h4 style="color: #6c757d;">No hay historial de solicitudes</h4>
            <p>Aún no se han registrado solicitudes en el sistema.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <p style="color: #6c757d; margin: 0;">
            <strong>Sistema de Workflow de Vacaciones</strong> | 
            Usuario: <?php echo $_SESSION["usuario"]; ?> | 
            Total solicitudes: <?php echo mysqli_num_rows($result_historial ?? []); ?>
        </p>
    </div>
</body>
</html>