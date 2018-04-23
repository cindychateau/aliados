$(document).ready(function() {

	getPagosPersonales();

	$(document).on('click','#guardar',function(e) {
    	e.preventDefault();
    	$('#pagosDesgl').submit();
    });

    $(document).on('submit','#pagosDesgl',function(e) {
    	e.preventDefault();
    	$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=savePagosPersonales',
			data: $(this).serialize(),
			dataType:'json',
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					//if(!$(this).hasClass("disabled")) {
						$(this).removeAttr('disabled');
					//}
				});
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			},
			success: function(result){
				$('input, file, textarea, button, select').each(function(){
					//if(!$(this).hasClass("disabled")) {
						$(this).removeAttr('disabled');
					//}
				});
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									bootbox.hideAll();
									$('#'+result.focus).focus();
								} else {
									//Cargamos de nuevo la tabla
									getPagosPersonales();
								}
							}
						}
					}
				});
			}
		});
    });

});

function getPagosPersonales() {
	params = {};
	params.gru_id = $("#gru_id").val();
	params.per_id = $("#per_id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getPagosPersonales',
		dataType:'json',
		data: params,
		beforeSend: function() {
			$(".cont-pagos").html("");
            $(".cont-pagos").addClass("loader");
        },
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							$(".cont-pagos").removeClass("loader");
                            $("input, button").removeAttr("disabled");
							bootbox.hideAll();
						}
					}
				}
			});
		},
		success: function(result){
			$(".cont-pagos").removeClass("loader");
			if(!result.error){
				$(".cont-pagos").html(result.pagos);
				$(".nombre").html(result.nombre);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
				$('.fecha').datepicker({
					dateFormat: 'dd/mm/yy',
					monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
						"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
					monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
					dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"]
				});
			} else {
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					}
				});
			}
		}		
	});
}