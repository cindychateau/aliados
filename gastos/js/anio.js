$(document).ready(function(){
	getRecords();

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2013-12-27
	 * 
	 * Acción de Borrar Perfil
	 */
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Está seguro de eliminar el gasto?",
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
									title: result.title,
									buttons: {
										cerrar: {
											label: "Cerrar",
											callback: function() {
												bootbox.hideAll();
												$("#table-usuario").dataTable().fnDestroy();
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
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2013­12-27
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	var anio = $("#anio").val();
	var recordsTable = $('#table-usuario').dataTable({
		'bProcessing': true,
		'bServerSide': true,
		'bDestroy': true,
		"iDisplayLength": 12,
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
		'sAjaxSource': 'include/Libs.php?accion=printTableYear&y='+anio,
		'aoColumns': [
			{'sClass': 'center', 'bSortable': false},//No.
			{'sClass': 'center', 'bSortable': false},	//MES
			{'sClass': 'center', 'bSortable': false},	//MONTO TOTAL
		],
		'aaSorting': [[1, 'asc']]
	});
}
