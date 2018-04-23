$(document).ready(function () {
	$(document).on('click','.visto',function(e){
		e.preventDefault();
       	var liga = $(this).attr("data-liga");
       	var ruta = $(this).attr("data-ruta"); //Donde se encuentra el método de cambio de estado
       	var link = $(this).attr("href"); //URL al que tiene que ir
        $.ajax({
			url: ruta+'include/Notices.php?accion=vista',
			type: 'POST',
			dataType: 'JSON',
			data: {liga: liga},
			error: function() {
				console.log("Error cambiar la notificación de estado.");
			}, 
			success: function (result) {
				window.location = link;
			}
		});
    });
});