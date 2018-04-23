$(document).ready(function(){
	getMonth();

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * Select de Semana
	 */
	$(document).on('change','#mes',function(e) {
		getWeek();
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
 * @version: 0.1 2016-02-19
 * 
 * Select de Mes
 */
function getMonth() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getMonth',
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
			$(".cont-mes").html(result.select);
			getWeek();
		}
	});
}

function getWeek() {
	var params = {};
	params.mes = $("#mes").val();
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
}








