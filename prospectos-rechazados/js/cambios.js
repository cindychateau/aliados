//var order = ["datos", "ingresos", "referencias", "garantias","credito"];
var order = ["datos", "ingresos", "referencias", "garantias"];
var actual = 0;
var map = null;
var geocoder = null;
var marker = null;
$(document).ready(function(){

	checkButtons();
	getRecord();

	//Encargada de poner los checkboxes bonitos
	$(".uniform").uniform();

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-21
	 * 
	 * Datepickers
	 */
	$('#fecha').datepicker({
		dateFormat: 'dd/mm/yy',
		monthNames: ["Enero","Febrero","Marzo","Abril","Mayo","Junio",
			"Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
		monthNamesShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"]
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

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2016-01-28
 * 
 * Consulta en DB los datos del Prospecto y los despliega
 */
function getRecord() {
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showRecord',
		dataType:'json',
		data: params,
		beforeSend: function(){
			$('#table-content').html("<div class='loader'></div>");
		},
		error: function(){
			bootbox.dialog({
				message: "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.",
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							bootbox.hideAll();
							window.location = "index.php";
						}
					}
				}
			});
		},
		success: function(result){
			if(!result.error){
				var prospecto = result.prospecto;

				//Asigna cada input con su respectiva variable
				$('#fecha').val(prospecto.PER_FECHA);
				$('#nombre').val(prospecto.PER_NOMBRE);
				$('#direccion').val(prospecto.PER_DIRECCION);
				$('#email').val(prospecto.PER_EMAIL);
				$('#telefono').val(prospecto.PER_TELEFONO);
				$('#celular').val(prospecto.PER_CELULAR);
				$('#facebook').val(prospecto.PER_FACEBOOK);
				$('#monto_solicitado').val(prospecto.MONTO_SOLICITADO);

				$('#depende_comment_padres').val(prospecto.DEPENDE_PADRES_COMMENT);
				$('#depende_comment_conyugue').val(prospecto.DEPENDE_CONYUGUE_COMMENT);
				$('#depende_comment_hijos').val(prospecto.DEPENDE_HIJOS_COMMENT);
				$('#depende_comment_hermanos').val(prospecto.DEPENDE_HERMANOS_COMMENT);
				$('#depende_comment_otros').val(prospecto.DEPENDE_OTROS_COMMENT);

				$('#act_otro').val(prospecto.ACT_OTRO);
				$('#antiguedad').val(prospecto.ACT_ANTIGUEDAD);
				$('#ingreso_promedio').val(prospecto.INGRESO_SEMANAL);
				$('#ingreso_adicional_1').val(prospecto.INGRESO_ADICIONAL_1);
				if(prospecto.INGRESO_MONTO_1 != "0")
					$('#ingreso_monto_1').val(prospecto.INGRESO_MONTO_1);
				$('#ingreso_adicional_2').val(prospecto.INGRESO_ADICIONAL_2);
				if(prospecto.INGRESO_MONTO_2 != "0")
					$('#ingreso_monto_2').val(prospecto.INGRESO_MONTO_2);
				$('#ingreso_adicional_3').val(prospecto.INGRESO_ADICIONAL_3);
				if(prospecto.INGRESO_MONTO_3 != "0")
					$('#ingreso_monto_3').val(prospecto.INGRESO_MONTO_3);

				if(prospecto.VIVIENDA_GASTO != "0")
					$('#vivienda_gasto').val(prospecto.VIVIENDA_GASTO);

				$('#prestamo_otro_1').val(prospecto.PRESTAMO_OTRO_1);
				if(prospecto.PRESTAMO_PAGO_1 != "0")
					$('#prestamos_pago_1').val(prospecto.PRESTAMO_PAGO_1);
				$('#prestamo_otro_2').val(prospecto.PRESTAMO_OTRO_2);
				if(prospecto.PRESTAMO_PAGO_2 != "0")
					$('#prestamos_pago_2').val(prospecto.PRESTAMO_PAGO_2);

				$('#proyecto_inversion').val(prospecto.PROYECTO_INVERSION);

				$('#referencia_nombre_1').val(prospecto.REFERENCIA_NOMBRE_1);
				$('#referencia_relacion_1').val(prospecto.REFERENCIA_RELACION_1);
				$('#referencia_telefono_1').val(prospecto.REFERENCIA_TELEFONO_1);

				$('#referencia_nombre_2').val(prospecto.REFERENCIA_NOMBRE_2);
				$('#referencia_relacion_2').val(prospecto.REFERENCIA_RELACION_2);
				$('#referencia_telefono_2').val(prospecto.REFERENCIA_TELEFONO_2);

				$('#referencia_nombre_3').val(prospecto.REFERENCIA_NOMBRE_3);
				$('#referencia_relacion_3').val(prospecto.REFERENCIA_RELACION_3);
				$('#referencia_telefono_3').val(prospecto.REFERENCIA_TELEFONO_3);

				$('#referencia_nombre_4').val(prospecto.REFERENCIA_NOMBRE_4);
				$('#referencia_relacion_4').val(prospecto.REFERENCIA_RELACION_4);
				$('#referencia_telefono_4').val(prospecto.REFERENCIA_TELEFONO_4);

				$('#garantia_bien_1').val(prospecto.GARANTIA_BIEN_1);
				$('#garantia_modelo_1').val(prospecto.GARANTIA_MODELO_1);
				$('#garantia_descripcion_1').val(prospecto.GARANTIA_DESCRIPCION_1);

				$('#garantia_bien_2').val(prospecto.GARANTIA_BIEN_2);
				$('#garantia_modelo_2').val(prospecto.GARANTIA_MODELO_2);
				$('#garantia_descripcion_2').val(prospecto.GARANTIA_DESCRIPCION_2);

				$('#garantia_bien_3').val(prospecto.GARANTIA_BIEN_3);
				$('#garantia_modelo_3').val(prospecto.GARANTIA_MODELO_3);
				$('#garantia_descripcion_3').val(prospecto.GARANTIA_DESCRIPCION_3);

				$('#comentarios').val(prospecto.COMENTARIOS);
				$('#razon_rechazo').val(prospecto.RAZON_RECHAZO);

				/*Checkboxes y cosas raras*/
				if(prospecto.VIVE_PADRES == "1") {
					$("#vive_padres").prop("checked", true);
				}

				if(prospecto.VIVE_CONYUGUE == "1") {
					$("#vive_conyugue").prop("checked", true);
				}

				if(prospecto.VIVE_HIJOS == "1") {
					$("#vive_hijos").prop("checked", true);
				}

				if(prospecto.VIVE_HERMANOS == "1") {
					$("#vive_hermanos").prop("checked", true);
				}

				if(!isEmpty(prospecto.VIVE_OTROS)) {
					$("#check_vive_otro").prop("checked", true);
					$("#vive_otros").val(prospecto.VIVE_OTROS);
				}

				if(prospecto.DEPENDE_PADRES == "1") {
					$("#depende_padres").prop("checked", true);
				}

				if(prospecto.DEPENDE_CONYUGUE == "1") {
					$("#depende_conyugue").prop("checked", true);
				}

				if(prospecto.DEPENDE_HIJOS == "1") {
					$("#depende_hijos").prop("checked", true);
				}

				if(prospecto.DEPENDE_HERMANOS == "1") {
					$("#depende_hermanos").prop("checked", true);
				}

				if(prospecto.DEPENDE_OTROS == "1") {
					$("#depende_otros").prop("checked", true);
				}

				if(prospecto.VIVIENDA == "1") {
					$('#vivienda_propia').prop("checked", true);
				} else {
					$('#vivienda_rentada').prop("checked", true);
				}

				$(".uniform").uniform();

				if(prospecto.STATUS == "2") {
					$("#rechazar").attr("checked", "checked");
					$("#rechazar").checked = true;
					$(".switch-off").addClass("switch-on");
					$(".switch-on").removeClass("switch-off");

					$('#razon_rechazo').fadeIn();
				}

				getActivities(prospecto.ACT_ID);
				if(prospecto.ACT_ID == "0") {
					$('#act_otro').fadeIn();
				}

				//Documentos Actuales
				$('#ife_actual').attr("href", ruta+"documentos/ife/"+prospecto.IFE);
				$('#cd_actual').attr("href", ruta+"documentos/domicilio/"+prospecto.COMPROBANTE_DOMICILIO);

			}
			else
				window.location = "index.php";
		}
	});
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
function getActivities(id) {
	var params = {};
	params.id = id;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getActivities',
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
				$(".div-actividad").html(result.select);
			}
		}
	});
}













