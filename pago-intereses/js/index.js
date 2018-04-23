$(document).ready(function(){
	getProgress();
	getPayments();

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-03-10
 * 
 * Toda la información de los indicadores
 */
function getProgress() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getProgress',
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
			$(".cont-panel").html(result.panel);
		}
	});
}

function getPayments() {
	$.ajax({
		type:'post',
		url:'include/Libs.php?accion=getPayments',
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
			$(".cont-table").html(result.calendar);
			$('.pop-hover').popover({
				trigger: 'hover',
				html: true
			});
		}
	});
}







