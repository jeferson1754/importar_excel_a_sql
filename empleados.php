<?php
include('dbconect.php');
require_once('vendor/php-excel-reader/excel_reader2.php');
require_once('vendor/SpreadsheetReader.php');


if (isset($_POST["import"])) {

    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    if (!in_array($_FILES["file"]["type"], $allowedFileType)) {
        $type = "error";
        $message = "El archivo enviado es inválido. Por favor, vuelva a intentarlo.";
    } else {
        $targetPath = 'subidas/' . $_FILES['file']['name'];

        // Verificar si se subió correctamente el archivo
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            try {
                $Reader = new SpreadsheetReader($targetPath);
                $Reader->ChangeSheet(0); // Solo procesar la primera hoja

                foreach ($Reader as $Row) {

                    // Verificación de datos antes de asignar
                    $rut             = isset($Row[0]) ? trim($Row[0]) : '';
                    $nombre_completo = isset($Row[1]) ? trim($Row[1]) : '';
                    $estado          = isset($Row[2]) ? trim($Row[2]) : '';
                    $cargo           = isset($Row[3]) ? trim($Row[3]) : '';
                    $centro_costo    = isset($Row[4]) ? trim($Row[4]) : '';
                    $fecha_ingreso   = isset($Row[5]) ? trim($Row[5]) : '';
                    $id_departamento = isset($Row[6]) ? trim($Row[6]) : '';

                    $rut_formateado = formatearRUT($rut);
                    $id_empleado = buscarEmpleadoporRut($conexion, $rut_formateado);
                    $correo = crear_correo_corporativo($nombre_completo, $conexion);

                    // Verificar que los campos esenciales no estén vacíos
                    if ($id_empleado != 'No Existe' && !empty($rut_formateado) && !empty($estado)) {
                        $update_empleados = "UPDATE empleados SET Nombre_Completo='$nombre_completo', Rut ='$rut_formateado', Cargo ='$cargo', Fecha_Ingreso ='$fecha_ingreso', Estado='$estado', ID_Departamento = '$id_departamento' WHERE ID='$id_empleado';";
                        echo "<br>" . $update_empleados;
                    } else if ($id_empleado == 'No Existe' && $estado == 'activo') {
                        $update_empleados = " INSERT INTO `empleados`( `Nombre_Completo`, `Rut`, `Cargo`, `Correo`,`Fecha_Ingreso`, `Estado`, `ID_Departamento`) VALUES ('$nombre_completo','$rut_formateado','$cargo','$correo','$fecha_ingreso','$estado','$id_departamento');";
                        echo "<br>" . $update_empleados;
                    }



                    /*
                    $id             = isset($Row[0]) ? trim($Row[0]) : '';
                    $nombre_departamento = isset($Row[1]) ? trim($Row[1]) : '';
                    $estado          = isset($Row[2]) ? trim($Row[2]) : '';
                    $centro_costo           = isset($Row[3]) ? trim($Row[3]) : '';
                    $departamento_padre    = isset($Row[4]) ? trim($Row[4]) : '';
                    $area   = isset($Row[5]) ? trim($Row[5]) : '';
                

                    if ($estado === 'active' && !empty($id) && !empty($nombre_departamento) && !empty($area) && !empty($centro_costo)) {
                        $update_departamento = "INSERT INTO `departamento`(`ID`, `Nombre`, `Departamento_Padre`, `Centro_Costos`, `Area`) VALUES ('$id','$nombre_departamento','$departamento_padre','$centro_costo','$area');";
                        echo "<br>" . $update_departamento;
                    }
                        */
                }
            } catch (Exception $e) {
                echo "Error al procesar el archivo: " . $e->getMessage();
            }
        } else {
            echo "Error al subir el archivo.";
        }
    }
}

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">
    <title>Importar archivo de Excel a MySQL usando PHP - BaulPHP</title>

    <!-- Bootstrap core CSS -->
    <link href="dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="assets/sticky-footer-navbar.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">

</head>

<body>


    <!-- Begin page content -->

    <div class="container">
        <h3 class="mt-5">Importar archivo de Excel a MySQL usando PHP</h3>
        <hr>
        <div class="row">
            <div class="col-12 col-md-12">
                <!-- Contenido -->

                <div class="outer-container">
                    <form action="" method="post"
                        name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
                        <div>
                            <label>Elija Archivo Excel</label> <input type="file" name="file"
                                id="file" accept=".xls,.xlsx">
                            <button type="submit" id="submit" name="import"
                                class="btn-submit">Importar Registros</button>

                        </div>

                    </form>

                </div>




                <!-- Fin Contenido -->
            </div>
        </div>
        <!-- Fin row -->


    </div>
    <!-- Fin container -->
    <footer class="footer">
        <div class="container"> <span class="text-muted">
                <p>Códigos <a href="https://www.baulphp.com/importar-archivo-de-excel-a-mysql-usando-php" target="_blank">BaulPHP</a></p>
            </span> </div>
    </footer>
    <script src="assets/jquery-1.12.4-jquery.min.js"></script>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->

    <script src="dist/js/bootstrap.min.js"></script>
</body>

</html>