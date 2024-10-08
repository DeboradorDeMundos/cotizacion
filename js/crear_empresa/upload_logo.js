/* 
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ 
*/


/* --------------------------------------------------------------------------------------------------------------
    -------------------------------------- Inicio ITred Spa Upload Logo .JS --------------------------------------
    ------------------------------------------------------------------------------------------------------------- */


document.querySelector('.logo-contenedor').addEventListener('click', function() {
    document.getElementById('cargar-logo').click();
});

document.getElementById('cargar-logo').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-previsualizar').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('cotizacion-form').addEventListener('submit', function(event) {
    const cargarLogo = document.getElementById('cargar-logo');
    const mensajeLogo = document.getElementById('logo-mensaje');

    if (!cargarLogo.files.length) {
        mensajeLogo.style.display = 'block'; // Muestra el mensaje si no hay logo
        event.preventDefault(); // Evita que se envíe el formulario
    } else {
        mensajeLogo.style.display = 'none'; // Oculta el mensaje si se ha seleccionado un logo
    }
});

/* --------------------------------------------------------------------------------------------------------------
    ---------------------------------------- FIN ITred Spa Upload Logo .JS ---------------------------------------
    ------------------------------------------------------------------------------------------------------------- */


/*
Sitio Web Creado por ITred Spa.
Direccion: Guido Reni #4190
Pedro Agui Cerda - Santiago - Chile
contacto@itred.cl o itred.spa@gmail.com
https://www.itred.cl
Creado, Programado y Diseñado por ITred Spa.
BPPJ
*/