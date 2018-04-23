var colocado_arr = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
var original = [21726.30, 44423.80, 68101.30, 73972.50, 57461.30, 17325.00, 700.00, 700.00, 525.00, 0.00, 0.00, 0.00];
var pagos_intereses = [0, 0, 3500,3500,3500,3500,3500,3500,3500,3500,3500,3500];	
$(document).ready(function(){
	getIS();

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


	$(document).on('change','.colocado',function(e) {
		var id = $(this).attr("data-id");
		var colocado = $(this).val();
		colocado_arr[id] = colocado;
		//alert(colocado_arr[id]);
		reevaluar();
	});



});

function reevaluar() {
	var pagos = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
	var intereses = [0, 0, 0, 73972.50, 0, 0, 0, 0, 0, 0, 0, 0];
	for (var i = 4; i < 12; i++) {
		tasa = 0.0175;
		plazo = 12;
		colocado_arr[i] = parseFloat(colocado_arr[i]);
		pagos[i] = ((colocado_arr[i]*(tasa * plazo)) + colocado_arr[i]) / plazo;
	}

	for (var x = 11; x >= 4; x--) {
		interes = original[x];
		interes += pagos[x]*4;
		if(x > 5) {
			interes += pagos[x-2]*4;
		}

		if(x > 4) {
			interes += pagos[x-1]*4;
		}

		intereses[x] = interes.toFixed();
		//alert(intereses[x]);

		$(".ingresos-"+x).html(intereses[x]);
		
	} 

	for (var i = 4; i < 12; i++) {
		incremento = 0;
		if(intereses[i-1] != 0 && intereses[i] != 0)
			incremento = ((intereses[i] / intereses[i-1])-1)*100;
		incremento = Math.round(incremento * 100) / 100;
		$(".incremento-"+i).html(incremento);

		revenue = 0;
		if(pagos_intereses[i] != 0 && intereses[i] != 0)		
			revenue = pagos_intereses[i] / intereses[i] * 100;
		revenue = Math.round(revenue * 100) / 100;
		$(".revenues-"+i).html(revenue);

		margen_financiero = intereses[i] - pagos_intereses[i];
		margen_financiero = Math.round(margen_financiero * 100) / 100;
		$(".margenf-"+i).html(margen_financiero);

		margen = 0;
		if(margen_financiero != 0 && intereses[i] != 0) 
			margen = margen_financiero / intereses[i] * 100;
		margen = Math.round(margen * 100) / 100;
		$(".margen-"+i).html(margen);

		
	}
}


/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-02-19
 * 
 * Select de Mes
 */
function getIS() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getIs',
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
			$("#table-content").html(result.tabla);
		}
	});
}








