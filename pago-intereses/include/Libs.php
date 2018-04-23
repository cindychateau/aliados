<?php
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

class Libs extends Common {

	function getProgress() {
		$json = array();

		$json['panel'] = '<div class="panel panel-default">
						  <div class="panel-body orders">
							<div class="scroller" data-height="650px" data-always-visible="1" data-rail-visible="1">
							<ul class="list-unstyled">';


		$sql = "SELECT *
				FROM PRESTAMOS_SOLICITADOS
				WHERE PS_VIGENTE = 1
				ORDER BY PS_PAGO_INICIAL ASC";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			if($consulta->rowCount() > 0 ){
				foreach ($result as $row) {
					$sql_pagos = "SELECT * FROM PAGOS_PRESTAMOS
								  WHERE PS_ID = ?
								  AND DATE(PP_FECHA) <= CURDATE() 
								  ORDER BY PP_FECHA DESC";

					$values_pagos = array($row['PS_ID']);	
					
					$consulta_pagos = $this->_conexion->prepare($sql_pagos);
					try {
						$consulta_pagos->execute($values_pagos);
						$row_pagos = $consulta_pagos->fetch(PDO::FETCH_ASSOC);

						if($consulta_pagos->rowCount() == 0 ) {
							$row_pagos['PP_FECHA'] = "-";
							$row_pagos['PP_NUM_PAGO'] = 0;
						}

						$acreditante = $row['PS_ACREDITANTE'];
						$cantidad = number_format($row['PS_MONTO_TOTAL']);
						$fecha = $row_pagos['PP_FECHA'];
						if($fecha != "-") {
							$dia = date('d', strtotime($fecha));
							$mes = date('m', strtotime($fecha));
							$ano = date('Y', strtotime($fecha));
							$mes_palabras = $this->getMonthWord($mes);
							$ultima_fecha =  $mes_palabras." ".$dia.", ".$ano;
						} else {
							$ultima_fecha =  "-";
						}
						
						$avance = ($row_pagos['PP_NUM_PAGO'] / $row['PS_PLAZO'])*100;
						$avance = number_format($avance);

						$avance_ = $row_pagos['PP_NUM_PAGO'] ."/". $row['PS_PLAZO'];

						$icon = "fa-star";
						$color = "info";
						if($avance > 33.33) {
							$icon = "fa-cog";
							$color = "warning";
						} elseif($avance > 66.66) {
							$icon = "fa-check";
							$color = "danger";
						}

						$json['panel'].='<li class="clearfix">
											<div class="pull-left">
												<p>
													<h5><strong>'.$acreditante.'</strong></h5>
												</p>
												<p><i class="fa fa-clock-o"></i> <abbr class="timeago" title="'.$ultima_fecha.'" >'.$ultima_fecha.'</abbr></p>
												
											</div>
											<div class="text-right pull-right">
												<h4 class="cost">$'.$cantidad.'</h4>
												<p>
													<span class="label label-'.$color.' arrow-in-right"><i class="fa '.$icon.'"></i> '.$avance_.'</span>
												</p>
											</div>
											<div class="clearfix"></div>
											<div class="progress progress-sm">
											  <div class="progress-bar progress-bar-'.$color.'" role="progressbar" aria-valuenow="'.$avance.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$avance.'%;">
												<span class="sr-only">'.$avance.'% Completado</span>
											  </div>
											</div>
										</li>';


					} catch (PDOException $e) {
					  	die($e->getMessage());
					}	  
				}
			} else {
				$json['panel'].='<li class="clearfix">
									<div class="pull-left">
										<p>
											<h5><strong>No se encontraron Préstamos Vigentes</strong></h5>
										</p>
									</div>
								</li>';
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}				

		$json['panel'].='</ul>
						</div>
					  </div>
					</div>';							

		echo json_encode($json);
	}

	function getMonthWord($month) {
		$mes = "Ene";
		switch ($month) {
			case 1:
				$mes = "Ene";
				break;
			case 2:
				$mes = "Feb";
				break;
			case 3:
				$mes = "Mar";
				break;
			case 4:
				$mes = "Abr";
				break;
			case 5:
				$mes = "May";
				break;
			case 6:
				$mes = "Jun";
				break;
			case 7:
				$mes = "Jul";
				break;
			case 8:
				$mes = "Ago";
				break;
			case 9:
				$mes = "Sep";
				break;
			case 10:
				$mes = "Oct";
				break;
			case 11:
				$mes = "Nov";
				break;
			case 12:
				$mes = "Dic";
				break;											
		}

		return $mes;
	}

	function getPayments() {
		$json = array();
		$json['calendar'] = '<table id="table-pagos" class="dataTable table table-striped"> 
								<thead>
									<tr>
										<th align="center">ACRED.</th>';
		/*BG: MESES*/
		$fecha = date("Y-01-01");
		$total = array();

		for ($i=0; $i < 12; $i++) { 
			$mes = date('m', strtotime($fecha));
			$mes_palabras = $this->getMonthWord($mes);
			$json['calendar'].='<th align="center">'.strtoupper($mes_palabras).'</th>';
			$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
			$fecha = date ('Y-m-d',$fecha);
			$total[$i] = 0;
		}
									

		$json['calendar'].='		<tr>
								</thead>';	
		/*END: MESES*/
		
		/*BG: BODY*/
		$json['calendar'].='	<tbody>';

		$sql = "SELECT PS_ACREDITANTE 
				FROM PRESTAMOS_SOLICITADOS
			    WHERE PS_VIGENTE = 1
			    GROUP BY PS_ACREDITANTE";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			if($consulta->rowCount() > 0 ) {
				foreach ($result as $row) {
					$sql_acr = "SELECT PS_ID 
								FROM PRESTAMOS_SOLICITADOS
								WHERE PS_ACREDITANTE = ?
								AND PS_VIGENTE = 1";
					$values_acr = array($row['PS_ACREDITANTE']);	
					$consulta_acr = $this->_conexion->prepare($sql_acr);
					try {
						$consulta_acr->execute($values_acr);
						$result_acr = $consulta_acr->fetchAll(PDO::FETCH_ASSOC);
						$title = $row['PS_ACREDITANTE'];

						$json['calendar'] .= '<tr>
												<td align="center">'.$title.'</td>';

						$fecha = date("Y-01-01");

						for ($i=0; $i < 12; $i++) { 
							$total_mes = 0;
							$hover = "";
							foreach ($result_acr as $row_acr) {
								$sql_pagos = "SELECT * FROM
											  PAGOS_PRESTAMOS
											  WHERE PS_ID = ?
											  AND MONTH(PP_FECHA) = MONTH(?)
											  AND YEAR(PP_FECHA) = YEAR(?)";
								$values_pagos = array($row_acr['PS_ID'],
													  $fecha,
													  $fecha);	
								$consulta_pagos = $this->_conexion->prepare($sql_pagos);
								try {
									$consulta_pagos->execute($values_pagos);
									$row_pagos = $consulta_pagos->fetch(PDO::FETCH_ASSOC);
									if($consulta_pagos->rowCount() > 0 ) {
										$total_mes += $row_pagos['PP_MONTO'];
										$fecha_pago = date("d/m/Y",strtotime($row_pagos["PP_FECHA"]));
										$hover.= $fecha_pago." - $".number_format($row_pagos['PP_MONTO'], 2)."<br>";
									}
								} catch (PDOException $e) {
								  	die($e->getMessage());
								}		  
							}
							$total[$i] += $total_mes;

							$json['calendar'].='<td align="center"><span class="pop-hover" data-title="'.$title.'" data-content="'.$hover.'">$'.number_format($total_mes, 2).'</span></td>';

							$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
							$fecha = date ('Y-m-d',$fecha);
						}

						$json['calendar'] .= '</tr>';

					} catch (PDOException $e) {
					  	die($e->getMessage());
					}		
				}
			}
		} catch (PDOException $e) {
		  	die($e->getMessage());
		}

		$json['calendar'].='	</tbody>';
		/*END: BODY*/

		/*BG: FOOTER*/
		$json['calendar'].='	<tfoot>
									<tr>
										<th rowspan="1" colspan="1">-</th>';

		for ($i=0; $i < 12 ; $i++) { 
			$json['calendar'].='			<th rowspan="1" colspan="1">$'.number_format($total[$i], 2).'</th>';
		}															

		$json['calendar'].='		</tr>
								</tfoot>';							
		$json['calendar'].='</table>';
		/*END: FOOTER*/

		echo json_encode($json);	
	}
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){	
		case "getProgress":
			$libs->getProgress();
			break;	
		case "getPayments":
			$libs->getPayments();
			break;	
	}
}

?>