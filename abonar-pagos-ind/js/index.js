$(document).ready(function(){

	$(document).on('click','.buscar',function(e) {
		e.preventDefault();
		var id_cred = $("#credito").val();
		if(!isEmpty(id_cred)) {
			params = {};
			params.id = id_cred;
			$.ajax({
				type:'post',
				url:'include/Libs.php?accion=getPagosInd',
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
				message: "Favor de seleccionar un Cliente válido.",
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

$(function() {
	$("#cliente").autocomplete({
		source:"include/Libs.php?accion=showClients",
		select: function (event, ui) {
	    	$("#credito").val(ui.item.credito);
		}
	});
});

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}






