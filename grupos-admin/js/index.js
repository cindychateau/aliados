$(document).ready(function(){
	getRecords();
	getPromotores();

	$('.fecha').datepicker({
		dateFormat: 'dd/mm/yy',
		monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
			"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
		monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
		changeMonth: true,
    	changeYear: true,
    	yearRange: "-80:+0"
	});

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

	$(document).on('change','.tipo',function(e) {
		e.preventDefault();
		/*var val = $(this).attr("data-value");
		$("#tipo").val(val);*/
		filtrarGrupos();
	});

	$(document).on('change','#promotor',function(e) {
		e.preventDefault();
		filtrarGrupos();
	});

	$(document).on('change','.fecha',function(e) {
		e.preventDefault();
		var fecha_1 = $("#fecha_1").val();
		var fecha_2 = $("#fecha_2").val();

		if(!isEmpty(fecha_1) && !isEmpty(fecha_2)) {
			filtrarGrupos();
		}
	});

	$(document).on('click','.editar-promotor',function(e) {
		e.preventDefault();
		var grupo = $(this).attr("data-id");
		var select = $('#promotor').html();
		bootbox.dialog({
		message: "<form id='form-promotor' name='form-promotor' role='form' class='form-horizontal' action='include/Libs.php?accion=editPromotor'>"+
					"<select class='form-control' id='promotor1' name='promotor1'>"+select+"</select>"+
					"<input type='hidden' id='grupo' name='grupo' value="+grupo+">"+
					"<script>$('#promotor1').select2();</script>"+
				  "</form>",
		buttons: {
			main: {
			    label: "Enviar",
			    className: "btn-primary",
			    callback: function() {
			    	$.ajax({
						url: $('#form-promotor').attr("action"),
						type: 'POST',
						data: $('#form-promotor').serialize(),
						dataType: 'JSON',
						error: function (){
							bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
						}, success: function (result) {
							bootbox.dialog({
								message: result.msg,
								buttons: {
									cerrar: {
										label: "Cerrar",
										callback: function() {
											if(!result.error) {
												bootbox.hideAll();
												getRecords();
											}
										}
									}
								}
							});
						}
					});
					return false;
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
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printGroups',
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
				$('#total').html(result.total);
			} else {
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
			}
		}		
	});
}

function getPromotores() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getPromotores2',
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
			$('.container-promotores').html(result.select);
			$('#promotor').select2()
		}
	}); 
}

function filtrarGrupos() {
	var promotor = $("#promotor").val();
	//var tipo = $("#tipo").val();
	var fecha_1 = $("#fecha_1").val();
	var fecha_2 = $("#fecha_2").val();

	var tipo = [];

	// Initializing array with Checkbox checked values
	$("input[name='tipo']:checked").each(function(){
	    tipo.push(this.value);
	});

	params = {};
	params.promotor = promotor;
	params.tipo = tipo;
	params.fecha_1 = fecha_1;
	params.fecha_2 = fecha_2;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=filterGroups',
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
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){
				$(".box-container").html(result.content);
				$('#total').html(result.total);
			} else {
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
			}
		}		
	});
}

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}
