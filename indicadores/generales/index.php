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
define("PAGE_TITLE", "Indicadores Generales");
define("DESCRIPTION", "Indicadores Generales de los Préstamos realizados");

$module = 13;

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

	<!-- PRELOADER -->
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>css/preloader.css" />	
	<script type="text/javascript" src="<?php echo $ruta;?>js/preloader.js" ></script>
	<style type="text/css">
		.well {
			text-align: center;
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
										<?php 	$params = array("link"		=>	"alta.php",
																"title"		=>	"Nuevo Gasto",
																"classes"	=>	"");
												//echo $common->printButton($module, "alta", $params);?>
									</div>
								</div>
							</div>
						</div>
						<!-- /PAGE HEADER -->
						<!-- CONTENIDO PRINCIPAL -->
						<div class="row">
							<div class="col-md-12">
								<div class="box border primary">
									<div class="box-title">
										<i class="fa fa-area-chart"></i> INDICADORES
									</div>
									<div class="box-body big">
										<!-- CONTENIDO -->
										<div class="row">
											<div class="col-sm-3">
												<div class="well">
													<h2 class="clientes">
														1200
													</h2>
													Total de Clientes
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="clientes-activos">
														1200
													</h2>
													Clientes Activos
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="grupos">
														1200
													</h2>
													Total de Grupos
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="grupos-activos">
														1200
													</h2>
													Grupos Activos
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-3">
												<div class="well">
													<h2 class="pendientes-entregar">
														1200
													</h2>
													Préstamos Pendientes de Entregar
												</div>
											</div>
											<div class="col-sm-6">
												<div class="well">
													<h2 class="saldo-bruto">
														1200
													</h2>
													Saldo Bruto de Cartera de Préstamos
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="saldo-promedio">
														1200
													</h2>
													Promedio de Saldo en Préstamos
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-3">
												<div class="well">
													<h2 class="riesgo-7">
														0
													</h2>
													<span class="per-7"></span>%<br>
													Cartera en Riesgo > 7 días
													<br>
													(<span class="fecha-7"></span>)
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="riesgo-15">
														0%
													</h2>
													<span class="per-15"></span>%<br>
													Cartera en Riesgo > 15 días
													(<span class="fecha-15"></span>)
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="riesgo-30">
														0%
													</h2>
													<span class="per-30"></span>%<br>
													Cartera en Riesgo > 30 días
													(<span class="fecha-30"></span>)
												</div>
											</div>
											<div class="col-sm-3">
												<div class="well">
													<h2 class="riesgo-90">
														0%
													</h2>
													<span class="per-90"></span>%<br>
													Cartera en Riesgo > 45 días
													(<span class="fecha-90"></span>)
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-4">
												<div class="well">
													<h2 class="ganancias-interes">
														1200
													</h2>
													Ganancias por Interés de Préstamos
												</div>
											</div>
											<div class="col-sm-4">
												<div class="well" style="background: #a8bc7b;">
													<h2 class="cartera-activa">
														1200
													</h2>
													Cartera Activa
												</div>
											</div>
											<div class="col-sm-4">
												<div class="well">
													<h2 class="cartera-historica">
														1200
													</h2>
													Cartera Histórica
												</div>
											</div>
										</div>
									</div>
									<!-- /CONTENIDO -->
								</div>
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
	
	<script>
		jQuery(document).ready(function() {		
			App.setPage("widgets_box");  //Set current page
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