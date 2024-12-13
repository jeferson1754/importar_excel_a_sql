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
function acortarCorreo($nombre_completo)
{
    // Divide el nombre completo en partes
    $partes = explode(" ", $nombre_completo);

    // Toma la primera palabra como nombre y la penúltima como apellido
    $nombre = strtolower($partes[0]); // El primer nombre
    $apellido = strtolower($partes[count($partes) - 2]); // El penúltimo elemento como apellido

    // Combina nombre y apellido en el formato requerido
    $correo = "$nombre.$apellido@revesol.com";

    return $correo;
}

function crear_correo_corporativo($nombre_completo, $conexion)
{
    // Llama a la función para crear el correo en formato nombre.apellido@revesol.com
    $correo = acortarCorreo($nombre_completo);

    // Verifica si el correo ya existe en la base de datos
    $consulta = "SELECT COUNT(*) AS total FROM empleados WHERE Correo = ?";
    $stmt = $conexion->prepare($consulta);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    // Si ya existe, devuelve una cadena vacía
    if ($resultado['total'] > 0) {
        return "";  // El correo ya existe, devolvemos vacío
    } else {
        return $correo;  // El correo no existe, lo devolvemos
    }
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


function buscarEmpleado($conexion, $nombre)
{
    try {

        // Si no encuentra por correo, buscar por nombre (coincidencia parcial)
        $nombres = explode(' ', $nombre); // Dividir el nombre en partes
        $criterioNombre = implode('%', $nombres); // Construir criterio para SQL con '%'

        $consultaNombre = $conexion->prepare("
            SELECT ID
            FROM empleados
            WHERE nombre_completo LIKE :nombre
            ");
        $consultaNombre->execute([':nombre' => '%' . $criterioNombre . '%']);

        if ($consultaNombre->rowCount() > 0) {
            return $consultaNombre->fetchColumn();
        }

        // Si no se encuentra ninguna coincidencia
        return null;

        //return $nombre . " " . $email;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}


function valor_excel($connection, $row, $index, $defaultValue = '')
{
    return isset($row[$index]) ? mysqli_real_escape_string($connection, $row[$index]) : $defaultValue;
}
