<?php
// simple conexion a la base de datos
include('funciones.php');

$host = 'localhost';
$dbname = 'inventario_revesol';
$username = 'root';
$password = '';

$conexion2 = mysqli_connect($host, $username, $password, $dbname);


function connect()
{
       return new mysqli("localhost", "root", "", "inventario_revesol");
}

$con = connect();
if (!$con->set_charset("utf8")) { //asignamos la codificación comprobando que no falle
       die("Error cargando el conjunto de caracteres utf8");
}

try {
       $conexion = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
       $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
       die("Error de conexión: " . $e->getMessage());
}
