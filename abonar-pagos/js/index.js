$(document).ready(function(){
	getPagos();
	getTotales();
	getPromotores();

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

    $(document).on('change','.pago, .ahorro',function(e) {
    	var num = $(this).attr("data-num");
    	var id = $(this).attr("data-id");
    	var grupo = $(this).attr("data-group");

    	var total_pago = 0;
    	var total_ahorrado = 0;
    	var total_pagado = 0;
    	var total_faltante = 0;
    	var total_individual = 0;
    	var faltante_individual = 0;

    	var semanal = $(".semanal_"+num).html();
    	semanal = semanal.replace(',','');
    	semanal = parseFloat(semanal);
    	var semanal_total = $(".semanal_total_"+grupo).html();
    	semanal_total = semanal_total.replace(',','');
    	semanal_total = parseFloat(semanal_total);

    	/*var pago = $("#pago_"+id+"_"+grupo).val();
    	pago = parseFloat(pago);
    	var ahorro = $("#ahorro_"+id+"_"+grupo).val();
    	ahorro = parseFloat(ahorro);*/
    	$('.dinero_'+num).each(function() {
			var dinero_i = $(this).val();
			if(!isEmpty(dinero_i)) {
				dinero_i = parseFloat(dinero_i);
				total_individual += dinero_i;
			}
		});

    	$(".pago_individual_"+num).html(total_individual);

    	var pago = $("#pago_"+id+"_"+grupo).val();
    	if(!isEmpty(pago)) {
    		pago = parseFloat(pago);
    	} else {
    		pago = 0;
    	}

    	var faltante_actual = $('#pendiente_org_'+id+"_"+grupo).val();
    	faltante_individual = faltante_actual-pago;
		$("#pendiente_"+id+"_"+grupo).val(faltante_individual); 
		$(".faltante_individual_"+num).html(faltante_individual); 
		if(faltante_individual > 0) {
			$(".falt_ind_"+num).addClass("danger");
			$(".status_"+num).html('<i class="fa fa-times"></i>');
			$(".status_"+num).removeClass("success");
		} else {
			$(".falt_ind_"+num).removeClass("danger");
			$(".status_"+num).html('<i class="fa fa-check"></i>');
			$(".status_"+num).addClass("success");
		}

		$('.pago_'+grupo).each(function() {
			var pago_i = $(this).val();
			if(!isEmpty(pago_i)) {
				pago_i = parseFloat(pago_i);
				total_pago += pago_i;
				total_pagado += pago_i;
			}
		});

		$(".pago-semanal-efectuado-"+grupo).html(total_pago);
		$("#efectuado-"+grupo).val(total_pago);

		$('.ahorro_'+grupo).each(function() {
			var ahorro_i = $(this).val();
			if(!isEmpty(ahorro_i)) {
				ahorro_i = parseFloat(ahorro_i);
				total_ahorrado += ahorro_i;
				total_pagado += ahorro_i;
			}
		});

		$(".total-ahorro-"+grupo).html(total_ahorrado);
		$("#total-ahorro-"+grupo).val(total_ahorrado);

		$(".total-pagado-"+grupo).html(total_pagado);
		$("#total-pagado-"+grupo).val(total_pagado);
		
		var total_faltante_actual = $('#total-faltante-org-'+grupo).val();
		total_faltante = total_faltante_actual - total_pago;
		$(".total-faltante-"+grupo).html(total_faltante); 
		$("#total-faltante-"+grupo).val(total_faltante);
		if(total_faltante > 0) {
			$(".total-faltante-"+grupo).addClass("danger");
		} else {
			$(".total-faltante-"+grupo).removeClass("danger");
		}
    });

    $(document).on('click','.guardar',function(e) {
    	e.preventDefault();
    	var id = $(this).attr("data-id");
    	$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=guardarPago',
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
								} /*else {
									window.location = "index.php";
								}*/
							}
						}
					}
				});
			}
		});
    });

    $(document).on('change','#promotor',function(e) {
		e.preventDefault();
		filtrarGrupos();
	});

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Select de Promotore
 */
function getPagos() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getPagos',
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
			$(".cont-pagos").html(result.pagos);

			$(".fechas").html(result.fecha_inicio+" - "+result.fecha_fin);

		}
	});
} 

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
		}
	}); 
}

function filtrarGrupos() {
	var promotor = $("#promotor").val();
	params = {};
	params.promotor = promotor;
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
				$(".cont-pagos").html(result.pagos);
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

$(function() {
	$("#clientes").autocomplete({
		source:"include/Libs.php?accion=showClients",
		select: function (event, ui) {
	    	$('form').addClass('display-none');
	    	$('.form-'+ui.item.grupo).removeClass('display-none');
		}
	});
});






