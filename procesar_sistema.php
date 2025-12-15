<?php
session_start();
include "conexion.inc.php";

$cod_flujo = $_GET["cod_flujo"] ?? 'VAC';
$cod_proceso = $_GET["cod_proceso"] ?? '';
$nrotramite = isset($_GET["nrotramite"]) ? (int) $_GET["nrotramite"] : 0;

if (!$nrotramite || !$cod_proceso) {
    header("Location: index.php");
    exit();
}

$sql_cerrar = "UPDATE seguimiento 
               SET fechafin = NOW() 
               WHERE nrotramite = $nrotramite 
                 AND flujo = '$cod_flujo' 
                 AND proceso = '$cod_proceso' 
                 AND fechafin IS NULL";
mysqli_query($con, $sql_cerrar);

switch ($cod_proceso) {
    case 'P4': 
        $proceso_siguiente = 'P2';
        break;
    case 'P6':
        $proceso_siguiente = 'P5';
        break;
    default:
        header("Location: index.php");
        exit();
}

$sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
            VALUES ($nrotramite, '$cod_flujo', '$proceso_siguiente', NOW(), 'system')";
mysqli_query($con, $sql_seg);

header("Location: motor.php?cod_flujo=$cod_flujo&cod_proceso=$proceso_siguiente&nrotramite=$nrotramite");
exit();
