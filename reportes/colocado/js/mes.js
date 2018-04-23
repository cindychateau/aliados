$(document).ready(function () {
	params = {};
	params.mes = $("#mes").val();
	params.anio = $("#anio").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getMes',
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
			if(result.error) {
				window.location = "index.php";
			} else {
				$('.mes').html(result.mes);
				$(".table-mes").html(result.table);
			}
			
		}
	}); 
});