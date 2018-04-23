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
define("PAGE_TITLE", "Reporte de Círculo de Crédito");
define("DESCRIPTION", "");

$module = 31;

$common->sentinel($module);

//Se definen los js y css - sólo poner los nombres de los archivos no la terminación
$css = array();
$js = array("index");

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
	<!-- DATATABLES -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/datatables/media/css/jquery.dataTables.css" >
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
	<!-- JQUERY UI -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/jquery-ui-1.10.3/themes/base/jquery-ui.css" >

	<!-- PRELOADER -->
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>css/preloader.css" />	
	<script type="text/javascript" src="<?php echo $ruta;?>js/preloader.js" ></script>
	<style type="text/css">
		.tickLabel {
			z-index: 9999;
		}

		.loader {
			height: 32px;
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
								</div>
							</div>
						</div>
						<!-- /PAGE HEADER -->
						<!-- CONTENIDO PRINCIPAL -->
						<!-- FILTRO -->
						<form id="circulo">
						<div class="row">
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input id="fecha_1" name="fecha_1" type="text" class="form-control fecha" placeholder="Fecha Inicio">
								</div>
							</div>
							<div class="col-sm-3">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									<input id="fecha_2" name="fecha_2" type="text" class="form-control fecha" placeholder="Fecha Fin">
								</div>
							</div>
							<div class="col-sm-1 cont-loader"></div>
							<div class="col-sm-2">
								<button class="btn btn-purple generar" title=""><i class="fa fa-file-excel-o"></i> Generar Excel</button>
							</div>
							<div class="col-sm-1 cont-loader-exc"></div>
							<div class="col-sm-2 cont-guardar"></div>
						</div>
						<div class="separator"></div>
						<div class="checkboxes" style="width: 5100px;">
							<table class="table">
								<th>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">APELLIDO PATERNO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">APELLIDO MATERNO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">NOMBRE(S)</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">FECHA DE NACIMIENTO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">RFC</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CURP</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled"># DE SEGURO SOCIAL</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">NACIONALIDAD</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">RESIDENCIA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled"># DE LICENCIA DE CONDUCIR</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">ESTADO CIVIL</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">SEXO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CLAVE ELECTORAL IFE</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled"># DE DEPENDIENTES</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TIPO DE PERSONA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">DIRECCIÓN</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">COLONIA POBLACIÓN</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">DELEGACIÓN MUNICIPIO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CIUDAD</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">ESTADO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CP</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled"># TELÉFONO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TIPO DOMICILIO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="asentamiento" name="asentamiento" value="1" checked="checked">TIPO ASENTAMIENTO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CUENTA ACTUAL</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">PROMOTOR</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TIPO RESPONSABILIDAD</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TIPO CUENTA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TIPO CONTRATO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CLAVE UNIDAD MONETARIA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">VALOR ACTIVO VALUACIÓN</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled"># PAGOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">FRECUENCIA DE PAGOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="monto_pagar" name="monto_pagar" value="1" checked="checked">MONTO PAGAR</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">FECHA APERTURA CUENTA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="fecha_ultimo_pago" name="fecha_ultimo_pago" value="1" checked="checked">FECHA ÚLTIMO PAGO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">FECHA ÚLTIMA COMPRA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">FECHA CORTE</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">GARANTÍA</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">CRÉDITO MÁXIMO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="saldo_actual" name="saldo_actual" value="1" checked="checked">SALDO ACTUAL</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked"  class="disabled" disabled="disabled">LIMITE CRÉDITO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="saldo_vencido" name="saldo_vencido" value="1" checked="checked">SALDO VENCIDO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="num_pagos_vencidos" name="num_pagos_vencidos" value="1" checked="checked"># PAGOS VENCIDOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="pago_actual" name="pago_actual" value="1" checked="checked">PAGO ACTUAL</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="total_pagos_rep" name="total_pagos_rep" value="1" checked="checked">TOTAL DE PAGOS REPORTADOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="fecha_inc" name="fecha_inc" value="1" checked="checked">FECHA PRIMER INCUMPLIMIENTO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="monto_ultimo_pago" name="monto_ultimo_pago" value="1" checked="checked">MONTO ÚLTIMO PAGO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="total_abonado" name="total_abonado" value="1" checked="checked">TOTAL ABONADO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="total_recuperado" name="total_recuperado" value="1" checked="checked">TOTAL RECUPERADO</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">PLAZO MESES</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">MONTO CRÉDITO ORIGINACIÓN</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="total_saldos_actuales" name="total_saldos_actuales" value="1" checked="checked">TOTAL SALDOS ACTUALES</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="total_saldos_vencidos" name="total_saldos_vencidos" value="1" checked="checked">TOTAL SALDOS VENCIDOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TOTAL ELEMENTOS NOMBRE REPORTADOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TOTAL ELEMENTOS DIRECCIÓN REPORTADOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TOTAL ELEMENTOS EMPLEO REPORTADOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="" name="" value="1" checked="checked" class="disabled" disabled="disabled">TOTAL ELEMENTOS CUENTA REPORTADOS</label></td>
									<td><label class="checkbox-inline"><input type="checkbox" id="saldo_insoluto" name="saldo_insoluto" value="1" checked="checked">SALDO INSOLUTO</label></td>
								</th>
							</table>
						</div>
						</form>
						<!-- /FILTRO -->
						<div class="row">
							<div class="col-md-12 table-circulo" style="background: white; width: 5100px;">
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
	<script src="<?php echo $ruta;?>js/flot/jquery.js"></script>
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
	<script src="js/index.js"></script>

	<!-- GRAFICOS -->
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.time.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.selection.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.resize.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.pie.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.stack.js"></script>
	<script src="<?php echo $ruta;?>js/flot/jquery.flot.crosshair.js"></script>

	<script>
		jQuery(document).ready(function() {		
			App.setPage("flot_charts");  //Set current page
			App.init(); //Initialise plugins and elements
			//Charts.initCharts();
			//Charts.initPieCharts();
		});
	</script>

	<!-- JS -->
	<!-- /JAVASCRIPTS -->
</body>
</html>