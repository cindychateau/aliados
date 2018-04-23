var clientes = 0;
var ids_clientes = new Array();
//var es_recredito = false;
$(document).ready(function(){
	getIndividual();
	//getPromotores();

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
		dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"]
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-03-19
	 * 
	 * Acción de hacer click en GUARDAR
	 */
	$(document).on('click','.guardar',function(e){
		e.preventDefault();
		$('#form-grupo').submit();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-03-19
	 * 
	 * Guardar Cliente
	 */
	 $(document).on('submit','#form-grupo',function(e){
	 	e.preventDefault();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=saveIndividual',
			data: $('form[name="form-grupo"]').serialize(),
			dataType:'json',
			beforeSend: function(){
				$('input, file, textarea, button, select').each(function(){
					$(this).attr('disabled','disabled');
				});
			},
			error: function(){
				$('input, file, textarea, button, select').each(function(){
					//if(!$(this).hasClass("disabled")) {
						$(this).removeAttr('disabled');
					//}
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
					//if(!$(this).hasClass("disabled")) {
						$(this).removeAttr('disabled');
					//}
				});
				bootbox.dialog({
					message: result.msg,
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								if(result.error) {
									bootbox.hideAll();
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
	 * @version: 0.1 2016-01-29
	 * 
	 * Acción de agregar Cliente
	 */
	$(document).on('click','.agregar-cl',function(e){
		e.preventDefault();
		$("#clientes").val("");
		var ahorro = $("#ahorro_di").val()
		var id_cl = $("#bck_id").val();
		if(!isEmpty(id_cl)) {
			if(jQuery.inArray(id_cl,ids_clientes) == -1) {
				var row = '<tr id="row_'+clientes+'">'+
							'<td align="center">'+
								$("#bck_nombre").val()+
							'	<input type="text" id="cli_id_'+clientes+'" name="cli_id" data-id="'+clientes+'" value="'+$("#bck_id").val()+'" style="display:none;">'+
							'</td>'+
							'<td align="center">'+
								$("#bck_direccion").val()+
							'</td>'+
							'<td align="center">'+
								$("#bck_telefono").val()+
							'</td>'+
							'<td align="center">'+
								$("#bck_monto").val()+
							'</td>'+
							'<td align="center">'+
								'<input id="monto_individual_'+clientes+'" name="monto_individual" type="text" class="form-control monto_individual" data-id="'+clientes+'">'+
							'</td>'+
							'<td align="center">'+
								'<input id="ahorro_d_'+clientes+'" name="ahorro_d" type="text" class="form-control ahorro_d" data-id="'+clientes+'" readonly="readonly" value="'+ahorro+'">'+
							'</td>'+
							'<td align="center">'+
								'<input id="comision_d_'+clientes+'" name="comision_d" type="text" class="form-control comision_d" data-id="'+clientes+'" readonly="readonly">'+
							'</td>'+
							'<td align="center">'+
								'<input id="monto_otorgar_'+clientes+'" name="monto_otorgar" type="text" class="form-control monto_otorgar" data-id="'+clientes+'" readonly="readonly">'+
							'</td>'+
							'<td align="center">'+
								'<input id="pago_semanal_'+clientes+'" name="pago_semanal" type="text" class="form-control pago_semanal" data-id="'+clientes+'" readonly="readonly">'+
							'</td>'+
							'<td align="center" class="cont-button">'+
							'	<a class="eliminar-cl" href="#" data-id="'+clientes+'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>'+
							'</td>'+
						'</tr>';
				$(".clientes-tb tbody").html(row);
				clientes++;	
				ids_clientes.push(id_cl);
				//recalculate();
			} else {
				bootbox.dialog({
					message: "El Cliente seleccionado ya se encuentra en la lista.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								$("#clientes").focus();
							}
						}
					}
				});
			}
		} else {
			bootbox.dialog({
					message: "Favor de elegir de un Cliente de los listados.",
					buttons: {
						cerrar: {
							label: "Cerrar",
							callback: function() {
								$("#clientes").focus();
							}
						}
					}
				});
		}
			
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-29
	 * 
	 * Acción de eliminar Cliente
	 
	$(document).on('click','.eliminar-cl',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var id_cl = $("#cli_id_"+id).val();

		if(es_recredito) {

			$("#row_"+id).addClass("danger");

			//Cambia nombre de inputs
			$("#cli_id_"+id).attr("name", "_cli_id_"+id);
			$("#monto_individual_"+id).attr("name", "_monto_individual_"+id);
			$("#monto_individual_"+id).removeClass("monto_individual");
			$("#ahorro_d_"+id).attr("name", "_ahorro_d_"+id);
			$("#comision_d_"+id).attr("name", "_comision_d_"+id);
			$("#monto_otorgar_"+id).attr("name", "_monto_otorgar_"+id);
			$("#pago_semanal_"+id).attr("name", "_pago_semanal_"+id);

			//Readonly
			$("#monto_individual_"+id).attr("readonly", "readonly");

			//Cambia propiedades del botón
			$("#row_"+id+" .cont-button").html('<a class="agregar-rec" href="#" data-id="'+id+'" ><button class="btn btn-success"><i class="fa fa-plus"></i></button></a>');

		} else {
			$("#row_"+id).remove();
		}

		//Elimina el ID de la lista
		var index = jQuery.inArray(id_cl,ids_clientes);
		ids_clientes.splice(index,1);

		recalculate();
	});*/

	/*$(document).on('click','.agregar-rec',function(e) {
		e.preventDefault();
		var id = $(this).attr("data-id");
		var id_cl = $("#cli_id_"+id).val();

		if(jQuery.inArray(id_cl,ids_clientes) == -1) {
			clientes++;	
			ids_clientes.push(id_cl);

			$("#row_"+id).removeClass("danger");

			//Cambia nombre de inputs
			$("#cli_id_"+id).attr("name", "cli_id["+id+"]");
			$("#monto_individual_"+id).attr("name", "monto_individual["+id+"]");
			$("#monto_individual_"+id).addClass("monto_individual");
			$("#ahorro_d_"+id).attr("name", "ahorro_d["+id+"]");
			$("#comision_d_"+id).attr("name", "comision_d["+id+"]");
			$("#monto_otorgar_"+id).attr("name", "monto_otorgar["+id+"]");
			$("#pago_semanal_"+id).attr("name", "pago_semanal["+id+"]");

			//Readonly
			$("#monto_individual_"+id).removeAttr("readonly");

			//Cambia propiedades del botón
			$("#row_"+id+" .cont-button").html('<a class="eliminar-cl" href="#" data-id="'+id+'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>');

			recalculate();
		}

	});*/


	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2016-02-02
	 * 
	 * Cálculo de Resultados Informativos 
	 */
	$(document).on('change','#tasa, #ahorro_di, #comision_p, #plazo',function(e) {
		//var monto_individual = $("#monto_individual").val();
		var tasa = $("#tasa").val();
		var ahorro_d = $("#ahorro_di").val();
		var comision_p = $("#comision_p").val();
		var plazo = $("#plazo").val();

		//For de cada uno de los Montos Individuales
		$('.monto_individual').each(function(){
			var id_row = $(this).attr("data-id");
			var monto_individual = $("#monto_individual_"+id_row).val();
			if(!isEmpty(monto_individual) && !isEmpty(tasa) && !isEmpty(ahorro_d) && !isEmpty(comision_p) && !isEmpty(plazo)) {
				monto_individual = parseFloat(monto_individual);
				tasa = parseFloat(tasa/100);
				//ahorro_p = parseFloat(ahorro_p/100);
				comision_p = parseFloat(comision_p/100);

				/*Calcula cuánto va a ahorrar el individuo*/
				var ahorro_d_cl = ahorro_d;
				ahorro_d_cl = Math.round(ahorro_d_cl * 100) / 100;
				$("#ahorro_d_"+id_row).val(ahorro_d_cl);

				/*Calcula la comision de apertura*/
				var comision_d = monto_individual * comision_p;
				comision_d = Math.round(comision_d * 100) / 100;
				$("#comision_d_"+id_row).val(comision_d);

				/*Calcula monto a otorgar*/
				var monto_otorgado = monto_individual - comision_d;
				$("#monto_otorgar_"+id_row).val(monto_otorgado);

				/*Pago semanal*/
				var pago = ((monto_individual*(tasa * plazo)) + monto_individual) / plazo;
				pago = pago.toFixed();
				//var entre = monto_individual/1000;
				//var pago = entre * 101;
				$("#pago_semanal_"+id_row).val(pago);
			}
		});

		//recalculate();

	});

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2016-02-10
	 * 
	 * Cálculo de Resultados Informativos 
	 */
	$(document).on('change','.monto_individual',function(e) {
		var id_row = $(this).attr("data-id");
		var monto_individual = $("#monto_individual_"+id_row).val();
		var tasa = $("#tasa").val();
		var ahorro_d = $("#ahorro_di").val();
		var comision_p = $("#comision_p").val();
		var plazo = $("#plazo").val();
		//alert("mi:"+monto_individual+" t:"+tasa+" a:"+ahorro_p+" c:"+comision_p+" p:"+plazo);

		if(!isEmpty(monto_individual) && !isEmpty(tasa) && !isEmpty(ahorro_d) && !isEmpty(comision_p) && !isEmpty(plazo)) {
			monto_individual = parseFloat(monto_individual);
			tasa = parseFloat(tasa/100);
			//ahorro_p = parseFloat(ahorro_p/100);
			comision_p = parseFloat(comision_p/100);

			//alert("mi:"+monto_individual+" t:"+tasa+" a:"+ahorro_p+" c:"+comision_p+" p:"+plazo);

			/*Calcula cuánto va a ahorrar el individuo*/
			var ahorro_d_cl = ahorro_d;
			ahorro_d_cl = Math.round(ahorro_d_cl * 100) / 100;
			$("#ahorro_d_"+id_row).val(ahorro_d_cl);

			/*Calcula la comision de apertura*/
			var comision_d = monto_individual * comision_p;
			comision_d = Math.round(comision_d * 100) / 100;
			$("#comision_d_"+id_row).val(comision_d);

			/*Calcula monto a otorgar*/
			var monto_otorgado = monto_individual - comision_d;
			$("#monto_otorgar_"+id_row).val(monto_otorgado);

			/*Pago semanal*/
			var pago = ((monto_individual*(tasa * plazo)) + monto_individual) / plazo;
			pago = pago.toFixed();
			//var entre = monto_individual/1000;
			//var pago = entre * 101;
			$("#pago_semanal_"+id_row).val(pago);

			//recalculate();
		}
	});


	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-21
	 * 
	 * Aparce que ponga el grupo si es recrédito
	 *
	$(document).on('change','#recredito',function(e) {
		if ($(this).is(":checked")) {
			$("#comision_p").val("2.5").change();
			$("#grupo_rec").removeClass('display-none');
		} else {
			$("#grupo_rec").addClass('display-none');
			es_recredito = false;
		}
	});*/

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-03-07
	 * 
	 * Pone en automático la fecha del Primer Pago
	 */
	$(document).on('change','#fecha_entrega',function(e) {
		var fecha_entrega = $(this).val();

		var f = fecha_entrega.split("/");

		var d = new Date(f[2], f[1]-1, f[0]);
		var saturday = new Date(d.getTime());
		saturday.setDate(saturday.getDate() + 13 - saturday.getDay()); 

		var yy=saturday.getUTCFullYear();
		var mm=saturday.getUTCMonth()+1;
		var dd=saturday.getDate();

		$("#fecha_inicial").val(dd+"/"+mm+"/"+yy);
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-05-16
	 * 
	 * Agrega a todas las personas de un recredito
	 *
	$(document).on('change','#grupo_rec',function(e) {
		params = {};
		params.id = $("#grupo_rec").val();
		$.ajax({
			type: 'POST',
			url: 'include/Libs.php?accion=getRecredito',
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
								//window.location = "../";
							}
						}
					}
				});
			},
			success: function(result){
				if(result.error) {
					bootbox.dialog({
						message: result.msg,
						buttons: {
							cerrar: {
								label: "Cerrar",
								callback: function() {
									bootbox.hideAll();
									$("#grupo_rec").focus();
								}
							}
						}
					});
				} else {
					ids_clientes = result.ids_clientes;
					clientes = (ids_clientes.length)+1;
					$(".clientes-tb tbody").html(result.tabla);
					es_recredito = true;
				}
			}
		}); 
	}); */

	$(document).on('change','#promotor',function(e) {
		getAddress();
	});

});

$(function() {
	$("#clientes").autocomplete({
		source:"include/Libs.php?accion=showClients",
		select: function (event, ui) {
	        $("#bck_id").val(ui.item.id);
	        $("#bck_nombre").val(ui.item.name);
			$("#bck_direccion").val(ui.item.address);
			$("#bck_telefono").val(ui.item.phone);
			$("#bck_monto").val(ui.item.money);
		}
	});
});

function isEmpty(str) {
    return (!str || 0 === str.length || str == "");
}

function recalculate() {
	var monto_individual = 0;
	var monto_otorgado = 0;
	var pago_semanal = 0;

	$('.monto_individual').each(function(){
		var id_row = $(this).attr("data-id");
		monto_individual += parseFloat($("#monto_individual_"+id_row).val());
		monto_otorgado += parseFloat($("#monto_otorgar_"+id_row).val());
		pago_semanal += parseFloat($("#pago_semanal_"+id_row).val());
	});

	if(monto_individual != 0 && monto_otorgado != 0 && pago_semanal != 0 && !isNaN(monto_individual) && !isNaN(monto_otorgado) && !isNaN(pago_semanal)) {
		//Clientes que están en lista
		//var num_cl = ids_clientes.length;

		//var monto_total = num_cl * monto_individual;
		$("#monto_total").val(monto_individual);

		//var monto_total_entregar = num_cl * monto_otorgado;
		$("#monto_total_entregar").val(monto_otorgado);

		//var pago_total_semanal = num_cl * pago_semanal;
		$("#pago_total_semanal").val(pago_semanal);

	}
}

function getPromotores(id_promotor) {
	params = {};
	params.id = id_promotor;
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=getPromotores',
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
							//window.location = "../";
						}
					}
				}
			});
		},
		success: function(result){
			$('.container-promotores').html(result.select);
			$('#promotor').select2();
			getAddress();
		}
	}); 
}

function getAddress() {
	var valor_prom = $("#promotor").val();
	var direccion = $('#promotor option[value="'+valor_prom+'"]').attr("data-dir");
	$("#domicilio").val(direccion);
}

function getIndividual() {
	params = {};
	params.id = $("#id").val();
	$.ajax({
		type: 'POST',
		url: 'include/Libs.php?accion=showIndividual',
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
			if(!result.error) {
				$("#fecha").val(result.CRE_FECHA);
				$("#plazo").val(result.CRE_PLAZO);
				$("#tasa").val(result.CRE_TASA);
				$("#fecha_inicial").val(result.CRE_FECHA_INICIAL);
				$("#ahorro_di").val(result.CRE_AHORRO_P);
				$("#comision_p").val(result.CRE_COMISION_P);
				$("#monto_otorgado").val(result.CRE_MONTO_OTORGADO);
				$("#pago").val(result.CRE_PAGO_SEMANAL);
				$("#monto_total").val(result.CRE_MONTO_TOTAL);
				$("#monto_total_entregar").val(result.CRE_MONTO_TOTAL_ENTREGAR);
				$("#pago_total_semanal").val(result.PAGO_SEMANAL);
				$("#domicilio").val(result.CRE_DOMICILIO);
				/*$("#").val();
				$("#").val();*/

				$("#fecha_entrega").val(result.CRE_FECHA_ENTREGA);

				getPromotores(result.SIU_ID);

				ids_clientes = result.ids_clientes;
				clientes = ids_clientes.length+1;
				$(".clientes-tb tbody").html(result.tabla);
			}
			else
				window.location = "index.php";
		}
	});
}




