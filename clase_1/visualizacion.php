<?php
// Conexión a la base de datos
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$basedatos = "datodb"; // tu base de datos real

$conexion = new mysqli($servidor, $usuario, $contraseña, $basedatos);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Sensor DHT11</title>

    <!-- Highcharts -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>

    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #container { height: 500px; max-width: 1000px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .last-values { text-align: center; font-size: 20px; margin: 15px 0; }
        .last-values span { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard Sensor DHT11</h1>
        <p>Visualización de Temperatura y Humedad en tiempo real</p>
    </div>

    <div id="container"></div>

    <div class="last-values">
        Última Temperatura: <span id="ultimaTemp">Cargando...</span> °C |
        Última Humedad: <span id="ultimaHum">Cargando...</span> %
    </div>

    <script>
    Highcharts.setOptions({
        time: { timezone: 'America/La_Paz' } // Ajusta tu zona horaria
    });

    const chart = Highcharts.chart('container', {
        chart: { type: 'spline', zoomType: 'x' },
        title: { text: 'Temperatura y Humedad' },
        subtitle: { text: 'Sensor DHT11' },
        xAxis: { type: 'datetime', title: { text: 'Fecha y Hora' } },
        yAxis: [
            { // Eje izquierdo - Temperatura
                title: { text: 'Temperatura (°C)' },
                min: 0
            },
            { // Eje derecho - Humedad
                title: { text: 'Humedad (%)' },
                opposite: true,
                min: 0,
                max: 100
            }
        ],
        tooltip: { shared: true, crosshairs: true },
        plotOptions: { spline: { marker: { enabled: true, radius: 4 } } },
        series: [
            { name: 'Temperatura', data: [], yAxis: 0 },
            { name: 'Humedad', data: [], yAxis: 1 }
        ],
        credits: { enabled: false }
    });

    // Cargar datos iniciales
    function cargarDatosIniciales() {
        fetch('obtener_datos.php?limit=100')
            .then(response => response.json())
            .then(data => {
                const tempData = data.map(item => [new Date(item.fecha).getTime(), parseFloat(item.temperatura)]);
                const humData = data.map(item => [new Date(item.fecha).getTime(), parseFloat(item.humedad)]);

                chart.series[0].setData(tempData);
                chart.series[1].setData(humData);

                if (data.length > 0) {
                    document.getElementById('ultimaTemp').textContent = parseFloat(data[data.length-1].temperatura).toFixed(1);
                    document.getElementById('ultimaHum').textContent = parseFloat(data[data.length-1].humedad).toFixed(1);
                }
            });
    }

    // Actualizar datos en tiempo real
    function actualizarDatos() {
        fetch('obtener_datos.php?limit=1')
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const tPoint = [new Date(data[0].fecha).getTime(), parseFloat(data[0].temperatura)];
                    const hPoint = [new Date(data[0].fecha).getTime(), parseFloat(data[0].humedad)];

                    const tempSeries = chart.series[0];
                    const humSeries = chart.series[1];

                    if (tempSeries.data.length > 100) tempSeries.data[0].remove();
                    if (humSeries.data.length > 100) humSeries.data[0].remove();

                    tempSeries.addPoint(tPoint, true, false);
                    humSeries.addPoint(hPoint, true, false);

                    document.getElementById('ultimaTemp').textContent = parseFloat(data[0].temperatura).toFixed(1);
                    document.getElementById('ultimaHum').textContent = parseFloat(data[0].humedad).toFixed(1);
                }
            });
    }

    cargarDatosIniciales();
    setInterval(actualizarDatos, 5000); // cada 5 segundos
    </script>
</body>
</html>
