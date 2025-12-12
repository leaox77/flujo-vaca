<?php
include "conexion.inc.php";

// Verificar que solo empleados puedan ver esto
if ($_SESSION["rol"] != 'empleado') {
    die("Acceso no autorizado");
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $dias_solicitados = $_POST['dias_solicitados'];
    $motivo = $_POST['motivo'] ?? '';
    
    // Validar que la fecha fin sea posterior a la fecha inicio
    if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
        $error = "La fecha de fin debe ser posterior a la fecha de inicio";
    } else {
        // Generar número de trámite único
        $nrotramite = rand(1000, 9999);
        
        // Verificar que no exista ya ese ID
        $sql_check = "SELECT id FROM vacaciones WHERE id = $nrotramite";
        $result_check = mysqli_query($con, $sql_check);
        while (mysqli_num_rows($result_check) > 0) {
            $nrotramite = rand(1000, 9999);
            $result_check = mysqli_query($con, "SELECT id FROM vacaciones WHERE id = $nrotramite");
        }
        
        // Insertar en vacaciones
        $sql = "INSERT INTO vacaciones (id, empleado_id, fecha_inicio, fecha_fin, 
                dias_solicitados, motivo, estado, dias_disponibles, fecha_solicitud) 
               VALUES ($nrotramite, " . $_SESSION["idusuario"] . ", 
               '$fecha_inicio', '$fecha_fin', $dias_solicitados, 
               '" . mysqli_real_escape_string($con, $motivo) . "', 
               'pendiente', 30, NOW())";
        
        if (mysqli_query($con, $sql)) {
            // Registrar en seguimiento
            $sql_seg = "INSERT INTO seguimiento (nrotramite, flujo, proceso, fechainicio, usuario) 
                       VALUES ($nrotramite, 'VAC', 'P4', NOW(), 'system')";
            mysqli_query($con, $sql_seg);
            
            // Mostrar mensaje de éxito
            echo '<div class="success">
                    <h3>¡Solicitud Creada Exitosamente!</h3>
                    <p><strong>Número de trámite:</strong> ' . $nrotramite . '</p>
                    <p><strong>Fecha inicio:</strong> ' . $fecha_inicio . '</p>
                    <p><strong>Fecha fin:</strong> ' . $fecha_fin . '</p>
                    <p><strong>Días solicitados:</strong> ' . $dias_solicitados . '</p>';
            
            if (!empty($motivo)) {
                echo '<p><strong>Motivo:</strong> ' . htmlspecialchars($motivo) . '</p>';
            }
            
            echo '<p>Su solicitud ha sido enviada a su supervisor para revisión.</p>
                  <p>Recibirá una notificación cuando haya una respuesta.</p>
                  </div>';
            echo '<br><a href="index.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Volver al Inicio</a>';
            exit();
        } else {
            $error = "Error al crear la solicitud: " . mysqli_error($con);
        }
    }
}

// Si hay error, mostrarlo
if (isset($error)) {
    echo '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin: 10px 0;">' . $error . '</div>';
}
?>

<h2>Nueva Solicitud de Vacaciones</h2>
<form method="POST" action="">
    <label>Fecha de Inicio:</label>
    <input type="date" name="fecha_inicio" required 
           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
           value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
    
    <label>Fecha de Fin:</label>
    <input type="date" name="fecha_fin" required 
           min="<?php echo date('Y-m-d', strtotime('+2 days')); ?>"
           value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
    
    <label>Días Solicitados:</label>
    <input type="number" name="dias_solicitados" min="1" max="30" required value="1">
    
    <label>Motivo (Opcional):</label>
    <textarea name="motivo" rows="3" placeholder="Explique el motivo de su solicitud..."></textarea>
    
    <div style="background: #e2e3e5; padding: 10px; border-radius: 5px; margin: 10px 0;">
        <strong>Información:</strong> 
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Tiene 30 días disponibles para vacaciones</li>
            <li>La solicitud será revisada por su supervisor</li>
            <li>El proceso de aprobación toma 2-3 días hábiles</li>
        </ul>
    </div>
    
    <div style="margin-top: 20px;">
        <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Enviar Solicitud
        </button>
        <a href="index.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 10px;">
            Cancelar
        </a>
    </div>
</form>

<script>
// Calcular días automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
    const fechaFin = document.querySelector('input[name="fecha_fin"]');
    const diasInput = document.querySelector('input[name="dias_solicitados"]');
    
    function calcularDias() {
        if (fechaInicio.value && fechaFin.value) {
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            
            // Validar que la fecha fin sea posterior
            if (fin < inicio) {
                alert('La fecha de fin debe ser posterior a la fecha de inicio');
                fechaFin.value = '';
                diasInput.value = '';
                return;
            }
            
            // Calcular diferencia en días
            const diffTime = fin.getTime() - inicio.getTime();
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            // Validar máximo 30 días
            if (diffDays > 30) {
                alert('No puede solicitar más de 30 días de vacaciones consecutivos');
                fechaFin.value = '';
                diasInput.value = '';
                return;
            }
            
            diasInput.value = diffDays;
        }
    }
    
    // Cuando cambia la fecha inicio, actualizar mínimo de fecha fin
    fechaInicio.addEventListener('change', function() {
        if (fechaInicio.value) {
            const fechaMin = new Date(fechaInicio.value);
            fechaMin.setDate(fechaMin.getDate() + 1);
            fechaFin.min = fechaMin.toISOString().split('T')[0];
            
            // Si la fecha fin es anterior a la nueva fecha mínima, limpiarla
            if (fechaFin.value && new Date(fechaFin.value) < fechaMin) {
                fechaFin.value = '';
                diasInput.value = '';
            }
        }
    });
    
    fechaFin.addEventListener('change', calcularDias);
});
</script>