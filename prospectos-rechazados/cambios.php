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
define("PAGE_TITLE", "Editar Prospecto");
define("DESCRIPTION", "Edición de Prospecto");

$module = 6;

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
	<!-- DATATABLES -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/datatables/media/css/jquery.dataTables.css" >
	<!-- <link rel="stylesheet" href="less/my-bootstrap-theme.css" > -->
	<style type="text/css">
		.radio {
			min-height: 0px !important;
		}

		.grp-vivienda {
			margin-top: 20px;
		}

		.form-group .row {
			margin-bottom: 5px;
		}
	</style>

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
	<!-- WIZARD -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/bootstrap-wizard/wizard.css" >
	<!-- UNIFORM -->
	<link rel="stylesheet" type="text/css" href="<?php echo $ruta;?>js/uniform/css/uniform.default.css">
	<!-- SWITCH -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/bootstrap-switch/bootstrap-switch.css" >
	<!-- FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
	<!-- JQUERY UI -->
	<link rel="stylesheet/less" type="text/css"  href="<?php echo $ruta;?>js/jquery-ui-1.10.3/themes/base/jquery-ui.css" >
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
	<style type="text/css">
		.erase-pic {
			cursor: pointer;
		}

		.no-pad {
			padding-top: 0px !important;
		}

		.container-map {
			margin-top: 20px;
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
						<div class="row">
							<div class="col-md-12">
								<!-- BOX -->
								<!--div class="box border primary" id="formWizard">
									<div class="box-title">
									</div>
									<div class="box-body form"-->
										<div class="margin-bottom-15">
											<small>
												*Todos los campos son obligatorios
											</small>
										</div>
										<form id="form-prospecto" name="form-prospecto" action="include/Libs.php?accion=saveRecord" class="form-horizontal" >
										<div class="wizard-form">
										   <div class="wizard-content">
										   	<!-- TABS -->
											  <ul class="nav nav-pills nav-justified steps">
												 <li class="active">
													<a href="#datos" data-toggle="tab" class="wiz-step lnk-datos" data-id="0">
													<span class="step-number">1</span>
													<span class="step-name"><i class="fa fa-check"></i> Datos Personales</span>   
													</a>
												 </li>
												 <li>
													<a href="#ingresos" data-toggle="tab" class="wiz-step lnk-ingresos" data-id="1">
													<span class="step-number">2</span>
													<span class="step-name"><i class="fa fa-check"></i> Ingresos</span>   
													</a>
												 </li>
												 <li>
													<a href="#referencias" data-toggle="tab" class="wiz-step lnk-referencias" data-id="2">
													<span class="step-number">3</span>
													<span class="step-name"><i class="fa fa-check"></i> Referencias</span>   
													</a> 
												 </li>
												 <li>
													<a href="#garantias" data-toggle="tab" class="wiz-step lnk-garantias" data-id="3">
													<span class="step-number">4</span>
													<span class="step-name"><i class="fa fa-check"></i> Garantías</span>   
													</a> 
												 </li>
												 <!--li>
													<a href="#credito" data-toggle="tab" class="wiz-step lnk-credito" data-id="6">
													<span class="step-number">7</span>
													<span class="step-name"><i class="fa fa-check"></i> Crédito</span>   
													</a> 
												 </li-->
											  </ul>
											  <!-- /TABS -->
											  <div id="bar" class="progress progress-striped progress-sm active" role="progressbar">
												 <div class="progress-bar progress-bar-warning"></div>
											  </div>											  
											  <div class="tab-content">
												<!-- DATOS -->
												 <div class="tab-pane active" id="datos">
												 	<div class="box border primary">
														<div class="box-title">
															DATOS PERSONALES
														</div>
														<div class="box-body big">
															<div class="form-group">
																<input name="id" type="hidden" id="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : '-1' ; ?>">
																<label class="col-sm-2 col-sm-offset-7 control-label">*Fecha</label>
																<div class="col-sm-3">
																	<input id="fecha" name="fecha" type="text" class="form-control">
																</div>
														  	</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">*Nombre</label>
																<div class="col-sm-10">
																	<input id="nombre" name="nombre" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
																<label class="col-sm-2 control-label">*Dirección</label>
																<div class="col-sm-10">
																	<textarea class="form-control" name="direccion" id="direccion"></textarea>
																</div>
														  	</div>
														  	<div class="form-group">
																<label class="col-sm-2 control-label">*E-mail</label>
																<div class="col-sm-4">
																	<input id="email" name="email" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">*Teléfono</label>
																<div class="col-sm-4">
																	<input id="telefono" name="telefono" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
																<label class="col-sm-2 control-label">*Celular/Whatsapp</label>
																<div class="col-sm-4">
																	<input id="celular" name="celular" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">Facebook <i class="fa fa-info-circle tip" data-original-title="/usuario"></i></label>
																<div class="col-sm-4">
																	<input id="facebook" name="facebook" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
														  		<div class="col-md-6">
																	<label class="col-md-4 control-label">Usted vive con: </label> 
																	<div class="col-md-8"> 
																		<label class="checkbox"> <div class="checker"><span><input id="vive_padres" name="vive_padres" type="checkbox" class="uniform" value="Padres"></span></div> Padres </label> 
																		<label class="checkbox"> <div class="checker"><span><input id="vive_conyugue" name="vive_conyugue" type="checkbox" class="uniform" value="Cóyugue"></span></div> Cónyugue</label>
																		<label class="checkbox"> <div class="checker"><span><input id="vive_hijos" name="vive_hijos" type="checkbox" class="uniform" value="Hijos"></span></div> Hijos</label>
																		<label class="checkbox"> <div class="checker"><span><input id="vive_hermanos" name="vive_hermanos" type="checkbox" class="uniform" value="Hermanos"></span></div> Hermanos</label>
																		<label class="checkbox"> <div class="checker"><span><input id="check_vive_otro" name="check_vive_otro" type="checkbox" class="uniform" value="Otros"></span></div> Otros</label>
																		<input id="vive_otros" name="vive_otros" type="text" class="form-control" style="display:none;" placeholder="Especifique">
																	</div>
																</div>
																<div class="col-md-6">
																	<label class="col-md-4 control-label">Dependen económicamente de usted: </label>
																	<div class="col-md-8">
																		<div class="row">
																			<div class="col-md-6">
																				<label class="checkbox"> <div class="checker"><span><input id="depende_padres" name="depende_padres" type="checkbox" class="uniform" value="Padres"></span></div> Padres </label> 
																			</div>
																			<div class="col-md-6">
																				<input id="depende_comment_padres" name="depende_comment_padres" type="text" class="form-control">
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label class="checkbox"> <div class="checker"><span><input id="depende_conyugue" name="depende_conyugue" type="checkbox" class="uniform" value="Cónyugue"></span></div> Cónyugue </label> 
																			</div>
																			<div class="col-md-6">
																				<input id="depende_comment_conyugue" name="depende_comment_conyugue" type="text" class="form-control">
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label class="checkbox"> <div class="checker"><span><input id="depende_hijos" name="depende_hijos" type="checkbox" class="uniform" value="Hijos"></span></div> Hijos </label> 
																			</div>
																			<div class="col-md-6">
																				<input id="depende_comment_hijos" name="depende_comment_hijos" type="text" class="form-control">
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label class="checkbox"> <div class="checker"><span><input id="depende_hermanos" name="depende_hermanos" type="checkbox" class="uniform" value="Hermanos"></span></div> Hermanos </label> 
																			</div>
																			<div class="col-md-6">
																				<input id="depende_comment_hermanos" name="depende_comment_hermanos" type="text" class="form-control">
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label class="checkbox"> <div class="checker"><span><input id="depende_otros" name="depende_otros" type="checkbox" class="uniform" value="Otros"></span></div> Otros </label> 
																			</div>
																			<div class="col-md-6">
																				<input id="depende_comment_otros" name="depende_comment_otros" type="text" class="form-control">
																			</div>
																		</div>
																	</div> 
																</div>
															</div>
														</div>
													</div>										
												 </div>
												 <!-- /DATOS -->
												 <!-- INGRESOS -->
												 <div class="tab-pane" id="ingresos">
												 	<div class="box border primary">
														<div class="box-title">
															INGRESOS
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">*Actividad Económica que realiza</label>
																<div class="col-sm-10 div-actividad">
																	<select class="form-control" disabled><option>Cargando...</option></select>
																</div>
																<div class="col-sm-10">
																	<input id="act_otro" name="act_otro" type="text" class="form-control" placeholder="Especifique" style="display: none;">
																</div>
															</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">*Antigüedad</label>
																<div class="col-sm-4">
																	<input id="antiguedad" name="antiguedad" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">*Ingreso Promedio Semanal</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="ingreso_promedio" name="ingreso_promedio" type="text" class="form-control">
																	</div>
																</div>
														  	</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">*Vivienda</label>
														  		<div class="col-sm-4">
														  			<div class="row">
														  				<div class="col-md-5">
																			<label class="radio">
																				<input type="radio" id="vivienda_propia" class="uniform" name="vivienda" value="Propia">
																				Propia 
																			</label>
																		</div>	
																	</div>
																	<div class="row">
														  				<div class="col-md-5">		
																			<label class="radio">
																				<input type="radio" id="vivienda_rentada" class="uniform" name="vivienda" value="Rentada">
																				Rentada 
																			</label>
																		</div>
																		<div class="col-md-7">
																			<div class="input-group">
																				<i class="fa fa-info-circle tip" data-original-title="Gasto mensual en vivienda"></i>
																				<span class="input-group-addon">$</span>
																				<input id="vivienda_gasto" name="vivienda_gasto" type="text" class="form-control">
																			</div>
																		</div>
														  			</div>
														  		</div>
														  		<label class="col-sm-2 control-label">Otros Préstamos</label>
														  		<div class="col-md-4">
														  			<div class="row">
														  				<div class="col-md-6">
														  					<input id="prestamo_otro_1" name="prestamo_otro_1" type="text" class="form-control" placeholder="Préstamo">
														  				</div>
														  				<div class="col-md-6">
														  					<div class="input-group">
																				<span class="input-group-addon">$</span>
																				<input id="prestamos_pago_1" name="prestamos_pago_1" type="text" class="form-control" placeholder="Semanal">
																			</div>
														  				</div>
														  			</div>
														  			<div class="row">
														  				<div class="col-md-6">
														  					<input id="prestamo_otro_2" name="prestamo_otro_2" type="text" class="form-control" placeholder="Préstamo">
														  				</div>
														  				<div class="col-md-6">
														  					<div class="input-group">
																				<span class="input-group-addon">$</span>
																				<input id="prestamos_pago_2" name="prestamos_pago_2" type="text" class="form-control" placeholder="Semanal">
																			</div>
														  				</div>
														  			</div>
														  		</div>
														  	</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															INGRESOS ADICIONALES
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Concepto</label>
																<div class="col-sm-4">
																	<div class="row">
																		<input id="ingreso_adicional_1" name="ingreso_adicional_1" type="text" class="form-control">
																	</div>
																	<div class="row">
																		<input id="ingreso_adicional_2" name="ingreso_adicional_2" type="text" class="form-control">
																	</div>
																	<div class="row">
																		<input id="ingreso_adicional_3" name="ingreso_adicional_3" type="text" class="form-control">
																	</div>
																</div>
																<label class="col-sm-2 control-label">Monto Semanal</label>
																<div class="col-sm-4">
																	<div class="row">
																		<div class="input-group">
																			<span class="input-group-addon">$</span>
																			<input id="ingreso_monto_1" name="ingreso_monto_1" type="text" class="form-control" placeholder="Semanal">
																		</div>
																	</div>
																	<div class="row">
																		<div class="input-group">
																			<span class="input-group-addon">$</span>
																			<input id="ingreso_monto_2" name="ingreso_monto_2" type="text" class="form-control" placeholder="Semanal">
																		</div>
																	</div>
																	<div class="row">
																		<div class="input-group">
																			<span class="input-group-addon">$</span>
																			<input id="ingreso_monto_3" name="ingreso_monto_3" type="text" class="form-control" placeholder="Semanal">
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															PROYECTO DE INVERSION
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">*Monto Solicitado</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="monto_solicitado" name="monto_solicitado" type="text" class="form-control">
																	</div>
																</div>
																<label class="col-sm-2 control-label">*Proyecto de Inversión</label>
																<div class="col-sm-4">
																	<textarea class="form-control" name="proyecto_inversion" id="proyecto_inversion"></textarea>
																</div>
															</div>
														</div>
													</div>											
												 </div>
												<!-- /INGRESOS --> 
												 <!-- REFERENCIAS -->
												<div class="tab-pane" id="referencias">
													<div class="box border primary">
														<div class="box-title">
															REFERENCIA 1
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Nombre</label>
																<div class="col-sm-10">
																	<input id="referencia_nombre_1" name="referencia_nombre_1" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">Relación</label>
																<div class="col-sm-4">
																	<select id="referencia_relacion_1" name="referencia_relacion_1" class="form-control">
																		<option value="Familia">Familia</option>
																		<option value="Trabajo">Trabajo</option>
																		<option value="Vecino">Vecino</option>
																		<option value="Otro">Otro</option>
																	</select>
																</div>
																<label class="col-sm-2 control-label">Teléfono</label>
																<div class="col-sm-4">
																	<input id="referencia_telefono_1" name="referencia_telefono_1" type="text" class="form-control">
																</div>
														  	</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															REFERENCIA 2
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Nombre</label>
																<div class="col-sm-10">
																	<input id="referencia_nombre_2" name="referencia_nombre_2" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">Relación</label>
																<div class="col-sm-4">
																	<select id="referencia_relacion_2" name="referencia_relacion_2" class="form-control">
																		<option value="Familia">Familia</option>
																		<option value="Trabajo">Trabajo</option>
																		<option value="Vecino">Vecino</option>
																		<option value="Otro">Otro</option>
																	</select>
																</div>
																<label class="col-sm-2 control-label">Teléfono</label>
																<div class="col-sm-4">
																	<input id="referencia_telefono_2" name="referencia_telefono_2" type="text" class="form-control">
																</div>
														  	</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															REFERENCIA 3
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Nombre</label>
																<div class="col-sm-10">
																	<input id="referencia_nombre_3" name="referencia_nombre_3" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">Relación</label>
																<div class="col-sm-4">
																	<select id="referencia_relacion_3" name="referencia_relacion_3" class="form-control">
																		<option value="Familia">Familia</option>
																		<option value="Trabajo">Trabajo</option>
																		<option value="Vecino">Vecino</option>
																		<option value="Otro">Otro</option>
																	</select>
																</div>
																<label class="col-sm-2 control-label">Teléfono</label>
																<div class="col-sm-4">
																	<input id="referencia_telefono_3" name="referencia_telefono_3" type="text" class="form-control">
																</div>
														  	</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															REFERENCIA 4
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Nombre</label>
																<div class="col-sm-10">
																	<input id="referencia_nombre_4" name="referencia_nombre_4" type="text" class="form-control">
																</div>
														  	</div>
														  	<div class="form-group">
														  		<label class="col-sm-2 control-label">Relación</label>
																<div class="col-sm-4">
																	<select id="referencia_relacion_4" name="referencia_relacion_4" class="form-control">
																		<option value="Familia">Familia</option>
																		<option value="Trabajo">Trabajo</option>
																		<option value="Vecino">Vecino</option>
																		<option value="Otro">Otro</option>
																	</select>
																</div>
																<label class="col-sm-2 control-label">Teléfono</label>
																<div class="col-sm-4">
																	<input id="referencia_telefono_4" name="referencia_telefono_4" type="text" class="form-control">
																</div>
														  	</div>
														</div>
													</div>
												 </div>	
												<!-- /REFERENCIAS -->
												<!-- GARANTIAS -->
												 <div class="tab-pane" id="garantias">
												 	<div class="box border primary">
														<div class="box-title">
															GARANTIA 1
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Bien</label>
																<div class="col-sm-4">
																	<input id="garantia_bien_1" name="garantia_bien_1" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">Modelo</label>
																<div class="col-sm-4">
																	<input id="garantia_modelo_1" name="garantia_modelo_1" type="text" class="form-control">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Descripción</label>
																<div class="col-sm-10">
																	<textarea class="form-control" id="garantia_descripcion_1" name="garantia_descripcion_1"></textarea>
																</div>
															</div>
														</div>
													 </div>	
													 <div class="box border primary">
														<div class="box-title">
															GARANTIA 2
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Bien</label>
																<div class="col-sm-4">
																	<input id="garantia_bien_2" name="garantia_bien_2" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">Modelo</label>
																<div class="col-sm-4">
																	<input id="garantia_modelo_2" name="garantia_modelo_2" type="text" class="form-control">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Descripción</label>
																<div class="col-sm-10">
																	<textarea class="form-control" id="garantia_descripcion_2" name="garantia_descripcion_2"></textarea>
																</div>
															</div>
														</div>
													 </div>
													 <div class="box border primary">
														<div class="box-title">
															GARANTIA 3
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">Bien</label>
																<div class="col-sm-4">
																	<input id="garantia_bien_3" name="garantia_bien_3" type="text" class="form-control">
																</div>
																<label class="col-sm-2 control-label">Modelo</label>
																<div class="col-sm-4">
																	<input id="garantia_modelo_3" name="garantia_modelo_3" type="text" class="form-control">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Descripción</label>
																<div class="col-sm-10">
																	<textarea class="form-control" id="garantia_descripcion_3" name="garantia_descripcion_3"></textarea>
																</div>
															</div>
														</div>
													 </div>
													 <div class="box border primary">
														<div class="box-title">
															DOCUMENTOS Y COMENTARIOS ADICIONALES
														</div>
														<div class="box-body big">
														  	<div class="form-group">
																<label class="col-sm-2 control-label">*IFE</label>
																<div class="col-sm-2">
																	<a id="ife_actual" href="#" target="_blank">IFE Actual</a>
																</div>
																<div class="col-sm-4">
																	<input id="ife" name="ife" type="file" class="ios_only" accept="image/*" capture="camera">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">*Comprobante de Domicilio</label>
																<div class="col-sm-2">
																	<a id="cd_actual" href="#" target="_blank">Comprobante Actual</a>
																</div>
																<div class="col-sm-4">
																	<input id="comprobante_domicilio" name="comprobante_domicilio" type="file" class="ios_only" accept="image/*" capture="camera">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Comentarios Adicionales del Prospecto</label>
																<div class="col-sm-10">
																	<textarea id="comentarios" name="comentarios" class="form-control"></textarea>
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Rechazar 
																	<i class="fa fa-info-circle tip" data-original-title="En caso de creer que el prospecto no cumple con los requisitos para entrar en el crédito, favor de seleccionar esta opción. X significando que no es apto para el crédito."></i>
																</label>
																<div class="col-sm-2">
																	<div class="make-switch switch-large has-switch" data-off="success" data-on="danger" data-off-label="<i class='fa fa-check'></i>" data-on-label="<i class='fa fa-times'></i>">
																		<input id="rechazar" name="rechazar" type="checkbox">
																	</div>
																</div>
																<div class="col-sm-8">
																	<textarea id="razon_rechazo" name="razon_rechazo" class="form-control" style="display:none;" placeholder="Razón de Rechazo"></textarea>
																</div>
															</div>
														</div>
													</div>
												 </div>
												<!-- /GARANTIAS --> 
											  	<!-- CRÉDITO -->
												<!--div class="tab-pane" id="credito">
												 	<div class="box border primary">
														<div class="box-title">
															CRÉDITO
														</div>
														<div class="box-body big">
															<div class="form-group">
																<label class="col-sm-2 control-label">*Empresa</label>
																<div class="col-sm-10">
																	<select class="form-control" name="cred_empresa" id="cred_empresa">
																		<option value="MARLO INSTITUCION">Marlo Institución</option>
																		<option value="RODOLFO MARTINEZ">Rodolfo Martínez</option>
																		<option value="HACIENDA SAN JERONIMO">Hacienda San Jerónimo</option>
																	</select>
															  	</div>
													   		</div>
															<div class="form-group">
																<label class="control-label col-md-2">*Tipo</label>
															   <div class="col-md-4">
																	<select class="form-control" name="cred_tipo" id="cred_tipo">
																		<option value="SIN GARANTIA">Sin Garantia</option>
																		<option value="SEMINUEVO">Seminuevo</option>
																		<option value="AUTO">Auto</option>
																		<option value="HIPOTECA">Hipoteca</option>
																		<option value="EMPENO">Empeño</option>
																		<option value="NOMINA">Nomina</option>
																	</select>
																</div>
															   <label class="control-label col-md-2">*Frecuencia de Pagos</label>
															   <div class="col-md-4">
																	<select class="form-control" name="cred_frec_pago" id="cred_frec_pago">
																		<option value="SEMANAL">Semanal</option>
																		<option value="QUINCENAL">Quincenal</option>
																		<option value="MENSUAL">Mensual</option>
																	</select>
																</div>
															</div>	
															<div class="form-group">
																<label class="control-label col-md-2">*Importe Autorizado</label>
															   <div class="col-md-4">
																 	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="cred_importe" name="cred_importe" type="text" class="form-control">
																	</div>
															   </div>
															   	<label class="control-label col-md-2">*Tasa</label>
															   	<div class="col-md-4">
															   		<div class="input-group">
																		<input id="cred_tasa" name="cred_tasa" type="number" class="form-control">
																		<span class="input-group-addon">%</span>
																	</div>
															   	</div>
															</div>
															<div class="form-group">
															   <label class="control-label col-md-2">*Plazo</label>
															   <div class="col-md-4">
															   		<input id="cred_plazo" name="cred_plazo" type="number" class="form-control">
																</div>
															   <label class="control-label col-md-2">*IVA</label>
															   <div class="col-md-4">
															   		<div class="input-group">
																		<input id="cred_iva" name="cred_iva" type="number" class="form-control" value="16">
																		<span class="input-group-addon">%</span>
																	</div>
															   </div>															   
															</div>															
															<div class="form-group">
															   <label class="control-label col-md-2">*Interés Moratorio Mensual</label>
															   <div class="col-md-4">
																 	<div class="input-group">
																		<input id="cred_interes_m" name="cred_interes_m" type="number" class="form-control" value="10">
																		<span class="input-group-addon">%</span>
																	</div>
															   </div>
															   <label class="control-label col-md-2">*Fecha de Primer Pago</label>
															   <div class="col-md-4">
															   		<input id="cred_fecha_pago" name="cred_fecha_pago" type="text" class="form-control">
															   </div>
															</div>															
													   	</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															RESULTADOS INFORMATIVOS
														</div>
														<div class="box-body big">
															<!--div class="form-group">
															   <label class="control-label col-md-2">Frecuencia de Pago</label>
															   <div class="col-md-10">
																	<input id="result_frec_pago" name="result_frec_pago" disabled="disabled" type="text" class="form-control">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Comisión por Apertura</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_com_apertura" name="result_com_apertura" readonly type="text" class="form-control">
																	</div>
															  	</div>
															  	<label class="col-sm-2 control-label">Capital</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_capital" name="result_capital" readonly type="text" class="form-control">
																	</div>
															  	</div>
													   		</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Tasa</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<input id="result_tasa" name="result_tasa" type="number" class="form-control" readonly>
																		<span class="input-group-addon">%</span>
																	</div>
															  	</div>
																<label class="col-sm-2 control-label">Tasa Global</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<input id="result_tasa_global" name="result_tasa_global" type="number" class="form-control" readonly>
																		<span class="input-group-addon">%</span>
																	</div>
															  	</div>
															  	<label class="col-sm-2 control-label">Total de Interés</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_interes" name="result_interes" readonly type="text" class="form-control">
																	</div>
															  	</div>
													   		</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Gastos de Cobranza</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_gastos" name="result_gastos" readonly type="text" class="form-control">
																	</div>
															  	</div>
															  	<label class="col-sm-2 control-label">Total Documentado</label>
																<div class="col-sm-4">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_total_documentado" name="result_total_documentado" readonly type="text" class="form-control">
																	</div>
															  	</div>
													   		</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">Importe de Pago</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_importe" name="result_importe" type="number" class="form-control" readonly>
																	</div>
															  	</div>
																<label class="col-sm-2 control-label">Bonificación por Pago Oportuno</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_bonificacion" name="result_bonificacion" type="number" class="form-control" readonly>																		
																	</div>
															  	</div>
															  	<label class="col-sm-2 control-label">Importe Pago Oportuno</label>
																<div class="col-sm-2">
																	<div class="input-group">
																		<span class="input-group-addon">$</span>
																		<input id="result_oportuno" name="result_oportuno" readonly type="text" class="form-control">
																	</div>
															  	</div>
													   		</div>
													   	</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															COMENTARIOS DE INVESTIGACION
														</div>
														<div class="box-body big">
														  	<div class="form-group">
																<textarea class="form-control" class="comentarios" id="comentarios" readonly></textarea>														  	
														  	</div>
														</div>
													</div>
													<div class="box border primary">
														<div class="box-title">
															DOCUMENTOS
														</div>
														<div class="box-body big">
														  	<div class="form-group">
																<label class="col-sm-2 control-label">*Minuta de Autorización</label>
																<div class="col-sm-4">
																	<input id="minuta" name="minuta" type="file">
																</div>
																<label class="col-sm-2 control-label">*Solicitud de crédito</label>
																<div class="col-sm-4">
																	<input id="solicitud_credito" name="solicitud_credito" type="file">
																</div>
															</div>
															<div class="form-group">
																<label class="col-sm-2 control-label">*Garantía</label>
																<div class="col-sm-4">
																	<input id="garantia" name="garantia" type="file">
																</div>
															</div>
														</div>
													</div>
												 </div-->
												<!-- /CRÉDITO --> 
												<!-- DOCUMENTOS -->
												 <div class="tab-pane" id="doctos2">
												 														
												 </div>
												<!-- /DOCUMENTOS --> 
											  	<!-- CRÉDITO -->
												 <div class="tab-pane" id="resultados">
												 	
												 </div>
												<!-- /CRÉDITO --> 

												 <!-- TABS -->
											  </div>
										   </div>
										   <!-- Botones -->
										   <div class="wizard-buttons">
											  <div class="row">
												 <div class="col-md-12">
													<div class="col-md-offset-3 col-md-9">
													   <a href="javascript:;" class="btn btn-default prevBtn">
														<i class="fa fa-arrow-circle-left"></i> Anterior 
													   </a>
													   <a href="javascript:;" class="btn btn-primary nextBtn">
														Siguiente <i class="fa fa-arrow-circle-right"></i>
													   </a>                           
													</div>
												 </div>
											  </div>
										   </div>
										   <!-- /Botones -->
										</div>
									 </form>
									<!--/div-->
								</div>
								<!-- /BOX -->
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
	<!-- SWITCH -->
	<script src="<?php echo $ruta;?>js/bootstrap-switch/bootstrap-switch.min.js"></script>
	<!-- INPUT MASK -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/bootstrap-inputmask/bootstrap-inputmask.min.js"></script>
	<!-- UNIFORM -->
	<script type="text/javascript" src="<?php echo $ruta;?>js/uniform/jquery.uniform.min.js"></script>
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

		echo '<script>var ruta = "'.$ruta.'"</script>';

	?>
	<!-- /JAVASCRIPTS -->
</body>
</html>