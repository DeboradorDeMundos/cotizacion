<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa Detalle.PHP --------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->



<link rel="stylesheet" href="../../css/nueva_cotizacion/detalle.css">
<fieldset>
    <legend>Detalle de la Cotización</legend>
    <div id="detalle-container">
        <div class="detalle-section">
            <!-- Aquí se agregarán las secciones dinámicamente -->
        </div>

        <div class="fixed-button-container">
            <button type="button" onclick="addDetailSection()">Agregar un nuevo título</button>
        </div>
    </div>
</fieldset>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $detalles_titulo = $_POST['detalle_titulo'] ?? [];
    $detalles_subtitulo = $_POST['detalle_subtitulo'] ?? [];
    $detalles_cantidad = $_POST['detalle_cantidad'] ?? [];
    $detalles_descripcion = $_POST['detalle_descripcion'] ?? [];
    $detalles_precio_unitario = $_POST['detalle_precio_unitario'] ?? [];
    $detalles_descuento = $_POST['detalle_descuento'] ?? [];
    $detalles_tipo = $_POST['tipo_producto'] ?? [];
    $detalles_nombre_producto = $_POST['nombre_producto'] ?? [];

    // Estructurar los datos en arrays anidados
    $estructura_datos = [];

    foreach ($detalles_titulo as $titulo_index => $titulo) {
        $estructura_datos[$titulo_index] = [
            'titulo' => $titulo,
            'subtitulos' => [],
            'detalles' => [],
        ];

        // Asignar subtítulos correspondientes a cada título
        foreach ($detalles_subtitulo[$titulo_index] ?? [] as $subtitulo) {
            $estructura_datos[$titulo_index]['subtitulos'][] = $subtitulo;
        }

       // Asignar detalles correspondientes a cada título
foreach ($detalles_cantidad[$titulo_index] ?? [] as $detalle_index => $cantidad) {
    // Convertir los valores a números
    $precio_unitario = floatval($detalles_precio_unitario[$titulo_index][$detalle_index] ?? 0);
    $cantidad = intval($cantidad); // Asegúrate de que la cantidad sea un entero
    $descuento = floatval($detalles_descuento[$titulo_index][$detalle_index] ?? 0);
    
    // Verificar que los valores sean válidos antes de calcular el total
    if ($cantidad > 0) { // Solo calcula si la cantidad es mayor que 0
        $total = ($precio_unitario * $cantidad) - (($precio_unitario * $cantidad) * ($descuento / 100));
    } else {
        $total = 0; // O maneja el caso según tus necesidades
    }

    $estructura_datos[$titulo_index]['detalles'][] = [
        'tipo' => $detalles_tipo[$titulo_index][$detalle_index] ?? '',
        'nombre_producto' => $detalles_nombre_producto[$titulo_index][$detalle_index] ?? '',
        'descripcion' => $detalles_descripcion[$titulo_index][$detalle_index] ?? '',
        'cantidad' => $cantidad,
        'precio_unitario' => $precio_unitario,
        'descuento' => $descuento,
        'total' => round($total, 2),
    ];
}

    }

    // Preparar las consultas de inserción
    $sql_insert_titulo = "INSERT INTO C_Titulos (id_cotizacion, nombre) VALUES (?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)";
    $sql_insert_subtitulo = "INSERT INTO C_Subtitulos (id_titulo, nombre) VALUES (?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)";
    $sql_insert_detalle = "INSERT INTO C_Detalles (id_titulo, id_subtitulo, tipo, nombre_producto, descripcion, cantidad, precio_unitario, descuento_porcentaje, total) 
                           VALUES (?, IFNULL(?, NULL), ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert_titulo = $mysqli->prepare($sql_insert_titulo);
    $stmt_insert_subtitulo = $mysqli->prepare($sql_insert_subtitulo);
    $stmt_insert_detalle = $mysqli->prepare($sql_insert_detalle);

    if (!$stmt_insert_titulo || !$stmt_insert_subtitulo || !$stmt_insert_detalle) {
        die("Error al preparar las consultas: " . $mysqli->error);
    }

    // Comenzar una transacción para asegurar consistencia
    $mysqli->begin_transaction();

    try {
        foreach ($estructura_datos as $titulo_index => $data) {
            // Insertar el título y obtener su ID
            $stmt_insert_titulo->bind_param("is", $id_cotizacion, $data['titulo']);
            if (!$stmt_insert_titulo->execute()) {
                throw new Exception("Error al insertar título: " . $stmt_insert_titulo->error);
            }
            $id_titulo = $stmt_insert_titulo->insert_id;

            // Insertar subtítulos y obtener sus IDs
            $id_subtitulo_actual = null;
            $detalle_index = 0; // Controlar el índice de detalles

            // Procesar los subtítulos y detalles asociados
            foreach ($data['subtitulos'] as $subtitulo) {
                // Insertar subtítulo y obtener su ID
                $stmt_insert_subtitulo->bind_param("is", $id_titulo, $subtitulo);
                if (!$stmt_insert_subtitulo->execute()) {
                    throw new Exception("Error al insertar subtítulo: " . $stmt_insert_subtitulo->error);
                }
                $id_subtitulo_actual = $stmt_insert_subtitulo->insert_id;

                // Insertar los detalles para este subtítulo
                while (isset($data['detalles'][$detalle_index])) {
                    $detalle = $data['detalles'][$detalle_index];

                    // Verificar si este detalle pertenece a este subtítulo
                    if ($detalle_index < count($data['detalles']) - 1) {
                        // Si el siguiente detalle está bajo un nuevo subtítulo, salir del bucle
                        if (isset($data['subtitulos'][$subtitulo + 1])) {
                            break;
                        }
                    }

                    $stmt_insert_detalle->bind_param(
                        "iisssiddi",
                        $id_titulo,
                        $id_subtitulo_actual,
                        $detalle['tipo'],
                        $detalle['nombre_producto'],
                        $detalle['descripcion'],
                        $detalle['cantidad'],
                        $detalle['precio_unitario'],
                        $detalle['descuento'],
                        $detalle['total']
                    );
                    if (!$stmt_insert_detalle->execute()) {
                        throw new Exception("Error al insertar detalle: " . $stmt_insert_detalle->error);
                    }
                    $detalle_index++;
                }
            }

            // Si no hay más subtítulos, insertar detalles directamente bajo el título
            while (isset($data['detalles'][$detalle_index])) {
                $detalle = $data['detalles'][$detalle_index];
                $stmt_insert_detalle->bind_param(
                    "iisssiddi",
                    $id_titulo,
                    null,  // Sin subtítulo
                    $detalle['tipo'],
                    $detalle['nombre_producto'],
                    $detalle['descripcion'],
                    $detalle['cantidad'],
                    $detalle['precio_unitario'],
                    $detalle['descuento'],
                    $detalle['total']
                );
                if (!$stmt_insert_detalle->execute()) {
                    throw new Exception("Error al insertar detalle: " . $stmt_insert_detalle->error);
                }
                $detalle_index++;
            }
        }

        // Confirmar la transacción
        $mysqli->commit();
        echo "Datos insertados correctamente";

    } catch (Exception $e) {
        // En caso de error, deshacer la transacción
        $mysqli->rollback();
        echo "Error al insertar los datos: " . $e->getMessage();
    }

    // Cerrar las consultas preparadas
    $stmt_insert_titulo->close();
    $stmt_insert_subtitulo->close();
    $stmt_insert_detalle->close();
}
?>





     <!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa Detalle.PHP ----------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Aguirre Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->
