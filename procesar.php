<?php
session_start();
include "conexion.inc.php";

$cod_flujo = $_POST["cod_flujo"];
$cod_proceso = $_POST["cod_proceso"];
$nrotramite = $_POST["nrotramite"];
$accion = $_POST["accion"];
$pantalla = $_POST["pantalla"];

// Obtener siguiente proceso según acción
$sql = "SELECT * FROM flujo WHERE codflujo='$cod_flujo' ";
if ($accion == 'siguiente') {
    $sql .= "AND codproceso='$cod_proceso'";
} elseif ($accion == 'anterior') {
    $sql .= "AND cod_procesosiguiente='$cod_proceso'";
} else {
    $sql .= "AND codproceso='$cod_proceso'";
}

$resultado = mysqli_query($con, $sql);
$fila = mysqli_fetch_array($resultado);

if ($accion == 'siguiente') {
    $proceso_siguiente = $fila["cod_procesosiguiente"];
} elseif ($accion == 'anterior') {
    $proceso_siguiente = $fila["codproceso"];
} else {
    $proceso_siguiente = 'P4'; // Para rechazos, ir a notificación
}

// Procesar según pantalla actual
switch ($pantalla) {
    case 'solicitud':
        if (!$nrotramite) {
            // Nueva solicitud
            $nrotramite = rand(1000, 9999);
            
            // Crear registro en vacaciones
            $sql_vac = "INSERT INTO vacaciones (id, empleado_id, fecha_inicio, fecha_fin, 
                        dias_solicitados, estado, dias_disponibles) 
                       VALUES ($nrotramite, " . $_SESSION["idusuario"] . ", 
                       '" . $_POST["fecha_inicio"] . "', '" . $_POST["fecha_fin"] . "', 
                       " . $_POST["dias_solicitados"] . ", 'pendiente', 
                       " . $_POST["dias_disponibles"] . ")";
            mysqli_query($con, $sql_vac);
        }
        break;
        
    case 'revision_supervisor':
        $decision = $_POST["decision_supervisor"];
        $estado = ($decision == 'aprobar') ? 'aprobado_supervisor' : 'rechazado_supervisor';
        
        $sql_vac = "UPDATE vacaciones SET 
                   estado = '$estado',
                   supervisor_id = " . $_SESSION["idusuario"] . ",
                   motivo_rechazo = '" . ($_POST["comentarios_supervisor"] ?? '') . "'
                   WHERE id = $nrotramite";
        mysqli_query($con, $sql_vac);
        break;
        
    case 'verificacion_rrhh':
        $decision = $_POST["decision_rrhh"];
        $estado = ($decision == 'aprobar') ? 'aprobado_rrhh' : 'rechazado_rrhh';
        
        $sql_vac = "UPDATE vacaciones SET 
                   estado = '$estado',
                   dias_descontar = " . $_POST["dias_descontar"] . ",
                   motivo_rechazo = '" . ($_POST["comentarios_rrhh"] ?? '') . "'
                   WHERE id = $nrotramite";
        mysqli_query($con, $sql_vac);
        break;
}

// Registrar en seguimiento
if ($accion != 'anterior') {
    $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
                VALUES ($nrotramite, '$cod_flujo', '$proceso_siguiente', NOW(), '" . $_SESSION["usuario"] . "')";
    mysqli_query($con, $sql_seg);
}

// Redirigir al siguiente proceso
header("Location: motor.php?cod_flujo=$cod_flujo&cod_proceso=$proceso_siguiente&nrotramite=$nrotramite");
exit();