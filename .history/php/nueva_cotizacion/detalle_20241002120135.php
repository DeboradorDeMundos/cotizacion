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

        // Verificar si los subtítulos están correctamente estructurados como un array
        if (isset($detalles_subtitulo[$titulo_index]) && is_array($detalles_subtitulo[$titulo_index])) {
            // Asignar subtítulos correspondientes a cada título
            foreach ($detalles_subtitulo[$titulo_index] as $subtitulo_index => $subtitulo) {
                $estructura_datos[$titulo_index]['subtitulos'][$subtitulo_index] = [
                    'nombre' => $subtitulo,
                    'detalles' => [],
                ];

                // Verificar si los detalles están correctamente estructurados como un array
                if (isset($detalles_cantidad[$titulo_index][$subtitulo_index]) && is_array($detalles_cantidad[$titulo_index][$subtitulo_index])) {
                    // Asignar detalles correspondientes a cada subtítulo
                    foreach ($detalles_cantidad[$titulo_index][$subtitulo_index] as $detalle_index => $cantidad) {
                        $precio_unitario = floatval($detalles_precio_unitario[$titulo_index][$subtitulo_index][$detalle_index] ?? 0);
                        $descuento = floatval($detalles_descuento[$titulo_index][$subtitulo_index][$detalle_index] ?? 0);
                        $total = ($precio_unitario * $cantidad) - (($precio_unitario * $cantidad) * ($descuento / 100));

                        $estructura_datos[$titulo_index]['subtitulos'][$subtitulo_index]['detalles'][] = [
                            'tipo' => $detalles_tipo[$titulo_index][$subtitulo_index][$detalle_index] ?? '',
                            'nombre_producto' => $detalles_nombre_producto[$titulo_index][$subtitulo_index][$detalle_index] ?? '',
                            'descripcion' => $detalles_descripcion[$titulo_index][$subtitulo_index][$detalle_index] ?? '',
                            'cantidad' => intval($cantidad),
                            'precio_unitario' => $precio_unitario,
                            'descuento' => $descuento,
                            'total' => round($total, 2),
                        ];
                    }
                }
            }
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

            // Insertar los subtítulos asociados y obtener su ID
            foreach ($data['subtitulos'] as $subtitulo_index => $subtitulo_data) {
                $stmt_insert_subtitulo->bind_param("is", $id_titulo, $subtitulo_data['nombre']);
                if (!$stmt_insert_subtitulo->execute()) {
                    throw new Exception("Error al insertar subtítulo: " . $stmt_insert_subtitulo->error);
                }
                $id_subtitulo = $stmt_insert_subtitulo->insert_id;

                // Insertar los detalles asociados con el id_titulo y id_subtitulo
                foreach ($subtitulo_data['detalles'] as $detalle) {
                    // Asegurarse de que al menos uno de los IDs no sea NULL
                    if ($id_titulo || $id_subtitulo) {
                        $stmt_insert_detalle->bind_param(
                            "iisssiddi",
                            $id_titulo,
                            $id_subtitulo,
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
                    } else {
                        throw new Exception("No se puede insertar un detalle sin ID de título o subtítulo.");
                    }
                }
            }
        }

        // Confirmar la transacción
        $mysqli->commit();

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
