<?php
session_start();
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 'empleado') {
    header("Location: login.php");
    exit();
}

include "conexion.inc.php";

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $dias_solicitados = $_POST['dias_solicitados'];
    $motivo = $_POST['motivo'] ?? '';
    
    // Validar fechas
    if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $error = "La fecha de fin debe ser posterior a la fecha de inicio";
    } else {
        // Insertar en vacaciones (id autoincremental)
        $sql = "INSERT INTO vacaciones (empleado_id, fecha_inicio, fecha_fin, 
                dias_solicitados, motivo, estado, dias_disponibles, fecha_solicitud) 
               VALUES (" . $_SESSION["idusuario"] . ", 
               '$fecha_inicio', '$fecha_fin', $dias_solicitados, 
               '$motivo', 'pendiente', 30, NOW())";
        
        if (mysqli_query($con, $sql)) {
            $nrotramite = mysqli_insert_id($con);
            // Registrar en seguimiento
            $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
                       VALUES ($nrotramite, 'VAC', 'P4', NOW(), 'system')";
            mysqli_query($con, $sql_seg);
            
            // Redirigir a página de éxito
            header("Location: solicitud_exitosa.php?id=" . $nrotramite);
            exit();
        } else {
            $error = "Error al crear la solicitud: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nueva Solicitud de Vacaciones</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
        .form-container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        label { display: block; margin: 10px 0 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="user-info">
        <strong>Usuario:</strong> <?php echo $_SESSION["nombre"]; ?> |
        <a href="index.php">Volver al Inicio</a> |
        <a href="logout.php">Cerrar Sesión</a>
    </div>
    
    <div class="form-container">
        <h2>Nueva Solicitud de Vacaciones</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Información importante:</strong>
            <ul>
                <li>Tiene 30 días disponibles para vacaciones</li>
                <li>La solicitud será revisada por su supervisor</li>
                <li>Recibirá una notificación por correo cuando haya respuesta</li>
                <li>Puede revisar el estado en cualquier momento desde el inicio</li>
            </ul>
        </div>
        
        <form method="POST" action="">
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required 
                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            
            <label for="fecha_fin">Fecha de Fin:</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required>
            
            <label for="dias_solicitados">Días Solicitados:</label>
            <input type="number" id="dias_solicitados" name="dias_solicitados" 
                   min="1" max="30" required readonly>
            
            <label for="motivo">Motivo (Opcional):</label>
            <textarea id="motivo" name="motivo" rows="4" 
                      placeholder="Explique brevemente el motivo de su solicitud de vacaciones..."></textarea>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
    
    <script>
    // Calcular días automáticamente
    document.addEventListener('DOMContentLoaded', function() {
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const diasInput = document.getElementById('dias_solicitados');
        
        function calcularDias() {
            if (fechaInicio.value && fechaFin.value) {
                const inicio = new Date(fechaInicio.value);
                const fin = new Date(fechaFin.value);
                const diffTime = fin.getTime() - inicio.getTime();
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                diasInput.value = diffDays;
            }
        }
        
        fechaInicio.addEventListener('change', function() {
            if (fechaInicio.value) {
                fechaFin.min = fechaInicio.value;
                calcularDias();
            }
        });
        
        fechaFin.addEventListener('change', calcularDias);
    });
    </script>
</body>
</html>
