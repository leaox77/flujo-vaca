<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$cod_flujo = $_GET["cod_flujo"] ?? 'VAC';
$cod_proceso = $_GET["cod_proceso"] ?? 'P1';
$nrotramite = $_GET["nrotramite"] ?? null;

// Obtener información del proceso actual
$sql = "SELECT * FROM flujo WHERE codflujo='$cod_flujo' AND codproceso='$cod_proceso'";
$resultado = mysqli_query($con, $sql);
$fila = mysqli_fetch_array($resultado);

if (!$fila) {
    die("Proceso no encontrado");
}

$pantalla = $fila["pantalla"];
$rol_proceso = $fila["rol"];

// Si el proceso es del sistema, manejar automáticamente
if ($rol_proceso == 'system') {
    include "procesar_sistema.php";
    exit();
}

// Solo empleados pueden acceder a P1 y P5
if ($cod_proceso == 'P1' || $cod_proceso == 'P5') {
    if ($_SESSION["rol"] != 'empleado') {
        die("No tiene permisos para acceder a esta página");
    }
}

// Solo supervisores pueden acceder a P2
if ($cod_proceso == 'P2') {
    if ($_SESSION["rol"] != 'supervisor') {
        die("No tiene permisos para acceder a esta página");
    }
}

// Solo RRHH puede acceder a P3
if ($cod_proceso == 'P3') {
    if ($_SESSION["rol"] != 'rrhh') {
        die("No tiene permisos para acceder a esta página");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Vacaciones</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .form-container { max-width: 600px; border: 1px solid #ccc; padding: 20px; border-radius: 5px; margin: 0 auto; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0; box-sizing: border-box; }
        button { padding: 10px 20px; margin-right: 10px; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> | 
        <strong>Rol:</strong> <?php echo $_SESSION["rol"]; ?> |
        <a href="index.php">Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="form-container">
        <?php include $pantalla . ".inc.php"; ?>
    </div>
</body>
</html>