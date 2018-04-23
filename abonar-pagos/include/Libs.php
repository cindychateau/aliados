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
	 * @version: 0.1 2016-03-10
	 * 
	 * 
	 * Obtiene todos los datos de los indicadores
	 */
	function getInfo() {
		$json = array();

		/*BG: TOTAL DE CLIENTES*/
		$sql_t_clientes = "SELECT COUNT(DISTINCT PERSONAS.PER_ID) AS clientes
						   FROM PERSONAS
						   JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
						   JOIN GRUPOS ON PERSONAS_GRUPOS.GRU_ID = GRUPOS.GRU_ID
						   WHERE STATUS != 2
						   AND STATUS != -1
						   AND GRUPOS.SIU_ID = ?";
		$values_t_clientes = array($_POST['id']);				   
		$consulta_t_clientes = $this->_conexion->prepare($sql_t_clientes);

		try {
			$consulta_t_clientes->execute($values_t_clientes);
			$row_t_clientes = $consulta_t_clientes->fetch(PDO::FETCH_ASSOC);
			$json['total_clientes'] = $row_t_clientes['clientes'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}				   
		/*END: TOTAL DE CLIENTES*/

		/*BG: TOTAL DE CLIENTES ACTIVOS*/
		$sql_clientes = "SELECT COUNT(DISTINCT PERSONAS.PER_ID) AS clientes
					     FROM PERSONAS
					     JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
						 JOIN GRUPOS ON PERSONAS_GRUPOS.GRU_ID = GRUPOS.GRU_ID
					     WHERE STATUS = 1
					     AND GRUPOS.SIU_ID = ?";
		$values_clientes = array($_POST['id']);			     
		$consulta_clientes = $this->_conexion->prepare($sql_clientes);

		try {
			$consulta_clientes->execute($values_clientes);
			$row_clientes = $consulta_clientes->fetch(PDO::FETCH_ASSOC);
			$json['clientes_activos'] = $row_clientes['clientes'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE CLIENTES ACTIVOS*/

		/*BG: TOTAL DE GRUPOS*/
		$sql_t_grupos = "SELECT COUNT(GRU_ID) AS grupos
					     FROM GRUPOS
					     WHERE SIU_ID = ?";
		$values_t_grupos = array($_POST['id']);				     
		$consulta_t_grupos = $this->_conexion->prepare($sql_t_grupos);

		try {
			$consulta_t_grupos->execute($values_t_grupos);
			$row_t_grupos = $consulta_t_grupos->fetch(PDO::FETCH_ASSOC);
			$json['total_grupos'] = $row_t_grupos['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE GRUPOS*/

		/*BG: TOTAL DE GRUPOS ACTIVOS*/
		$sql_grupos = "SELECT COUNT(GRU_ID) AS grupos
					   FROM GRUPOS
					   WHERE GRU_VIGENTE = 1
					   AND SIU_ID = ?";
		$values_grupos = array($_POST['id']);			   
		$consulta_grupos = $this->_conexion->prepare($sql_grupos);

		try {
			$consulta_grupos->execute($values_grupos);
			$row_grupos = $consulta_grupos->fetch(PDO::FETCH_ASSOC);
			$json['grupos_activos'] = $row_grupos['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE GRUPOS ACTIVOS*/

		/*BG: PRÉSTAMOS PENDIENTES DE ENTREGAR*/
		$sql_grupos_pendientes = "SELECT COUNT(GRU_ID) AS grupos
								  FROM GRUPOS
								  WHERE DATE(GRU_FECHA_ENTREGA) > CURDATE()
								  AND SIU_ID = ?";
		$values_grupos_pendientes = array($_POST['id']);						  
		$consulta_grupos_pendientes = $this->_conexion->prepare($sql_grupos_pendientes);

		try {
			$consulta_grupos_pendientes->execute($values_grupos_pendientes);
			$row_grupos_pendientes = $consulta_grupos_pendientes->fetch(PDO::FETCH_ASSOC);
			$json['pendientes_entregar'] = $row_grupos_pendientes['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: PRÉSTAMOS PENDIENTES DE ENTREGAR*/

		/*BG: SALDO BRUTO*/
		$sql_saldo_bruto = "SELECT SUM(GRU_MONTO_TOTAL_ENTREGAR) AS saldo
					      	FROM GRUPOS
					      	WHERE SIU_ID = ?";
		$values_saldo_bruto = array($_POST['id']);			      	
		$consulta_saldo_bruto = $this->_conexion->prepare($sql_saldo_bruto);

		try {
			$consulta_saldo_bruto->execute($values_saldo_bruto);
			$row_saldo_bruto = $consulta_saldo_bruto->fetch(PDO::FETCH_ASSOC);
			$json['saldo_bruto'] = number_format($row_saldo_bruto['saldo'], 2);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: SALDO BRUTO*/

		/*BG: SALDO PROMEDIO*/
		$sql_promedio = "SELECT AVG(MONTO_INDIVIDUAL) AS promedio
					     FROM PERSONAS_GRUPOS
					     JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					     WHERE SIU_ID = ?";
		$values_promedio = array($_POST['id']);			     
		$consulta_promedio = $this->_conexion->prepare($sql_promedio);

		try {
			$consulta_promedio->execute($values_promedio);
			$row_promedio = $consulta_promedio->fetch(PDO::FETCH_ASSOC);
			$json['saldo_promedio'] = number_format($row_promedio['promedio'], 2);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: SALDO PROMEDIO*/

		/*BG: GANANCIAS POR INTERES*/
		$sql_ganancias = "SELECT SUM(PAGO_INTERES*GRU_PLAZO) AS ganancias
					      FROM GRUPOS
					      WHERE SIU_ID = ?";
		$values_ganancias = array($_POST['id']);		      
		$consulta_ganancias = $this->_conexion->prepare($sql_ganancias);

		try {
			$consulta_ganancias->execute($values_ganancias);
			$row_ganancias = $consulta_ganancias->fetch(PDO::FETCH_ASSOC);
			$json['ganancias'] = number_format($row_ganancias['ganancias'], 2);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: GANANCIAS POR INTERES*/

		/*BG: CARTERA HISTORICA*/
		$sql_cartera_historica = "SELECT SUM(GRU_MONTO_TOTAL) AS cartera
					      	   	  FROM GRUPOS
					      	   	  WHERE SIU_ID = ?";
		$values_cartera_historica = array($_POST['id']);			      	   	  
		$consulta_cartera_historica = $this->_conexion->prepare($sql_cartera_historica);

		try {
			$consulta_cartera_historica->execute($values_cartera_historica);
			$row_cartera_historica = $consulta_cartera_historica->fetch(PDO::FETCH_ASSOC);
			$json['cartera_historica'] = number_format($row_cartera_historica['cartera'], 2);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: CARTERA HISTORICA*/

		/*BG: CARTERA ACTIVA*/
		$sql_cartera_activa = "SELECT SUM(GRU_MONTO_TOTAL) AS cartera
				      	   	   FROM GRUPOS
				      	   	   WHERE GRU_VIGENTE = 1
				      	   	   AND SIU_ID = ?";
		$values_cartera_activa = array($_POST['id']);		      	   	   
		$consulta_cartera_activa = $this->_conexion->prepare($sql_cartera_activa);

		try {
			$consulta_cartera_activa->execute($values_cartera_activa);
			$row_cartera_activa = $consulta_cartera_activa->fetch(PDO::FETCH_ASSOC);
			$json['cartera_activa'] = number_format($row_cartera_activa['cartera'], 2);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: CARTERA HISTORICA*/

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-03-10
	 * 
	 * 
	 * Select de Promotores
	 */
	function getPromotor() {
		$json = array();

		$json = array();
		$json['select'] = '<select id="promotor" name="mes" class="form-control">
							<option value="0">Seleccione el Promotor</option>';

		$sql = "SELECT SIU_ID, SIU_NOMBRE
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				$json['select'].= '<option value="'.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</option>';
			}
		} catch (PDOException $e) {
			die($e->getMessage());
		}

		$json['select'].= '</select>';

		echo json_encode($json);
	}

	function getPagos() {
		$json = array();
		$json['pagos'] = "";

		$fecha = date("Y-m-d");

		$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );

		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$json['fecha_inicio'] = date("d/m/Y", $inicio_str);
		$json['inicio'] = $fecha_inicio;
		$fin_str = strtotime('next sunday', $inicio_str);
		$fecha_fin = date("Y-m-d", $fin_str);
		$json['fecha_fin'] = date("d/m/Y", $fin_str);
		$json['fin'] = $fecha_fin;

		/*Verifica tipo de Usuario*/
		if(!isset($_SESSION)){
			@session_start();
		}

		$siu_id = $_SESSION["mp"]["userid"];

		$sql_user = "SELECT SUP_ID FROM SISTEMA_USUARIO
					 WHERE SIU_ID = ?";
		$values_user = array($siu_id);
		$consulta_user = $this->_conexion->prepare($sql_user);

		try {
			$consulta_user->execute($values_user);
			$row_user = $consulta_user->fetch(PDO::FETCH_ASSOC);	 

			if($row_user['SUP_ID'] != 3) {
				$sql = "SELECT TP_ID,
							   TP_FECHA,
							   TP_MONTO,
							   TABLA_PAGOS.GRU_ID,
							   GRU_RECREDITO,
							   GRU_PLAZO,
							   TP_EFECTUADO,
							   TP_AHORRO,
							   TP_PAGADO,
							   TP_FALTANTE
						FROM TABLA_PAGOS
						JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
						WHERE TP_FECHA >= ?
						AND TP_FECHA <= ?
						ORDER BY TP_FECHA, GRU_ID";
				$values = array($fecha_inicio,
								$fecha_fin);
			} else {
				$sql = "SELECT TP_ID,
							   TP_FECHA,
							   TP_MONTO,
							   TABLA_PAGOS.GRU_ID,
							   GRU_RECREDITO,
							   GRU_PLAZO,
							   TP_EFECTUADO,
							   TP_AHORRO,
							   TP_PAGADO,
							   TP_FALTANTE
						FROM TABLA_PAGOS
						JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
						WHERE TP_FECHA >= ?
						AND TP_FECHA <= ?
						AND SIU_ID = ?
						ORDER BY TP_FECHA, GRU_ID";
				$values = array($fecha_inicio,
								$fecha_fin,
								$siu_id);
			}

		} catch (PDOException $e) {
		 	die($e->getMessage());
		}		 

		
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$num2 = 0;
			foreach ($result as $row) {

				//Verifica qué num de pago es
				$num_pago = 1;
				$sql_num = "SELECT  l.TP_ID,
							        l.TP_FECHA, 
							        l.TP_MONTO,
							        l.GRU_ID,
							        @curRow := @curRow + 1 AS row_number,
							        GRU_PLAZO
							FROM    TABLA_PAGOS l
							JOIN    (SELECT @curRow := 0) r
							JOIN 	GRUPOS ON GRUPOS.GRU_ID = l.GRU_ID
							WHERE l.GRU_ID = ?";
				$values_num = array($row['GRU_ID']);
				$consulta_num = $this->_conexion->prepare($sql_num);
				$consulta_num->execute($values_num);
				$result_num = $consulta_num->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result_num as $row_num) {
					if($row_num['TP_ID'] == $row['TP_ID']) {
						$num_pago = $row_num['row_number'];
					}
				}

				$sql_faltante = "SELECT SUM(TP_FALTANTE) TP_FALTANTE
								 FROM TABLA_PAGOS
								 WHERE GRU_ID = ?
								 AND TP_FECHA <= ?";
				$values_faltante = array($row['GRU_ID'],
										 $fecha_fin);	
				$consulta_faltante = $this->_conexion->prepare($sql_faltante);	
				$consulta_faltante->execute($values_faltante);
				$row_faltante = $consulta_faltante->fetch(PDO::FETCH_ASSOC);						 		 


				$json['pagos'] .= '<div class="col-md-12">
										<form class="form-'.$row['GRU_ID'].'">
										<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary').'">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].' ('.$num_pago.'/'.$row['GRU_PLAZO'].')</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>
												</div>
											</div>
											<div class="box-body" style="display:none;">
												
												<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
													<tbody>
														<tr>
															<td align="center"><b>Fecha</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["TP_FECHA"])).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Pago Grupal Semanal</b></td>
															<td align="center">$<span class="semanal_total_'.$row['GRU_ID'].'">'.number_format($row['TP_MONTO'], 2).'</span></td>
															<input type="hidden" id="grupo-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="grupo['.$row['TP_ID'].']" value="'.$row['GRU_ID'].'">
															<input type="hidden" id="num-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="num['.$row['TP_ID'].']" value="'.$num_pago.'">
														</tr>
														<tr>
															<td align="center"><b>Pago Semanal Efectuado</b></td>
															<td align="center">$
																<span class="pago-semanal-efectuado-'.$row['GRU_ID'].'">'
																	.(number_format($row['TP_EFECTUADO'], 2)).
																'</span>
																<input class="pago-semanal-efectuado-inpt-'.$row['GRU_ID'].'" type="hidden" id="efectuado-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="efectuado['.$row['TP_ID'].']" value="'.$row['TP_EFECTUADO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Ahorro</b></td>
															<td align="center">$
																<span class="total-ahorro-'.$row['GRU_ID'].'">
																	'.(number_format($row['TP_AHORRO'], 2)).'
																</span>
																<input class="total-ahorro-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-ahorro-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_ahorro['.$row['TP_ID'].']" value="'.$row['TP_AHORRO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Pagado</b></td>
															<td align="center">$
																<span class="total-pagado-'.$row['GRU_ID'].'">
																	'.(number_format($row['TP_PAGADO'], 2)).'
																</span>
																<input class="total-pagado-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-pagado-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_pagado['.$row['TP_ID'].']" value="'.$row['TP_PAGADO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Faltante</b></td>
															<td align="center">$
																<span class="total-faltante-'.$row['GRU_ID'].' '.($row_faltante['TP_FALTANTE'] > 0 ? 'danger' : '' ).'">
																	'.number_format($row_faltante['TP_FALTANTE'], 2).'
																</span>
																<input class="total-faltante-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-faltante-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_faltante['.$row['TP_ID'].']" value="'.$row_faltante['TP_FALTANTE'].'">
																<input class="total-faltante-inpt-org-'.$row['GRU_ID'].'" type="hidden" id="total-faltante-org-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_faltante_org['.$row['TP_ID'].']" value="'.$row_faltante['TP_FALTANTE'].'">
															</td>
														</tr>
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th>Acreditado</th>
															<th>Préstamo Otorgado</th>
															<th>Pago Semanal</th>
															<th>Pago Efectuado</th>
															<th>Nuevo Pago</th>
															<th>Ahorro</th>
															<th>Total</th>
															<th>Completo</th>
															<th>Faltante</th>
															<th>Comentarios</th>
														</tr>
													</thead>
													<tbody>';

				$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
										PERSONAS.PER_ID,
										MONTO_INDIVIDUAL,
										PI_MONTO,
										PI_PAGO,
										PI_PENDIENTE,
										PI_AHORRO,
										PI_NUM,
										PAGO_SEMANAL_IND,
										PI_ID
								 FROM PAGOS_INDIVIDUALES
								 JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_INDIVIDUALES.PER_ID
								 JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								 WHERE PERSONAS_GRUPOS.GRU_ID = ?
								 AND PI_NUM = ?
								 AND PI_FECHA >= ?
								 AND PI_FECHA <= ?";
				$values_personas = array($row['GRU_ID'],
										 $num_pago,
										 $fecha_inicio,
										 $fecha_fin,);
				$consulta_personas = $this->_conexion->prepare($sql_personas);	
				try {
					$consulta_personas->execute($values_personas);
					$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
					$num = 0;
					foreach ($result_personas as $row_personas) {
						//Sumatoria de los pagos pendientes
						$sql_pendiente = "SELECT SUM(PI_PENDIENTE) as PI_PENDIENTE
										  FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  AND PI_FECHA <= ?";
						$values_pendiente = array($row_personas['PER_ID'],
												  $row['GRU_ID'],
												  $fecha_fin);

						$consulta_pendiente = $this->_conexion->prepare($sql_pendiente);	
						try {
							$consulta_pendiente->execute($values_pendiente);
							$row_pendiente = $consulta_pendiente->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage());
						}


						$num++;
						$num2++;
						$json['pagos'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center">'.$row_personas['PER_NOMBRE'].'</td>
												<td align="center">$'.$row_personas['MONTO_INDIVIDUAL'].'</td>
												<td align="center">$<span class="semanal_'.$num2.'">'.$row_personas['PAGO_SEMANAL_IND'].'</span></td>
												<td align="center">
													$<span class="pago_realizado_'.$num2.'">'.(number_format($row_personas['PI_PAGO'], 2)).'</span>
													<input class="form-control '.$num2.'  pago_r_'.$row['GRU_ID'].'" type="hidden" id="pago_realizado_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pago_realizado['.$row_personas['PI_ID'].']" value="'.$row_personas['PI_PAGO'].'">
												</td>
												<td align="center">
													<input class="form-control pago dinero_'.$num2.'  pago_'.$row['GRU_ID'].'" type="text" id="pago_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pago['.$row_personas['PI_ID'].']" value="">
												</td>
												<td align="center">
													<input class="form-control ahorro dinero_'.$num2.'  ahorro_'.$row['GRU_ID'].'" type="text" id="ahorro_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="ahorro['.$row_personas['PI_ID'].']" value="'.$row_personas['PI_AHORRO'].'">
												</td>
												<td align="center">
													$<span class="pago_individual_'.$num2.'">
														'.number_format(($row_personas['PI_AHORRO'] + $row_personas['PI_PAGO']), 2).'
													</span>
												</td>
												<td align="center" class="status_'.$num2.' '.($row_pendiente['PI_PENDIENTE'] > 0 ? '' : 'success').'">
													<i class="fa fa-'.($row_pendiente['PI_PENDIENTE'] > 0 ? 'times' : 'check').'"></i>
												</td>
												<td align="center" class="'.($row_pendiente['PI_PENDIENTE'] > 0 ? 'danger' : '').' falt_ind_'.$num2.'">
													$<span class="faltante_individual_'.$num2.'">'.number_format($row_pendiente['PI_PENDIENTE'], 2).'
													</span>
													<input class="form-control pendiente '.$num2.'  pendiente_'.$row['GRU_ID'].'" type="hidden" id="pendiente_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pendiente['.$row_personas['PI_ID'].']" value="'.$row_pendiente['PI_PENDIENTE'].'">
													<input class="form-control pendiente '.$num2.'  pendiente_'.$row['GRU_ID'].'" type="hidden" id="pendiente_org_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pendiente_org['.$row_personas['PI_ID'].']" value="'.$row_pendiente['PI_PENDIENTE'].'">
												</td>
												<td align="center">
													<textarea class="form-control" value="1" id="comment-'.$row_personas['PER_ID'].'" name="comment['.$row_personas['PI_ID'].']"></textarea>
												</td>
											</tr>';
					} 	

					$json['pagos'] .='			</tbody>
											</table>
											</form>
											<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'"><button class="btn btn-info"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button></a>
											</div>
										</div>
									</div>';

				 } catch (PDOException $e) {
				 	die($e->getMessage());
				 }		 
			}
		} catch (PDOException $e) {
			die($e->getMessage());
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

	function getTotales() {
		$json = array();
		$json['totales'] = "";

		$fecha = date("Y-m-d");
		//Desde la semana pasada
		$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$json['inicio'] = $fecha_inicio;
		$fin_str = strtotime('next sunday', $inicio_str);
		$fecha_fin = date("Y-m-d", $fin_str);
		$json['fin'] = $fecha_fin;

		/*Verifica tipo de Usuario*/
		if(!isset($_SESSION)){
			@session_start();
		}

		$siu_id = $_SESSION["mp"]["userid"];

		$sql_user = "SELECT SUP_ID FROM SISTEMA_USUARIO
					 WHERE SIU_ID = ?";
		$values_user = array($siu_id);
		$consulta_user = $this->_conexion->prepare($sql_user);

		try {
			$consulta_user->execute($values_user);
			$row_user = $consulta_user->fetch(PDO::FETCH_ASSOC);	 

			if($row_user['SUP_ID'] != 3) {
				$sql = "SELECT SUM(TP_MONTO) as monto, 
							   GRUPOS.SIU_ID,
							   SIU_NOMBRE
						FROM TABLA_PAGOS
						JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
						JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
						WHERE TP_FECHA >= ?
						AND TP_FECHA <= ?
                        GROUP BY GRUPOS.SIU_ID
						ORDER BY TP_FECHA, TABLA_PAGOS.GRU_ID";

				$values = array($fecha_inicio,
								$fecha_fin);

				$consulta = $this->_conexion->prepare($sql);
				try {
					$consulta->execute($values);
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

					$json['totales'] .= '<table class="table table-striped">
											<thead>
												<tr>
													<td align="center"><b>Promotor</b></td>
													<td align="center"><b>Monto Total</b></td>
												</tr>
											</thead>
											<tbody>';

					foreach ($result as $row) {
						$json['totales'] .= '<tr>
												<td align="center">'.$row['SIU_NOMBRE'].'</td>
												<td align="center">$'.$row['monto'].'</td>
											</tr>';
					}

					$json['totales'] .= '</tbody>
									</table>';

				} catch (PDOException $e) {
				 	die($e->getMessage());
				}	
			} 

		} catch (PDOException $e) {
		 	die($e->getMessage());
		}	

		echo json_encode($json);
	}

	function registrarGrupo() {
		$fecha = date("Y-m-d");
		//Desde la semana pasada
		$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		echo $fecha;
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$sql = "UPDATE TABLA_PAGOS SET TP_FALTANTE = TP_MONTO
			    WHERE TP_FECHA >= ? ";
		$values = array($fecha_inicio);	
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			/*$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				$sql_up = "UPDATE TP SET TP.TP_FALTANTE = TP.TP_MONTO
						   FROM TABLA_PAGOS
						   WHERE TP_FECHA >= ? ";
			}*/

		} catch (PDOException $e) {
		 	die($e->getMessage());
		}
	}

	function registrarInd() {
		$fecha = date("Y-m-d");
		//Desde la semana pasada
		$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		echo $fecha;
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);

		$sql = "SELECT TP_ID,
					   TP_FECHA,
					   TP_MONTO,
					   GRU_ID
				FROM TABLA_PAGOS
				WHERE TP_FECHA >= ?";
		$values = array($fecha_inicio);
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$num = 1;
			foreach ($result as $row) {

				$sql_num = "SELECT  l.TP_ID,
							        @curRow := @curRow + 1 AS row_number,
							        GRU_PLAZO
							FROM    TABLA_PAGOS l
							JOIN    (SELECT @curRow := 0) r
							JOIN 	GRUPOS ON GRUPOS.GRU_ID = l.GRU_ID
							WHERE l.GRU_ID = ?";
				$values_num = array($row['GRU_ID']);
				$consulta_num = $this->_conexion->prepare($sql_num);
				$consulta_num->execute($values_num);
				$result_num = $consulta_num->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result_num as $row_num) {
					if($row_num['TP_ID'] == $row['TP_ID']) {
						$num_pago = $row_num['row_number'];
					}
				}

				$sql_per = "SELECT * FROM PERSONAS_GRUPOS
							WHERE GRU_ID = ?";
				$values_per = array($row['GRU_ID']);
				$consulta_per = $this->_conexion->prepare($sql_per);
				try {
					$consulta_per->execute($values_per);
					$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_per as $row_per) {
						$sql_ind = "INSERT INTO PAGOS_INDIVIDUALES (PI_FECHA, 
																	PER_ID,
																	GRU_ID,
																	PI_MONTO,
																	PI_PAGO,
																	PI_PENDIENTE,
																	PI_AHORRO,
																	PI_NUM)
								  	VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
						$values_ind = array($row['TP_FECHA'],
											$row_per['PER_ID'],
											$row['GRU_ID'],
											$row_per['PAGO_SEMANAL_IND'],
											0,
											$row_per['PAGO_SEMANAL_IND'],
											0,
											$num_pago);		
						$consulta_ind = $this->_conexion->prepare($sql_ind);	
						$consulta_ind->execute($values_ind);				  	
					}

					$num++;

				} catch (PDOException $e) {
				 	die($e->getMessage());
				}	
			}

		} catch (PDOException $e) {
		 	die($e->getMessage());
		}

	}

	function guardarPago() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "El Grupo se guardó con éxito.";

		$db = $this->_conexion;
		$db->beginTransaction();
		$pago_efectuado = 0;

		foreach ($_POST['pago'] as $key => $value) {
			$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
												     PI_AHORRO = ?,
												     PI_PENDIENTE = ?,
												     PI_COMMENT = ?,
												     PI_FECHA_REG = ?
						WHERE PI_ID = ?";
			$pago = $_POST['pago'][$key] + $_POST['pago_realizado'][$key];
			$pago_efectuado += $pago;		
			$values_pi = array($pago,
							   $_POST['ahorro'][$key],
							   $_POST['pendiente'][$key],
							   $_POST['comment'][$key],
							   date("Y-m-d"),
							   $key);	
			$consulta_pi = $db->prepare($sql_pi);
			try{
				$consulta_pi->execute($values_pi);
			}catch(PDOException $e){
				$db->rollBack();
				die($e->getMessage());
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}
		}


		foreach ($_POST['efectuado'] as $key => $value) {
			$sql_tp = "UPDATE TABLA_PAGOS SET TP_EFECTUADO = ?,
											  TP_AHORRO = ?,
											  TP_PAGADO = ?,
											  TP_FALTANTE = ?
						WHERE TP_ID = ?";
			$values_tp = array($pago_efectuado,
							   $_POST['total_ahorro'][$key],
							   $_POST['total_pagado'][$key],
							   $_POST['total_faltante'][$key],
							   $key);	
			$consulta_tp = $db->prepare($sql_tp);
			try{
				$consulta_tp->execute($values_tp);
			}catch(PDOException $e){
				$db->rollBack();
				die($e->getMessage().$sql_tp);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}

			//Hace update de los pendientes totales anteriores
			$sql_tp = "UPDATE TABLA_PAGOS SET TP_FALTANTE = ?
					   WHERE TP_ID < ?
					   AND GRU_ID = ?";
			$values_tp = array(0,
							   $key,
							   $_POST['grupo'][$key]);	
			$consulta_tp = $db->prepare($sql_tp);
			try{
				$consulta_tp->execute($values_tp);
			}catch(PDOException $e){
				$db->rollBack();
				die($e->getMessage().$sql_tp);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}


			//Hace update de todos los pendientes anteriores
			if($_POST['num'][$key] > 1) {
				$sql_pendientes = "UPDATE PAGOS_INDIVIDUALES SET PI_PENDIENTE = ?
								   WHERE GRU_ID = ?
								   AND PI_NUM < ?";
				$values_pendiente = array(0,
										  $_POST['grupo'][$key],
										  ($_POST['num'][$key]));
				$consulta_pendientes = $db->prepare($sql_pendientes);
				try{
					$consulta_pendientes->execute($values_pendiente);
				}catch(PDOException $e){
					$db->rollBack();
					die($e->getMessage().$sql_pendientes);
					$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
				}

			}

		}

		$db->commit();

		echo json_encode($json);
	}

	function getPromotores2() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="promotor" name="promotor" class="form-control">
								<option value="0">Seleccione el Promotor</option>';

		$sql = "SELECT SIU_ID, SIU_NOMBRE, SIU_DIRECCION 
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option data-dir="'.$row['SIU_DIRECCION'].'" value="'.$row['SIU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SIU_ID'] ? 'selected' : '' : '').' >'.$row['SIU_NOMBRE'].'</option>';
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}


		$json["select"] .= '</select>';



		echo json_encode($json);
	}

	function filterGroups() {
		$json = array();
		$json['pagos'] = "";

		$fecha = date("Y-m-d");

		$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );

		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$json['fecha_inicio'] = date("d/m/Y", $inicio_str);
		$json['inicio'] = $fecha_inicio;
		$fin_str = strtotime('next sunday', $inicio_str);
		$fecha_fin = date("Y-m-d", $fin_str);
		$json['fecha_fin'] = date("d/m/Y", $fin_str);
		$json['fin'] = $fecha_fin;
			
		$sql = "SELECT TP_ID,
					   TP_FECHA,
					   TP_MONTO,
					   TABLA_PAGOS.GRU_ID,
					   GRU_RECREDITO,
					   GRU_PLAZO,
					   TP_EFECTUADO,
					   TP_AHORRO,
					   TP_PAGADO,
					   TP_FALTANTE
				FROM TABLA_PAGOS
				JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
				WHERE TP_FECHA >= ?
				AND TP_FECHA <= ?
				AND SIU_ID = ?
				ORDER BY TP_FECHA, GRU_ID";
		$values = array($fecha_inicio,
						$fecha_fin,
						$_POST['promotor']);	 

		
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$num2 = 0;
			foreach ($result as $row) {

				//Verifica qué num de pago es
				$num_pago = 1;
				$sql_num = "SELECT  l.TP_ID,
							        l.TP_FECHA, 
							        l.TP_MONTO,
							        l.GRU_ID,
							        @curRow := @curRow + 1 AS row_number,
							        GRU_PLAZO
							FROM    TABLA_PAGOS l
							JOIN    (SELECT @curRow := 0) r
							JOIN 	GRUPOS ON GRUPOS.GRU_ID = l.GRU_ID
							WHERE l.GRU_ID = ?";
				$values_num = array($row['GRU_ID']);
				$consulta_num = $this->_conexion->prepare($sql_num);
				$consulta_num->execute($values_num);
				$result_num = $consulta_num->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result_num as $row_num) {
					if($row_num['TP_ID'] == $row['TP_ID']) {
						$num_pago = $row_num['row_number'];
					}
				}

				$sql_faltante = "SELECT SUM(TP_FALTANTE) TP_FALTANTE
								 FROM TABLA_PAGOS
								 WHERE GRU_ID = ?
								 AND TP_FECHA <= ?";
				$values_faltante = array($row['GRU_ID'],
										 $fecha_fin);	
				$consulta_faltante = $this->_conexion->prepare($sql_faltante);	
				$consulta_faltante->execute($values_faltante);
				$row_faltante = $consulta_faltante->fetch(PDO::FETCH_ASSOC);						 		 


				$json['pagos'] .= '<div class="col-md-12">
										<form class="form-'.$row['GRU_ID'].'">
										<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary').'">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].' ('.$num_pago.'/'.$row['GRU_PLAZO'].')</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>
												</div>
											</div>
											<div class="box-body" style="display:none;">
												
												<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
													<tbody>
														<tr>
															<td align="center"><b>Fecha</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["TP_FECHA"])).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Pago Grupal Semanal</b></td>
															<td align="center">$<span class="semanal_total_'.$row['GRU_ID'].'">'.number_format($row['TP_MONTO'], 2).'</span></td>
															<input type="hidden" id="grupo-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="grupo['.$row['TP_ID'].']" value="'.$row['GRU_ID'].'">
															<input type="hidden" id="num-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="num['.$row['TP_ID'].']" value="'.$num_pago.'">
														</tr>
														<tr>
															<td align="center"><b>Pago Semanal Efectuado</b></td>
															<td align="center">$
																<span class="pago-semanal-efectuado-'.$row['GRU_ID'].'">'
																	.(number_format($row['TP_EFECTUADO'], 2)).
																'</span>
																<input class="pago-semanal-efectuado-inpt-'.$row['GRU_ID'].'" type="hidden" id="efectuado-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="efectuado['.$row['TP_ID'].']" value="'.$row['TP_EFECTUADO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Ahorro</b></td>
															<td align="center">$
																<span class="total-ahorro-'.$row['GRU_ID'].'">
																	'.(number_format($row['TP_AHORRO'], 2)).'
																</span>
																<input class="total-ahorro-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-ahorro-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_ahorro['.$row['TP_ID'].']" value="'.$row['TP_AHORRO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Pagado</b></td>
															<td align="center">$
																<span class="total-pagado-'.$row['GRU_ID'].'">
																	'.(number_format($row['TP_PAGADO'], 2)).'
																</span>
																<input class="total-pagado-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-pagado-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_pagado['.$row['TP_ID'].']" value="'.$row['TP_PAGADO'].'">
															</td>
														</tr>
														<tr>
															<td align="center"><b>Total Faltante</b></td>
															<td align="center">$
																<span class="total-faltante-'.$row['GRU_ID'].' '.($row_faltante['TP_FALTANTE'] > 0 ? 'danger' : '' ).'">
																	'.number_format($row_faltante['TP_FALTANTE'], 2).'
																</span>
																<input class="total-faltante-inpt-'.$row['GRU_ID'].'" type="hidden" id="total-faltante-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_faltante['.$row['TP_ID'].']" value="'.$row_faltante['TP_FALTANTE'].'">
																<input class="total-faltante-inpt-org-'.$row['GRU_ID'].'" type="hidden" id="total-faltante-org-'.$row['GRU_ID'].'" data-id="'.$row['TP_ID'].'" data-group="'.$row['GRU_ID'].'" name="total_faltante_org['.$row['TP_ID'].']" value="'.$row_faltante['TP_FALTANTE'].'">
															</td>
														</tr>
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th>Acreditado</th>
															<th>Préstamo Otorgado</th>
															<th>Pago Semanal</th>
															<th>Pago Efectuado</th>
															<th>Nuevo Pago</th>
															<th>Ahorro</th>
															<th>Total</th>
															<th>Completo</th>
															<th>Faltante</th>
															<th>Comentarios</th>
														</tr>
													</thead>
													<tbody>';

				$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
										PERSONAS.PER_ID,
										MONTO_INDIVIDUAL,
										PI_MONTO,
										PI_PAGO,
										PI_PENDIENTE,
										PI_AHORRO,
										PI_NUM,
										PAGO_SEMANAL_IND,
										PI_ID
								 FROM PAGOS_INDIVIDUALES
								 JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_INDIVIDUALES.PER_ID
								 JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								 WHERE PERSONAS_GRUPOS.GRU_ID = ?
								 AND PI_NUM = ?
								 AND PI_FECHA >= ?
								 AND PI_FECHA <= ?";
				$values_personas = array($row['GRU_ID'],
										 $num_pago,
										 $fecha_inicio,
										 $fecha_fin,);
				$consulta_personas = $this->_conexion->prepare($sql_personas);	
				try {
					$consulta_personas->execute($values_personas);
					$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
					$num = 0;
					foreach ($result_personas as $row_personas) {
						//Sumatoria de los pagos pendientes
						$sql_pendiente = "SELECT SUM(PI_PENDIENTE) as PI_PENDIENTE
										  FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  AND PI_FECHA <= ?";
						$values_pendiente = array($row_personas['PER_ID'],
												  $row['GRU_ID'],
												  $fecha_fin);

						$consulta_pendiente = $this->_conexion->prepare($sql_pendiente);	
						try {
							$consulta_pendiente->execute($values_pendiente);
							$row_pendiente = $consulta_pendiente->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage());
						}


						$num++;
						$num2++;
						$json['pagos'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center">'.$row_personas['PER_NOMBRE'].'</td>
												<td align="center">$'.$row_personas['MONTO_INDIVIDUAL'].'</td>
												<td align="center">$<span class="semanal_'.$num2.'">'.$row_personas['PAGO_SEMANAL_IND'].'</span></td>
												<td align="center">
													$<span class="pago_realizado_'.$num2.'">'.(number_format($row_personas['PI_PAGO'], 2)).'</span>
													<input class="form-control '.$num2.'  pago_r_'.$row['GRU_ID'].'" type="hidden" id="pago_realizado_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pago_realizado['.$row_personas['PI_ID'].']" value="'.$row_personas['PI_PAGO'].'">
												</td>
												<td align="center">
													<input class="form-control pago dinero_'.$num2.'  pago_'.$row['GRU_ID'].'" type="text" id="pago_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pago['.$row_personas['PI_ID'].']" value="">
												</td>
												<td align="center">
													<input class="form-control ahorro dinero_'.$num2.'  ahorro_'.$row['GRU_ID'].'" type="text" id="ahorro_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="ahorro['.$row_personas['PI_ID'].']" value="'.$row_personas['PI_AHORRO'].'">
												</td>
												<td align="center">
													$<span class="pago_individual_'.$num2.'">
														'.number_format(($row_personas['PI_AHORRO'] + $row_personas['PI_PAGO']), 2).'
													</span>
												</td>
												<td align="center" class="status_'.$num2.' '.($row_pendiente['PI_PENDIENTE'] > 0 ? '' : 'success').'">
													<i class="fa fa-'.($row_pendiente['PI_PENDIENTE'] > 0 ? 'times' : 'check').'"></i>
												</td>
												<td align="center" class="'.($row_pendiente['PI_PENDIENTE'] > 0 ? 'danger' : '').' falt_ind_'.$num2.'">
													$<span class="faltante_individual_'.$num2.'">'.number_format($row_pendiente['PI_PENDIENTE'], 2).'
													</span>
													<input class="form-control pendiente '.$num2.'  pendiente_'.$row['GRU_ID'].'" type="hidden" id="pendiente_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pendiente['.$row_personas['PI_ID'].']" value="'.$row_pendiente['PI_PENDIENTE'].'">
													<input class="form-control pendiente '.$num2.'  pendiente_'.$row['GRU_ID'].'" type="hidden" id="pendiente_org_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'" name="pendiente_org['.$row_personas['PI_ID'].']" value="'.$row_pendiente['PI_PENDIENTE'].'">
												</td>
												<td align="center">
													<textarea class="form-control" value="1" id="comment-'.$row_personas['PER_ID'].'" name="comment['.$row_personas['PI_ID'].']"></textarea>
												</td>
											</tr>';
					} 	

					$json['pagos'] .='			</tbody>
											</table>
											</form>
											<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'"><button class="btn btn-info"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button></a>
											</div>
										</div>
									</div>';

				 } catch (PDOException $e) {
				 	die($e->getMessage());
				 }		 
			}
		} catch (PDOException $e) {
			die($e->getMessage());
		}

		echo json_encode($json);
	}

	function showClients() {
		$personas = array();
		$term = trim($_GET['term']); //retrieve the search term that autocomplete sends
		try {
			$db = $this->_conexion;
			$sql = "SELECT GRUPOS.GRU_ID,
						   PERSONAS.PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_CELULAR,
						   MONTO_SOLICITADO
					FROM PERSONAS 
                    JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
                    JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					WHERE (STATUS != -1
					AND STATUS != 2)
					AND PER_NOMBRE LIKE '%".$term."%'
                    AND GRU_VIGENTE = 1
                    GROUP BY PER_ID";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $term);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$persona['id'] = $row['PER_ID'];
					$persona['name'] = $row['PER_NOMBRE'];
					$persona['value'] = $row['PER_NOMBRE'];
					$persona['grupo'] = $row['GRU_ID'];
					$personas[] = $persona;
				}

			} 
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($personas);
	}

	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getInfo":
			$libs->getInfo();
			break;	
		case "getPromotor":
			$libs->getPromotor();
			break;	
		case "getPagos":
			$libs->getPagos();
			break;
		case "getTotales":
			$libs->getTotales();
			break;	
		case "registrarGrupo":
			$libs->registrarGrupo();
			break;	
		case "registrarInd":
			$libs->registrarInd();
			break;		
		case "guardarPago":
			$libs->guardarPago();
			break;	
		case "getPagosInd":
			$libs->getPagosInd();
			break;
		case "getPromotores2":
			$libs->getPromotores2();
			break;	
		case "filterGroups":
			$libs->filterGroups();
			break;	
		case "showClients":
			$libs->showClients();
			break;									
	}
}

?>