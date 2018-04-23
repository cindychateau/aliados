$(document).ready(function(){
	getRecords();
	numeroContraseña();

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 * 
	 * Acción de Borrar Perfil
	 */
	$(document).on('click','.borrar',function(e){
		e.preventDefault();
		var nombre = $(this).attr("data-name");
		var id = $(this).attr("data-id");
		var params = {};
		params.id = id;
		params.accion = 'deleteRecord';
		bootbox.dialog({
			message: "¿Desea eliminar el Usuario "+nombre+"?",
			title: "Eliminar Usuario",
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

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-13
	 * 
	 * Acción de Reestablecer una contraseña
	 */
	$(document).on('click','.mod-contr',function(e){
		var id = $(this).attr("data-id");
		newPassword(id, "", "");
	});

	/*$('.visto').click(function () { 
       var tipo = $(this).attr("data-tipo");
       var perfil = $(this).attr("data-idperfil");
        $.ajax({
		url: 'include/Libs.php',
		type: 'POST',
		dataType: 'JSON',
		data: {accion:'noticiaVista', id_t: tipo, id_p : perfil},
		error: function() {
			console.log("error en el ajax");
		}, success: function (result) {
			console.log(result.msg);
			switch(tipo)
			{
			case "1":
			  document.location.href="../../sistema/usuarios/usuarios/"
			  break;
			case "2":
			  document.location.href="../../../produccion/operarios/";
			  break;
			default:
				console.log("no se hizo nada");
			}
		}
			});
        console.log(tipo);
    });*/	

});

/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2013­12-27
 * 
 * Imprime la tabla y le da la funcionalidad adecuada
 */
function getRecords(){
	var recordsTable = $('#table-usuario').dataTable({
		'bProcessing': true,
		'bServerSide': true,
		'bDestroy': true,
		"iDisplayLength": 300,
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
			{'sClass': 'center', 'bSortable': false},//No.
			{'sClass': 'center'},	//Usuario
			{'sClass': 'center'},	//Email
			{'sClass': 'center'},	//Perfil
			{'sClass': 'center', 'bSortable': false}//Acciones
		],
		'aaSorting': [[1, 'DESC']]
	});
}

/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2014­01-13
 * 
 * Modal para la modificación de contraseña
 */
function newPassword(id, pswd, conf_pswd) {
	bootbox.dialog({
		message: "<form id='frm-contra' role='form' action='include/Libs.php?accion=newPassword'>"+
					"<div class='form-group'>"+
					    "<label for='pswd'>Nueva Contraseña</label>"+
					    "<input type='password' class='form-control' id='pswd' value="+pswd+" >"+
					    "<label for='conf-pswd'>Confirmación de Contraseña</label>"+
					    "<input type='password' class='form-control' id='conf-pswd' value="+conf_pswd+">"+
					"</div>"+
				  "</form>",
		title: "Reestablecer Contraseña",
		buttons: {
			main: {
			    label: "Modificar",
			    className: "btn-primary",
			    callback: function() {
			    	var accion = $('#frm-contra').attr("action");
			    	var pswd = $('#pswd').val();
			    	var conf_pswd = $('#conf-pswd').val();

			    	var params = {};
			    	params.id = id;
			    	params.pswd = pswd;
			    	params.conf_pswd = conf_pswd;
			    	$.ajax({
			    		url: accion,
			    		type:'post',
			    		data:params,
			    		dataType: 'json',
			    		error: function (){
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
			    		success: function (result) {
			    			bootbox.dialog({
								message: result.msg,
								title: result.title,
								buttons: {
									cerrar: {
										label: "Cerrar",
										callback: function() {
											if(result.error) {
												$('.modal-dialog').modal('hide');
												newPassword(id, pswd, conf_pswd);
											} else {
												bootbox.hideAll();
												$("#table-usuario").dataTable().fnDestroy();
												$("#table-password").dataTable().fnDestroy();
												getRecords();
												numeroContraseña();
											}
										}
									}
								}
							});
			    		}
			    	});
			    }
			},
			danger: {
				label: "Cancelar",
			    className: "btn-danger",
			    callback: function() {
			    	//$('.modal-dialog').modal('hide');
			    }
			}
		}
	});
}

/*
 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
 * @version: 0.1 2014­01-13
 * 
 * Imprime el total de filas que existen en modificación de contraseña
 */
function numeroContraseña() {
	var params = {};
	params.accion = 'countPassword';
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
			if(result.num>0){
				$("#num-contr").html(result.num);
			}
			else {
				$("#num-contr").html("");
			}
		}
	});
}