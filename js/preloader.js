<!-- PRELOADER -->
// Se asegura que todo el sitio haya sido cargado
jQuery(window).load(function() {
    // Hace fadeout de la animación de carga
    jQuery("#status").fadeOut();
    // Hace fadeout de la pantalla que cubre el sitio
    jQuery("#preloader").delay(1000).fadeOut("slow");
})
<!-- /PRELOADER -->