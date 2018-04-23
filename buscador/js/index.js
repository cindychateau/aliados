$(document).ready(function(){

	getAll();
	getClientes();

	$(document).on('change','#cliente',function(e){
		e.preventDefault();
		var cliente = $(this).val();
		var params = {};
		params.cliente = cliente;
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=searchClient',
			data:params,
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
				if(result.error) {
					bootbox.dialog({
						message: result.msg,
						buttons: {
							cerrar: {
								label: "Cerrar"
							}
						}
					});
				} else {
					$(".resultados").html(result.resultados);
				}
			}
		});
	});	


	/*$(document).on('click','.buscar',function(e){
		e.preventDefault();
		$('#form-search').submit();
	});*/


	/*$(document).on('submit','#form-search',function(e){
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=searchClient',
			data: $('form[name="form-search"]').serialize(),
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
				if(result.error) {
					bootbox.dialog({
						message: result.msg,
						buttons: {
							cerrar: {
								label: "Cerrar"
							}
						}
					});
				} else {
					$(".resultados").html(result.resultados);
				}
			}
		});
	});*/

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Acción de Editar Medio
	 */
	/*$(document).on('click','.maximo_otorgar', function (e) {
		e.preventDefault();
		var id = $(this).attr("data-id");
		var val = $(this).attr("data-value");
		editar(id, val);
	});*/

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Submit de Forma Medio al editar
	 */
	/*$(document).on('submit','#formEd-maximo',function(e){
		e.preventDefault();
		guardaEdicion();
	});*/

	$(document).on('click','.eliminar',function(e){
		e.preventDefault();
		var nombre = $(this).attr("data-name");
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar al Cliente "+nombre+"?",
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							type:'post',
							data:params,
							url:'include/Libs.php',
							dataType:'json',
							error:function(){
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
							success:function(result){
								bootbox.dialog({
									message: result.msg,
									title: result.title,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												getAll();
											}
										}
									}
								});
							}
						});
					}
				},
				cancelar: {
					label: "Cancelar",
					className: "btn-danger",
					callback: function() {
						$('.modal-dialog').modal('hide');
					}
				}
			}
		});
	});
});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * Muestra el modal para editar un producto con los valores anteriores
 */
/*function editar(id, maximo) {
	bootbox.dialog({
		message: "<form id='formEd-maximo' name='formEd-maximo' role='form' class='form-horizontal' action='include/Libs.php?accion=saveMaximo'>"+
					"<div class='form-group'>"+
					    "<label for='maximo' class='col-sm-4 text-right'>Actividad Económica</label>"+
					    "<div class='col-sm-7'>" +
					    	"<input type='text' id='maximo' name='maximo' class='form-control' value='"+maximo+"'>"+
					    "</div>"+ 		
					    "<input type='hidden' class='form-control col-sm-5' id='id' name='id' value='"+id+"'>"+
					"</div>"+
				  "</form>",
		title: "Edición de Máximo a Otorgar",
		buttons: {
			main: {
			    label: "Guardar",
			    className: "btn-primary",
			    callback: function() {
			    	guardaEdicion();	
			    }
			},
			danger: {
				label: "Cancelar",
			    className: "btn-danger"
			}
		}
	});
} */

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * AJAX para guardar la edición de un IVA
 */
/* function guardaEdicion() {
 	var maximo = $("#maximo").val();
	var id = $("#id").val();
	params = {};
	params.id = id;
	params.maximo = maximo;
	$.ajax({
		url: $('#formEd-maximo').attr("action"),
		type: 'POST',
		data: params,
		dataType: 'JSON',
		error: function (){
			bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
		}, success: function (result) {
			bootbox.dialog({
				message: result.msg,
				title: result.title,
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							if(result.error) {
								editar(id, maximo);
							} else {
								$('#form-search').submit();
							}
						}
					}
				}
			});
		}
	});
 }*/

 function getAll() {
 	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=displayClients',
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
			if(result.error) {
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar"
						}
					}
				});
			} else {
				$(".resultados").html(result.resultados);
			}
		}
	});
 }

function getClientes() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getClientes',
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
			$('.container-clientes').html(result.select);
			$('#cliente').select2();
		}
	}); 
}






