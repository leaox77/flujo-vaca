<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 'empleado') {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

$id = $_GET['id'] ?? 0;

$sql = "SELECT * FROM vacaciones WHERE id = $id AND empleado_id = " . $_SESSION["idusuario"];
$resultado = mysqli_query($con, $sql);
$solicitud = mysqli_fetch_array($resultado);

if (!$solicitud) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Solicitud Creada Exitosamente</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .success-box { max-width: 600px; margin: 50px auto; border: 1px solid #28a745; padding: 30px; border-radius: 10px; text-align: center; }
        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px; }
        .details { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: left; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <a href="index.php">Volver al Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>¡Solicitud Creada Exitosamente!</h1>
        <p>Su solicitud de vacaciones ha sido registrada correctamente.</p>
        
        <div class="details">
            <h3>Detalles de la Solicitud:</h3>
            <p><strong>Número de Trámite:</strong> <?php echo $solicitud['id']; ?></p>
            <p><strong>Fecha de Solicitud:</strong> <?php echo $solicitud['fecha_solicitud']; ?></p>
            <p><strong>Fecha de Inicio:</strong> <?php echo $solicitud['fecha_inicio']; ?></p>
            <p><strong>Fecha de Fin:</strong> <?php echo $solicitud['fecha_fin']; ?></p>
            <p><strong>Días Solicitados:</strong> <?php echo $solicitud['dias_solicitados']; ?></p>
            <p><strong>Estado Actual:</strong> Pendiente de revisión por supervisor</p>
        </div>
        
        <p>Se ha enviado una notificación a su supervisor para su revisión.</p>
        <p>Recibirá una respuesta en los próximos días hábiles.</p>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn">Ver Mis Solicitudes</a>
            <a href="solicitud.php" class="btn" style="background: #28a745;">Nueva Solicitud</a>
        </div>
    </div>
    
    <script>
    function imprimirComprobante() {
        window.print();
    }
    </script>
</body>
</html>