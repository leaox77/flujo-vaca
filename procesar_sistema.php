<?php
// Maneja pasos automáticos del flujo (rol "system")
session_start();
include "conexion.inc.php";

$cod_flujo = $_GET["cod_flujo"] ?? 'VAC';
$cod_proceso = $_GET["cod_proceso"] ?? '';
$nrotramite = isset($_GET["nrotramite"]) ? (int) $_GET["nrotramite"] : 0;

if (!$nrotramite || !$cod_proceso) {
    header("Location: index.php");
    exit();
}

// Cierra el proceso actual en seguimiento si está abierto
$sql_cerrar = "UPDATE seguimiento 
               SET fechafin = NOW() 
               WHERE nrotramite = $nrotramite 
                 AND flujo = '$cod_flujo' 
                 AND proceso = '$cod_proceso' 
                 AND fechafin IS NULL";
mysqli_query($con, $sql_cerrar);

switch ($cod_proceso) {
    case 'P4': // notificación inicial -> pasa a supervisor (P2)
        $proceso_siguiente = 'P2';
        break;
    case 'P6': // procesamiento interno RRHH -> pasa a notificación final (P5)
        $proceso_siguiente = 'P5';
        break;
    default:
        // Si no se reconoce, volver al inicio
        header("Location: index.php");
        exit();
}

// Inserta el siguiente paso en seguimiento
$sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
            VALUES ($nrotramite, '$cod_flujo', '$proceso_siguiente', NOW(), 'system')";
mysqli_query($con, $sql_seg);

// Redirige al motor en el siguiente paso
header("Location: motor.php?cod_flujo=$cod_flujo&cod_proceso=$proceso_siguiente&nrotramite=$nrotramite");
exit();
