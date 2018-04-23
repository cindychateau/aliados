$(document).ready(function(){
	getInfo();

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * Select de Semana
	 */
	$(document).on('change','#mes',function(e) {
		var params = {};
		params.mes = $(this).val();
		$.ajax({
			type:'post',
			data:params,
			url:'include/Libs.php?accion=getWeek',
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
				$(".cont-semana").html(result.select);
			}
		});
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * Tablita completita
	 */
	$(document).on('change','#semana',function(e) {
		var params = {};
		params.semana = $(this).val();
		$.ajax({
			type:'post',
			data:params,
			url:'include/Libs.php?accion=getFlujo',
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
				$("#table-flujo tbody").html(result.tabla);
			}
		});
	});

	$(document).on('click','.tck',function(e) {
		var zone = $(this).attr("data-id");
        var el = $(".zone-"+zone);
        if ($(this).hasClass("collapsar")) {
			$(this).removeClass("collapsar").addClass("expandir");
            var i = $(this).children(".fa-chevron-up");
			i.removeClass("fa-chevron-up").addClass("fa-chevron-down");
            el.slideUp(200);
        } else {
			$(this).removeClass("expandir").addClass("collapsar");
            var i = $(this).children(".fa-chevron-down");
			i.removeClass("fa-chevron-down").addClass("fa-chevron-up");
            el.slideDown(200);
        }
    });

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Toda la información de los indicadores
 */
function getInfo() {
	$.ajax({
		type:'post',
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
			$(".riesgo-7").html(result.riesgo_7);
			$(".riesgo-15").html(result.riesgo_15);
			$(".riesgo-30").html(result.riesgo_30);
			$(".riesgo-90").html(result.riesgo_90);
			$(".fecha-7").html(result.fecha_7);
			$(".fecha-15").html(result.fecha_15);
			$(".fecha-30").html(result.fecha_30);
			$(".fecha-90").html(result.fecha_90);
			$(".per-7").html(result.per_7);
			$(".per-15").html(result.per_15);
			$(".per-30").html(result.per_30);
			$(".per-90").html(result.per_90);
		}
	});
}








