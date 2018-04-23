$(document).ready(function(){

	$('#fecha').datepicker({
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
		url: 'include/Libs.php?accion=showRecordEntrada',
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
				$('#tipo').val(result.tipo);
				$('#monto').val(result.monto);
				$('#grupo').val(result.grupo);
				$('#comentarios').val(result.comentarios);
				$('#pago_correspondiente').val(result.pago);
				getPromotores(result.promotor);
			}
			else
				window.location = "index.php";
		}
	});

	$(document).on('change','#grupo',function(e) {
		e.preventDefault();
		var grupo = $("#grupo").val();
		params = {};
		params.id = grupo;

		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=getPago',
			dataType:'json',
			data: params,
			error: function(){
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
								//window.location = "../";
							}
						}
					}
				});
			},
			success: function(result){
				if(result.error) {
					bootbox.dialog({
						message: result.msg,
						buttons: {
							cerrar: {
								label: "Cerrar",
								callback: function() {
									bootbox.hideAll();
									$("#grupo").focus();
								}
							}
						}
					});
				} else {
					$("#pago_correspondiente").val(result.pago);
					$("#promotor").val(result.promotor);
					$("#promotor").select2();
				}
			}
		}); 
	})

	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('#form-entrada').submit();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-01-02
	 * 
	 * Guardar Gasto
	 */
	$(document).on('submit','#form-entrada',function(e){
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveEntrada',
			data: $('form[name="form-entrada"]').serialize(),
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

});

function getPromotores(id) {
	var params = {};
	params.id = id;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getPromotores',
		dataType:'json',
		data: params,
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							//window.location = "../";
						}
					}
				}
			});
		},
		success: function(result){
			$('.cont-promotor').html(result.select);
			$('#promotor').select2();
		}
	}); 
}