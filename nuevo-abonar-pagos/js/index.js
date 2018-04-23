var last_function = "none";
var load = 1;
$(document).ready(function(){
	getTotales();
	getPromotores();
	getClientes();

	if($('#accion').val() == 1) {
		filtrarGrupos();
		last_function == "filtrarGrupos"
	}

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

    $(document).on('click','.paginator',function(e) {
		//alert();
		var new_page = $(this).attr("data-pag");
		$("#page").val(new_page);
		filtrarGrupos();
	});

    $(document).on('click','.autocomplete',function(e) {
    	e.preventDefault();
    	var grupo = $(this).attr("data-id");
    	//alert(grupo);
    	$('.form-'+grupo+' .pago_semanal_ind').each(function(){
		    var id_persona = $(this).attr("data-id");
		    //alert(id_persona);
		    var pago_semanal_ind = $(this).val();
		    $('.form-'+grupo+' #pago_'+id_persona).val(pago_semanal_ind);
		});
    });

    $(document).on('click','.guardar',function(e) {
    	e.preventDefault();
    	var id = $(this).attr("data-id");
    	$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=savePagos',
			data: $('.form-'+id).serialize(),
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
									getGroup(id);
								}
							}
						}
					}
				});
			}
		});
    });

    $(document).on('change','#promotor',function(e) {
		e.preventDefault();
		last_function = "filtrarGrupos";
		filtrarGrupos();
		$('#cliente').val(0).trigger('change.select2');
		$('#grupo').val('');
		$('#page').val(1);
		load = 1;
	});

	$(document).on('change','#cliente',function(e) {
		e.preventDefault();
		last_function = "filterClients";
		filterClients();
		$('#promotor').val(0).trigger('change.select2');
		$('#grupo').val('');
		$('#page').val(1);
	});

	$(document).on('change','#activo_inac',function(e) {
		if(last_function == "filtrarGrupos") {
			filtrarGrupos();
			$('#page').val(1);
		}
	});

	$(document).on('click','.reload',function(e) {
		e.preventDefault();
		var gru_id = $(this).attr("data-id");
		getGroup(gru_id);
	});

	$(document).on('click','.transferir-ahorro',function(e) {
		e.preventDefault();
		var per_id = $(this).attr("data-per");
		var gru_id = $(this).attr("data-gru");
		var select = $('.cont-sel-'+gru_id).html();
		select = '<select class="form-control" id="select-'+gru_id+'">'+select+'</select>';

		var params = {};
		params.per_id = per_id;
		params.gru_id = gru_id;
		params.accion = 'transferirAhorro';


		bootbox.dialog({
			message: "Escoja el Cliente a transferir. <br><br>"+select,
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {

						params.destino = $("#select-"+gru_id).val();
						//console.log(params.destino);
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
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												getGroup(gru_id);
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

	$(document).on('click','#buscar',function(e) {
		e.preventDefault();
		last_function = "filterByGroup";
		filterByGroup();
		$('#promotor').val(0).trigger('change.select2');
		$('#cliente').val(0).trigger('change.select2');
		$('#page').val(1);
	});

});


/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Select de Promotore
 */
function getTotales() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getTotales',
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
			$(".cont-totales").html(result.totales);
		}
	});
} 

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
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
			$('#cliente').select2()
		}
	}); 
}

function filtrarGrupos() {
	var promotor = $("#promotor").val();
	var activo_inac = $("#activo_inac").val();
	params = {};
	params.promotor = promotor;
	params.activo_inac = activo_inac;
	params.page = $('#page').val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=filterGroups',
		dataType:'json',
		data: params,
		beforeSend: function() {
			$(".cont-pagos").html("");
            $("input, button").attr("disabled", "disabled");
            $(".cont-pagos").addClass("loader");
            $('.cont-paginas').html('');
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
            $("input, button").removeAttr("disabled");
			if(!result.error){
				$(".cont-pagos").html(result.pagos);
				$('.cont-paginas').html(result.paginas);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
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

/*function filtrarGrupos() {
	var promotor = $("#promotor").val();
	var activo_inac = $("#activo_inac").val();
	params = {};
	params.promotor = promotor;
	params.activo_inac = activo_inac;
	params.load = load;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=filterGroups',
		dataType:'json',
		data: params,
		beforeSend: function() {
            $("input, button").attr("disabled", "disabled");
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
            $("input, button").removeAttr("disabled");
			if(!result.error){
				$(".cont-pagos").append(result.pagos);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
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
}*/

function filterClients() {
	var cliente = $("#cliente").val();
	var activo_inac = $("#activo_inac").val();
	params = {};
	params.cliente = cliente;
	params.activo_inac = activo_inac;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=filterClients',
		dataType:'json',
		data: params,
		beforeSend: function() {
			$(".cont-pagos").html("");
            $("input, button").attr("disabled", "disabled");
            $(".cont-pagos").addClass("loader");
            $('.cont-paginas').html('');

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
            $("input, button").removeAttr("disabled");
			if(!result.error){
				$(".cont-pagos").html(result.pagos);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
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

function filterByGroup() {
	var grupo = $("#grupo").val();
	params = {};
	params.grupo = grupo;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=filterByGroup',
		dataType:'json',
		data: params,
		beforeSend: function() {
			$(".cont-pagos").html("");
            $("input, button").attr("disabled", "disabled");
            $(".cont-pagos").addClass("loader");
            $('.cont-paginas').html('');
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
            $("input, button").removeAttr("disabled");
			if(!result.error){
				$(".cont-pagos").html(result.pagos);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
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

/*$(function() {
	$("#clientes").autocomplete({
		source:"include/Libs.php?accion=showClients",
		select: function (event, ui) {
	    	$('form').addClass('display-none');
	    	$('.form-'+ui.item.grupo).removeClass('display-none');
		}
	});
});*/

function getGroup(id) {
	params = {};
	params.grupo = id;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=reloadGroup',
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
				$(".div-"+id+" .box-body").html(result.pagos);
				$('.pop-hover').popover({
					trigger: 'hover',
					placement: 'left'
				});
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






