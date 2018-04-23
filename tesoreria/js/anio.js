$(document).ready(function(){
	getRecords();
});

/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2013­12-27
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	var anio = $("#anio").val();
	var recordsTable = $('#table-tesoreria').dataTable({
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
			{'sClass': 'center', 'bSortable': false},	//Entradas
			{'sClass': 'center', 'bSortable': false}	//Salidas
		],
		'aaSorting': [[1, 'asc']]
	});
}
