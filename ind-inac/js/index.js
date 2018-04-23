$(document).ready(function(){
	getRecords();
	getPromotores();

	$('.fecha').datepicker({
		dateFormat: 'dd/mm/yy',
		monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
			"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
		monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
		changeMonth: true,
    	changeYear: true,
    	yearRange: "-80:+0"
	});

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

	$(document).on('change','#promotor',function(e) {
		e.preventDefault();
		filtrarGrupos();
	});

	$(document).on('change','.fecha',function(e) {
		e.preventDefault();
		var fecha_1 = $("#fecha_1").val();
		var fecha_2 = $("#fecha_2").val();

		if(!isEmpty(fecha_1) && !isEmpty(fecha_2)) {
			filtrarGrupos();
		}
	});
	
});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2013­12-27
 * 
 * Imprime grupos
 */
function getRecords(){
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=printGroups',
		dataType:'json',
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
				$(".box-container").html(result.content);
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
	var fecha_1 = $("#fecha_1").val();
	var fecha_2 = $("#fecha_2").val();
	params = {};
	params.promotor = promotor;
	params.fecha_1 = fecha_1;
	params.fecha_2 = fecha_2;
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
				$(".box-container").html(result.content);
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

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}

