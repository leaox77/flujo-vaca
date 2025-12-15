<?php
session_start();
include "conexion.inc.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $resultado = mysqli_query($con, $sql);
    
    if ($fila = mysqli_fetch_array($resultado)) {
        if ($password == '123456') {
            $_SESSION["usuario"] = $fila['usuario'];
            $_SESSION["idusuario"] = $fila['id'];
            $_SESSION["rol"] = $fila['rol'];
            $_SESSION["nombre"] = $fila['nombre'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "Usuario no encontrado";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistema de Vacaciones</title>
    <style>
        body { 
            font-family: Arial; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-box { 
            background: white; 
            border-radius: 10px;
            padding: 40px;
            width: 350px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h2 { 
            text-align: center; 
            color: #333;
            margin-bottom: 30px;
        }
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 10px 0; 
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 12px; 
            border: none; 
            width: 100%; 
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        button:hover {
            opacity: 0.9;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Login - Sistema de Vacaciones</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div class="info">
            <strong>Credenciales de prueba:</strong><br>
            Empleado: empleado1 / 123456<br>
            Supervisor: supervisor1 / 123456<br>
            RRHH: rrhh1 / 123456
        </div>
    </div>
</body>
</html>