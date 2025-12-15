<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql_tramite = "SELECT v.*, u.nombre as empleado_nombre 
               FROM vacaciones v 
               JOIN usuarios u ON v.empleado_id = u.id 
               WHERE v.id = $id";
$result_tramite = mysqli_query($con, $sql_tramite);
$tramite = mysqli_fetch_array($result_tramite);

if (!$tramite) {
    die("Trámite no encontrado");
}

$sql = "SELECT s.*, f.pantalla, f.rol, f.cod_procesosiguiente 
        FROM seguimiento s 
        LEFT JOIN flujo f ON s.flujo = f.codflujo AND s.proceso = f.codproceso
        WHERE s.nrotramite = $id 
        ORDER BY s.fechainicio";
$resultado = mysqli_query($con, $sql);
$total_pasos = mysqli_num_rows($resultado);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workflow - Trámite #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .user-info { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 15px; margin-bottom: 20px; 
            border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .container { 
            max-width: 1000px; margin: 0 auto; 
        }
        .header-card {
            background: white; padding: 20px; border-radius: 8px;
            margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .timeline-container {
            background: white; padding: 30px; border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .step { 
            display: flex; align-items: stretch; margin: 0; 
            position: relative; min-height: 100px;
        }
        .step-line {
            position: absolute; left: 30px; top: 0; bottom: 0;
            width: 4px; background: #e0e0e0; z-index: 1;
        }
        .step:last-child .step-line { display: none; }
        .step-circle { 
            width: 60px; height: 60px; border-radius: 50%; 
            background: #6c757d; color: white; display: flex; 
            align-items: center; justify-content: center; 
            margin-right: 20px; z-index: 2; position: relative;
            font-size: 20px; font-weight: bold; border: 4px solid white;
            box-shadow: 0 0 0 4px #e0e0e0;
        }
        .step.completed .step-circle { 
            background: #28a745; box-shadow: 0 0 0 4px #d4edda;
        }
        .step.active .step-circle { 
            background: #007bff; box-shadow: 0 0 0 4px #cce5ff;
            animation: pulse 2s infinite;
        }
        .step-content { 
            flex: 1; border: 2px solid #e0e0e0; padding: 20px; 
            border-radius: 8px; margin-bottom: 30px; background: white;
            transition: all 0.3s ease;
        }
        .step.completed .step-content { 
            border-color: #d4edda; background: #f8fff9;
        }
        .step.active .step-content { 
            border-color: #007bff; background: #f0f7ff;
            transform: translateX(5px);
        }
        .step-info { display: flex; justify-content: space-between; }
        .step-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .step-details { color: #666; font-size: 14px; }
        .badge { 
            padding: 4px 8px; border-radius: 4px; font-size: 12px; 
            font-weight: bold; margin-left: 10px;
        }
        .badge-rol { background: #6f42c1; color: white; }
        .badge-completed { background: #28a745; color: white; }
        .badge-active { background: #007bff; color: white; }
        .badge-pending { background: #ffc107; color: #212529; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }
        .status-indicator {
            display: inline-block; width: 10px; height: 10px;
            border-radius: 50%; margin-right: 5px;
        }
        .status-completed { background: #28a745; }
        .status-active { background: #007bff; }
        .status-pending { background: #ffc107; }
        .btn-back { 
            background: #6c757d; color: white; padding: 10px 20px;
            text-decoration: none; border-radius: 5px; display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>👤 Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <a href="index.php" style="color: white; text-decoration: underline;">🏠 Inicio</a> |
        <a href="detalle.php?id=<?php echo $id; ?>" style="color: white; text-decoration: underline;">📋 Ver Detalle</a>
    </div>
    
    <div class="container">
        <div class="header-card">
            <h2 style="margin-top: 0;">🔄 Workflow del Trámite #<?php echo $id; ?></h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p><strong>👤 Empleado:</strong> <?php echo $tramite['empleado_nombre']; ?></p>
                    <p><strong>📅 Fecha Solicitud:</strong> <?php echo $tramite['fecha_solicitud']; ?></p>
                    <p><strong>📊 Estado:</strong> 
                        <span style="padding: 4px 8px; border-radius: 4px; background: <?php
                            echo ($tramite['estado'] == 'aprobado_rrhh') ? '#d4edda' :
                                 (strpos($tramite['estado'], 'rechazado') !== false ? '#f8d7da' : '#fff3cd');
                        ?>;">
                            <?php echo $tramite['estado']; ?>
                        </span>
                    </p>
                </div>
                <div>
                    <p><strong>📅 Periodo:</strong> <?php echo $tramite['fecha_inicio']; ?> al <?php echo $tramite['fecha_fin']; ?></p>
                    <p><strong>📊 Días:</strong> <?php echo $tramite['dias_solicitados']; ?> días solicitados</p>
                    <p><strong>✅ Progreso:</strong> <?php echo $total_pasos; ?> pasos en el flujo</p>
                </div>
            </div>
        </div>
        
        <div class="timeline-container">
            <h3 style="margin-top: 0; margin-bottom: 30px; color: #333;">📈 Progreso del Flujo</h3>
            
            <?php 
            $counter = 1;
            mysqli_data_seek($resultado, 0);
            while ($fila = mysqli_fetch_array($resultado)): 
                $is_completed = !empty($fila['fechafin']);
                $is_active = !$is_completed && $counter == $total_pasos;
                $clase = '';
                if ($is_completed) $clase = 'completed';
                elseif ($is_active) $clase = 'active';
            ?>
            <div class="step <?php echo $clase; ?>">
                <div class="step-line"></div>
                <div class="step-circle">
                    <?php echo $counter; ?>
                </div>
                <div class="step-content">
                    <div class="step-info">
                        <div>
                            <div class="step-title">
                                <?php echo $fila['proceso']; ?> - <?php echo $fila['pantalla']; ?>
                                <span class="badge badge-rol"><?php echo $fila['rol']; ?></span>
                                <?php if ($is_completed): ?>
                                <span class="badge badge-completed">Completado</span>
                                <?php elseif ($is_active): ?>
                                <span class="badge badge-active">Activo</span>
                                <?php else: ?>
                                <span class="badge badge-pending">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <div class="step-details">
                                <p>
                                    <span class="status-indicator status-<?php echo $is_completed ? 'completed' : ($is_active ? 'active' : 'pending'); ?>"></span>
                                    <strong>Usuario:</strong> <?php echo $fila['usuario']; ?>
                                </p>
                                <p><strong>Inicio:</strong> <?php echo $fila['fechainicio']; ?></p>
                                <?php if ($is_completed): ?>
                                <p><strong>Fin:</strong> <?php echo $fila['fechafin']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($fila['datos'])): ?>
                                <p><strong>Datos:</strong> <?php echo htmlspecialchars($fila['datos']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 24px;">
                                <?php 
                                if ($fila['proceso'] == 'P1') echo '📝';
                                elseif ($fila['proceso'] == 'P2') echo '👤';
                                elseif ($fila['proceso'] == 'P2a') echo '👨‍💼';
                                elseif ($fila['proceso'] == 'P3') echo '🏢';
                                elseif ($fila['proceso'] == 'P4') echo '🔄';
                                elseif ($fila['proceso'] == 'P5') echo '✅';
                                else echo '📋';
                                ?>
                            </div>
                            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                Paso <?php echo $counter; ?> de <?php echo $total_pasos; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
            $counter++;
            endwhile; 
            ?>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="detalle.php?id=<?php echo $id; ?>" class="btn-back">📋 Ver Detalle Completo</a>
                <a href="index.php" class="btn-back" style="background: #007bff;">🏠 Volver al Inicio</a>
            </div>
        </div>
    </div>
</body>
</html>