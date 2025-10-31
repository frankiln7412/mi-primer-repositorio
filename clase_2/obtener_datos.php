<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Permite acceso desde cualquier origen

// Configuración de la base de datos
$servidor = "localhost";
$usuario = "root";
$contraseña = ""; // Tu contraseña de MySQL, si no hay deja vacío
$basedatos = "datodb"; // Base de datos que usamos antes
$tabla = "datos";

// Obtener parámetro 'limit' (cantidad de registros a devolver)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if ($limit < 1) $limit = 10;

// Conexión a la base de datos
$conexion = new mysqli($servidor, $usuario, $contraseña, $basedatos);

if ($conexion->connect_error) {
    die(json_encode(['error' => "Error de conexión: " . $conexion->connect_error]));
}

$conexion->set_charset("utf8mb4");

// Consulta SQL: obtener fecha, temperatura y humedad
$sql = "SELECT fecha, temperatura, humedad FROM $tabla ORDER BY fecha DESC LIMIT $limit";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die(json_encode(['error' => "Error en consulta: " . $conexion->error]));
}

$datos = [];
while ($fila = $resultado->fetch_assoc()) {
    $datos[] = $fila;
}

// Invertir orden para que se muestren del más antiguo al más reciente
$datos = array_reverse($datos);

// Devolver datos en formato JSON
echo json_encode($datos);

// Cerrar conexión
$conexion->close();
?>
