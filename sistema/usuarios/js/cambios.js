$(document).ready(function(){

	var id_profile = 0;

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
				$('#nombre').val(result.nombre);
				$('#email').val(result.email);
				$("#direccion").val(result.direccion);
				id_profile = result.profile;
				 
				/*
				 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
				 * @version: 0.1 2013-01-03
				 * 
				 * Carga por medio de AJAX el select de Profiles
				 */
				params = {};
				params.id = id_profile;

				$.ajax({
					type: 'POST',
					url: 'include/Libs.php?accion=showProfiles',
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
						$('#container-sel').html(result.select);

						var perfil = $("#perfil").val();
						if(perfil == 3) {
							$(".cont-direccion").removeClass("display-none");
						} else {
							$(".cont-direccion").addClass("display-none");
						}
					}
				});

			}
			else
				window.location = "index.php";
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