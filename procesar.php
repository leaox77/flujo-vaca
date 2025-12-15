<?php
session_start();
include "conexion.inc.php";

$cod_flujo = $_GET["cod_flujo"] ?? 'VAC';
$cod_proceso = $_GET["cod_proceso"] ?? 'P1';
$nrotramite = isset($_GET["nrotramite"]) ? (int)$_GET["nrotramite"] : 0;
$accion = $_GET["accion"] ?? 'siguiente';
$pantalla = $_GET["pantalla"] ?? '';

if (!$nrotramite && $cod_proceso != 'P1') {
    die("Error: No se especificó número de trámite");
}

$sql_cerrar = "UPDATE seguimiento 
               SET fechafin = NOW() 
               WHERE nrotramite = $nrotramite 
                 AND flujo = '$cod_flujo' 
                 AND proceso = '$cod_proceso' 
                 AND fechafin IS NULL";
mysqli_query($con, $sql_cerrar);

$proceso_siguiente = null;

if ($accion == 'anterior') {
    $sql_ant = "SELECT * FROM flujo 
                WHERE codflujo='$cod_flujo' 
                AND cod_procesosiguiente='$cod_proceso'";
    $result_ant = mysqli_query($con, $sql_ant);
    
    if ($fila_ant = mysqli_fetch_array($result_ant)) {
        $proceso_siguiente = $fila_ant['codproceso'];
    } else {
        $proceso_siguiente = $cod_proceso; 
    }
} else {
    switch ($pantalla) {
        case 'solicitud':
            $fecha_inicio = $_GET["fecha_inicio"] ?? '';
            $fecha_fin = $_GET["fecha_fin"] ?? '';
            $dias_solicitados = (int)($_GET["dias_solicitados"] ?? 0);
            $motivo = $_GET["motivo"] ?? '';
            
            if ($fecha_inicio && $fecha_fin && $dias_solicitados > 0) {
                $sql_update = "UPDATE vacaciones SET 
                              fecha_inicio = '$fecha_inicio',
                              fecha_fin = '$fecha_fin',
                              dias_solicitados = $dias_solicitados,
                              motivo = '" . mysqli_real_escape_string($con, $motivo) . "'
                              WHERE id = $nrotramite";
                mysqli_query($con, $sql_update);
            }
            
            $proceso_siguiente = 'P4';
            break;
            
        case 'listado':
            $proceso_siguiente = 'P2a';
            break;
            
        case 'revision_supervisor':
            $decision = $_GET["decision_supervisor"] ?? '';
            $comentarios = $_GET["comentarios_supervisor"] ?? '';
            
            if ($decision == 'aprobar') {
                $estado = 'aprobado_supervisor';
                $motivo_rechazo = '';
                $proceso_siguiente = 'P3';
            } elseif ($decision == 'rechazar') {
                $estado = 'rechazado_supervisor';
                $motivo_rechazo = $comentarios;
                $proceso_siguiente = 'P5'; 
            } else {
                $estado = 'pendiente';
                $proceso_siguiente = $cod_proceso;
            }
            
            if ($decision) {
                $sql_update = "UPDATE vacaciones SET 
                              estado = '$estado',
                              supervisor_id = " . $_SESSION["idusuario"] . ",
                              motivo_rechazo = '" . mysqli_real_escape_string($con, $motivo_rechazo) . "',
                              comentarios_supervisor = '" . mysqli_real_escape_string($con, $comentarios) . "',
                              fecha_aprobacion_supervisor = NOW()
                              WHERE id = $nrotramite";
                mysqli_query($con, $sql_update);
            }
            break;
            
        case 'verificacion_rrhh':
            $decision = $_GET["decision_rrhh"] ?? '';
            $dias_descontar = (int)($_GET["dias_descontar"] ?? 0);
            $comentarios = $_GET["comentarios_rrhh"] ?? '';
            
            if ($decision == 'aprobar') {
                $estado = 'aprobado_rrhh';
                $motivo_rechazo = '';
                
                $sql_vac = "UPDATE vacaciones SET 
                           dias_disponibles = dias_disponibles - $dias_descontar,
                           dias_descontar = $dias_descontar
                           WHERE id = $nrotramite";
                mysqli_query($con, $sql_vac);
            } elseif ($decision == 'rechazar') {
                $estado = 'rechazado_rrhh';
                $motivo_rechazo = $comentarios;
            } else {
                $estado = 'aprobado_supervisor';
                $proceso_siguiente = $cod_proceso; 
                break;
            }
            
            $sql_update = "UPDATE vacaciones SET 
                          estado = '$estado',
                          rrhh_id = " . $_SESSION["idusuario"] . ",
                          motivo_rechazo = '" . mysqli_real_escape_string($con, $motivo_rechazo) . "',
                          comentarios_rrhh = '" . mysqli_real_escape_string($con, $comentarios) . "',
                          fecha_aprobacion_rrhh = NOW()
                          WHERE id = $nrotramite";
            mysqli_query($con, $sql_update);
            
            $proceso_siguiente = 'P5'; 
            break;
            
        case 'notificacion_inicial':
            $proceso_siguiente = 'P2';
            break;
            
        case 'notificacion_final':
            $sql_update = "UPDATE vacaciones SET estado = 'finalizado' WHERE id = $nrotramite";
            mysqli_query($con, $sql_update);
            $proceso_siguiente = null; 
            break;
            
        default:
            $sql_next = "SELECT cod_procesosiguiente FROM flujo 
                        WHERE codflujo='$cod_flujo' 
                        AND codproceso='$cod_proceso'";
            $result_next = mysqli_query($con, $sql_next);
            if ($fila_next = mysqli_fetch_array($result_next)) {
                $proceso_siguiente = $fila_next['cod_procesosiguiente'];
            }
    }
}

if ($proceso_siguiente) {
    $sql_next_info = "SELECT * FROM flujo 
                     WHERE codflujo='$cod_flujo' 
                     AND codproceso='$proceso_siguiente'";
    $result_next_info = mysqli_query($con, $sql_next_info);
    $next_info = mysqli_fetch_array($result_next_info);
    
    if ($next_info) {
        $usuario_siguiente = 'system';
        
        if ($next_info['rol'] != 'system') {
            if ($next_info['rol'] == 'supervisor') {
                $sql_supervisor = "SELECT u.usuario 
                                  FROM vacaciones v
                                  JOIN usuarios u ON v.empleado_id = u.supervisor_id
                                  WHERE v.id = $nrotramite
                                  LIMIT 1";
                $result_supervisor = mysqli_query($con, $sql_supervisor);
                if ($supervisor = mysqli_fetch_array($result_supervisor)) {
                    $usuario_siguiente = $supervisor['usuario'];
                } else {
                    $sql_supervisor_alt = "SELECT usuario FROM usuarios WHERE rol = 'supervisor' LIMIT 1";
                    $result_supervisor_alt = mysqli_query($con, $sql_supervisor_alt);
                    if ($supervisor_alt = mysqli_fetch_array($result_supervisor_alt)) {
                        $usuario_siguiente = $supervisor_alt['usuario'];
                    }
                }
            } elseif ($next_info['rol'] == 'rrhh') {
                $sql_rrhh = "SELECT usuario FROM usuarios WHERE rol = 'rrhh' LIMIT 1";
                $result_rrhh = mysqli_query($con, $sql_rrhh);
                if ($rrhh = mysqli_fetch_array($result_rrhh)) {
                    $usuario_siguiente = $rrhh['usuario'];
                }
            } elseif ($next_info['rol'] == 'empleado') {
                $sql_empleado = "SELECT u.usuario 
                                FROM vacaciones v
                                JOIN usuarios u ON v.empleado_id = u.id
                                WHERE v.id = $nrotramite
                                LIMIT 1";
                $result_empleado = mysqli_query($con, $sql_empleado);
                if ($empleado = mysqli_fetch_array($result_empleado)) {
                    $usuario_siguiente = $empleado['usuario'];
                }
            }
        }
        
        $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                   VALUES ($nrotramite, '$cod_flujo', '$proceso_siguiente', NOW(), '$usuario_siguiente', 'pendiente')";
        mysqli_query($con, $sql_seg);
        
        if ($usuario_siguiente == 'system' && $next_info['rol'] != 'system') {
            if ($next_info['rol'] == 'supervisor') {
                $sql_find_user = "SELECT usuario FROM usuarios WHERE rol = 'supervisor' LIMIT 1";
            } elseif ($next_info['rol'] == 'rrhh') {
                $sql_find_user = "SELECT usuario FROM usuarios WHERE rol = 'rrhh' LIMIT 1";
            } elseif ($next_info['rol'] == 'empleado') {
                $sql_find_user = "SELECT u.usuario 
                                 FROM vacaciones v
                                 JOIN usuarios u ON v.empleado_id = u.id
                                 WHERE v.id = $nrotramite
                                 LIMIT 1";
            }
            
            $result_find = mysqli_query($con, $sql_find_user);
            if ($user_found = mysqli_fetch_array($result_find)) {
                $sql_seg_extra = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario, estado) 
                                 VALUES ($nrotramite, '$cod_flujo', '$proceso_siguiente', NOW(), '{$user_found['usuario']}', 'pendiente')";
                mysqli_query($con, $sql_seg_extra);
            }
        }
    }
}

if ($proceso_siguiente) {
    header("Location: motor.php?cod_flujo=$cod_flujo&cod_proceso=$proceso_siguiente&nrotramite=$nrotramite");
} elseif ($accion == 'fin') {
    header("Location: index.php?msg=Proceso finalizado correctamente&id=$nrotramite");
} else {
    header("Location: index.php?msg=Acción completada");
}
exit();
?>