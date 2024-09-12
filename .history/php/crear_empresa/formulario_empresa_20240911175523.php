<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->

<!-- ------------------------------------------------------------------------------------------------------------
    ------------------------------------- INICIO ITred Spa Formulario Empresa.PHP --------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->

<div class="row"> <!-- Crea una fila para organizar los elementos en una disposición horizontal -->
    <div class="box-12 data-box"> <!-- Crea una caja para ingresar datos, ocupando las 12 columnas disponibles en el diseño. Esta caja contiene varios campos de entrada de datos -->

        <label for="empresa_nombre">Nombre de la Empresa:</label> <!-- Etiqueta para el campo de entrada del nombre de la empresa -->
        <input type="text" id="empresa_nombre" name="empresa_nombre" required> <!-- Campo de texto para ingresar el nombre de la empresa. El atributo "required" hace que el campo sea obligatorio -->

        
        <label for="empresa_area">Área de la Empresa:</label> <!-- Etiqueta para el campo de entrada del área de la empresa -->
        <input type="text" id="empresa_area" name="empresa_area"> <!-- Campo de texto para ingresar el área de la empresa. Este campo no es obligatorio -->


        <label for="empresa_direccion">Dirección de la Empresa:</label> <!-- Etiqueta para el campo de entrada de la dirección de la empresa -->
        <input type="text" id="empresa_direccion" name="empresa_direccion"> <!-- Campo de texto para ingresar la dirección de la empresa. Este campo no es obligatorio -->


        <label for="empresa_telefono">Teléfono de la Empresa:</label> <!-- Etiqueta para el campo de entrada del teléfono de la empresa -->
        <input type="text" id="empresa_telefono" name="empresa_telefono" pattern="\+?\d{7,15}" placeholder="+1234567890"> <!-- Campo de texto para ingresar el teléfono de la empresa. Este campo no es obligatorio -->


        <label for="empresa_email">Email de la Empresa:</label> <!-- Etiqueta para el campo de entrada del email de la empresa -->
        <input type="email" id="empresa_email" name="empresa_email"> <!-- Campo de correo electrónico para ingresar el email de la empresa. El tipo "email" valida que el texto ingresado sea una dirección de correo electrónico -->

        
    </div> <!-- Cierra la caja de datos -->
</div> <!-- Cierra la fila -->





<!-- falta php de esto -->
<?php
// Verifica si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $empresa_rut = $_POST['empresa_rut'];
    $empresa_nombre = $_POST['empresa_nombre'];
    $empresa_area = $_POST['empresa_area'];
    $empresa_direccion = $_POST['empresa_direccion'];
    $empresa_telefono = $_POST['empresa_telefono'];
    $empresa_email = $_POST['empresa_email'];
    $fecha_creacion = $_POST['fecha_creacion'];
    $validez_cotizacion = $_POST['validez_cotizacion'];

    // Insertar o actualizar la empresa
    $sql = "INSERT INTO e_empresa (rut_empresa, id_foto, nombre_empresa, area_empresa, direccion_empresa, telefono_empresa, email_empresa, fecha_creacion, dias_validez)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre_empresa=VALUES(nombre_empresa), area_empresa=VALUES(area_empresa), direccion_empresa=VALUES(direccion_empresa), telefono_empresa=VALUES(telefono_empresa), email_empresa=VALUES(email_empresa), fecha_creacion=VALUES(fecha_creacion), dias_validez=VALUES(dias_validez)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sissssssi", $empresa_rut, $empresa_id_foto, $empresa_nombre, $empresa_area, $empresa_direccion, $empresa_telefono, $empresa_email, $fecha_creacion, $validez_cotizacion);

    if (!$stmt->execute()) {
        die("Error en la ejecución de la consulta: " . $stmt->error);
    }

    // Obtener el ID de la empresa después de la inserción/actualización
    $id_empresa = $stmt->insert_id;

    if ($id_empresa === 0) {
        $result = $mysqli->query("SELECT id_empresa FROM e_empresa WHERE rut_empresa = '$empresa_rut'");
        $row = $result->fetch_assoc();
        $id_empresa = $row['id_empresa'];
    }

    echo "Empresa insertada/actualizada. ID: $id_empresa<br>";

    // Redirigir a la siguiente página para procesar cotización
    header("Location: process_cotizacion.php?id_empresa=$id_empresa");
    exit();
}
?>
<!-- ------------------------------------------------------------------------------------------------------------
    -------------------------------------- FIN ITred Spa Formulario Empresa .PHP ----------------------------------------
    ------------------------------------------------------------------------------------------------------------- -->

<!--
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
-->
