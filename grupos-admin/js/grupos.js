$(document).ready(function(){
	getRecords();

	$(document).on('click','.box .tools .collapse, .box .tools .expand', function(e) {
        var el = jQuery(this).parents(".box").children(".box-body");
        if (jQuery(this).hasClass("collapse")) {
			jQuery(this).removeClass("collapse").addClass("expand");
            var i = jQuery(this).children(".fa-chevron-up");
			i.removeClass("fa-chevron-up").addClass("fa-chevron-down");
            el.slideUp(200);
        } else {
			jQuery(this).removeClass("expand").addClass("collapse");
            var i = jQuery(this).children(".fa-chevron-down");
			i.removeClass("fa-chevron-down").addClass("fa-chevron-up");
            el.slideDown(200);
        }
    });

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2013-11-25
	 * 
	 * Acción de Borrar Prospecto
	 */
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar el Prospecto seleccionado?",
			title: "Eliminar Prospecto",
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
												$("#table-prospectos").dataTable().fnDestroy();
												getRecords();
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

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2013-11-28
	 * 
	 * Acción de Borrar Prospecto
	 */
	$(document).on('click','.ver',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'showRecordModal';
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
					message: result,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
							}
						}
					},
					className: "modal-ver"
				});

				$('.modal-ver input, file, textarea, select').each(function(){
					$(this).attr('disabled','disabled');
				});

				params = {};
				params.id = id;
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
					}		
				});
			}
		});
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2015-02-06
	 * 
	 * Acción de cambiar el Edo. del Prospecto a 'En Investigación'
	 */
	$(document).on('click','.investigacion',function(e){
		var id = $(this).attr("data-id");
		var element = $(this);

		//Cambia de color el radio
		element.removeClass("btn-default");
		element.addClass("btn-primary");

		element.siblings("label").removeClass("btn-primary");
		element.siblings("label").addClass("btn-default");

		var params = {};
		params.id = id;
		params.accion = "changeStatus";	

		bootbox.dialog({
			message: "¿Está seguro de poner el estado del Prospecto como 'En Investigación'?",
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							type: 'POST',
							url: 'include/Libs.php',
							data: params,
							dataType:'json',
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
												//Lo regresa a su color y lo desactiva
												element.addClass("btn-default");
												element.removeClass("btn-primary");
												element.removeClass("active");

												//Lo deja en estado pendiente
												element.next("label").addClass("btn-primary");
												element.next("label").addClass("active");
												element.next("label").removeClass("btn-default");
											}
										}
									}
								});
							},
							success: function(result){
								bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												if(result.error) {
													element.addClass("btn-default");
													element.removeClass("btn-primary");
													element.removeClass("active");

													//Lo deja en estado pendiente
													element.siblings("label").addClass("btn-primary");
													element.siblings("label").addClass("active");
													element.siblings("label").removeClass("btn-default");
												} else {
													bootbox.hideAll();
													getRecords();
													$("#table-prospectos").css("width", "100%");
												}
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
						element.addClass("btn-default");
						element.removeClass("btn-primary");
						element.removeClass("active");

						//Lo deja en estado pendiente
						element.siblings("label").addClass("btn-primary");
						element.siblings("label").addClass("active");
						element.siblings("label").removeClass("btn-default");
					}
				}
			}
		});
	});

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2013­12-27
 * 
 * Imprime grupos
 */
function getRecords(){
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printGroupsPr',
		data: params,
		dataType:'json',
		error: function(){
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
			if(!result.error){
				$(".box-container").html(result.content);
				$(".promotor").html(result.promotor);
			} else {
				bootbox.dialog({
					message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								bootbox.hideAll();
								window.location = "../index.php";
							}
						}
					}
				});
			}
		}		
	});
}
