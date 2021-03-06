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

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * 
	 * Select con Meses
	 */
	function getMonth() {
		$json = array();
		$json['select'] = '<select id="mes" name="mes" class="form-control">
							<option value="0">Seleccione el Mes</option>';

		$fecha = "2015-10-15";
		$fecha_actual = date("Y-m-d");
		do {
			$mes = date('m', strtotime($fecha));
			$ano = date('Y', strtotime($fecha));

			$mes_palabras = $this->getMonthWord($mes);

			$json['select'].= '<option value="'.$mes.'-'.$ano.'">'.$mes_palabras.' - '.$ano.'</option>';

			$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
			$fecha = date ('Y-m-d',$fecha);


		} while (date('Y-m', strtotime($fecha)) <= date('Y-m', strtotime($fecha_actual)));			


		$json['select'].= '</select>';	
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

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * 
	 * Select con getWeek
	 */
	function getWeek() {
		$json = array();
		$json['select'] = '<select id="semana" name="semana" class="form-control" disabled>
								<option value="0">Cargando...</option>
							</select>';
		if(isset($_POST['mes']) && $_POST['mes'] != 0) {
			$json['select'] = '<select id="semana" name="semana" class="form-control">
									<option value="0">Seleccione una Semana</option>';

			//Si es el primer mes, que la fecha inicial sea el 15 de Oct
			if($_POST['mes'] == '10-2015') {
				$fecha = "15-10-2015";
			} else {
				$fecha = "01-".$_POST['mes'];
			}

			$fecha_inicial = $fecha;

			$mes = date('m', strtotime($fecha));

			do {
				$lunes = date("Y-m-d", $this->last_monday($fecha));
				$lunes_str = strtotime($lunes);
				$domingo_str = strtotime('next sunday', $lunes_str);
				$domingo = date("Y-m-d", $domingo_str);
				$fecha = $domingo;

				$fecha_referencia = date("Y-m-d", strtotime('+1 day', $domingo_str));

				$lunes_bonito = date("d/m/Y", $lunes_str);
				$domingo_bonito = date("d/m/Y", $domingo_str);

				$json['select'] .= '<option value="'.$lunes.'.'.$domingo.'">'.$lunes_bonito.' - '.$domingo_bonito.'</option>';

			} while (date('m', strtotime($fecha_referencia)) == date('m', strtotime($fecha_inicial)));

			
			$json['select']	.= '</select>';				
		}

		echo json_encode($json);
	}

	function last_monday($date) {
		if (!is_numeric($date))
		    $date = strtotime($date);
		if (date('w', $date) == 1)
		    return $date;
		elseif (date('w', $date) == 0 || date('w', $date) == 7)
		    return strtotime(
		        'next monday',
		         $date
		    );
		else
		    return strtotime(
		        'last monday',
		         $date
		    );
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-19
	 * 
	 * 
	 * Select con getWeek
	 */
	function getFlujo() {
		$json = array();
		$json['tabla'] = "";
		$debug = true;

		$total_prestamos = 0;
		$total_pagos = 0;
		$total_comisiones_ap = 0;
		$total_comisiones_ap_pro = 0;
		$total_aportaciones = 0;
		$total_gastos = 0;
		$saldo_acumulado = 0;
		$total_fondeo = 0;
		$total_intereses = 0;
		$comisiones_ap = array();
		$fechas_coms = array();

		$expansiones = 0;

		$fechas = explode(".", $_POST['semana']);

		/*BG: SALDO ACUMULADO*/
		if($fechas[0] != '2015-10-12') {
			$saldo_acumulado = $this->getAcumulado($fechas[0]);
			$json['tabla'].= "<tr><td colspan='3'></td></tr>
						  <tr style='background: #DDD'>
							<td></td>
							<td align='center'>
								<strong>SALDO ACUMULADO</strong>
							</td>
							<td align='center'><strong>$".number_format($saldo_acumulado, 2)."</strong></td>
						 </tr>";
		}
		/*END: SALDO ACUMULADO*/
		
		/*BG: PRÉSTAMOS*/
		$sql_prestamos = "SELECT GRU_ID,
								 GRU_FECHA,
								 GRU_MONTO_TOTAL,
								 GRU_MONTO_TOTAL_ENTREGAR,
								 GRU_COMISION_P,
								 SIU_ID
						  FROM GRUPOS
						  WHERE GRU_FECHA >= ? 
						  AND GRU_FECHA <= ?";
		$values_prestamos = array($fechas[0],
								  $fechas[1]);	
		$consulta_prestamos = $this->_conexion->prepare($sql_prestamos);
		try {
			$consulta_prestamos->execute($values_prestamos);
			$result = $consulta_prestamos->fetchAll(PDO::FETCH_ASSOC);
			if ($consulta_prestamos->rowCount() > 0) {
				/*$json['tabla'].= "<tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>PRÉSTAMOS OTORGADOS</strong></td>
								 </tr>";*/
				$prestamos = "";
				$expansiones++;
				foreach ($result as $row) {
					$prestamos.= "<tr class='zone-".$expansiones."' style='display:none;'>
										<td align='center'>".date("d/m/Y",strtotime($row["GRU_FECHA"]))."</td>
										<td align='center'>Préstamo de Grupo ".$row['GRU_ID']."</td>
										<td align='center'>$".number_format($row['GRU_MONTO_TOTAL'], 2)."</td>
									 </tr>";
					$total_prestamos += $row['GRU_MONTO_TOTAL'];				 

					$comision_grupo = $row['GRU_MONTO_TOTAL'] * $row['GRU_COMISION_P'];
					$comisiones_ap[$row['GRU_ID']] = number_format($comision_grupo, 2);
					$fechas_coms[$row['GRU_ID']] = date("d/m/Y",strtotime($row["GRU_FECHA"]));						   
					$total_comisiones_ap += $comision_grupo;
					
				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE PRÉSTAMOS OTORGADOS</strong>
									</td>
									<td align='center'><strong>$".number_format($total_prestamos, 2)."</strong></td>
								 </tr>".$prestamos;


			}

		/*END: PRÉSTAMOS*/

		/*BG: COMISIONES DE APERTURA*/

			$coms = true;
			$comisiones_apertura = "";
			$expansiones++;
			foreach ($comisiones_ap as $grupo => $cantidad) {
				if($cantidad > 0) {
					/*if($coms) {
						$json['tabla'].= "<tr><td colspan='3'></td></tr>
										 <tr>
											<td colspan='3' align='center' style='background: #CCC'><strong>COMISIONES DE APERTURA</strong></td>
										 </tr>";
						$coms = false;				 
					}*/

					$comisiones_apertura.= "<tr class='zone-".$expansiones."' style='display:none;'>
												<td align='center'>".$fechas_coms[$grupo]."</td>
												<td align='center'>Comisión de Apertura de Grupo ".$grupo."</td>
												<td align='center'>$".$cantidad."</td>
											 </tr>";
				}
			}

			if ($total_comisiones_ap > 0) {
				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE COMISIONES DE APERTURA</strong>
									</td>
									<td align='center'><strong>$".number_format($total_comisiones_ap, 2)."</strong></td>
								 </tr>".$comisiones_apertura;
			}

		/*END: COMISIONES DE APERTURA*/	


		/*BG: COMISIONES DE APERTURA A PROMOTOR*/
			
			//Se otorgan una semana después de que el grupo se dió de alta
			$fecha_actual = strtotime($fechas[0]);
			$fecha_str = strtotime('-1 day', $fecha_actual);
			$fecha_atras = date("Y-m-d", $fecha_str);
			$f_str = strtotime($fecha_atras);
			$fecha_inicio = date("Y-m-d", strtotime('last monday', $f_str));

			$sql_promotor = "SELECT GRU_ID,
									 GRU_FECHA,
									 GRU_MONTO_TOTAL,
									 GRU_COMISION_P,
									 GRUPOS.SIU_ID,
									 SIU_NOMBRE
							  FROM GRUPOS
							  JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
							  WHERE GRU_FECHA >= ? 
							  AND GRU_FECHA <= ?
							  ORDER BY GRUPOS.SIU_ID";

			$values_promotor = array($fecha_inicio,
									 $fecha_atras);	

			$consulta_promotor = $this->_conexion->prepare($sql_promotor);	
			
			try {
				$consulta_promotor->execute($values_promotor);
				$result_promotor = $consulta_promotor->fetchAll(PDO::FETCH_ASSOC);

				if ($consulta_promotor->rowCount() > 0) {
					$expansiones++;
					/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
											 <tr>
												<td colspan='3' align='center' style='background: #CCC'><strong>COMISIONES DE APERTURA A PROMOTORES</strong></td>
											 </tr>";*/
				}

				$comisiones_promotoras = "";
				foreach ($result_promotor as $row_promotor) {
					$comision_grupo = $row_promotor['GRU_MONTO_TOTAL'] * $row_promotor['GRU_COMISION_P'];
					if($row_promotor["GRU_FECHA"] > '2016-02-15') {
						$cantidad = 500.00;
					} else {
						$cantidad = number_format(($comision_grupo * .3), 2);
					}

					$comisiones_promotoras.= "<tr class='zone-".$expansiones."' style='display:none;'>
												<td align='center'>".date("d/m/Y",strtotime($row_promotor["GRU_FECHA"]))."</td>
												<td align='center'>Comisión de Apertura para ".$row_promotor['SIU_NOMBRE']." de Grupo ".$row_promotor['GRU_ID']."</td>
												<td align='center'>$".$cantidad."</td>
											 </tr>";

					$total_comisiones_ap_pro += $cantidad;

				}

				if ($total_comisiones_ap_pro > 0) {
					$json['tabla'].= "<tr><td colspan='3'></td></tr>
									<tr style='background: #E7E7E7'>
										<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
										<td align='center'>
											<strong>TOTAL DE COMISIONES DE APERTURA A PROMOTORES</strong>
										</td>
										<td align='center'><strong>$".number_format($total_comisiones_ap_pro, 2)."</strong></td>
									 </tr>".$comisiones_promotoras;
				}

			} catch (PDOException $e) {
				$json["error"] = true;
				die($e->getMessage());
				//$json["msg"] = isset($debug)?"--SQL: ".$sql_promotor.(isset($values_promotor)?"\n--Values: ".print_r($values_promotor):""):"";
			}

		/*END: COMISIONES DE APERTURA A PROMOTOR*/


		/*BG: COMISIONES SEMANAL A PROMOTOR*/

		/*END: COMISIONES SEMANAL A PROMOTOR*/


		/*BG: PAGOS SEMANALES*/
		$sql_pagos = "SELECT * 
							 FROM TABLA_PAGOS
							 WHERE TP_FECHA >= ? 
						  	 AND TP_FECHA <= ?
						  	 ORDER BY TP_FECHA ASC";

		$values_pagos = array($fechas[0],
							  $fechas[1]);

		$consulta_pagos = $this->_conexion->prepare($sql_pagos);

		try {
			$consulta_pagos->execute($values_pagos);
			$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta_pagos->rowCount() > 0) {
				/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
								  <tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>PAGOS SEMANALES</strong></td>
								 </tr>";*/
				$expansiones++;	
				$pagos = "";			 

				foreach ($result_pagos as $row_pagos) {
					$pagos.= "<tr class='zone-".$expansiones."' style='display:none;'>
								<td align='center'>".date("d/m/Y",strtotime($row_pagos["TP_FECHA"]))."</td>
								<td align='center'>Pago de Grupo ".$row_pagos['GRU_ID']."</td>
								<td align='center'>$".number_format($row_pagos['TP_MONTO'], 2)."</td>
							 </tr>";

					$total_pagos += $row_pagos['TP_MONTO'];				 

				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE PAGOS SEMANALES</strong>
									</td>
									<td align='center'><strong>$".number_format($total_pagos, 2)."</strong></td>
								 </tr>".$pagos;

			}

		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_pagos.(isset($values_pagos)?"\n--Values: ".print_r($values_pagos):""):"";
		}
		/*END: PAGOS SEMANALES*/

		/*BG: SALDO APORTACIONES*/
		$sql_aportaciones = "SELECT * 
							 FROM APORTACIONES
							 WHERE AP_FECHA >= ? 
						  	 AND AP_FECHA <= ?";

		$values_aportaciones = array($fechas[0],
								  	 $fechas[1]);

		$consulta_aportaciones = $this->_conexion->prepare($sql_aportaciones);

		try {
			$consulta_aportaciones->execute($values_aportaciones);
			$result_aportaciones = $consulta_aportaciones->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta_aportaciones->rowCount() > 0) {
				/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
								  <tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>APORTACIONES</strong></td>
								 </tr>";*/
				$expansiones++;
				$aportaciones = "";				 

				foreach ($result_aportaciones as $row_aportaciones) {
					$aportaciones.= "<tr class='zone-".$expansiones."' style='display:none;'>
										<td align='center'>".date("d/m/Y",strtotime($row_aportaciones["AP_FECHA"]))."</td>
										<td align='center'>".$row_aportaciones['AP_CONCEPTO']."</td>
										<td align='center'>$".number_format($row_aportaciones['AP_MONTO'], 2)."</td>
									 </tr>";

					$total_aportaciones += $row_aportaciones['AP_MONTO'];			 

				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE APORTACIONES</strong>
									</td>
									<td align='center'><strong>$".number_format($total_aportaciones, 2)."</strong></td>
								 </tr>".$aportaciones;

			}

		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_aportaciones.(isset($values_aportaciones)?"\n--Values: ".print_r($values_aportaciones):""):"";
		}
								  	 		  	 
		/*END: SALDO APORTACIONES*/

		/*BG: FONDEO*/
		$sql_fondeo = "SELECT * 
					   FROM PRESTAMOS_SOLICITADOS
					   WHERE PS_FECHA >= ? 
				  	   AND PS_FECHA <= ?";

		$values_fondeo = array($fechas[0],
							   $fechas[1]);

		$consulta_fondeo = $this->_conexion->prepare($sql_fondeo);

		try {
			$consulta_fondeo->execute($values_fondeo);
			$result_fondeo = $consulta_fondeo->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta_fondeo->rowCount() > 0) {
				/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
								  <tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>APORTACIONES</strong></td>
								 </tr>";*/
				$expansiones++;
				$fondeo = "";				 

				foreach ($result_fondeo as $row_fondeo) {
					$fondeo.= "<tr class='zone-".$expansiones."' style='display:none;'>
										<td align='center'>".date("d/m/Y",strtotime($row_fondeo["PS_FECHA"]))."</td>
										<td align='center'>Fondeo de Acreditante:  ".$row_fondeo['PS_ACREDITANTE']."</td>
										<td align='center'>$".number_format($row_fondeo['PS_MONTO_TOTAL'], 2)."</td>
									 </tr>";

					$total_fondeo += $row_fondeo['PS_MONTO_TOTAL'];			 

				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE FONDEO</strong>
									</td>
									<td align='center'><strong>$".number_format($total_fondeo, 2)."</strong></td>
								 </tr>".$fondeo;

			}

		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_fondeo.(isset($values_fondeo)?"\n--Values: ".print_r($values_fondeo):""):"";
		}
								  	 		  	 
		/*END: FONDEO*/

		/*BG: PAGO DE INTERESES FONDEO*/
		$sql_intereses = "SELECT PP_FECHA,
								 PP_MONTO,
								 PP_NUM_PAGO,
								 PS_ACREDITANTE,
								 PS_PLAZO
					      FROM PAGOS_PRESTAMOS
					      JOIN PRESTAMOS_SOLICITADOS ON PRESTAMOS_SOLICITADOS.PS_ID = PAGOS_PRESTAMOS.PS_ID
					      WHERE PP_FECHA >= ? 
				  	      AND PP_FECHA <= ?";

		$values_intereses = array($fechas[0],
							      $fechas[1]);

		$consulta_intereses = $this->_conexion->prepare($sql_intereses);

		try {
			$consulta_intereses->execute($values_intereses);
			$result_intereses = $consulta_intereses->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta_intereses->rowCount() > 0) {
				/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
								  <tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>APORTACIONES</strong></td>
								 </tr>";*/
				$expansiones++;
				$intereses = "";				 

				foreach ($result_intereses as $row_intereses) {
					$intereses.= "<tr class='zone-".$expansiones."' style='display:none;'>
										<td align='center'>".date("d/m/Y",strtotime($row_intereses["PP_FECHA"]))."</td>
										<td align='center'>Pago de Intereses de Acreditante:  ".$row_intereses['PS_ACREDITANTE']." (".$row_intereses['PP_NUM_PAGO']."/".$row_intereses['PS_PLAZO'].")</td>
										<td align='center'>$".number_format($row_intereses['PP_MONTO'], 2)."</td>
									 </tr>";

					$total_intereses += $row_intereses['PP_MONTO'];			 

				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE PAGO DE INTERESES</strong>
									</td>
									<td align='center'><strong>$".number_format($total_intereses, 2)."</strong></td>
								 </tr>".$intereses;

			}

		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_intereses.(isset($values_intereses)?"\n--Values: ".print_r($values_intereses):""):"";
		}
								  	 		  	 
		/*END: PAGO DE INTERESES FONDEO*/

		/*BG: GASTOS*/
		$sql_gastos = "SELECT * 
					   FROM GASTOS
					   WHERE GAS_FECHA >= ? 
				  	   AND GAS_FECHA <= ?";

		$values_gastos = array($fechas[0],
							   $fechas[1]);

		$consulta_gastos = $this->_conexion->prepare($sql_gastos);

		try {
			$consulta_gastos->execute($values_gastos);
			$result_gastos = $consulta_gastos->fetchAll(PDO::FETCH_ASSOC);

			if ($consulta_gastos->rowCount() > 0) {
				/*$json['tabla'].= "<tr><td colspan='3'></td></tr>
								  <tr>
									<td colspan='3' align='center' style='background: #CCC'><strong>GASTOS</strong></td>
								 </tr>";*/
				$expansiones++;
				$gastos = "";
				foreach ($result_gastos as $row_gastos) {
					$gastos.= "<tr class='zone-".$expansiones."' style='display:none;'>
									<td align='center'>".date("d/m/Y",strtotime($row_gastos["GAS_FECHA"]))."</td>
									<td align='center'>".$row_gastos['GAS_CONCEPTO']."</td>
									<td align='center'>$".number_format($row_gastos['GAS_MONTO'], 2)."</td>
								 </tr>";

					$total_gastos += $row_gastos['GAS_MONTO'];					 

				}

				$json['tabla'].= "<tr><td colspan='3'></td></tr>
								<tr style='background: #E7E7E7'>
									<td  align='center' class='expandir tck' data-id='".$expansiones."'><i class='fa fa-chevron-down'></i></td>
									<td align='center'>
										<strong>TOTAL DE GASTOS</strong>
									</td>
									<td align='center'><strong>$".number_format($total_gastos, 2)."</strong></td>
								 </tr>".$gastos;

			}

		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_aportaciones.(isset($values_gastos)?"\n--Values: ".print_r($values_gastos):""):"";
		}
		/*END: GASTOS*/

		/*BG: TOTAL DE TOTALES*/
		$total_completo = $total_pagos - $total_prestamos + $total_comisiones_ap - $total_comisiones_ap_pro - $total_gastos + $total_aportaciones + $saldo_acumulado + $total_fondeo - $total_intereses;
		if($total_completo < 0 ) {
			$json['tabla'].= "<tr><td colspan='3'></td></tr>
							  <tr style='background: #DDD'>
								<td></td>
								<td align='center'>
									<strong>APORTACIÓN FALTANTE</strong>
								</td>
								<td align='center'><strong>$".number_format($total_completo*-1, 2)."</strong></td>
							 </tr>";
		} else {
			$json['tabla'].= "<tr><td colspan='3'></td></tr>
							  <tr style='background: #DDD'>
								<td></td>
								<td align='center'>
									<strong>SALDO ACTUAL</strong>
								</td>
								<td align='center'><strong>$".number_format($total_completo, 2)."</strong></td>
							 </tr>";
		} 
		
		/*END: TOTAL DE TOTALES*/


		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = isset($debug)?"--SQL: ".$sql_prestamos.(isset($values_prestamos)?"\n--Values: ".print_r($values_prestamos):""):"";
		}					  			  

		echo json_encode($json);				  
	}

	function getAcumulado($fecha_actual) {
		if($fecha_actual != '2015-10-12') {
			$fecha_actual = strtotime($fecha_actual);
			$fecha_str = strtotime('-1 day', $fecha_actual);
			$fecha_atras = date("Y-m-d", $fecha_str);
			$sql_acumulado = "SELECT * FROM SALDO_ANTERIOR WHERE SAL_FECHA = ?";
			$values_acumulado = array($fecha_atras);
			$consulta_acumulado = $this->_conexion->prepare($sql_acumulado);

			try {
				$consulta_acumulado->execute($values_acumulado);
				$row_acumulado = $consulta_acumulado->fetch(PDO::FETCH_ASSOC);
				if ($consulta_acumulado->rowCount() > 0) {
					return $row_acumulado['SAL_MONTO'];
				} else {
					//Tiene que calcular el saldo anterior//
					$f_str = strtotime($fecha_atras);
					$fecha_inicio = date("Y-m-d", strtotime('last monday', $f_str));

					$total_prestamos = 0;
					$total_pagos = 0;
					$total_comisiones_ap = 0;
					$total_comisiones_ap_pro = 0;
					$total_aportaciones = 0;
					$total_gastos = 0;
					$total_fondeo = 0;
					$total_intereses = 0;
					$saldo_acumulado = 0;
					$total_completo = 0;

					/*BG: PRÉSTAMOS*/
					$sql_prestamos = "SELECT GRU_ID,
											 GRU_FECHA,
											 GRU_MONTO_TOTAL,
											 GRU_MONTO_TOTAL_ENTREGAR,
											 GRU_COMISION_P,
											 SIU_ID
									  FROM GRUPOS
									  WHERE GRU_FECHA >= ? 
									  AND GRU_FECHA <= ?";
					$values_prestamos = array($fecha_inicio,
											  $fecha_atras);	
					$consulta_prestamos = $this->_conexion->prepare($sql_prestamos);
					try {
						$consulta_prestamos->execute($values_prestamos);
						$result = $consulta_prestamos->fetchAll(PDO::FETCH_ASSOC);
						if ($consulta_prestamos->rowCount() > 0) {
							foreach ($result as $row) {
								$total_prestamos += $row['GRU_MONTO_TOTAL'];				 

								$comision_grupo = $row['GRU_MONTO_TOTAL'] * $row['GRU_COMISION_P'];
								//$comisiones_ap[$row['GRU_ID']] = number_format($comision_grupo, 2);					   
								$total_comisiones_ap += $comision_grupo;
								//$total_comisiones_ap_pro += $comision_grupo * .3;

							}

						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}	
					/*END: PRÉSTAMOS*/

					/*BG: COMISIONES DE APERTURA DE PROMOTORES*/
					$fecha1_str = strtotime($fecha_inicio);
					$fecha1_str = strtotime('-1 day', $fecha1_str);
					$fecha1 = date("Y-m-d", $fecha1_str);
					$fecha2_str = strtotime($fecha1);
					$fecha2 = date("Y-m-d", strtotime('last monday', $fecha2_str));

					$sql_promotor = "SELECT GRU_ID,
											 GRU_FECHA,
											 GRU_MONTO_TOTAL,
											 GRU_COMISION_P,
											 GRUPOS.SIU_ID,
											 SIU_NOMBRE
									  FROM GRUPOS
									  JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
									  WHERE GRU_FECHA >= ? 
									  AND GRU_FECHA <= ?
									  ORDER BY GRUPOS.SIU_ID";

					$values_promotor = array($fecha2,
											 $fecha1);	

					$consulta_promotor = $this->_conexion->prepare($sql_promotor);	
					
					try {
						$consulta_promotor->execute($values_promotor);
						$result_promotor = $consulta_promotor->fetchAll(PDO::FETCH_ASSOC);

						foreach ($result_promotor as $row_promotor) {
							$comision_grupo = $row_promotor['GRU_MONTO_TOTAL'] * $row_promotor['GRU_COMISION_P'];
							if($row_promotor["GRU_FECHA"] > '2016-02-15') {
								$cantidad = 500.00;
							} else {
								$cantidad = number_format(($comision_grupo * .3), 2);
							}

							$total_comisiones_ap_pro += $cantidad;

						}

					} catch (PDOException $e) {
						$json["error"] = true;
						die($e->getMessage());
						//$json["msg"] = isset($debug)?"--SQL: ".$sql_promotor.(isset($values_promotor)?"\n--Values: ".print_r($values_promotor):""):"";
					}

					/*END: COMISIONES DE APERTURA DE PROMOTORES*/
					
					/*BG: PAGOS SEMANALES*/
					$sql_pagos = "SELECT * 
								 FROM TABLA_PAGOS
								 WHERE TP_FECHA >= ? 
							  	 AND TP_FECHA <= ?
							  	 ORDER BY TP_FECHA ASC";

					$values_pagos = array($fecha_inicio,
										  $fecha_atras);

					$consulta_pagos = $this->_conexion->prepare($sql_pagos);

					try {
						$consulta_pagos->execute($values_pagos);
						$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);

						if ($consulta_pagos->rowCount() > 0) {
							foreach ($result_pagos as $row_pagos) {
								$total_pagos += $row_pagos['TP_MONTO'];				 
							}

						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}
					/*END: PAGOS SEMANALES*/

					/*BG: SALDO APORTACIONES*/
					$sql_aportaciones = "SELECT * 
										 FROM APORTACIONES
										 WHERE AP_FECHA >= ? 
									  	 AND AP_FECHA <= ?";

					$values_aportaciones = array($fecha_inicio,
											  	 $fecha_atras);

					$consulta_aportaciones = $this->_conexion->prepare($sql_aportaciones);

					try {
						$consulta_aportaciones->execute($values_aportaciones);
						$result_aportaciones = $consulta_aportaciones->fetchAll(PDO::FETCH_ASSOC);

						if ($consulta_aportaciones->rowCount() > 0) {
							
							foreach ($result_aportaciones as $row_aportaciones) {
								$total_aportaciones += $row_aportaciones['AP_MONTO'];			 
							}
						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}
											  	 		  	 
					/*END: SALDO APORTACIONES*/

					/*BG: FONDEO*/
					$sql_fondeo = "SELECT * 
								   FROM PRESTAMOS_SOLICITADOS
								   WHERE PS_FECHA >= ? 
							  	   AND PS_FECHA <= ?";

					$values_fondeo = array($fecha_inicio,
										   $fecha_atras);

					$consulta_fondeo = $this->_conexion->prepare($sql_fondeo);

					try {
						$consulta_fondeo->execute($values_fondeo);
						$result_fondeo = $consulta_fondeo->fetchAll(PDO::FETCH_ASSOC);

						if ($consulta_fondeo->rowCount() > 0) {
							
							foreach ($result_fondeo as $row_fondeo) {
								$total_fondeo += $row_fondeo['PS_MONTO_TOTAL'];			 
							}
						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}
											  	 		  	 
					/*END: FONDEO*/

					/*BG: FONDEO*/
					$sql_intereses = "SELECT * 
								      FROM PAGOS_PRESTAMOS
								      WHERE PP_FECHA >= ? 
							  	      AND PP_FECHA <= ?";

					$values_intereses = array($fecha_inicio,
										  	  $fecha_atras);

					$consulta_intereses = $this->_conexion->prepare($sql_intereses);

					try {
						$consulta_intereses->execute($values_intereses);
						$result_intereses = $consulta_intereses->fetchAll(PDO::FETCH_ASSOC);

						if ($consulta_intereses->rowCount() > 0) {
							
							foreach ($result_intereses as $row_intereses) {
								$total_intereses += $row_intereses['PP_MONTO'];			 
							}
						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}
											  	 		  	 
					/*END: FONDEO*/

					/*BG: GASTOS*/
					$sql_gastos = "SELECT * 
								   FROM GASTOS
								   WHERE GAS_FECHA >= ? 
							  	   AND GAS_FECHA <= ?";

					$values_gastos = array($fecha_inicio,
										   $fecha_atras);

					$consulta_gastos = $this->_conexion->prepare($sql_gastos);

					try {
						$consulta_gastos->execute($values_gastos);
						$result_gastos = $consulta_gastos->fetchAll(PDO::FETCH_ASSOC);

						if ($consulta_gastos->rowCount() > 0) {
							foreach ($result_gastos as $row_gastos) {
								$total_gastos += $row_gastos['GAS_MONTO'];					 
							}
						}

					} catch (PDOException $e) {
						die($e->getMessage());
					}
					/*END: GASTOS*/

					$saldo_acumulado = $this->getAcumulado($fecha_inicio);

					$total_completo = $total_pagos - $total_prestamos + $total_comisiones_ap - $total_comisiones_ap_pro - $total_gastos + $total_aportaciones + $saldo_acumulado + $total_fondeo - $total_intereses;

					$sql = "INSERT INTO SALDO_ANTERIOR (SAL_FECHA,
														SAL_MONTO) 
							 VALUES( ?, ? )";

					$values = array($fecha_atras,
									$total_completo);		 


					$consulta = $this->_conexion->prepare($sql);

					try {
						$consulta->execute($values);
						return $total_completo;
					} catch(PDOException $e) {
						die($e->getMessage());
					}

				}

			}  catch (PDOException $e) {
				die($e->getMessage());
			}
		} else {
			return 0;
		}
	}

	function getIs() {
		$json = array();
		$json['tabla'] = "";

		$json['tabla'] = '<table id="table-is" class="dataTable table table-striped"> 
								<thead>';
		/*BG: MESES*/


		$json['tabla'].= '<tr><th align="center"></th>';
		$fecha_hoy = date("Y-m-d");
		$ano = date('Y', strtotime($fecha_hoy));
		$fecha_inicial = $ano."-01-01";
		$fecha = date('Y-m-d', strtotime($fecha_inicial));
		for ($i=0; $i < 12; $i++) { 
			$mes_c = date('m', strtotime($fecha));
			$mes_hoy = date('m', strtotime($fecha_hoy));

			if($mes_c > $mes_hoy) {
				$json['tabla'].='<th align="center"><input class="form-control colocado" data-id="'.$i.'"></th>';
			} else {
				$json['tabla'].='<th align="center"></th>';
			}

			$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
			$fecha = date ('Y-m-d',$fecha);
		}
		$json['tabla'].= '</tr>';
		$json['tabla'] .= '<tr>
								<th align="center"></th>';

		$fecha_hoy = date("Y-m-d");
		$ano = date('Y', strtotime($fecha_hoy));
		$fecha_inicial = $ano."-01-01";
		$fecha = date('Y-m-d', strtotime($fecha_inicial));

		for ($i=0; $i < 12; $i++) { 
			$mes = date('m', strtotime($fecha));
			$mes_palabras = $this->getMonthWord($mes);
			$json['tabla'].='<th align="center">'.strtoupper($mes_palabras).'</th>';
			$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
			$fecha = date ('Y-m-d',$fecha);
		}
									

		$json['tabla'].='		</tr>
								</thead>';	
		/*END: MESES*/
		
		$json['tabla'].='	<tbody>';

		/*BG: INGRESO POR INTERESES*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Ingreso por Intereses</b></td>';
		$fecha_inicial = $ano."-01-01";
		$fecha = date('Y-m-d', strtotime($fecha_inicial));

		$fake_ingresos = array(21726.3, 44423.8, 68101.3, 73972.5, 57461.3, 17325.0, 700.0, 700.0, 525.0, 0, 0, 0);
		$incremento = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

		for ($i=0; $i < 12; $i++) { 
			/*$fecha_f = date("Y-m-t", strtotime($fecha));
			$sql_ingreso = "SELECT SUM(PAGO_INTERES) as ingresos
					FROM TABLA_PAGOS
					JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
					WHERE TP_FECHA >= ? AND TP_FECHA <= ?";
			$values_ingreso = array($fecha,
							$fecha_f);	

			$consulta_ingreso = $this->_conexion->prepare($sql_ingreso);

			try {
				$consulta_ingreso->execute($values_ingreso);
				$row_ingreso = $consulta_ingreso->fetch(PDO::FETCH_ASSOC);

				if ($consulta_ingreso->rowCount() > 0) {
					$json['tabla'].='<td align="center">'.number_format($row_ingreso['ingresos'], 2).'</td>';
				}

			} catch (PDOException $e) {
				die($e->getMessage());
			}

			$fecha = strtotime ( '+1 month' , strtotime ($fecha)) ;
			$fecha = date ('Y-m-d',$fecha);*/

			$json['tabla'].='<td align="center"><span class="ingresos-'.$i.'">'.number_format($fake_ingresos[$i], 2).'</span></td>';

			if($i != 0 && $fake_ingresos[$i-1] != 0 && $fake_ingresos[$i] != 0) {
				$incremento[$i] = (($fake_ingresos[$i] / $fake_ingresos[$i-1])-1)*100;
			}
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Incremento</i></b></td>
									<td align="center"><b><i>-</i></b></td>';	
		for ($i=1; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="incremento-'.$i.'">'.number_format($incremento[$i], 2).'</span>%</i></td>';
		}	

		$json['tabla'].='		</tr>';							

		/*END: INGRESO POR INTERESES*/


		/*BG: GASTO POR INTERESES*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Gasto por Intereses</b></td>';

		$fake_intereses = array(0, 0, 3500,3500,3500,3500,3500,3500,3500,3500,3500,3500);							
		$revenues = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="intereses-'.$i.'">'.number_format($fake_intereses[$i], 2).'</span></td>';

			if($fake_intereses[$i] != 0 && $fake_ingresos[$i] != 0) 
				$revenues[$i] = $fake_intereses[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Revenues</i></b></td>';	
		for ($i=0; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="revenues-'.$i.'">'.number_format($revenues[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';

		/*END: GASTO POR INTERESES*/


		/*BG: MARGEN FINANCIERO*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Margen Financiero</b></td>';
		$margen_financiero = array();
		$margen = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$margen_financiero[$i] = $fake_ingresos[$i] - $fake_intereses[$i];
			$json['tabla'].='		<td align="center"><span class="margenf-'.$i.'">'.number_format($margen_financiero[$i], 2).'</span></td>';
			if($margen_financiero[$i] != 0 && $fake_ingresos[$i] != 0) 
				$margen[$i] = $margen_financiero[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Margen</i></b></td>';	
		for ($i=0; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="margen-'.$i.'">'.number_format($margen[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';

		/*END: MARGEN FINANCIERO*/


		/*BG: SELLING EXPENSES*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Selling Expenses</b></td>';

		$selling = array(3000.0, 9400.0, 12500.0, 2500, 0, 0, 0, 0, 0, 0, 0, 0);							
		$revenues_2 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="selling-'.$i.'">'.number_format($selling[$i], 2).'</span></td>';
			if($selling[$i] != 0 && $fake_ingresos[$i] != 0) 
				$revenues_2[$i] = $selling[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Revenues</i></b></td>';	
		for ($i=0; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="revenues-2-'.$i.'">'.number_format($revenues_2[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';

		/*END: SELLING EXPENSES*/


		/*BG: ADMINISTRATION EXPENSES*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Administration Expenses</b></td>';
		$administration	= array(0, 0, 0, 0, 10000.0, 10000.0, 10000.0, 15000.0, 20000.0, 20000.0, 20000.0, 20000.0);
		$revenues_3 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="admin-'.$i.'">'.number_format($administration[$i], 2).'</span></td>';
			if($administration[$i] != 0 && $fake_ingresos[$i] != 0) 
				$revenues_3[$i] = $administration[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Revenues</i></b></td>';	
		for ($i=0; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="revenues-3-'.$i.'">'.number_format($revenues_3[$i], 2).'</span>%</i></td>';
		}	

		$json['tabla'].='		</tr>';	

		/*END: ADMINISTRATION EXPENSES*/


		/*BG: SG & A*/
		$json['tabla'].='		<tr>
									<td align="center"><b>SG & A</b></td>';

		$sg = array();							
		$revenues_4 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$sg[$i] = $selling[$i] + $administration[$i];
			$json['tabla'].='		<td align="center"><span class="sg-'.$i.'">'.number_format($sg[$i], 2).'</span></td>';
			if($sg[$i] != 0 && $fake_ingresos[$i] != 0) 
				$revenues_4[$i] = $sg[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Revenues</i></b></td>';	
		for ($i=0; $i < 12; $i++) {
			$json['tabla'].='<td align="center"><i><span class="revenues-4-'.$i.'">'.number_format($revenues_4[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';

		/*END: SG & A*/

		$comisiones_cobradas = array(14555.0, 20900.0, 11775.0, 5450.0, 0, 0, 0, 0, 0, 0, 0, 0);

		$comisiones_pagadas = array(4491.5, 5050.0, 3532.5, 360.0, 0, 0, 0, 0, 0, 0, 0, 0);

		/*BG: COMISIONES*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Comisiones</b></td>';
		$comision = array();
		for ($i=0; $i < 12; $i++) { 
			$comision[$i] = $comisiones_cobradas[$i] - $comisiones_pagadas[$i];
			$json['tabla'].='		<td align="center"><span class="comisiones-'.$i.'">'.number_format($comision[$i], 2).'</span></td>';
		}

		$json['tabla'].='		</tr>';
		/*END: COMISIONES*/


		/*BG: COMISIONES COBRADAS*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Comisiones Cobradas</b></td>';

		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="comisionesc-'.$i.'">'.number_format($comisiones_cobradas[$i], 2).'</span></td>';
		}

		$json['tabla'].='		</tr>';
		/*END: COMISIONES COBRADAS*/


		/*BG: COMISIONES PAGADAS*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Comisiones Pagadas</b></td>';

		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="comisionesp-'.$i.'">'.number_format($comisiones_pagadas[$i], 2).'</span></td>';
		}

		$json['tabla'].='		</tr>';
		/*END: COMISIONES PAGADAS*/


		/*BG: EBT*/
		$json['tabla'].='		<tr>
									<td align="center"><b>EBT</b></td>';

		$ebt = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);							
		$margen_2 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$ebt[$i] = $comision[$i] + $fake_ingresos[$i] - $sg[$i];
			$json['tabla'].='		<td align="center"><span class="ebt-'.$i.'">'.number_format($ebt[$i], 2).'</span></td>';
			if($ebt[$i] != 0 && $fake_ingresos[$i] != 0) 
				$margen_2[$i] = $ebt[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		</tr>';

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Margen</i></b></td>';

		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><i><span class="margen-2-'.$i.'">'.number_format($margen_2[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';


		/*END: EBT*/


		/*BG: OTROS*/
		$json['tabla'].='		<tr>
									<td align="center"><b>Otros</b></td>';
		$otros = array(4200.0, 15000.0, 15000.0, 15000.0, 15000.0, 0, 0, 0, 0, 0, 0, 0);						

		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><span class="otros-'.$i.'">'.number_format($otros[$i], 2).'</span></td>';
		}

		$json['tabla'].='		</tr>';
		/*END: OTROS*/

		$json['tabla'].='	</tbody>
							<tfoot>
								<tr>';


		/*BG: NET INCOME*/
		$json['tabla'].='			<th rowspan="1" colspan="1"><b>Net Income</b></th>';
		$net = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		$margen_3 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
		for ($i=0; $i < 12; $i++) { 
			$net[$i] = $ebt[$i] - $otros[$i];
			$json['tabla'].='		<th align="center"><span class="net-'.$i.'">'.number_format($net[$i], 2).'</span></td>';
			if($net[$i] != 0 && $fake_ingresos[$i] != 0) 
				$margen_3[$i] = $net[$i] / $fake_ingresos[$i] * 100;
		}

		$json['tabla'].='		<tr>
									<td align="center"><b><i>(%) Margen</i></b></td>';

		for ($i=0; $i < 12; $i++) { 
			$json['tabla'].='		<td align="center"><i><span class="margen-3-'.$i.'">'.number_format($margen_3[$i], 2).'</span>%</i></td>';
		}

		$json['tabla'].='		</tr>';

		/*END: NET INCOME*/


		$json['tabla'].='		</tr>
							</tfoot>
						</table>';					

		echo json_encode($json);
	}
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getMonth":
			$libs->getMonth();
			break;			
		case "getWeek":
			$libs->getWeek();
			break;
		case "getFlujo":
			$libs->getFlujo();
			break;
		case "getIs":
			$libs->getIs();
			break;	

	}
}

?>