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
                $area = valor_excel($con, $Row, 0);
                $cargo = valor_excel($con, $Row, 1);
                $nombre = valor_excel($con, $Row, 2);
                $documento = valor_excel($con, $Row, 3);
                $tipo = valor_excel($con, $Row, 4);
                $marca = valor_excel($con, $Row, 5);
                $numero_serie = valor_excel($con, $Row, 7);
                $fecha_entrega = valor_excel($con, $Row, 5);


                if (!empty($area) || !empty($cargo) || !empty($nombre) || !empty($numero_serie)) {


                    $query = "insert into equipos(ID_Categoria, IMEI, Marca, Modelo, Detalle) values('" . $id_categoria . "','" . $area . "','" . $area . "','" . $area . "','" . $area . "');";
                    echo $query . "<br>";
                    //$resultados = mysqli_query($con, $query);

                    if (! empty($resultados)) {
                        $type = "success";
                        $message = "Excel importado correctamente";
                    } else {
                        $type = "error";
                        $message = "Hubo un problema al importar registros";
                    }
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
    <header>
        <!-- Fixed navbar -->
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark"> <a class="navbar-brand" href="#">BaulPHP</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active"> <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a> </li>
                </ul>
                <form class="form-inline mt-2 mt-md-0">
                    <input class="form-control mr-sm-2" type="text" placeholder="Buscar" aria-label="Search">
                    <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Busqueda</button>
                </form>
            </div>
        </nav>
    </header>

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
                <div id="response" class="<?php if (!empty($type)) {
                                                echo $type . " display-block";
                                            } ?>"><?php if (!empty($message)) {
                                                        echo $message;
                                                    } ?></div>


                <?php
                $sqlSelect = "SELECT * FROM empleados";
                $result = mysqli_query($con, $sqlSelect);

                if (mysqli_num_rows($result) > 0) {
                ?>

                    <table class='tutorial-table'>
                        <thead>
                            <tr>
                                <th>Nombres</th>
                                <th>Cargo</th>
                                <th>Celular</th>
                                <th>Descripcion</th>

                            </tr>
                        </thead>
                        <?php
                        while ($row = mysqli_fetch_array($result)) {
                        ?>
                            <tbody>
                                <tr>
                                    <td><?php echo $row['Nombre_Completo']; ?></td>
                                    <td><?php echo $row['Rut']; ?></td>
                                    <td><?php echo $row['Cargo']; ?></td>
                                    <td><?php echo $row['Correo']; ?></td>
                                </tr>
                            <?php
                        }
                            ?>
                            </tbody>
                    </table>
                <?php
                }
                ?>
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