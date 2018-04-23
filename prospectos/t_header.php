<body>
<header>
	<div class="top hidden-xs">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<div class="logo pull-left">
						<a href="<?php echo base_url(); ?>">
							<img class="ems-tactico" src="<?php echo base_url("images/ems.png"); ?>" width="193" height="100">
						</a>
						<img class="slogan" src="<?php echo base_url("images/equipando-a-los-profesionales.png"); ?>" alt="Equipando A los Profesionales" width="320" height="14">
					</div>
					<div class="social pull-right">
						<h6><span>Conectate</span> con EMS MÉXICO</h6>
						<ul>
						    <li>
						        <a href="https://www.facebook.com/EMS.LaTienda" target="_blank">
						            <i class="fa fa-facebook"></i>
						        </a>
						    </li>
						    <li>
						        <a href="https://twitter.com/EMS_MEXICO" target="_blank">
						            <i class="fa fa-twitter"></i>
						        </a>
						    </li>
						    <li>
						        <a href="https://plus.google.com/+Emsmexico_EMSLaTienda/posts" target="_blank">
						            <i class="fa fa-google-plus"></i>
						        </a>
						    </li>
						    <li>
						        <a href="https://www.youtube.com/user/emsmexico" target="_blank">
						            <i class="fa fa-youtube"></i>
						        </a>
						    </li>
						    <li>
						        <a href="https://www.pinterest.com/emsmexico/" target="_blank">
						            <i class="fa fa-pinterest"></i>
						        </a>
						    </li>
						    <li>
						        <a href="https://foursquare.com/ems_mexico" target="_blank">
						            <i class="fa fa-foursquare"></i>
						        </a>
						    </li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div><!-- top  hidden-xs -->
	<div class="navigation">
		<div class="container">
			<div class="row">
				<div class="col-xs-12">
					<nav class="navbar">
						<div class="container-fluid">
							<!-- Brand and toggle get grouped for better mobile display -->
							<div class="navbar-header">
								<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
									<span class="icon-bar"></span>
								</button>
								<a class="navbar-brand visible-xs" href="<?php echo base_url(); ?>">
									<img class="ems-tactico" src="<?php echo base_url("images/ems.png"); ?>" width="100">
								</a>
							</div>
							<!-- Collect the nav links, forms, and other content for toggling -->
							<div class="collapse navbar-collapse">
								<ul class="nav navbar-nav hidden-xs">

                                	<!-- nuevo menu -->
									<div class="menu">
									    <nav>
							                <div class="openMenu">
							                    <div class="nav-btn">Productos</div>
							                    <ul class="submenu">
							                        <?php foreach ($categorias as $c): ?>
							                        <li><?php echo htmlspecialchars($c['categoria'], ENT_QUOTES, "UTF-8"); ?>
							                            <?php
							                                $ok = TRUE;
							                                foreach ($subcategorias as $x){
							                                    if($x['id_categoria'] == $c['id']) {
							                                        $ok = TRUE;
							                                        break;
							                                    } else {
							                                        $ok = FALSE;
							                                    }
							                                }
							                            if($ok) {?>
							                            <div class="in-side">
							                                <ul>
							                                <?php
							                                    $n = 1;
							                                    foreach ($subcategorias as $s): ?>
							                                    <?php if($s['id_categoria'] == $c['id']){?>
							                                        <li <?php if($n == 1) echo 'style="clear:both;"'; ?>>
							                                            <a href="<?php echo base_url($c['url'].'/'.$s['url']); ?>">
							                                                <div>
							                                                    <figure><?php if($s['thumb']) { ?><img src="<?php echo base_url('img/categorias/'.$s['thumb']); ?>" height="150" width="150"><?php } else { ?><img style="display: block;" src="<?php echo base_url('files/images/no_img.png'); ?>" height="150" width="180"><?php } ?></figure>
							                                                    <span><?php echo htmlspecialchars($s['subcategoria'], ENT_QUOTES, "UTF-8"); ?></span>
							                                                </div>
							                                            </a>
							                                        </li>
							                                        <?php if($n == 6 ) { $n = 1; } else { $n++; } } ?>
							                                    <?php endforeach ?>
							                                </ul>
							                            </div>
							                            <?php } ?>
							                        </li>
							                        <?php endforeach ?>

							                    </ul>
							                </div>
							            </nav>
								    </div>
								    <!-- nuevo menu -->

                                    <!--
                                    <?php foreach ($categorias as $c): ?>
                                        <li class="dropdown">
    										<a href="#" class="dropdown-toggle"><?php echo htmlspecialchars($c['categoria'], ENT_QUOTES, "UTF-8"); ?></a>
    										<ul class="dropdown-menu dropdown-menu-left" role="menu"  style="width: auto;">
                                                <?php foreach ($subcategorias as $s): ?>
                                                    <?php if($s['id_categoria'] == $c['id']){?>
                                                        <li><a href="<?php echo base_url($c['url'].'/'.$s['url']); ?>"><?php echo htmlspecialchars($s['subcategoria'], ENT_QUOTES, "UTF-8"); ?></a></li>
                                                    <?php } ?>
                                                <?php endforeach ?>
    										</ul>
    									</li>
                                    <?php endforeach ?>
                                	-->
                                    <!--
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Ropa</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="categoria.php">Pantalones</a></li>
											<li><a href="#">Camisas</a></li>
											<li><a href="#">Polos</a></li>
											<li><a href="#">Camisetas</a></li>
											<li><a href="#">Crossfit</a></li>
											<li><a href="#">Abrigo</a></li>
											<li><a href="#">Interior</a></li>
											<li><a href="#">Dama</a></li>
										</ul>
									</li>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Calzado</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="#">Boots</a></li>
											<li><a href="#">Tennis</a></li>
											<li><a href="#">Crossfit</a></li>
											<li><a href="#">Escalada</a></li>
											<li><a href="#">Dama</a></li>
										</ul>
									</li>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Mochilas y Bolsos</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="#">Montaña</a></li>
											<li><a href="#">Maletas</a></li>
											<li><a href="#">Campamento</a></li>
											<li><a href="#">Dama</a></li>
										</ul>
									</li>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Accesorios</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="#">Lentes</a></li>
											<li><a href="#">Gorras</a></li>
											<li><a href="#">Guantes</a></li>
										</ul>
									</li>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Accesorios para arma</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="#">Estuches</a></li>
											<li><a href="#">Balas</a></li>
											<li><a href="#">Cañones</a></li>
											<li><a href="#">Lentes</a></li>
										</ul>
									</li>
									<li class="dropdown">
										<a href="#" class="dropdown-toggle">Equipo protección</a>
										<ul class="dropdown-menu dropdown-menu-left" role="menu">
											<li><a href="#">Cascos</a></li>
											<li><a href="#">Chalecos</a></li>
											<li><a href="#">Botas</a></li>
											<li><a href="#">Arneses</a></li>
										</ul>
									</li>
                                    -->
								</ul>
								<div class="extra-links pull-right">
									<a href="<?php echo base_url("como-comprar"); ?>">Como comprar</a>
									<a href="<?php echo base_url("como-cotizar"); ?>">Como cotizar</a>
									<!-- <a data-toggle="modal" href="#myModal" data-target="#myModal">Métodos de pago</a> -->
									<a href="<?php echo base_url("empresa"); ?>">Empresa</a>
									<a href="<?php echo base_url("contacto"); ?>">Contacto</a>
								</div>
							</div><!-- hidden-xs -->
							<div class="visible-xs">
								<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                                <?php
                                    $f['method'] = 'get';
                                    $f = array('method' => 'get', 'class' => 'navbar-form clearfix', 'role' => 'search');

                                    echo form_open('buscar', $f);
                                ?>
									<!--<form class="navbar-form clearfix" role="search">-->
										<div class="form-group pull-left">
											<input name="q" type="text" class="form-control" placeholder="Buscar ...">
										</div>
										<button type="submit" class="btn btn-default pull-right"><i class="fa fa-search"></i></button>
									<!--</form>-->
                                    <?php
                                        echo form_close();
                                    ?>
									<ul class="nav navbar-nav">
                                        <?php foreach ($categorias as $c): ?>
                                            <li class="dropdown">
    											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?php echo htmlspecialchars($c['categoria'], ENT_QUOTES, "UTF-8"); ?></a>
    											<ul class="dropdown-menu dropdown-menu-left" role="menu">
                                                    <?php foreach ($subcategorias as $s): ?>
                                                        <?php if($s['id_categoria'] == $c['id']){?>
                                                            <li><a href="<?php echo base_url($c['url'].'/'.$s['url']); ?>"><?php echo htmlspecialchars($s['subcategoria'], ENT_QUOTES, "UTF-8"); ?></a></li>
                                                        <?php } ?>
                                                    <?php endforeach ?>
    											</ul>
    										</li>
                                        <?php endforeach ?>
										<!--
                                        <li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Ropa</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="categoria.php">Pantalones</a></li>
												<li><a href="#">Camisas</a></li>
												<li><a href="#">Polos</a></li>
												<li><a href="#">Camisetas</a></li>
												<li><a href="#">Crossfit</a></li>
												<li><a href="#">Abrigo</a></li>
												<li><a href="#">Interior</a></li>
												<li><a href="#">Dama</a></li>
											</ul>
										</li>
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Calzado</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="#">Boots</a></li>
												<li><a href="#">Tennis</a></li>
												<li><a href="#">Crossfit</a></li>
												<li><a href="#">Escalada</a></li>
												<li><a href="#">Dama</a></li>
											</ul>
										</li>
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Mochilas y Bolsos</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="#">Montaña</a></li>
												<li><a href="#">Maletas</a></li>
												<li><a href="#">Campamento</a></li>
												<li><a href="#">Dama</a></li>
											</ul>
										</li>
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Accesorios</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="#">Lentes</a></li>
												<li><a href="#">Gorras</a></li>
												<li><a href="#">Guantes</a></li>
											</ul>
										</li>
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Accesorios para arma</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="#">Estuches</a></li>
												<li><a href="#">Balas</a></li>
												<li><a href="#">Cañones</a></li>
												<li><a href="#">Lentes</a></li>
											</ul>
										</li>
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Equipo protección</a>
											<ul class="dropdown-menu dropdown-menu-left" role="menu">
												<li><a href="#">Cascos</a></li>
												<li><a href="#">Chalecos</a></li>
												<li><a href="#">Botas</a></li>
												<li><a href="#">Arneses</a></li>
											</ul>
										</li>
                                        -->
									</ul>
									<div class="contact">
										<a href="callto:83705006">
											<i class="fa fa-phone"></i>
											<p>01 81 83705006</p>
										</a>
										<a href="mailto:Ventas@EMSMex.com">
											<i class="fa fa-envelope"></i>
											<p>Ventas@EMSMex.com</p>
										</a>
									</div>
									<div class="social clearfix">
										<h6><span>Conectate</span> con EMS MÉXICO</h6>
										<ul>
										    <li>
										        <a href="https://www.facebook.com/EMS.LaTienda" target="_blank">
										            <i class="fa fa-facebook"></i>
										        </a>
										    </li>
										    <li>
										        <a href="https://twitter.com/EMS_MEXICO" target="_blank">
										            <i class="fa fa-twitter"></i>
										        </a>
										    </li>
										    <li>
										        <a href="https://plus.google.com/+EMSMexicoLaTienda/posts" target="_blank">
										            <i class="fa fa-google-plus"></i>
										        </a>
										    </li>
										    <li>
										        <a href="https://www.youtube.com/user/emsmexico" target="_blank">
										            <i class="fa fa-youtube"></i>
										        </a>
										    </li>
										    <li>
										        <a href="http://www.pinterest.com/emsmexico/productos-ems-mexico-en-acci%C3%B3n/" target="_blank">
										            <i class="fa fa-pinterest"></i>
										        </a>
										    </li>
										    <li>
										        <a href="https://foursquare.com/ems_mexico" target="_blank">
										            <i class="fa fa-foursquare"></i>
										        </a>
										    </li>
										</ul>
									</div><!-- social -->
								</div><!-- /.navbar-collapse -->
							</div><!-- visible-xs -->
						</div><!-- /.container-fluid -->
					</nav>
				</div>
			</div>
		</div><!-- container -->
	</div><!-- navigation all in xs-->



	<div class="bottom hidden-xs">
		<div class="container">
			<div class="row">
				<div class="contact pull-left">
					<a href="callto:83705006">
						<i class="fa fa-phone"></i>
						<p>01 81 8340.3850</p>
					</a>
					<a href="mailto:Ventas@EMSMex.com">
						<i class="fa fa-envelope"></i>
						<p>Ventas@EMSMex.com</p>
					</a>
				</div>
                <?php

                $f['method'] = 'get';
                $f = array('method' => 'get', 'class' => 'navbar-form navbar-right', 'role' => 'search');

                echo form_open('buscar', $f);
                ?>
				<!--<form class="navbar-form navbar-right" role="search">-->
					<div class="form-group">
						<input name="q" type="text" class="form-control" placeholder="Buscar ...">
					</div>
					<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
				<!--</form>-->
                <?php
                echo form_close();
                ?>
			</div>
		</div>
	</div><!-- bottom hidden-xs -->
</header>


