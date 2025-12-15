<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

// Obtener parámetros
$cod_flujo = $_GET["cod_flujo"] ?? 'VAC';
$cod_proceso = $_GET["cod_proceso"] ?? 'P1';
$nrotramite = isset($_GET["nrotramite"]) ? (int)$_GET["nrotramite"] : 0;

// Obtener información del proceso actual
$sql_flujo = "SELECT * FROM flujo WHERE codflujo='$cod_flujo' AND codproceso='$cod_proceso'";
$result_flujo = mysqli_query($con, $sql_flujo);
$fila_flujo = mysqli_fetch_array($result_flujo);

if (!$fila_flujo) {
    die("❌ Proceso no encontrado: $cod_flujo - $cod_proceso");
}

$pantalla = $fila_flujo["pantalla"];
$rol_proceso = $fila_flujo["rol"];
$proceso_siguiente = $fila_flujo["cod_procesosiguiente"];

// ========== MANEJO DE NUEVOS TRÁMITES ==========
if ($nrotramite == 0 && $cod_proceso == 'P1') {
    // Crear nueva solicitud en vacaciones con valores temporales
    $sql_vac = "INSERT INTO vacaciones (empleado_id, estado, fecha_solicitud, dias_disponibles, 
                fecha_inicio, fecha_fin, dias_solicitados) 
                VALUES (" . $_SESSION["idusuario"] . ", 'pendiente', NOW(), 30, 
                CURDATE(), CURDATE(), 0)";
    if (!mysqli_query($con, $sql_vac)) {
        die("Error al crear solicitud: " . mysqli_error($con));
    }
    $nrotramite = mysqli_insert_id($con);
    
    // Registrar primer paso en seguimiento (P1)
    $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                VALUES ($nrotramite, '$cod_flujo', '$cod_proceso', NOW(), '" . $_SESSION["usuario"] . "', 'pendiente')";
    mysqli_query($con, $sql_seg);
    
    // También registrar proceso P4 (sistema) para que aparezca en seguimiento
    $sql_seg_p4 = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                  VALUES ($nrotramite, '$cod_flujo', 'P4', NOW(), 'system', 'pendiente')";
    mysqli_query($con, $sql_seg_p4);
}

// ========== VERIFICACIÓN DE PERMISOS ==========
if ($nrotramite > 0) {
    if ($rol_proceso == 'system') {
        // Procesos del sistema no requieren verificación
    } elseif ($rol_proceso == $_SESSION["rol"]) {
        // Verificar si ya existe un registro en seguimiento para este proceso
        $sql_check = "SELECT * FROM seguimiento 
                     WHERE nrotramite = $nrotramite 
                     AND flujo = '$cod_flujo' 
                     AND proceso = '$cod_proceso'
                     AND fechafin IS NULL";
        $result_check = mysqli_query($con, $sql_check);
        
        if (mysqli_num_rows($result_check) == 0) {
            // Verificar si hay algún registro anterior en seguimiento
            $sql_prev = "SELECT * FROM seguimiento 
                        WHERE nrotramite = $nrotramite 
                        AND flujo = '$cod_flujo'
                        ORDER BY fechainicio DESC LIMIT 1";
            $result_prev = mysqli_query($con, $sql_prev);
            
            if (mysqli_num_rows($result_prev) > 0) {
                // Hay registros anteriores, crear nuevo para este usuario
                $sql_seg_new = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                               VALUES ($nrotramite, '$cod_flujo', '$cod_proceso', NOW(), '" . $_SESSION["usuario"] . "', 'pendiente')";
                mysqli_query($con, $sql_seg_new);
            } else {
                // No hay registros, pero el usuario tiene el rol correcto
                // Crear registro para que pueda acceder
                $sql_seg_new = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                               VALUES ($nrotramite, '$cod_flujo', '$cod_proceso', NOW(), '" . $_SESSION["usuario"] . "', 'pendiente')";
                mysqli_query($con, $sql_seg_new);
            }
        }
    } else {
        // Verificar si el usuario puede acceder aunque no tenga registro directo
        // Esto pasa cuando un supervisor/RRHH entra por primera vez a un trámite
        $sql_vac_check = "SELECT v.* FROM vacaciones v WHERE v.id = $nrotramite";
        $result_vac_check = mysqli_query($con, $sql_vac_check);
        $vac_check = mysqli_fetch_array($result_vac_check);
        
        if ($vac_check) {
            $puede_acceder = false;
            
            if ($_SESSION["rol"] == 'supervisor' && $cod_proceso == 'P2' && $vac_check['estado'] == 'pendiente') {
                $puede_acceder = true;
            } elseif ($_SESSION["rol"] == 'rrhh' && $cod_proceso == 'P3' && $vac_check['estado'] == 'aprobado_supervisor') {
                $puede_acceder = true;
            }
            
            if ($puede_acceder) {
                // Crear registro en seguimiento para este usuario
                $sql_seg_new = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                               VALUES ($nrotramite, '$cod_flujo', '$cod_proceso', NOW(), '" . $_SESSION["usuario"] . "', 'pendiente')";
                mysqli_query($con, $sql_seg_new);
            } else {
                die("❌ No tiene permisos para acceder a este proceso. Rol requerido: $rol_proceso<br>
                    Estado actual del trámite: " . $vac_check['estado']);
            }
        } else {
            die("❌ No tiene permisos para acceder a este proceso. Rol requerido: $rol_proceso");
        }
    }
}

// ========== OBTENER DATOS DE LA SOLICITUD ==========
$solicitud = null;
if ($nrotramite > 0) {
    $sql_solicitud = "SELECT v.*, u.nombre as empleado_nombre 
                     FROM vacaciones v 
                     JOIN usuarios u ON v.empleado_id = u.id 
                     WHERE v.id = $nrotramite";
    $result_solicitud = mysqli_query($con, $sql_solicitud);
    $solicitud = mysqli_fetch_array($result_solicitud);
}

// ========== OBTENER PROCESO ANTERIOR ==========
$proceso_anterior = null;
if ($nrotramite > 0) {
    $sql_ant = "SELECT * FROM flujo 
               WHERE codflujo='$cod_flujo' 
               AND cod_procesosiguiente='$cod_proceso'";
    $result_ant = mysqli_query($con, $sql_ant);
    if (mysqli_num_rows($result_ant) > 0) {
        $fila_ant = mysqli_fetch_array($result_ant);
        $proceso_anterior = $fila_ant['codproceso'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Workflow - <?php echo $pantalla; ?></title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-container { 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .info-box { 
            background: #e3f2fd; 
            border-left: 4px solid #2196F3; 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 5px;
        }
        .nav-buttons { 
            margin-top: 30px; 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 8px;
            border-top: 2px solid #e9ecef;
            text-align: center;
        }
        .btn { 
            padding: 12px 25px; 
            margin: 0 10px; 
            cursor: pointer; 
            font-weight: bold;
            border: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .btn-primary { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
        }
        .btn-secondary { 
            background: #6c757d; 
            color: white; 
        }
        .btn-success { 
            background: linear-gradient(135deg, #42e695 0%, #3bb2b8 100%);
            color: white; 
        }
        .btn-danger { 
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white; 
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #4a6ee0;
            padding-bottom: 10px;
            margin-top: 0;
        }
        .tramite-info {
            background: #f0f7ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>👤 Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> | 
        <strong>🎭 Rol:</strong> <?php echo $_SESSION["rol"]; ?> |
        <strong>🔢 Trámite:</strong> #<?php echo $nrotramite ?: 'Nuevo'; ?> |
        <strong>📋 Proceso:</strong> <?php echo $cod_proceso; ?> |
        <a href="index.php" style="color: white; text-decoration: underline;">🏠 Inicio</a> |
        <a href="logout.php" style="color: white; text-decoration: underline;">🚪 Salir</a>
    </div>
    
    <div class="form-container">
        <div class="info-box">
            <strong>🔄 Flujo:</strong> <?php echo $cod_flujo; ?> | 
            <strong>📱 Pantalla:</strong> <?php echo $pantalla; ?> |
            <strong>👥 Rol Requerido:</strong> <?php echo $rol_proceso; ?>
            <?php if ($solicitud): ?>
            | <strong>👤 Empleado:</strong> <?php echo $solicitud['empleado_nombre']; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($nrotramite): ?>
        <div class="tramite-info">
            <strong>📅 Fecha Solicitud:</strong> <?php echo $solicitud['fecha_solicitud'] ?? 'Nueva'; ?> |
            <strong>📊 Estado Actual:</strong> 
            <span style="background: <?php 
                $estado_color = '#fff3cd';
                if (strpos($solicitud['estado'] ?? '', 'aprobado') !== false) $estado_color = '#d4edda';
                if (strpos($solicitud['estado'] ?? '', 'rechazado') !== false) $estado_color = '#f8d7da';
                echo $estado_color;
            ?>; padding: 3px 8px; border-radius: 3px;">
                <?php echo $solicitud['estado'] ?? 'pendiente'; ?>
            </span>
        </div>
        <?php endif; ?>
        
        <form action="procesar.php" method="GET">
            <input type="hidden" name="cod_flujo" value="<?php echo $cod_flujo; ?>">
            <input type="hidden" name="cod_proceso" value="<?php echo $cod_proceso; ?>">
            <input type="hidden" name="nrotramite" value="<?php echo $nrotramite; ?>">
            <input type="hidden" name="pantalla" value="<?php echo $pantalla; ?>">
            
            <?php 
            // Incluir la pantalla correspondiente
            if (file_exists($pantalla . ".inc.php")) {
                include $pantalla . ".inc.php";
            } else {
                echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px;'>";
                echo "<h3>⚠️ Pantalla no encontrada</h3>";
                echo "<p>No se encontró el archivo: <strong>{$pantalla}.inc.php</strong></p>";
                echo "</div>";
            }
            ?>
            
            <div class="nav-buttons">
                <?php if ($proceso_anterior): ?>
                <button type="submit" name="accion" value="anterior" class="btn btn-secondary">
                    ← Volver a <?php echo $proceso_anterior; ?>
                </button>
                <?php endif; ?>
                
                <?php if ($cod_proceso != 'P5'): ?>
                <button type="submit" name="accion" value="siguiente" class="btn btn-primary">
                    Continuar a <?php echo $proceso_siguiente ?? 'Siguiente'; ?> →
                </button>
                <?php else: ?>
                <button type="submit" name="accion" value="fin" class="btn btn-success">
                    ✅ Finalizar Proceso
                </button>
                <?php endif; ?>
                
                <a href="index.php" class="btn btn-danger">
                    ❌ Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>