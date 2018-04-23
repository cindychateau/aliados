<?php

/*
 *	Se identifica la ruta	
 */
/*$url = explode("/aliados/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);*/

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
	$ruta .= "../";
}

//Se incluye la clase Common
include_once($ruta."include/Common.php");


/*
 *	Se definen los parámetros de la página
 */
define("PAGE_TITLE", "Editar Grupo <span class='group'>".$_GET['id']."</span>");
define("DESCRIPTION", "Edición de Grupo <span class='group'>".$_GET['id']."</span>");

$module = 7;

$common->sentinel($module, 'cambios.php');

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array("cambios");

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<title><?php echo(TITLE_MAIN); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<!-- STYLESHEETS --><!--[if lt IE 9]><script src="js/flot/excanvas.min.js"></script><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script><![endif]-->
	<link rel="stylesheet/less" type="text/css" href="<?php echo $ruta;?>less/my-bootstrap-theme.less" >
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>less/themes/default.less" id="skin-switcher" >
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>less/responsive.less" >
	<!-- SWITCH -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/bootstrap-switch/bootstrap-switch.css" >
	<!-- DATATABLES -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/datatables/media/css/jquery.dataTables.css" >
	<!-- SELECT2 -->
    <link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/select2/select2.css" >
	<!-- WIZARD -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/bootstrap-wizard/wizard.css" >
	<!-- JQUERY UI -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/jquery-ui-1.10.3/themes/base/jquery-ui.css" >
	<!-- <link rel="stylesheet" href="less/my-bootstrap-theme.css" > -->

	<!-- CSS -->
	<?php 
		if (count($css) > 0) {
			foreach ($css as $clave => $valor) {
				echo '<link rel="stylesheet" href="'.$ruta.'css/'.$valor.'.css" />';
			}
		}
	?>

	<link href="<?php echo $ruta;?>font-awesome/css/font-awesome.css" rel="stylesheet">
	<!-- DATE RANGE PICKER -->
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>js/bootstrap-daterangepicker/daterangepicker-bs3.css" />
	<!-- FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
	<!-- FAVICON -->
	<link rel="icon" 
      type="image/png" 
      href="<?php echo $ruta;?>img/favicon.png" />
    <!-- JQUERY -->
	<script src="<?php echo $ruta;?>js/jquery/jquery-2.0.3.min.js"></script>

	<!-- PRELOADER -->
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>css/preloader.css" />	
	<script type="text/javascript" src="<?php echo $ruta;?>js/preloader.js" ></script>

	<!-- GOOGLE MAPS -->
	<script src="http://maps.google.com/maps?file=api&amp;v=3&amp;key=ABQIAAAAjU0EJWnWPMv7oQ-jjS7dYxSPW5CJgpdgO_s4yyMovOaVh_KvvhSfpvagV18eOyDWu7VytS6Bi1CWxw"
      type="text/javascript"></script>
    <script type="text/javascript">
	    var map = null;
	    var geocoder = null;

	    function initialize() {
	      if (GBrowserIsCompatible()) {
	        map = new GMap2(document.getElementById("map_canvas"));
	        map.setCenter(new GLatLng(37.4419, -122.1419), 1);
	        map.setUIToDefault();
	        geocoder = new GClientGeocoder();
	      }
	    }

	    function showAddress(address) {
	      if (geocoder) {
	        geocoder.getLatLng(
	          address,
	          function(point) {
	            if (!point) {
	              alert(address + " not found");
	            } else {
	              map.setCenter(point, 15);
	              var marker = new GMarker(point, {draggable: true});
	              map.addOverlay(marker);
	              GEvent.addListener(marker, "dragend", function() {
	                marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
	              });
	              GEvent.addListener(marker, "click", function() {
	                marker.openInfoWindowHtml(marker.getLatLng().toUrlValue(6));
	              });
		      GEvent.trigger(marker, "click");
	            }
	          }
	        );
	      }
	    }
    </script>  

	<style type="text/css">
		.select2-container {
			padding-top: 0px;
			height: 31px;
		}

		.popover {
			width: 300px;
		}

		.btn .fa {
			margin-right: 0;
		}

		.container-map {
			margin-top: 20px;
		}

		.container-search {
			margin-bottom: 10px;
		}

		input[type="file"] {
			margin-top: 6px;
		}

		.add-img {
			margin-bottom:  10px;
		}

		.tools {
			margin-top: -15px;
			margin-right: -10px;
		}

		.has-switch label {
			z-index: 0 !important;
		}

	</style>

</head>
<body>
	<!-- PRELOADER -->	
	<div id="preloader">
		<div id="status">&nbsp;</div>
		<div id="status1" ><center>Cargando...</center></div>
	</div>
	<!-- /PRELOADER -->

	<!-- HEADER -->
	<header class="navbar clearfix" id="header">
		<div class="container">
				<?php echo $common->printLeftHeader();?>

				<!-- BEGIN TOP NAVIGATION MENU -->					
				<ul class="nav navbar-nav pull-right">
					<?php echo $common->printHeader(); ?>
				</ul>
				<!-- END TOP NAVIGATION MENU -->

		</div>		
	</header>
	<!--/HEADER -->
	
	<!-- PAGE -->
	<section id="page">
				<!-- SIDEBAR -->
				<div id="sidebar" class="sidebar">
					<div class="sidebar-menu nav-collapse">
						<!-- SIDEBAR MENU -->
						<?php echo $common->printMenu($module); ?>
						<!-- /SIDEBAR MENU -->
					</div>
				</div>
				<!-- /SIDEBAR -->
		<div id="main-content">
			<div class="container">
				<div class="row">
					<div id="content" class="col-lg-12">
						<!-- PAGE HEADER-->
						<div class="row">
							<div class="col-sm-12">
								<div class="page-header">
									<!-- STYLER -->
									
									<!-- /STYLER -->
									<!-- BREADCRUMBS -->
									<?php echo $common->printBreadcrumbs($module); ?>
									<!-- /BREADCRUMBS -->
									<div class="pull-left">
										<div class="clearfix">
											<h3 class="content-title pull-left"><?php echo(PAGE_TITLE); ?></h3>
										</div>
										<div class="description"><?php echo(DESCRIPTION);?></div>
									</div>
									<div class="pull-right margin-right-85">
										<a class="guardar" href="#"><button class="btn btn-info"><i class="fa fa-save"></i>Guardar</button></a>
									</div>
								</div>
							</div>
						</div>
						<!-- /PAGE HEADER -->
						<!-- CONTENIDO PRINCIPAL -->
						<div class="margin-bottom-15">
							<small>
								Los campos marcados con asterisco (*) son obligatorios.
							</small>
						</div>
						<div class="row">
							<div class="col-md-12">
								<form class="form-horizontal" id="form-grupo" name="form-grupo" action="include/Libs.php?accion=saveRecord">
									<input name="id" type="hidden" id="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '-1' ; ?>">
									<div class="row">
										<div class="col-md-11 center-box-11">
											<div class="box border primary">
												<div class="box-title">
													INFORMACIÓN DEL CRÉDITO
												</div>
												<div class="box-body big">
													<div class="form-group">
														<label class="col-sm-2 col-sm-offset-7 control-label">*Fecha</label>
														<div class="col-sm-3">
															<input id="fecha" name="fecha" type="text" class="form-control fecha">
														</div>
												  	</div>
													<div class="form-group">
														<label class="col-sm-2 control-label">*Fecha de Entrega</label>
														<div class="col-sm-4">
															<input id="fecha_entrega" name="fecha_entrega" type="text" class="form-control fecha">
														</div>
														<label class="col-sm-2 control-label">*Fecha del Primer Pago</label>
														<div class="col-sm-4">
															<input id="fecha_inicial" name="fecha_inicial" type="text" class="form-control fecha">
														</div>
												  	</div>
												  	<div class="form-group">
												  		<label class="col-sm-2 control-label">*Tasa (Semanal)</label>
														<div class="col-sm-4">
															<div class="input-group">
																<input id="tasa" name="tasa" type="text" class="form-control" value="1.75">
																<span class="input-group-addon">%</span>
															</div>
														</div>
														<label class="col-sm-2 control-label">*Plazo (Semanas)</label>
														<div class="col-sm-4">
															<input id="plazo" name="plazo" type="text" class="form-control" value="12">
														</div>
												  	</div>
												  	<div class="form-group">
														<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="¿El grupo pertenece a un Recrédito?"></i>*Recrédito</label>
												  		<div class="col-sm-2">
												  			<div class="make-switch switch-large has-switch" data-on="success" data-off="danger" data-on-label="<i class='fa fa-check'></i>" data-off-label="<i class='fa fa-times'></i>">
																<input id="recredito" name="recredito" type="checkbox">
															</div>
												  		</div>
												  		<div class="col-sm-2">
															<input id="grupo_rec" name="grupo_rec" type="text" class="form-control display-none" placeholder="Grupo">
														</div>
												  		<label class="col-sm-2 control-label">*Comisión por Apertura (%)</label>
														<div class="col-sm-4">
															<div class="input-group">
																<input id="comision_p" name="comision_p" type="text" class="form-control" value="5">
																<span class="input-group-addon">%</span>
															</div>
														</div>
												  	</div>
												  	<div class="form-group">
														<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="¿El grupo hará pagos vía transferencia?"></i>*Transferencia</label>
												  		<div class="col-sm-2">
												  			<div class="make-switch switch-large has-switch" data-on="success" data-off="danger" data-on-label="<i class='fa fa-check'></i>" data-off-label="<i class='fa fa-times'></i>">
																<input id="transferencia" name="transferencia" type="checkbox">
															</div>
												  		</div>
												  		<label class="col-sm-2 col-sm-offset-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="¿El grupo es de Reestructura?"></i>*Reestructura</label>
												  		<div class="col-sm-2 res">
												  			<div class="make-switch switch-large has-switch" data-on="success" data-off="danger" data-on-label="<i class='fa fa-check'></i>" data-off-label="<i class='fa fa-times'></i>">
																<input id="reestructura" name="reestructura" type="checkbox">
															</div>
												  		</div>
												  	</div>
												</div>
											</div>
											<div class="box border primary">
												<div class="box-title">
													CLIENTES
												</div>
												<div class="box-body big content-clientes">
													<div class="form-group">
														<div class="col-sm-10">
															<input id="clientes" name="clientes" type="text" class="form-control">
														</div>
														<div class="col-sm-2">
															<a class="agregar-cl" href="#"><button class="btn btn-success"><i class="fa fa-plus"></i>Agregar</button></a>
														</div>
														<input type="text" id="bck_id" style="display:none;">
														<input type="text" id="bck_nombre" style="display:none;">
														<input type="text" id="bck_direccion" style="display:none;">
														<input type="text" id="bck_telefono" style="display:none;">
														<input type="text" id="bck_monto" style="display:none;">
														<input type="text" id="bck_pend" style="display:none;">
														<input type="text" id="bck_grupo" style="display:none;">
													</div>
													<table class="clientes-tb table table-striped table-hover">
														<thead>
															<tr>
																<th></th>
																<th>Nombre</th>
																<th>Dirección</th>
																<th>Teléfono</th>
																<th>Monto Solicitado</th>
																<th>Pendiente</th>
																<th>Grupo</th>
																<th>Préstamo Individual</th>
																<th>Comisión de Apertura ($)</th>
																<th>Monto a Otorgar <i class="fa fa-info-circle pop-top" data-content="Cantidad que se le entrega al Cliente."></i></th>
																<th>Pago Semanal</th>
																<th>Orden</th>
																<th class="lasttd"></th>
															</tr>
														</thead>
														<tbody>
															<!--tr id="row_1">
																<td align="center">
																	Name
																	<input type="text" id="cli_id_1" name="cli_id[1]" data-id="1" style="display:none;">
																</td>
																<td align="center">
																	Address
																</td>
																<td align="center">
																	Phone
																</td>
																<td align="center">
																	Amount
																</td>
																<td align="center">
																	<a class="eliminar-cl" href="#" data-id="1" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>
																</td>
															</tr-->
														</tbody>
													</table>
												</div>
											</div>
											<div class="box border primary">
												<div class="box-title">
													INFORMACIÓN DEL GRUPO
												</div>	
												<div class="box-body big">
													<div class="form-group">
														<label class="col-sm-2 control-label">*Promotor</label>
														<div class="col-sm-10 container-promotores">
															<select class="form-control" disabled><option>Cargando...</option></select>
														</div>
												  	</div>
													<div class="form-group">
												  		<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="Cantidad de crédito para todo el grupo"></i>*Monto Total</label>
												  		<div class="col-sm-4">
												  			<div class="input-group">
																<span class="input-group-addon">$</span>
																<input id="monto_total" name="monto_total" type="text" class="form-control disabled" readonly="readonly">
															</div>
												  		</div>
												  		<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="Cantidad que se le entrega a todo el grupo"></i>*Monto Total a Entregar</label>
												  		<div class="col-sm-4">
												  			<div class="input-group">
																<span class="input-group-addon">$</span>
																<input id="monto_total_entregar" name="monto_total_entregar" type="text" class="form-control disabled" readonly="readonly">
															</div>
												  		</div>
												  	</div>
												  	<div class="form-group">
												  		<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="Pago semanal del grupo"></i>*Pago Total Semanal</label>
												  		<div class="col-sm-4">
												  			<div class="input-group">
																<span class="input-group-addon">$</span>
																<input id="pago_total_semanal" name="pago_total_semanal" type="text" class="form-control disabled" readonly="readonly">
															</div>
												  		</div>
												  	</div>
												  	<div class="form-group">
												  		<label class="col-sm-2 control-label"><i class="fa fa-info-circle pop-hover" data-content="Dirección del domicilio en donde se realizarán las juntas"></i>*Domicilio de Junta</label>
												  		<div class="col-sm-4">
												  			<input type="text" name="domicilio" id="domicilio" class="form-control" placeholder="Calle">
												  		</div>
												  		<div class="col-sm-2">
												  			<input type="text" name="num_ext" id="num_ext" class="form-control" placeholder="No. Ext">
												  		</div>
												  		<div class="col-sm-2">
												  			<input type="text" name="num_int" id="num_int" class="form-control" placeholder="No. Int">
												  		</div>
												  		<div class="col-sm-2">
												  			<input type="text" name="cp" id="cp" class="form-control" placeholder="C.P.">
												  		</div>
												  	</div>
												  	<div class="form-group">
												  		<div class="col-sm-4">
												  			<input type="text" name="colonia" id="colonia" class="form-control" placeholder="Colonia">
												  		</div>
												  		<div class="col-sm-4">
												  			<input type="text" name="municipio" id="municipio" class="form-control" placeholder="Municipio">
												  		</div>
												  		<div class="col-sm-4">
												  			<input type="text" name="estado" id="estado" class="form-control" placeholder="Estado" value="Nuevo León">
												  		</div>
												  	</div>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
						<!-- /CONTENIDO PRINCIPAL -->
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--/PAGE -->
	<!-- JAVASCRIPTS -->
	<!-- Placed at the end of the document so the pages load faster -->
	<!-- JQUERY UI-->
	<script src="<?php echo $ruta;?>js/jquery-ui-1.10.3/ui/jquery-ui.js"></script>
	<!-- BOOTSTRAP -->
	<script src="<?php echo $ruta;?>bootstrap/js/transition.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/alert.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/modal.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/dropdown.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/scrollspy.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/tab.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/tooltip.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/popover.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/button.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/collapse.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/carousel.js"></script>
	<script src="<?php echo $ruta;?>bootstrap/js/typeahead.js"></script>
	<!-- LESS CSS -->
	<script src="<?php echo $ruta;?>js/lesscss/less-1.4.1.min.js" type="text/javascript"></script>	
	<!-- DATE RANGE PICKER -->
	<script src="<?php echo $ruta;?>js/bootstrap-daterangepicker/moment.min.js"></script>
	<script src="<?php echo $ruta;?>js/bootstrap-daterangepicker/daterangepicker.js"></script>
	<!-- SLIMSCROLL -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/jQuery-slimScroll-1.3.0/jquery.slimscroll.min.js"></script><script type="text/javascript" src="<?php echo $ruta;?>js/jQuery-slimScroll-1.3.0/slimScrollHorizontal.js"></script>
	<!-- COOKIE -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/jQuery-Cookie/jquery.cookie.js"></script>
	<!-- DATATABLE -->
	<script src="<?php echo $ruta;?>js/datatables/media/js/jquery.dataTables.js"></script>
	<!-- BOOTBOX -->
	<script src="<?php echo $ruta;?>js/bootbox/bootbox.min.js"></script>
	<!-- CUSTOM SCRIPT -->
	<script src="<?php echo $ruta;?>js/script.js"></script>
	<script src="<?php echo $ruta;?>js/notices.js"></script>
	<!-- INPUT MASK -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>
	<!-- CKEDITOR -->
	<script src="<?php echo $ruta;?>js/ckeditor/ckeditor.js"></script>
	<script src="<?php echo $ruta;?>js/ckeditor/adapters/jquery.js"></script>
	<!-- DROPZONE -->
	<script src="<?php echo $ruta;?>js/dropzone/dropzone.js"></script>
	<!-- SWITCH -->
	<script src="<?php echo $ruta;?>js/bootstrap-switch/bootstrap-switch.min.js"></script>
	<!-- RATY - RATING CON ESTRELLAS -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/jquery-raty/jquery.raty.js"></script>
	<!-- SELECT2 -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/select2/select2.js"></script>
	<!-- WIZARD -->
	<script src="<?php echo $ruta;?>js/bootstrap-wizard/form-wizard.js"></script>
	<script src="<?php echo $ruta;?>js/bootstrap-wizard/jquery.bootstrap.wizard.js"></script>
	<script>
		jQuery(document).ready(function() {		
			App.setPage("widgets_box");  //Set current page
			App.setPage("dropzone_file_upload");  //Set current page
			App.init(); //Initialise plugins and elements
		});
	</script>

	<!-- JS -->
	<?php 
		if (count($js) > 0) {
			foreach ($js as $clave => $valor) {
				echo '<script type="text/javascript" src="js/'.$valor.'.js"></script>';
			}
		}
	?>
	<!-- /JAVASCRIPTS -->
</body>
</html>