<?php
include('dbconect.php');
require_once('vendor/php-excel-reader/excel_reader2.php');
require_once('vendor/SpreadsheetReader.php');


if (isset($_POST["import"])) {

    $allowedFileType = ['application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

    if (in_array($_FILES["file"]["type"], $allowedFileType)) {

        $targetPath = 'subidas/' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);

        $Reader = new SpreadsheetReader($targetPath);

        $sheetCount = count($Reader->sheets());
        for ($i = 0; $i < $sheetCount; $i++) {

            $Reader->ChangeSheet($i);

            foreach ($Reader as $Row) {

                // Create a function to safely escape and retrieve values

                // Use the function to set variables
                $id_ticket = isset($Row[0]) ? $Row[0] : '';
                $fecha_completa = isset($Row[1]) ? $Row[1] : '';
                $fecha_creacion = isset($Row[2]) ? $Row[2] : '';
                $hora_creacion = isset($Row[3]) ? trim($Row[3]) : '';

                $fecha_final = isset($Row[4]) ? $Row[4] : '';
                $fecha_termino = isset($Row[5]) ? $Row[5] : '';
                $hora_termino = isset($Row[6]) ? trim($Row[6]) : '';


                $fecha_inicio = formatear_fecha($fecha_creacion);
                $fecha_fin = formatear_fecha($fecha_termino);

                $hora_inicio = formatear_hora($hora_creacion);
                $hora_fin = formatear_hora($hora_termino);

                    

                if (!empty($id_ticket) || !empty($fecha_creacion) || !empty($fecha_termino)) {



                    $update_tickets = "UPDATE `tickets` SET Fecha_creacion='$fecha_inicio $hora_inicio', Fecha_resolucion='$fecha_fin $hora_fin' WHERE ID= '$id_ticket';";


                    echo "<br>" . $update_tickets;
       
                }
            }
        }
    } else {
        $type = "error";
        $message = "El archivo enviado es invalido. Por favor vuelva a intentarlo";
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