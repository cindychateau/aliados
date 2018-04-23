
$(document).ready(function () {
    getColocacion();
});

function getColocacion() {
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getColocacion',
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
            if(result.error) {
                window.location = "index.php";
            } else {
                $(".table-colocacion").html(result.table);
            }
            
        }
    }); 
}