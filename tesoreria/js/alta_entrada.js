$(document).ready(function(){

	getPromotores();

	//Ponemos por default la fecha de hoy
	var myDate = new Date();
	var prettyDate =myDate.getDate() + '/' + (myDate.getMonth()+1) + '/' + myDate.getFullYear();
	$("#fecha").val(prettyDate);

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-21
	 * 
	 * Datepickers
	 */
	$('#fecha').datepicker({
		dateFormat: 'dd/mm/yy',
		monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
			"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
		monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"]
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

function getPromotores() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getPromotores',
		dataType:'json',
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