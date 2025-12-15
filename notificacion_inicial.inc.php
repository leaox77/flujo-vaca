<?php
$nrotramite = isset($_GET['nrotramite']) ? (int)$_GET['nrotramite'] : 0;
?>
<div style="text-align: center; padding: 40px 20px;">
    <div style="font-size: 60px; margin-bottom: 20px;">⏳</div>
    <h2>🔄 Procesando Solicitud (P4)</h2>
    
    <div style="max-width: 500px; margin: 30px auto; background: #e7f3fe; padding: 20px; border-radius: 10px;">
        <p style="font-size: 18px; margin-bottom: 15px;">
            Su solicitud está siendo procesada por el sistema...
        </p>
        
        <div style="background: white; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>📋 Número de Trámite:</strong></p>
            <div style="font-size: 28px; font-weight: bold; color: #2196F3;">
                #<?php echo $nrotramite; ?>
            </div>
        </div>
        
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
            <div style="background: #4CAF50; width: 20px; height: 20px; border-radius: 50%;"></div>
            <div style="background: #2196F3; width: 20px; height: 20px; border-radius: 50%;"></div>
            <div style="background: #FF9800; width: 20px; height: 20px; border-radius: 50%;"></div>
        </div>
    </div>
    
    <p style="color: #666; margin-top: 30px;">
        <strong>Por favor espere, será redirigido automáticamente...</strong>
    </p>
</div>

<script>
let progreso = 0;
const barra = document.createElement('div');
barra.style.cssText = 'width: 100%; height: 10px; background: #e0e0e0; border-radius: 5px; margin: 20px auto; max-width: 400px;';
const relleno = document.createElement('div');
relleno.style.cssText = 'width: 0%; height: 100%; background: linear-gradient(90deg, #4CAF50, #2196F3); border-radius: 5px; transition: width 0.5s;';
barra.appendChild(relleno);
document.querySelector('.form-container').appendChild(barra);

const intervalo = setInterval(() => {
    progreso += 10;
    relleno.style.width = progreso + '%';
    
    if (progreso >= 100) {
        clearInterval(intervalo);
        setTimeout(() => {
            window.location.href = "motor.php?cod_flujo=VAC&cod_proceso=P2&nrotramite=<?php echo $nrotramite; ?>";
        }, 500);
    }
}, 200);

setTimeout(() => {
    window.location.href = "motor.php?cod_flujo=VAC&cod_proceso=P2&nrotramite=<?php echo $nrotramite; ?>";
}, 5000);
</script>