<?php
// Función para acortar el nombre
function acortarNombre($nombreCompleto)
{
    // Eliminar la coma y los espacios extra
    $nombreCompleto = str_replace(",", "", $nombreCompleto);

    // Dividir el nombre completo en un arreglo de palabras
    $partes = explode(" ", trim($nombreCompleto));

    // El apellido completo está en los dos primeros elementos
    $primerApellido = $partes[0] . ' ' . $partes[1]; // Los dos primeros elementos son el primer y segundo apellido

    // El primer nombre y segundo nombre están en el resto de los elementos
    $primerNombre = implode(" ", array_slice($partes, 2)); // Desde el tercer elemento hasta el final son los nombres

    // Concatenar el nombre y apellido en el formato deseado
    return $primerNombre . ' ' . $primerApellido;
}


function limpiarTexto($texto)
{
    return iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
}
function acortarCorreo($nombre_completo)
{
    // Eliminar tildes y caracteres especiales

    // Divide el nombre completo en partes
    $partes = explode(" ", $nombre_completo);

    // Toma la primera palabra como nombre y la penúltima como apellido
    $nombre = limpiarTexto(strtolower($partes[0])); // El primer nombre
    $apellido = limpiarTexto(strtolower($partes[count($partes) - 2])); // El penúltimo apellido

    // Combina nombre y apellido en el formato requerido
    $correo = "$nombre.$apellido@revesol.com";

    return $correo;
}

function crear_correo_corporativo($nombre_completo, $conexion)
{
    // Llama a la función para crear el correo en formato nombre.apellido@revesol.com
    $correo = acortarCorreo($nombre_completo);

    // Verifica si el correo ya existe en la base de datos
    $consulta = "SELECT COUNT(*) FROM empleados WHERE Correo = :correo";
    $stmt = $conexion->prepare($consulta);
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
    $stmt->execute();

    return ($stmt->fetchColumn() > 0) ? "Correo Existe" : $correo;
}

function formatoFecha($fecha)
{
    // Quitar la coma al final de la fecha si está presente
    $fecha = rtrim($fecha, ',');

    // Crear un objeto DateTime a partir de la fecha
    $date = DateTime::createFromFormat('Y-m-d', $fecha);

    // Comprobar si la fecha es válida
    if ($date) {
        // Definir los nombres de los meses en español
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        // Obtener el día, el mes y el año
        $dia = $date->format('d');
        $mes = $meses[(int)$date->format('m')];  // Convertir el mes numérico a nombre
        $anio = $date->format('Y');

        // Retornar la fecha formateada
        return $dia . ' de ' . $mes . ' del ' . $anio;
    } else {
        // Si la fecha no es válida, retornar un mensaje de error
        return "Fecha inválida";
    }
}

function formatearRUT($rut)
{
    // Eliminar cualquier carácter no numérico o el dígito verificador
    $rut = preg_replace('/[^0-9kK]/', '', $rut);

    // Verificar que el RUT tenga el formato adecuado (número + dígito verificador)
    if (strlen($rut) < 2) {
        return false; // RUT inválido
    }

    // Obtener el número y el dígito verificador
    $numero = substr($rut, 0, strlen($rut) - 1);
    $digito = strtoupper(substr($rut, -1));

    // Formatear el número del RUT
    $numero_formateado = number_format($numero, 0, '', '.');

    // Devolver el RUT formateado
    return $numero_formateado . '-' . $digito;
}

function buscarTicket($conexion, $id_empleado, $fecha_creacion)
{
    try {
        // Buscar por correo electrónico exacto primero
        $consultaCorreo = $conexion->prepare("SELECT ID FROM tickets WHERE ID_empleado_cliente = :id AND Fecha_creacion = :fecha");
        $consultaCorreo->execute([':id' => $id_empleado, ':fecha' => $fecha_creacion]);

        if ($consultaCorreo->rowCount() > 0) {
            return $consultaCorreo->fetchColumn();
        }
        // Si no se encuentra ninguna coincidencia
        return null;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}


function buscarEmpleado($conexion, $nombre)
{
    try {
        // Dividir el nombre ingresado en palabras
        $nombres = explode(' ', trim($nombre));

        // Generar el criterio para SQL usando '%' para coincidencia parcial
        $criterioNombre = implode('%', $nombres);

        // Consulta para buscar por coincidencia parcial del nombre completo
        $consultaNombre = $conexion->prepare("
            SELECT ID
            FROM empleados
            WHERE nombre_completo LIKE :nombre
        ");
        $consultaNombre->execute([':nombre' => '%' . $criterioNombre . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaNombre->rowCount() > 0) {
            return $consultaNombre->fetchColumn();
        }

        // Si no encuentra con el nombre completo, buscar solo por el primer nombre y apellido (si ambos existen)
        if (count($nombres) >= 2) {
            $primerNombre = $nombres[0];
            $primerApellido = $nombres[1];

            $consultaNombreParcial = $conexion->prepare("
                SELECT ID
                FROM empleados
                WHERE nombre_completo LIKE :primerNombre
                AND nombre_completo LIKE :primerApellido
            ");
            $consultaNombreParcial->execute([
                ':primerNombre' => '%' . $primerNombre . '%',
                ':primerApellido' => '%' . $primerApellido . '%'
            ]);

            if ($consultaNombreParcial->rowCount() > 0) {
                return $consultaNombreParcial->fetchColumn();
            }
        }

        // Si no se encuentra ninguna coincidencia
        return ""; // ID de Soporte
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarEmpleado: " . $e->getMessage());
        return null;
    }
}

function buscarEmpleadoporRut($conexion, $rut)
{
    try {
        // Dividir el nombre ingresado en palabras
        $nombres = explode(' ', trim($rut));

        // Generar el criterio para SQL usando '%' para coincidencia parcial
        $criterioNombre = implode('%', $nombres);

        // Consulta para buscar por coincidencia parcial del nombre completo
        $consultaNombre = $conexion->prepare("
            SELECT ID
            FROM empleados
            WHERE Rut LIKE :nombre
        ");
        $consultaNombre->execute([':nombre' => '%' . $criterioNombre . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaNombre->rowCount() > 0) {
            return $consultaNombre->fetchColumn();
        }
        // Si no se encuentra ninguna coincidencia
        return "No Existe"; // ID de Soporte
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarEmpleado: " . $e->getMessage());
        return null;
    }
}


function buscarEquipo($conexion, $numero_serie)
{
    try {
        // Dividir el número de serie en partes (en caso de que esté compuesto por más de un segmento)
        $numeros_serie = explode(' ', trim($numero_serie)); // Dividir el número de serie en partes
        $criterioSerie = implode('%', $numeros_serie); // Construir criterio para SQL con '%'

        // Consulta para buscar el equipo por el número de serie
        $consultaSerie = $conexion->prepare("
            SELECT ID
            FROM equipos
            WHERE Numero_Serie LIKE :numero_serie
        ");
        $consultaSerie->execute([':numero_serie' => '%' . $criterioSerie . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaSerie->rowCount() > 0) {
            return $consultaSerie->fetchColumn();
        }

        // Si no encuentra ninguna coincidencia
        return null;
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarEquipo: " . $e->getMessage());
        return null;
    }
}

function buscarCelular($conexion, $imei)
{
    try {
        // Dividir el número de serie en partes (en caso de que esté compuesto por más de un segmento)
        $imeis = explode(' ', trim($imei)); // Dividir el número de serie en partes
        $criterioimei = implode('%', $imeis); // Construir criterio para SQL con '%'

        // Consulta para buscar el equipo por el número de serie
        $consultaimei = $conexion->prepare("
            SELECT ID
            FROM equipos
            WHERE IMEI LIKE :imei
        ");
        $consultaimei->execute([':imei' => '%' . $criterioimei . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaimei->rowCount() > 0) {
            return $consultaimei->fetchColumn();
        }

        // Si no encuentra ninguna coincidencia
        return null;
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarCelular: " . $e->getMessage());
        return null;
    }
}
function formatear_fecha($fecha)
{
    // Crear el objeto DateTime desde el formato original
    $fecha_objeto = DateTime::createFromFormat('Y/m/d', $fecha);

    // Verificar si la fecha fue procesada correctamente
    if ($fecha_objeto) {
        // Retornar la fecha en el formato deseado
        return $fecha_objeto->format('Y-m-d');
    } else {
        // Manejar error si el formato es incorrecto
        return "Formato de fecha inválido.";
    }
}
function formatear_hora($hora)
{
    // Crear el objeto DateTime desde el formato original
    $hora = trim($hora); // Eliminar espacios en blanco adicionales
    $hora_objeto = DateTime::createFromFormat('h:i:s A', $hora);

    // Verificar si la hora fue procesada correctamente
    if ($hora_objeto) {
        // Retornar la hora en el formato deseado (24 horas)
        return $hora_objeto->format('i:s');
    } else {
        // Manejar error si el formato es incorrecto
        return "Formato de hora inválido.";
    }
}



function valor_excel($connection, $row, $index, $defaultValue = '')
{
    return isset($row[$index]) ? mysqli_real_escape_string($connection, $row[$index]) : $defaultValue;
}

function formatearFecha($fecha_original)
{
    try {
        // Si no se proporciona una fecha, usar la fecha actual
        if ($fecha_original == NULL) {
            $fecha_original = date('m-d-y'); // Asume formato 'd-m-y' para la fecha actual
        }

        // Crear un objeto DateTime con el formato correcto ('m-d-y')
        $fecha = DateTime::createFromFormat('m-d-y', $fecha_original);

        // Validar si la fecha fue correctamente creada
        if (!$fecha) {
            throw new Exception("");
        }


        // Retornar la fecha formateada al nuevo formato 'd-m-Y'
        return $fecha->format('Y-m-d');
    } catch (Exception $e) {
        // Manejar errores en caso de formato incorrecto o fallos
        return " " . $e->getMessage();
    }
}

function formatearFecha2($fecha_original)
{
    try {


        // Crear un objeto DateTime con el formato correcto ('m-d-y')
        $fecha = DateTime::createFromFormat('m-d-y', $fecha_original);

        // Validar si la fecha fue correctamente creada
        if (!$fecha) {
            throw new Exception("");
        }


        // Retornar la fecha formateada al nuevo formato 'd-m-Y'
        return $fecha->format('Y-m-d');
    } catch (Exception $e) {
        // Manejar errores en caso de formato incorrecto o fallos
        return " " . $e->getMessage();
    }
}


function buscarCategoria($conexion, $categoria): mixed
{
    try {
        // Dividir el nombre ingresado en palabras
        $categorias = explode(' ', trim($categoria));

        // Generar el criterio para SQL usando '%' para coincidencia parcial
        $criterioNombre = implode('%', $categorias);

        // Consulta para buscar por coincidencia parcial del nombre completo
        $consultaNombre = $conexion->prepare("
            SELECT ID
            FROM categoria_ticket
            WHERE Nombre LIKE :nombre
        ");
        $consultaNombre->execute([':nombre' => '%' . $criterioNombre . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaNombre->rowCount() > 0) {
            return $consultaNombre->fetchColumn();
        }

        // Si no encuentra con el nombre completo, buscar solo por el primer nombre y apellido (si ambos existen)
        if (count($categorias) >= 2) {
            $primerNombre = $categorias[0];
            $primerApellido = $categorias[1];

            $consultaNombreParcial = $conexion->prepare("
                SELECT ID
                FROM categoria_ticket
                WHERE Nombre LIKE :primerNombre
                AND Nombre LIKE :primerApellido
            ");
            $consultaNombreParcial->execute([
                ':primerNombre' => '%' . $primerNombre . '%',
                ':primerApellido' => '%' . $primerApellido . '%'
            ]);

            if ($consultaNombreParcial->rowCount() > 0) {
                return $consultaNombreParcial->fetchColumn();
            }
        }

        // Si no se encuentra ninguna coincidencia
        return "7"; // ID de Soporte
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarEmpleado: " . $e->getMessage());
        return null;
    }
}

function buscarPrioridad($conexion, $prioridad): mixed
{
    try {
        // Dividir el nombre ingresado en palabras
        $prioridades = explode(' ', trim($prioridad));

        // Generar el criterio para SQL usando '%' para coincidencia parcial
        $criterioNombre = implode('%', $prioridades);

        // Consulta para buscar por coincidencia parcial del nombre completo
        $consultaNombre = $conexion->prepare("
            SELECT ID
            FROM prioridad_ticket
            WHERE Nombre LIKE :nombre
        ");
        $consultaNombre->execute([':nombre' => '%' . $criterioNombre . '%']);

        // Si encuentra coincidencias, devolver el primer resultado
        if ($consultaNombre->rowCount() > 0) {
            return $consultaNombre->fetchColumn();
        }

        // Si no encuentra con el nombre completo, buscar solo por el primer nombre y apellido (si ambos existen)
        if (count($prioridades) >= 2) {
            $primerNombre = $prioridades[0];
            $primerApellido = $prioridades[1];

            $consultaNombreParcial = $conexion->prepare("
                SELECT ID
                FROM prioridad_ticket
                WHERE Nombre LIKE :primerNombre
                AND Nombre LIKE :primerApellido
            ");
            $consultaNombreParcial->execute([
                ':primerNombre' => '%' . $primerNombre . '%',
                ':primerApellido' => '%' . $primerApellido . '%'
            ]);

            if ($consultaNombreParcial->rowCount() > 0) {
                return $consultaNombreParcial->fetchColumn();
            }
        }

        // Si no se encuentra ninguna coincidencia
        return "1"; // ID de Soporte
    } catch (PDOException $e) {
        // Manejo de excepciones
        error_log("Error en buscarEmpleado: " . $e->getMessage());
        return null;
    }
}
function convertirFecha($fecha)
{
    // Intentar detectar el formato de la fecha
    $partes = explode(' ', $fecha);
    $fechaHora = $partes[0];  // Ejemplo: '12/2/24'
    $hora = isset($partes[1]) ? $partes[1] : '00:00';  // Ejemplo: '0:13'

    // Separamos la fecha (mes/día/año o día/mes/año)
    $fechaPartes = explode('/', $fechaHora);

    // Detectamos el formato de la fecha
    if (count($fechaPartes) === 3) {
        // Si el segundo número es mayor que 12, es mes/día/año
        if ($fechaPartes[1] > 12) {
            $formato = 'm/d/y';
        } else {
            $formato = 'd/m/y';
        }
    } else {
        $formato = 'm/d/y';
    }

    // Convertir a DateTime según el formato detectado
    $fechaObjeto = null;
    if ($formato == 'm/d/y') {
        $fechaObjeto = DateTime::createFromFormat('m/d/y H:i', $fecha);
    } elseif ($formato == 'd/m/y') {
        $fechaObjeto = DateTime::createFromFormat('d/m/y H:i', $fecha);
    }

    if ($fechaObjeto) {
        // Ajustamos la hora para que sea en formato de 24 horas (si la hora es 0, se cambia a 9)
        if ($fechaObjeto->format('H') == '00') {
            $minutos = $fechaObjeto->format('i');
            $fechaObjeto->setTime($minutos, 0); // Establecemos la hora a las 09:00
        }

        // Compara la fecha con la fecha actual
        $fechaActual = new DateTime();
        if ($fechaObjeto > $fechaActual) {
            // Si la fecha es mayor a la fecha actual, ajustamos el año a la fecha actual
            $fechaObjeto->setDate($fechaActual->format('Y'), $fechaObjeto->format('m'), $fechaObjeto->format('d'));
        }

        // Devuelve la fecha en el formato 'Y-m-d H:i'
        return $fechaObjeto->format('Y-m-d H:i');
    } else {
        return false; // Si la fecha no es válida, retorna false
    }
}
