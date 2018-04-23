$(document).ready(function(){
	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-01-03
	 * 
	 * Carga por medio de AJAX el select de Máquinas
	 */
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showProfiles',
		dataType:'json',
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
			$('#container-sel').html(result.select);
		}
	});

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-02
	 * 
	 * Guardar Turno
	 */
	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('input').each(function(){
			$(this).removeAttr('disabled');
		});
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: $('form[name="form-usuario"]').serialize(),
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

	$(document).on('change','#perfil',function(e) {
		var perfil = $(this).val();
		if(perfil == 3) {
			$(".cont-direccion").removeClass("display-none");
		} else {
			$(".cont-direccion").addClass("display-none");
		}
	});

});