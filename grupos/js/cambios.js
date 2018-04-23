var order = ["credito", "prospecto", "referencias", "conyuge"];
var actual = 0;
$(document).ready(function(){
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
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
							window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){
				$('#nombre').val(result.CLI_NOMBRE);
				$('#apellido-p').val(result.CLI_APELLIDO_PATERNO);
				$('#apellido-m').val(result.CLI_APELLIDO_MATERNO);
				$("#email").val(result.CLI_EMAIL);
				$("#celular").val(result.CLI_MOVIL);
			}
			else
				window.location = "index.php";
		}
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-10-15
	 * 
	 * Es virtuoso? -> Despliega/Esconde Amenidades Virtuoso
	 */
	$(document).on('change','#virtuoso',function(e){
		e.preventDefault();
		if ($('#virtuoso').is(':checked')) {
			$('.amenidades-virtuoso').fadeIn();
		} else {
			$('.amenidades-virtuoso').hide();
		}
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-03-19
	 * 
	 * Acción de hacer click en GUARDAR
	 */
	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('#form-prospecto').submit();
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-03-19
	 * 
	 * Guardar Cliente
	 */
	 $(document).on('submit','#form-prospecto',function(e){
	 	e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: $('form[name="form-prospecto"]').serialize(),
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


	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-11-26
	 * 
	 * Al cambiar de tab guarda la tab actual y checa los botones
	 */
	$(document).on('click','.wiz-step',function(e){
		e.preventDefault();
		actual = $(this).attr("data-id");
		checkButtons();
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-11-26
	 * 
	 * Al cambiar de tab guarda la tab actual y checa los botones
	 */
	$(document).on('click','.prevBtn, .nextBtn',function(e){
		e.preventDefault();
		if($(this).hasClass("prevBtn")) {
			actual--;
		} else {
			actual++;
		}

		var tab_actual = order[actual];

		$(".lnk-"+tab_actual).click();

	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-11-27
	 * 
	 * Tienen la misma dirección? -> Esconde campos de dirección de Conyuge
	 */
	$(document).on('change','#misma_direccion',function(e){
		e.preventDefault();
		if ($('#misma_direccion').is(':checked')) {
			$('.cont-direccion_coyuge').hide();
		} else {
			$('.cont-direccion_coyuge').fadeIn();
		}
	});

});


/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2014-11-26
 * 
 * Verifica si se debe de esconder algún botón
 */
function checkButtons() {
	if(actual == 0) {
		$(".prevBtn").addClass("display-none");
	} else {
		$(".prevBtn").removeClass("display-none");
	}

	if(actual == 3) {
		$(".nextBtn").addClass("display-none");
	} else {
		$(".nextBtn").removeClass("display-none");
	}
}