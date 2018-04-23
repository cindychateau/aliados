$(document).ready(function(){
	getPromotor();

	$(document).on('change','#promotor',function(e) {
		var promotor = $(this).val();
		getInfo(promotor);
	});

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Toda la información de los indicadores
 */
function getInfo(id_promotor) {
	var params = {};
	params.id = id_promotor;
	$.ajax({
		type:'post',
		data:params,
		url:'include/Libs.php?accion=getInfo',
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
			$(".clientes").html(result.total_clientes);
			$(".clientes-activos").html(result.clientes_activos);
			$(".grupos").html(result.total_grupos);
			$(".grupos-activos").html(result.grupos_activos);
			$(".clientes").html(result.total_clientes);
			$(".pendientes-entregar").html(result.pendientes_entregar);
			$(".saldo-promedio").html(result.saldo_promedio);
			$(".saldo-bruto").html(result.saldo_bruto);
			$(".ganancias-interes").html(result.ganancias);
			$(".cartera-activa").html(result.cartera_activa);
			$(".cartera-historica").html(result.cartera_historica);
		}
	});
}

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Select de Promotore
 */
function getPromotor() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getPromotor',
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
			$(".cont-promotor").html(result.select);
		}
	});
} 








