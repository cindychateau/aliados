<?php
/*
 *	Verificación de Sesión
 */
if(!isset($_SESSION)){
	@session_start();

}

if (isset($_SESSION["mp"]["userid"])) {
	//Si ya se encuentra registrado el usuario en la session lo redirecciona al sistema
	header("Location: home.php");
}

$error = false;

if (isset($_SESSION["mp"]["loginError"])) {
	$error = true;
	$errorNo = $_SESSION["mp"]["loginError"];
	unset($_SESSION["mp"]["loginError"]);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<title>Aliados | Iniciar Sesión</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="apple-mobile-web-app-title" content="">
    <meta name="HandheldFriendly" content="True">
    <!-- hide the browser UI -->
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Enable Startup Image for iOS Home Screen Web App -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- Status bar style -->
    <meta name="apple-mobile-web-app-status-bar-style" content="#3d5473">
    <!-- Tile icon for IE10 on Win8 (144x144 + tile color) -->

    <link rel="apple-touch-icon" href="img/apple-touch-icon.png">
	<!-- STYLESHEETS --><!--[if lt IE 9]><script src="js/flot/excanvas.min.js"></script><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script><![endif]-->
	<link rel="stylesheet/less" type="text/css" href="less/my-bootstrap-theme.less" >
	<!-- <link rel="stylesheet" href="less/my-bootstrap-theme.css" > -->
	<link href="font-awesome/css/font-awesome.css" rel="stylesheet">
	<!-- DATE RANGE PICKER -->
	<link rel="stylesheet" type="text/css" href="js/bootstrap-daterangepicker/daterangepicker-bs3.css" />
	<!-- UNIFORM -->
	<link rel="stylesheet" type="text/css" href="js/uniform/css/uniform.default.css" />
	<!-- ANIMATE -->
	<link rel="stylesheet" type="text/css" href="css/animatecss/animate.min.css" />
	<!-- FONTS -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
	<!-- FAVICON -->
	<link rel="icon" 
      type="image/png" 
      href="img/favicon.png" />
	<!-- JQUERY -->
	<script src="js/jquery/jquery-2.0.3.min.js"></script>
	
	<!-- PRELOADER -->
	<link rel="stylesheet" type="text/css" href="css/preloader.css" />
	<script type="text/javascript" src="js/preloader.js" ></script>

</head>
<body class="login">
	<!-- PRELOADER -->	
	<div id="preloader">
		<div id="status">&nbsp;</div>
		<div id="status1" ><center>Cargando...</center></div>
	</div>
	<!-- /PRELOADER -->

	<!-- PAGE -->
	<section id="page">
			<!-- HEADER -->
			<header>
				<!-- NAV-BAR -->
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-md-offset-4">
							<div id="logo">
								<a href="index.php"><img src="img/logo/logo-alt.png" height="100" alt="logo name" /></a>
							</div>
						</div>
					</div>
				</div>
				<!--/NAV-BAR -->
			</header>
			<!--/HEADER -->
			<!-- LOGIN -->
			<section id="login" class="visible">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-md-offset-4">
							<div class="login-box-plain">
								<h2 class="bigintro">Plataforma Financiera</h2>
								<div id="login-info" class="alert alert-block alert-danger fade in" style="display:none">
									<a class="close" data-dismiss="alerta" href="#" aria-hidden="true">×</a>
									<h4><span id="login-msg"></span></h4>
									<p></p>
								</div>
								<?php 
									if ($error) {
								?>

								<div class="alert alert-block alert-danger fade in">
									<a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>
									<h4><i class="fa fa-times"></i><?php echo $errorNo ; ?></h4>
									<p></p>
								</div>

								<?php
									}
								?>
								
								<div class="divide-40"></div>
								<form id="frm-login" role="form">
								  <div class="form-group">
									<label for="exampleInputEmail1">Correo Electrónico</label>
									<i class="fa fa-envelope"></i>
									<input type="email" class="form-control" id="exampleInputEmail1" name="email" >
								  </div>
								  <div class="form-group"> 
									<label for="exampleInputPassword1">Contraseña</label>
									<i class="fa fa-lock"></i>
									<input type="password" class="form-control" id="exampleInputPassword1" name="password">
								  </div>
								  <div class="form-actions">
									<button type="submit" class="btn btn-danger">Iniciar Sesión</button>
								  </div>
								</form>
								<div class="login-helpers">
									<a href="#" onclick="swapScreen('forgot');return false;">¿Olvidaste tu contraseña?</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!--/LOGIN -->
			<!-- FORGOT PASSWORD -->
			<section id="forgot">
				<div class="container">
					<div class="row">
						<div class="col-md-4 col-md-offset-4">
							<div class="login-box-plain">
								<h2 class="bigintro">Recuperar Contraseña</h2>
								<div id="login-info2" class="alert alert-block alert-info fade in" style="display:none">
									<a class="close" data-dismiss="alerta" href="#" aria-hidden="true">×</a>
									<h4><span id="login-msg2"></span></h4>
									<p></p>
								</div>
								<div class="divide-40"></div>
								<form id="frm-forgot" role="form">
								  <div class="form-group">
									<label for="exampleInputEmail1">Correo Electrónico</label>
									<i class="fa fa-envelope"></i>
									<input type="email" class="form-control" id="exampleInputEmail1" name="email">
								  </div>
								  <div class="form-actions">
									<button type="submit" class="btn btn-info">Enviar Instrucciones</button>
								  </div>
								</form>
								<div class="login-helpers">
									<a href="#" onclick="swapScreen('login');return false;">Regresar a Login</a> <br>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- FORGOT PASSWORD -->
	</section>
	<!--/PAGE -->
	<!-- JAVASCRIPTS -->
	<!-- Placed at the end of the document so the pages load faster -->
	<!-- JQUERY -->
	<!-- JQUERY UI-->
	<script src="js/jquery-ui-1.10.3/ui/jquery-ui.js"></script>
	<!-- BOOTSTRAP -->
	<script src="bootstrap/js/transition.js"></script>
	<script src="bootstrap/js/alert.js"></script>
	<script src="bootstrap/js/modal.js"></script>
	<script src="bootstrap/js/dropdown.js"></script>
	<script src="bootstrap/js/scrollspy.js"></script>
	<script src="bootstrap/js/tab.js"></script>
	<script src="bootstrap/js/tooltip.js"></script>
	<script src="bootstrap/js/popover.js"></script>
	<script src="bootstrap/js/button.js"></script>
	<script src="bootstrap/js/collapse.js"></script>
	<script src="bootstrap/js/carousel.js"></script>
	<script src="bootstrap/js/typeahead.js"></script>
	<script src="js/bootbox/bootbox.min.js"></script>
	<!-- LESS CSS -->
	<script src="js/lesscss/less-1.4.1.min.js" type="text/javascript"></script>
	<!-- UNIFORM -->
	<script type="text/javascript" src="js/uniform/jquery.uniform.min.js"></script>
	<!-- CUSTOM SCRIPT -->
	<script src="js/script.js"></script>
	<script src="js/index.js"></script>
	<script>
		jQuery(document).ready(function() {		
			App.setPage("login");  //Set current page
			App.init(); //Initialise plugins and elements
		});
	</script>
	<script type="text/javascript">
		function swapScreen(id) {
			jQuery('.visible').removeClass('visible animated fadeInUp');
			jQuery('#'+id).addClass('visible animated fadeInUp');
		}
	</script>
	<!-- /JAVASCRIPTS -->
</body>
</html>