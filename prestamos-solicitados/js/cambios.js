$(document).ready(function(){

	$('.fecha').datepicker({
		dateFormat: 'dd/mm/yy',
		monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
			"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
		monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"]
	});

	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
		dataType:'json',
		data: params,
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){
				$('#fecha').val(result.fecha);
				$('#acreditante').val(result.acreditante);
				$('#monto').val(result.monto);
				$('#tasa').val(result.tasa);
				$('#plazo').val(result.plazo);
				$('#frecuencia').val(result.frecuencia);
				$('#primer-pago').val(result.fecha_pago);
				//$('#monto_pagos').val(result.monto_pagos);
				$('#intereses').val(result.intereses);
			}
			else
				window.location = "index.php";
		}
	});

	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('#form-prestamo').submit();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-01-02
	 * 
	 * Guardar Gasto
	 */
	$(document).on('submit','#form-prestamo',function(e){
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: $('form[name="form-prestamo"]').serialize(),
			dataType:'json',
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
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
					$(this).removeAttr('disabled');
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
									window.location = "index.php";
								}
							}
						}
					}
				});
			}
		});
	});

	$(document).on('change','#tasa, #plazo, #monto',function(e) {
		var tasa = $("#tasa").val();
		var plazo = $("#plazo").val();
		var monto = $("#monto").val();

		if(!isEmpty(monto) && !isEmpty(tasa) && !isEmpty(plazo)) {
			monto = parseFloat(monto);
			plazo = parseInt(plazo);
			tasa = parseFloat(tasa/100);
			var pago = ((monto*tasa) + monto) / plazo;
			pago = pago.toFixed();
			$("#monto_pagos").val(pago);

			var intereses = monto*tasa/plazo;
			intereses = intereses.toFixed();
			$('#intereses').val(intereses);
		}
	});

});

$(function() {
	$("#acreditante").autocomplete({
		source:"include/Libs.php?accion=showAcreditantes"
	});
});

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}