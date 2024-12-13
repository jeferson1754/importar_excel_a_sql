<?php
// simple conexion a la base de datos
include('funciones.php');
function connect()
{
       return new mysqli("localhost", "root", "", "inventario_revesol");
}

$con = connect();
if (!$con->set_charset("utf8")) { //asignamos la codificación comprobando que no falle
       die("Error cargando el conjunto de caracteres utf8");
}
