$(document).ready(function(){
	getTotales();

	/*$(document).on('click','.box .tools .collapse, .box .tools .expand', function(e) {
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
    });*/

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






