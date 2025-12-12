<?php 
$con = mysqli_connect("localhost", "root", "", "flujo_vaca");
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>