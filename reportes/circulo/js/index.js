$(document).ready(function () {

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

    $(document).on('change','.fecha',function(e) {
        e.preventDefault();
        var fecha_1 = $("#fecha_1").val();
        var fecha_2 = $("#fecha_2").val();

        if(!isEmpty(fecha_1) && !isEmpty(fecha_2)) {
            getCirculo();
        }
    });

    $(document).on('click','.generar',function(e) {
        e.preventDefault();
        var fecha_1 = $("#fecha_1").val();
        var fecha_2 = $("#fecha_2").val();

        if(!isEmpty(fecha_1) && !isEmpty(fecha_2)) {
            getExcel();
        } else {
            bootbox.dialog({
                message: "Favor de especificar las Fechas.",
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

    $(document).on('submit','#circulo',function(e) {
        e.preventDefault();
    });

});

function getCirculo() {
    /*var fecha_1 = $("#fecha_1").val();
    var fecha_2 = $("#fecha_2").val();
    params = {};
    params.fecha_1 = fecha_1;
    params.fecha_2 = fecha_2;*/
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getCirculo',
        dataType:'json',
        data: $('#circulo').serialize(),
        beforeSend: function() {
            $("input, button").attr("disabled", "disabled");
            $(".cont-loader").addClass("loader");
        },
        error: function(){
            bootbox.dialog({
                message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
                buttons: {
                    cerrar: {
                        label: "Cerrar",
                        callback: function() {
                            $(".cont-loader").removeClass("loader");
                            $("input, button").removeAttr("disabled");
                            $(".disabled").attr("disabled", "disabled");
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
                $(".table-circulo").html(result.table);
                $(".cont-loader").removeClass("loader");
                $("input, button").removeAttr("disabled");
                $(".disabled").attr("disabled", "disabled");
            }
            
        }
    }); 
}

function getExcel() {
    /*var fecha_1 = $("#fecha_1").val();
    var fecha_2 = $("#fecha_2").val();
    params = {};
    params.fecha_1 = fecha_1;
    params.fecha_2 = fecha_2;*/
    $.ajax({
        type: 'POST',
        url: 'include/Libs.php?accion=getExcel',
        dataType:'json',
        data: $('#circulo').serialize(),
        beforeSend: function() {
            $("input, button").attr("disabled", "disabled");
            $(".cont-loader").addClass("loader");
            $(".cont-guardar").html('');
        },
        error: function(){
            bootbox.dialog({
                message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
                buttons: {
                    cerrar: {
                        label: "Cerrar",
                        callback: function() {
                            $(".cont-loader").removeClass("loader");
                            $("input, button").removeAttr("disabled");
                            $(".disabled").attr("disabled", "disabled");
                            $(".cont-guardar").html('');
                            bootbox.hideAll();
                        }
                    }
                }
            });
        },
        success: function(result){
            if(result.error) {
                window.location = "index.php";
            } else {
                $(".cont-loader").removeClass("loader");
                $("input, button").removeAttr("disabled");
                $(".disabled").attr("disabled", "disabled");
                $(".cont-guardar").html('<a class="btn btn-success"  href="include/circulo-credito.xlsx"><i class="fa fa-save"></i> Guardar Excel</a>');
            }
            
        }
    });
}

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}