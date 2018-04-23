//var order = ["datos", "ingresos", "referencias", "garantias","credito"];
var order = ["grupos", "datos", "ingresos", "referencias", "garantias"];
var actual = 0;
var map = null;
var geocoder = null;
var marker = null;
$(document).ready(function(){

	/*$('input, file, textarea, select, checkbox').each(function(){
		$(this).attr('disabled','disabled');
	});*/

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
		var val = $(this).val();
		var res = val.toUpperCase();
		$(this).val(res);
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

	if(actual == 4) {
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
				$('#fecha_nac').val(prospecto.PER_FECHA_NAC);
				$('#nombre').val(prospecto.PER_NOMBRE);
				$('#apellido_pat').val(prospecto.PER_APELLIDO_PAT);
				$('#apellido_mat').val(prospecto.PER_APELLIDO_MAT);
				$("#genero").val(prospecto.PER_GENERO);
				$("#edo_civil").val(prospecto.PER_EDO_CIVIL);
				$("#escolaridad").val(prospecto.PER_ESCOLARIDAD);
				if(prospecto.PER_ESCOLARIDAD == 'Otro') {
					$('.cont-otro-esc').removeClass('display-none');
				}
				$("#otro_escolaridad").val(prospecto.PER_ESCOLARIDAD_OTRO);

				$('#direccion').val(prospecto.PER_DIRECCION);
				$('#numero').val(prospecto.PER_NUM);

				getMunicipios(prospecto.PER_MUNICIPIO, prospecto.PER_COLONIA);

				if(prospecto.PER_COLONIA == 0) {
					$("#otra_colonia").css("display", "block");
				}

				//$('#colonia').val(prospecto.PER_COLONIA);
				$('#otra_colonia').val(prospecto.PER_COLONIA_OTRA);
				//$('#municipio').val(prospecto.PER_MUNICIPIO);

				//$('#estado').val(prospecto.PER_ESTADO);
				$('#cp').val(prospecto.PER_CP);
				$("#antiguedad_propiedad").val(prospecto.ANTIGUEDAD_DIRECCION);

				$('#email').val(prospecto.PER_EMAIL);
				$('#telefono').val(prospecto.PER_TELEFONO);
				$('#celular').val(prospecto.PER_CELULAR);
				$('#monto_solicitado').val(prospecto.MONTO_SOLICITADO);

				if(prospecto.DEPENDE_PADRES_COMMENT != "0")
					$('#depende_comment_padres').val(prospecto.DEPENDE_PADRES_COMMENT);
				if(prospecto.DEPENDE_CONYUGUE_COMMENT != "0")
					$('#depende_comment_conyugue').val(prospecto.DEPENDE_CONYUGUE_COMMENT);
				if(prospecto.DEPENDE_HIJOS_COMMENT != "0")
					$('#depende_comment_hijos').val(prospecto.DEPENDE_HIJOS_COMMENT);
				if(prospecto.DEPENDE_HERMANOS_COMMENT != "0")
					$('#depende_comment_hermanos').val(prospecto.DEPENDE_HERMANOS_COMMENT);
				if(prospecto.DEPENDE_OTROS_COMMENT != "0")
					$('#depende_comment_otros').val(prospecto.DEPENDE_OTROS_COMMENT);

				$('#act_otro').val(prospecto.ACT_OTRO);
				$('#antiguedad').val(prospecto.ACT_ANTIGUEDAD);
				$('#act_direccion').val(prospecto.ACT_DIRECCION);
				$('#act_establecimiento').val(prospecto.ACT_ESTABLECIMIENTO);
				$('#act_num_trabajadores').val(prospecto.ACT_NUM_TRABAJADORES);
				if(prospecto.ACT_VENTAS != ''){
					$("#ventas_empresa").val(prospecto.ACT_VENTAS);
				}
				if(prospecto.ACT_ID == 1 || prospecto.ACT_ID == 13 || prospecto.ACT_ID == 14 || prospecto.ACT_ID == 20) {
					$(".act_ventas").removeClass("display-none");
				} else {
					$(".act_ventas").addClass("display-none");
				}

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

				$('#egr_luz').val(prospecto.EGR_LUZ);
				$('#egr_gas').val(prospecto.EGR_GAS);
				$('#egr_agua').val(prospecto.EGR_AGUA);
				$('#egr_transporte').val(prospecto.EGR_TRANSPORTE);
				$('#egr_alimentos').val(prospecto.EGR_ALIMENTOS);
				$('#egr_celular').val(prospecto.EGR_CELULAR);
				$('#egr_recreacion').val(prospecto.EGR_RECREACION);


				if(prospecto.VIVIENDA_GASTO != "0")
					$('#vivienda_gasto').val(prospecto.VIVIENDA_GASTO);
				$('#vivienda_nombre').val(prospecto.VIVIENDA_NOMBRE);
				$('#vivienda_num_habitaciones').val(prospecto.VIVIENDA_NUM_HABITACIONES);
				$('#vivienda_num_autos').val(prospecto.VIVIENDA_NUM_AUTOS);

				$('#prestamo_otro_1').val(prospecto.PRESTAMO_OTRO_1);
				if(prospecto.PRESTAMO_PAGO_1 != "0")
					$('#prestamos_pago_1').val(prospecto.PRESTAMO_PAGO_1);
				$('#prestamo_otro_2').val(prospecto.PRESTAMO_OTRO_2);
				if(prospecto.PRESTAMO_PAGO_2 != "0")
					$('#prestamos_pago_2').val(prospecto.PRESTAMO_PAGO_2);

				//$('#proyecto_inversion').val(prospecto.PROYECTO_INVERSION);

				$('#referencia_nombre_1').val(prospecto.REFERENCIA_NOMBRE_1);
				if(!isEmpty(prospecto.REFERENCIA_RELACION_1))
					$('#referencia_relacion_1').val(prospecto.REFERENCIA_RELACION_1);
				$('#referencia_telefono_1').val(prospecto.REFERENCIA_TELEFONO_1);
				if(prospecto.REFERENCIA_CLIENTE_1 == "1") {
					$("#referencia_cliente_1").attr("checked", "checked");
					$("#referencia_cliente_1").checked = true;
					$(".cont_ref_cliente_1 .switch-off").addClass("switch-on");
					$(".cont_ref_cliente_1 .switch-on").removeClass("switch-off");
				}

				$('#referencia_nombre_2').val(prospecto.REFERENCIA_NOMBRE_2);
				if(!isEmpty(prospecto.REFERENCIA_RELACION_2))
					$('#referencia_relacion_2').val(prospecto.REFERENCIA_RELACION_2);
				$('#referencia_telefono_2').val(prospecto.REFERENCIA_TELEFONO_2);
				if(prospecto.REFERENCIA_CLIENTE_2 == "1") {
					$("#referencia_cliente_2").attr("checked", "checked");
					$("#referencia_cliente_2").checked = true;
					$(".cont_ref_cliente_2 .switch-off").addClass("switch-on");
					$(".cont_ref_cliente_2 .switch-on").removeClass("switch-off");
				}

				$('#referencia_nombre_3').val(prospecto.REFERENCIA_NOMBRE_3);
				if(!isEmpty(prospecto.REFERENCIA_RELACION_3))
					$('#referencia_relacion_3').val(prospecto.REFERENCIA_RELACION_3);
				$('#referencia_telefono_3').val(prospecto.REFERENCIA_TELEFONO_3);
				if(prospecto.REFERENCIA_CLIENTE_2 == "1") {
					$("#referencia_cliente_3").attr("checked", "checked");
					$("#referencia_cliente_3").checked = true;
					$(".cont_ref_cliente_3 .switch-off").addClass("switch-on");
					$(".cont_ref_cliente_3 .switch-on").removeClass("switch-off");
				}

				$('#referencia_nombre_4').val(prospecto.REFERENCIA_NOMBRE_4);
				if(!isEmpty(prospecto.REFERENCIA_RELACION_4))
					$('#referencia_relacion_4').val(prospecto.REFERENCIA_RELACION_4);
				$('#referencia_telefono_4').val(prospecto.REFERENCIA_TELEFONO_4);
				if(prospecto.REFERENCIA_CLIENTE_2 == "1") {
					$("#referencia_cliente_4").attr("checked", "checked");
					$("#referencia_cliente_4").checked = true;
					$(".cont_ref_cliente_4 .switch-off").addClass("switch-on");
					$(".cont_ref_cliente_4 .switch-on").removeClass("switch-off");
				}

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

				$('#ife_num').val(prospecto.IFE_NUM);
				$('#rfc').val(prospecto.PER_RFC);
				$('#curp').val(prospecto.PER_CURP);

				$("#jsub_riesgo").val(prospecto.JSUB_RIESGO);
				$("#jsub_honestiadad").val(prospecto.JSUB_HONESTIDAD);
				$("#jsub_calidad_ref").val(prospecto.JSUB_CALIDAD_REF);
				$("#jsub_habilidad_empr").val(prospecto.JSUB_HABILIDADES_EMPR);
				$("#jsub_calidad_neg").val(prospecto.JSUB_CALIDAD_NEG);
				$("#jsub_entendimiento_cred").val(prospecto.JSUB_ENTENDIMIENTO_CRED);
				$("#jsub_inver_rec").val(prospecto.JSUB_INVERS_REC);
				$("#jsub_entendimiento_tasas").val(prospecto.JSUB_ENTENDIMIENTO_TASAS);
				$("#jsub_apoyo_fam").val(prospecto.JSUB_APOYO_FAM);
				$("#jsub_apariencia_casa").val(prospecto.JSUB_APARIENCIA_CASA);

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
					$(".cont-rechazar .switch-off").addClass("switch-on");
					$(".cont-rechazar .switch-on").removeClass("switch-off");

					$('#razon_rechazo').fadeIn();
				}

				getActivities(prospecto.ACT_ID);
				if(prospecto.ACT_ID == "0") {
					$('#act_otro').fadeIn();
				}

				//Documentos Actuales
				$('#ife_actual').attr("href", ruta+"documentos/ife/"+prospecto.IFE);
				$('#cd_actual').attr("href", ruta+"documentos/domicilio/"+prospecto.COMPROBANTE_DOMICILIO);

				getGrupos(prospecto.PER_ID);

			}
			else
				window.location = "index.php";
		}
	});
}

function getMunicipios(municipio, colonia) {
	var params = {};
	params.muni = municipio;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getMunicipios',
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
				$(".cont-municipios").html(result.select);
				$("#municipio").select2();
				getColoniasC(colonia);
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

function getColoniasC(colonia) {
	var muni = $("#municipio").val();
	var params = {};
	params.municipio = muni;
	params.colonia = colonia;
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

				if(result.otra) {
					$("#otra_colonia").css("display", "block");
					$("#otra_colonia").val(colonia);
				}
				//var cp = $("#colonia option:selected").attr("data-cp");
				//$("#cp").val(cp);
			}
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

function getGrupos(id) {
	var params = {};
	params.id = id;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getGrupos',
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
				$(".cont-grupos").html(result.grupos);
			}
		}
	});
}

$(function() {
	$("#municipio").autocomplete({
		source:"include/Libs.php?accion=showMunicipios",
		select: function (event, ui) {
	        $("#municipio").val(ui.item.name);
		}
	});
});













