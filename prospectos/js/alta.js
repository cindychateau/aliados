//var order = ["datos", "ingresos", "referencias", "garantias","credito"];
var order = ["datos", "ingresos", "referencias", "garantias"];
var actual = 0;
var map = null;
var geocoder = null;
var marker = null;
$(document).ready(function(){

	checkButtons();
	//initialize();
	getActivities();
	getMunicipios();

	//Encargada de poner los checkboxes bonitos
	$(".uniform").uniform();

	//Ponemos por default la fecha de hoy
	var myDate = new Date();
	var prettyDate =myDate.getDate() + '/' + (myDate.getMonth()+1) + '/' + myDate.getFullYear();
	$("#fecha").val(prettyDate);

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-21
	 * 
	 * Datepickers
	 */
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

	$(document).on('change','input, textarea',function(e) {
		if($(this).attr("id") != "email") {
			var val = $(this).val();
			var res = val.toUpperCase();
			$(this).val(res);
		}
	});

	$(document).on('change','#municipio',function(e) {
		e.preventDefault();
		getColonias();
		$("#otra_colonia").val("");
		$("#otra_colonia").css("display", "none");
	});

	$(document).on('change','#colonia',function(e) {
		var colonia = $("#colonia").val();
		var cp = $("#colonia option:selected").attr("data-cp");
		$("#cp").val(cp);

		if(colonia == 0) {
			$("#otra_colonia").css("display", "block");
		} else {
			$("#otra_colonia").css("display", "none");
		}

	});


	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-21
	 * 
	 * Aparece el campo de "Especifique" de Vive con Otros
	 */
	$(document).on('change','#check_vive_otro',function(e) {
		if ($(this).is(":checked")) {
			$('#vive_otros').fadeIn();
		} else {
			$('#vive_otros').fadeOut();
		}
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-25
	 * 
	 * Aparece el campo de "Especifique" de Otra Actividad Económica
	 */
	$(document).on('change','#actividades',function(e) {
		var actividad = $(this).val();
		//alert(actividad);
		if (actividad == 0) {
			$('#act_otro').fadeIn();
		} else {
			$('#act_otro').fadeOut();
		}
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-27
	 * 
	 * Aparece el campo de "Razón de Rechazo" si escoge que lo rechacen
	 */
	$(document).on('change','#rechazar',function(e) {
		if ($(this).is(":checked")) {
			$('#razon_rechazo').fadeIn();
		} else {
			$('#razon_rechazo').fadeOut();
		}
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-03-19
	 * 
	 * Acción de hacer click en GUARDAR
	 */
	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('#form-prospecto').submit();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-03-19
	 * 
	 * Guardar Cliente
	 */
	 $(document).on('submit','#form-prospecto',function(e){
	 	e.preventDefault();
		var formdata = new FormData($('form[name="form-prospecto"]')[0]);
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveRecord',
			data: formdata,
			dataType:'json',
			processData: false,
        	contentType: false,
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
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
				$('input, file, textarea, button, select').each(function(){
					$(this).removeAttr('disabled');
				});
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									bootbox.hideAll();
									var id_padre = $('#'+result.focus).parents(".tab-pane").attr("id");
									$(".lnk-"+id_padre).click();
									$('#'+result.focus).focus();
								} else {
									window.location = "index.php";
								}
							}
						}
					}
				});
			}
		});
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-26
	 * 
	 * Al cambiar de tab guarda la tab actual y checa los botones
	 */
	$(document).on('click','.wiz-step',function(e){
		e.preventDefault();
		actual = $(this).attr("data-id");
		checkButtons();
	});


	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-26
	 * 
	 * Al cambiar de tab guarda la tab actual y checa los botones
	 */
	$(document).on('click','.prevBtn, .nextBtn',function(e){
		e.preventDefault();
		if($(this).hasClass("prevBtn")) {
			actual--;
		} else {
			actual++;
		}

		var tab_actual = order[actual];

		$(".lnk-"+tab_actual).click();
		$("html, body").animate({ scrollTop: 0 }, "slow");

	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-10-15
	 * 
	 * Acción de hacer click en Buscar del Mapa
	 */
	$(document).on('click','.search',function(e){
		e.preventDefault();
		var address = $(".address-map").val();
		showAddress(address);
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2015-02-16
	 * 
	 * Cálculo de Resultados Informativos 
	 */
	$(document).on('change','#cred_frec_pago, #cred_importe, #cred_tasa, #cred_plazo, #cred_iva, #cred_interes_m',function(e){
		var frecuencia = $("#cred_frec_pago").val();
		var importe = $("#cred_importe").val();
		var tasa = $("#cred_tasa").val();
		var plazo = $("#cred_plazo").val();
		var iva = $("#cred_iva").val();
		var interes_m = $("#cred_interes_m").val();

		//Si ya están todos los datos llenos
		if(!isEmpty(frecuencia) && !isEmpty(importe) && !isEmpty(tasa) && !isEmpty(plazo) && !isEmpty(iva) && !isEmpty(interes_m)) {
			//Variable para el cálculo de total de interés y gastos de cobranza
			var frec = 1;
			switch(frecuencia) {
			    case "SEMANAL":
			        frec = 4;
			        break;
			    case "QUINCENAL":
			        frec = 2;
			        break;
			    case "MENSUAL":
			        frec = 1;
			        break;
			}

			importe = parseFloat(importe);
			tasa = parseFloat(tasa);
			iva = parseFloat(iva/100);
			interes_m = parseFloat(interes_m/100);

			/*Cálculo de Comisión por Apertura*/
			var comision_apertura = importe*.1;
			comision_apertura = Math.round(comision_apertura * 100) / 100;
			$("#result_com_apertura").val(comision_apertura);

			/*Cálculo de Capital (Total de Crédito)*/
			var capital = importe+comision_apertura;
			capital = Math.round(capital * 100) / 100;
			$("#result_capital").val(capital);

			/*TASA*/
			$("#result_tasa").val(tasa);

			/*Cálculo de Tasa Global*/
			var tasa_global = plazo * (tasa * (1 + iva));
			tasa_global = Math.round(tasa_global * 100) / 100;
			$("#result_tasa_global").val(tasa_global);
			tasa /= 100;
			tasa_global /= 100;

			/*Cálculo Total de Interés*/
			var total_interes = (tasa_global * capital) / frec;
			total_interes = Math.round(total_interes * 100) / 100;
			$("#result_interes").val(total_interes);

			/*Cálculo de Importe por Pago Oportuno*/
			var importe_oportuno = (total_interes + capital) / plazo;
			importe_oportuno = Math.round(importe_oportuno * 100) / 100;
			$("#result_oportuno").val(importe_oportuno);

			/*Cálculo de Bonificación por Pago Oportuno*/
			var bonificacion = importe_oportuno * 0.05;
			bonificacion = Math.round(bonificacion * 100) / 100;
			$("#result_bonificacion").val(bonificacion);

			/*Cálculo de Gastos de Cobranza*/
			var gastos_cobranza = bonificacion * plazo;
			gastos_cobranza = Math.round(gastos_cobranza * 100) / 100;
			$("#result_gastos").val(gastos_cobranza);

			/*Cálculo de Total Documentado*/
			var total_documentado = capital + total_interes + gastos_cobranza;
			total_documentado = Math.round(total_documentado * 100) / 100;
			$("#result_total_documentado").val(total_documentado);

			/*Cálculo de Importe de Pago*/
			var importe_pago = total_documentado / plazo;
			importe_pago = Math.round(importe_pago * 100) / 100;
			$("#result_importe").val(importe_pago);
		}
	}); 

	$(document).on('change','#actividades',function(e) {
		e.preventDefault();
		var actividad = $(this).val();
		if(actividad == 1 || actividad == 13 || actividad == 14 || actividad == 20) {
			$(".act_ventas").removeClass("display-none");
		} else {
			$(".act_ventas").addClass("display-none");
		}
	});

	$(document).on('change','#escolaridad',function(e) {
		e.preventDefault();
		var escolaridad = $(this).val();
		if(escolaridad == 'Otro') {
			$('.cont-otro-esc').removeClass('display-none');
		} else {
			$('.cont-otro-esc').addClass('display-none');
		}
	});

});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-11-26
 * 
 * Verifica si se debe de esconder algún botón
 */
function checkButtons() {
	if(actual == 0) {
		$(".prevBtn").addClass("display-none");
	} else {
		$(".prevBtn").removeClass("display-none");
	}

	if(actual == 3) {
		$(".nextBtn").addClass("display-none");
	} else {
		$(".nextBtn").removeClass("display-none");
	}
}

function initialize() {
  if (GBrowserIsCompatible()) {
    map = new GMap2(document.getElementById("map_canvas"));
    map.setCenter(new GLatLng(37.4419, -122.1419), 1);
    map.setUIToDefault();
    geocoder = new GClientGeocoder();
    marker = new GMarker(new GLatLng(37.4419, -122.1419), {draggable: true});
  }
}

function showAddress(address) {
  if (geocoder) {
    geocoder.getLatLng(
      address,
      function(point) {
        if (!point) {
          alert(address + " no fue encontrada.");
        } else {
          map.setCenter(point, 15);
          marker.setPoint(point);
          //marker = GMarker(point, {draggable: true});
          map.addOverlay(marker);
          GEvent.addListener(marker, "dragend", function() {
            $("#altitud-longitud").val(marker.getLatLng().toUrlValue(6));
          });
          GEvent.addListener(marker, "click", function() {
            $("#altitud-longitud").val(marker.getLatLng().toUrlValue(6));
          });
      GEvent.trigger(marker, "click");
        }
      }
    );
  }
}

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}

/*
 * @author: Cynthia Castillo
 * @version: 0.1 2016-01-21
 * 
 * Regresa select de Actividades
 */	
function getActivities() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getActivities',
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
				$(".div-actividad").html(result.select);
			}
		}
	});
}

function getMunicipios() {
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getMunicipios',
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
				$(".cont-municipios").html(result.select);
				$("#municipio").select2();
				getColonias();
			}
		}
	});
}

function getColonias() {
	var muni = $("#municipio").val();
	var params = {};
	params.municipio = muni;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getColonias',
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
				$(".cont-colonias").html(result.select);
				$("#colonia").select2();
				var cp = $("#colonia option:selected").attr("data-cp");
				$("#cp").val(cp);
			}
		}
	});
}

/*$(function() {
	$("#direccion").autocomplete({
		source:"include/Libs.php?accion=showStreet",
		select: function (event, ui) {
	        $("#direccion").val(ui.item.calle);
			$("#colonia").val(ui.item.colonia);
			$("#municipio").val(ui.item.municipio);
			$("#estado").val(ui.item.estado);
			$("#cp").val(ui.item.cp);
		}
	});
});*/

/*$(function() {
	$("#municipio").autocomplete({
		source:"include/Libs.php?accion=showMunicipios",
		select: function (event, ui) {
	        $("#municipio").val(ui.item.name);
		}
	});
});*/







