$(document).ready(function (){
	getRecords();

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Acción de Agregar Medio
	 */
	$(document).on('click','.alta', function (e) {
		e.preventDefault();
		alta("");
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Submit de Forma Medio
	 */
	$(document).on('submit','#form-actividad',function(e){
		e.preventDefault();
		guardar();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Acción de Editar Medio
	 */
	$(document).on('click','.editar', function (e) {
		e.preventDefault();
		var id = $(this).attr("data-id");
		showProduct(id);
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Submit de Forma Medio al editar
	 */
	$(document).on('submit','#formEd-actividad',function(e){
		e.preventDefault();
		guardaEdicion();
	});

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-09-25
	 * 
	 * Acción de Borrar Actividad
	 */
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar la Actividad seleccionada?",
			title: "Eliminar Actividad Económica",
			buttons: {
				aceptar: {
					label: "Aceptar",
					className: "btn-primary",
					callback: function() {
						$.ajax({
							type:'post',
							data:params,
							url:'include/Libs.php',
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
								bootbox.dialog({
									message: result.msg,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												getRecords();
											}
										}
									}
								});
							}
						});
					}
				},
				cancelar: {
					label: "Cancelar",
					className: "btn-danger",
					callback: function() {
						$('.modal-dialog').modal('hide');
					}
				}
			}
		});
	});
});

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * Muestra el modal para dar de alta un nuevo Medio con los valores anteriores
 */
function alta(actividad) {
	bootbox.dialog({
		message: "<form id='form-actividad' name='form-actividad' role='form' class='form-horizontal' action='include/Libs.php?accion=saveRecord'>"+
					"<div class='form-group'>"+
					    "<label for='actividad' class='col-sm-4 text-right'>Actividad Económica</label>"+
						"<div class='col-sm-7'>" +
							"<input type='text' id='actividad' name='actividad' class='form-control' value='"+actividad+"'>"+
						"</div>"+ 
					"</div>"+
				  "</form>",
		title: "Alta de Actividad Económica",
		buttons: {
			main: {
			    label: "Guardar",
			    className: "btn-primary",
			    callback: function() {
			    	guardar();
			    }
			},
			danger: {
				label: "Cancelar",
			    className: "btn-danger"
			}
		}
	});
}

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * AJAX para guardar el Medio
 */
function guardar() {
	var actividad = $("#actividad").val();
	params = {};
	params.actividad = actividad;
	$.ajax({
		url: $('#form-actividad').attr("action"),
		type: 'POST',
		data: params,
		dataType: 'JSON',
		error: function (){
			bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
		}, success: function (result) {
			bootbox.dialog({
				message: result.msg,
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							if(result.error) {
								alta(actividad);
							} else {
								bootbox.hideAll();
								getRecords();
							}
						}
					}
				}
			});
		}
	});
}

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	var recordsTable = $('#table-actividad').dataTable({
		'bProcessing': true,
		'bServerSide': true,
		'bDestroy': true,
		'oLanguage': {
			'sLengthMenu': 'Mostrar _MENU_ resultados',
			'sZeroRecords': 'No se encontraron resultados',
			'sInfo': '<strong>Total:</strong>  _TOTAL_ resultados',
			'sInfoEmpty': '0 resultados',
			'sInfoFiltered': '',
			'sSearch': 'Buscar:',
			'oPaginate': {
				'sNext': 'Siguiente',
				'sPrevious': 'Anterior'
			},
			'sProcessing': 'Cargando...'
		},
		'sAjaxSource': 'include/Libs.php?accion=printTable',
		'aoColumns': [
			{'sClass': 'center', 'bSortable': false},	//No.
			{'sClass': 'center'},						//Medio de Marketing
			{'sClass': 'center', 'bSortable': false}	//Acciones
		],
		'aaSorting': [[1, 'asc']]
	});
}

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * Obtiene los datos de un concepto en base a su id y llama a la función editars
 */
function showProduct(id) {
	params = {};
	params.id = id;
	$.ajax({
		url: "include/Libs.php?accion=showRecord",
		type: 'POST',
		data: params,
		dataType: 'JSON',
		error: function (){
			bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
		}, success: function (result) {
			if(!result.error) {
				editar(id, result.actividad);
			} else {
				bootbox.alert(result.msg);
			}
		}
	});
}

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * Muestra el modal para editar un producto con los valores anteriores
 */
function editar(id, actividad) {
	bootbox.dialog({
		message: "<form id='formEd-actividad' name='formEd-actividad' role='form' class='form-horizontal' action='include/Libs.php?accion=saveRecord'>"+
					"<div class='form-group'>"+
					    "<label for='actividad' class='col-sm-4 text-right'>Actividad Económica</label>"+
					    "<div class='col-sm-7'>" +
					    	"<input type='text' id='actividad' name='actividad' class='form-control' value='"+actividad+"'>"+
					    "</div>"+ 		
					    "<input type='hidden' class='form-control col-sm-5' id='id' name='id' value='"+id+"'>"+
					"</div>"+
				  "</form>",
		title: "Edición de Actividad Económica",
		buttons: {
			main: {
			    label: "Guardar",
			    className: "btn-primary",
			    callback: function() {
			    	guardaEdicion();	
			    }
			},
			danger: {
				label: "Cancelar",
			    className: "btn-danger"
			}
		}
	});
} 

/*
 * @author: Cynthia Castillo 
 * @version: 0.1 2014-09-25
 * 
 * AJAX para guardar la edición de un IVA
 */
 function guardaEdicion() {
 	var actividad = $("#actividad").val();
	var id = $("#id").val();
	params = {};
	params.id = id;
	params.actividad = actividad;
	$.ajax({
		url: $('#formEd-actividad').attr("action"),
		type: 'POST',
		data: params,
		dataType: 'JSON',
		error: function (){
			bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
		}, success: function (result) {
			bootbox.dialog({
				message: result.msg,
				title: result.title,
				buttons: {
					cerrar: {
						label: "Cerrar",
						callback: function() {
							if(result.error) {
								editar(id, actividad);
							} else {
								getRecords();
							}
						}
					}
				}
			});
		}
	});
 }