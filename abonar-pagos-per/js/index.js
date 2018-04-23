$(document).ready(function(){

	getClientes();

	$(document).on('change','#cliente',function(e) {
		var cliente = $("#cliente").val();
		params = {};
		params.cliente = cliente;
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=getGrupos',
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
				$('.grupo').html(result.select);
			}
		}); 
	});

	$(document).on('change','#grupo',function(e) {
		e.preventDefault();
		var cliente = $("#cliente").val();
		var grupo = $("#grupo").val();
		if(cliente != -1 && grupo != -1) {
			params = {};
			params.cliente = cliente;
			params.grupo = grupo;
			$.ajax({
				type:'post',
				url:'include/Libs.php?accion=getPagosIndividuales',
				dataType:'json',
				data: params,
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
				}
			});

		} else {
			bootbox.dialog({
				message: "Favor de seleccionar un Cliente y Grupo válido.",
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
	});

	$(document).on('change','.pago, .ahorro',function(e) {
		var id = $(this).attr("data-id");
		var pago = $("#tpi_efectuado_"+id).val();
		var ahorro = $("#tpi_ahorro_"+id).val();
		var monto_pagar = $("#tpi_monto_"+id).val();

		pago = parseFloat(pago);
		ahorro = parseFloat(ahorro);
		monto_pagar = parseFloat(monto_pagar);

		if(!isEmpty(pago)) {
			total = pago + ahorro;
			faltante = monto_pagar - pago;

			$("#tpi_pagado_span_"+id).html(total);
			$("#tpi_pagado_"+id).val(total);

			$("#tpi_faltante_span_"+id).html(faltante);
			$("#tpi_faltante_"+id).val(faltante);
		}

	});

    

    $(document).on('click','.guardar',function(e) {
    	e.preventDefault();
    	$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=savePagosInd',
			data: $('#form-pago').serialize(),
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

});

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
			$('.cliente').html(result.select);
			$('#cliente').select2();
		}
	}); 
}

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}






