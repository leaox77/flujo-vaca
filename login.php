<?php
session_start();
include "conexion.inc.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $resultado = mysqli_query($con, $sql);
    
    if ($fila = mysqli_fetch_array($resultado)) {
        //if (password_verify($password, $fila['password'])) {
        if ($password == '123456') { // Contraseña fija para pruebas
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
        body { font-family: Arial; max-width: 400px; margin: 50px auto; }
        .login-box { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        input { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #007bff; color: white; padding: 10px; border: none; width: 100%; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login - Sistema de Vacaciones</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>