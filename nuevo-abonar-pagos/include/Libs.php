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

$module = 33;

//Se incluye la clase Common
include_once($ruta."include/Common.php");

class Libs extends Common {

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

		$fecha_inic_cobro = $fecha_inicio;
		$fecha_fin_cobro = $fecha_fin;

		/*Verifica tipo de Usuario*/
		if(!isset($_SESSION)){
			@session_start();
		}

		/*$siu_id = $_SESSION["mp"]["userid"];

		$sql_user = "SELECT SUP_ID FROM SISTEMA_USUARIO
					 WHERE SIU_ID = ?";
		$values_user = array($siu_id);
		$consulta_user = $this->_conexion->prepare($sql_user);*/



		try {
			/*$consulta_user->execute($values_user);
			$row_user = $consulta_user->fetch(PDO::FETCH_ASSOC);	 */

			if($_SESSION["mp"]["userprofile"] != 3 && $_SESSION["mp"]["userprofile"] != 5) {
				$sql = "SELECT SUM(TP_MONTO) as monto, 
							   GRUPOS.SIU_ID,
							   SIU_NOMBRE,
							   GRUPOS.SIU_ID
						FROM TABLA_PAGOS
						JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
						JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
						WHERE TP_FECHA >= ?
						AND TP_FECHA <= ?
						AND TP_PAGADO_REC = 0
                        GROUP BY GRUPOS.SIU_ID
						ORDER BY SIU_NOMBRE, TP_FECHA, TABLA_PAGOS.GRU_ID";

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
													<td align="center"><b>Cantidad a Entregar</b></td>
													<td align="center"><b>Cantidad Entregada</b></td>
													<td align="center"><b>% Faltante</b></td>
													<td align="center"><b>Cantidad Recuperada</b></td>
												</tr>
											</thead>
											<tbody>';


					$fecha = date("Y-m-d");
					//Desde la semana pasada
					//$fecha = date ( 'Y-m-j' , $fecha );
					$inicio_str = $this->last_monday($fecha);
					$fecha_inicio = date("Y-m-d", $inicio_str);
					$fin_str = strtotime('next sunday', $inicio_str);
					$fecha_fin = date("Y-m-d", $fin_str);

					$total_entregar = 0;
					$total_entregado = 0;	
					$total_recuperado = 0;					



					foreach ($result as $row) {

						$sql_desglosado = "SELECT PD_FECHA,
												  SUM(PD_MONTO) as entregado,
												  PAGOS_DESGLOSADOS.GRU_ID,
												  SIU_ID
											FROM PAGOS_DESGLOSADOS
											JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_DESGLOSADOS.GRU_ID
											WHERE SIU_ID = ?
											AND PI_ID IN
												(SELECT PI_ID 
                                                 FROM PAGOS_INDIVIDUALES
                                                 JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_INDIVIDUALES.GRU_ID
                                                 WHERE PI_FECHA >= ?
                                                 AND PI_FECHA <= ?
                                                 AND SIU_ID = ?
                                                 AND PI_REC != 2)";

						$values_desglosado = array($row['SIU_ID'],
												   $fecha_inic_cobro,
												   $fecha_fin_cobro,
												   $row['SIU_ID']);	
						$consulta_desglosado = $this->_conexion->prepare($sql_desglosado);	
						
						$consulta_desglosado->execute($values_desglosado);
						$row_desglosado = $consulta_desglosado->fetch(PDO::FETCH_ASSOC);

						$sql_recuperado = "SELECT PR_FECHA,
												  SUM(PR_MONTO) as recuperado,
												  PAGOS_RECUPERADOS.GRU_ID,
												  SIU_ID
											FROM PAGOS_RECUPERADOS
											JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_RECUPERADOS.GRU_ID
											WHERE SIU_ID = ?
											AND PR_FECHA >= ?
											AND PR_FECHA <= ?";

						$values_recuperado = array($row['SIU_ID'],
												   $fecha_inicio,
												   $fecha_fin);	
						$consulta_recuperado = $this->_conexion->prepare($sql_recuperado);	
						
						$consulta_recuperado->execute($values_recuperado);
						$row_recuperado = $consulta_recuperado->fetch(PDO::FETCH_ASSOC);

						$row_desglosado['entregado'] = (is_null($row_desglosado['entregado']) ? 0 : $row_desglosado['entregado']);					   				
						$row_recuperado['recuperado'] = (is_null($row_recuperado['recuperado']) ? 0 : $row_recuperado['recuperado']);					   				

						$total_entregar += $row['monto'];
						$total_entregado += $row_desglosado['entregado'];
						$total_recuperado += $row_recuperado['recuperado'];


						$json['totales'] .= '<tr>
												<td align="center">'.$row['SIU_NOMBRE'].'</td>
												<td align="center">$'.number_format($row['monto'], 2).'</td>
												<td align="center">$'.number_format($row_desglosado['entregado'],2).'</td>
												<td align="center">'.number_format((100 / $row['monto'] * ($row['monto']-$row_desglosado['entregado'])), 2).'%</td>
												<td align="center">$'.$row_recuperado['recuperado'].'</td>
											</tr>';
					}

					$total_entregar = ($total_entregar == 0 ? 1 : $total_entregar);

					$json['totales'] .= '</tbody>
										<tfoot>
											<tr>
												<td align="center">Totales</td>
												<td align="center">'.number_format($total_entregar, 2).'</td>
												<td align="center">'.number_format($total_entregado, 2).'</td>
												<td align="center">'.number_format((100 / $total_entregar * ($total_entregar - $total_entregado)), 2).'%</td>
												<td align="center">'.$total_recuperado.'</td>
											</tr>
										</tfoot>
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

	function getClientes() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="cliente" name="cliente" class="form-control">
								<option value="0">Seleccione el Cliente</option>';

		$sql = "SELECT PER_ID, CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE
				FROM PERSONAS
				WHERE STATUS != -1
				AND STATUS != 2";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['PER_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['PER_ID'] ? 'selected' : '' : '').' >'.$row['PER_NOMBRE'].'</option>';
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}


		$json["select"] .= '</select>';



		echo json_encode($json);
	}

	function filterGroups2() {
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

	function filterGroups() {
		$json = array();
		$json['pagos'] = "";

		global $module;

		if(isset($_POST['promotor']) && $_POST['promotor'] > 0) {

			if(isset($_POST['page']) && $_POST['page'] > 0 ) {
				$pagina = $_POST['page'];
			} else {
				$pagina = 1;
			}

			//Calcula el límite
			$cantidad = 25;
			$fin = $pagina*$cantidad;
			$inicio = $fin - $cantidad;
			$limit = " LIMIT ".$inicio.",".$cantidad;

			//Obtiene todos los grupos (aunque estén inactivos) del Promotor
			$db = $this->_conexion;

			//TOTAL de grupos
			$sql_num = 'SELECT COUNT(GRU_ID) as Total_Registros
						FROM GRUPOS WHERE SIU_ID = ? AND GRU_VIGENTE = ? ';
			$consulta_num = $db->prepare($sql_num);
			$values_num = array($_POST['promotor'],
								$_POST['activo_inac']);
			$consulta_num->execute($values_num);
			$row_num = $consulta_num->fetch(PDO::FETCH_ASSOC);

			$json['num_res'] = $row_num['Total_Registros'];	 		
			$cant_pags = ceil($row_num['Total_Registros'] / $cantidad);


			$sql_grupos = "SELECT GRU_ID,
								  GRU_FECHA_ENTREGA,
								  GRU_FECHA_FINAL,
								  PAGO_SEMANAL,
								  GRU_MONTO_TOTAL,
								  GRU_RECREDITO,
								  GRU_VIGENTE,
								  GRU_PLAZO,
								  GRU_REESTRUCTURA
							FROM GRUPOS
							WHERE SIU_ID = ?
							AND GRU_VIGENTE = ?
							ORDER BY GRU_ID DESC ".$limit;

			$values_grupos = array($_POST['promotor'],
								   $_POST['activo_inac']);
			$consulta_grupos = $db->prepare($sql_grupos);


			//RESUMEN GENERAL DE PROMOTOR
			$sql_promotor_total = "SELECT SUM(GRU_MONTO_TOTAL) as total
								   FROM GRUPOS
								   WHERE SIU_ID = ?";

			$values_promotor_total = array($_POST['promotor']);
			$consulta_promotor_total= $db->prepare($sql_promotor_total);
			$consulta_promotor_total->execute($values_promotor_total);
			$row_promotor_total = $consulta_promotor_total->fetch(PDO::FETCH_ASSOC);

			$sql_promotor_pend = "SELECT SUM(PI_PENDIENTE) as pendiente
							 	  FROM PAGOS_INDIVIDUALES
							 	  JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_INDIVIDUALES.GRU_ID
							 	  WHERE SIU_ID = ?
							 	  AND PI_FECHA < CURRENT_DATE";

			$values_promotor_pend = array($_POST['promotor']);
			$consulta_promotor_pend= $db->prepare($sql_promotor_pend);
			$consulta_promotor_pend->execute($values_promotor_pend);
			$row_promotor_pend = $consulta_promotor_pend->fetch(PDO::FETCH_ASSOC);

			$json['pagos'] = '<table class="table">
								<thead>
									<tr>
										<th>Colocación Total Histórica</th>
										<th>Deuda Total Histórica</th>
										<th>% Total Histórica</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td align="center">'.$row_promotor_total['total'].'</td>
										<td align="center">'.$row_promotor_pend['pendiente'].'</td>
										<td align="center">'.number_format((100 / $row_promotor_total['total'] * $row_promotor_pend['pendiente']), 2).'%</td>
									</tr>
								</tbody>
							  </table>';




			try {
				$consulta_grupos->execute($values_grupos);

				if ($consulta_grupos->rowCount()) {
					$result_grupos = $consulta_grupos->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_grupos as $row) {

						$sql_tabla_pagos = "SELECT TP_FECHA
											FROM TABLA_PAGOS
											WHERE GRU_ID = ?
											ORDER BY TP_FECHA ASC";
						$value_tabla_pagos = array($row['GRU_ID']);	
						$consulta_tabla_pagos = $db->prepare($sql_tabla_pagos);	
						$consulta_tabla_pagos->execute($value_tabla_pagos);	
						$result_tabla_pagos = $consulta_tabla_pagos->fetchAll(PDO::FETCH_ASSOC);

						//Semana en la que esta el Crédito
						$fecha = date("Y-m-d");
						$fecha = strtotime ( '-1 week' , strtotime ( $fecha ) ) ;
						$fecha = date ( 'Y-m-j' , $fecha );

						$inicio_str = $this->last_monday($fecha);
						$fecha_inicio = date("Y-m-d", $inicio_str);
						$json['inicio'] = $fecha_inicio;
						$fin_str = strtotime('next sunday', $inicio_str);
						$fecha_fin = date("Y-m-d", $fin_str);
						$sql_semana = "SELECT PI_NUM
									   FROM PAGOS_INDIVIDUALES
									   WHERE GRU_ID = ?
									   AND PI_FECHA >= ?
									   AND PI_FECHA <= ?";
						$values_semana = array($row['GRU_ID'],
											   $fecha_inicio,
											   $fecha_fin);
						$consulta_semana = $db->prepare($sql_semana);
						$consulta_semana->execute($values_semana);
						$semana_num = "12 / ".$row['GRU_PLAZO'];
						if ($consulta_semana->rowCount()) {
							$row_semana = $consulta_semana->fetch(PDO::FETCH_ASSOC);
							$semana_num = $row_semana['PI_NUM']." / ".$row['GRU_PLAZO'];
						} 

						$str_fecha = strtotime(date("Y-m-d"));
						$str_fecha2 = strtotime($result_tabla_pagos[0]["TP_FECHA"]);

						if($str_fecha2 > $str_fecha) {
							$semana_num = "0 / ".$row['GRU_PLAZO'];
						}

						$color = 'primary';
						if($row['GRU_VIGENTE'] == 0) {
							$color = 'gray';
						} else if($row['GRU_REESTRUCTURA'] == 1) {
							$color = 'orange';
						} else if($row['GRU_RECREDITO'] != 0) {
							$color = 'purple';
						}

						
						$json['pagos'] .= '<div class="col-md-12 div-'.$row['GRU_ID'].'">
											<form class="form-'.$row['GRU_ID'].'">
											<div class="box border '.$color.'">
												<input type="hidden" class="form-control" id="grupo_'.$row['GRU_ID'].'" name="grupo" value="'.$row['GRU_ID'].'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].' - '.$semana_num.'</h4>
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
																<td align="center"><b>Fecha de Apertura:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
																<td align="center"><b>Fecha de Vencimiento:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_FINAL"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Crédito Grupal Autorizado: </b> $'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
																<td align="center"><b>Pago Semanal Grupal: </b> $'.number_format($row['PAGO_SEMANAL'], 2).'</td>
															</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>Nombre</th>
																<th>Pago Semanal</th>
																<th></th>';
						$num_pagos = 0;										

						foreach ($result_tabla_pagos as $row_tabla_pagos) {
							$num_pagos++;
							$json['pagos'] .= '<th><span class="pop-hover" data-content="'.(isset($row_tabla_pagos['TP_FECHA']) ? date("d/m/Y",strtotime($row_tabla_pagos["TP_FECHA"])) : '-').'">'.$num_pagos.'</span></th>';
						}

						if(count($result_tabla_pagos) < 12) {
							$i_x = count($result_tabla_pagos) +1;
							for ($i=$i_x; $i < ($row['GRU_PLAZO'] + 1); $i++) { 
								$json['pagos'] .= '<th><span class="pop-hover" data-content="-">'.$i.'</span></th>';
							}
						}		

						$json['pagos'].=						'<th>Total Abonado</th>
																<th>Pago Semanal</th>
																<th>Ahorro</th>
																<th>Suma Ahorro</th>
																<th>Total Recup.</th>
																<th>Deuda Real</th>
															</tr>
														</thead>
														<tbody>';


					$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
											PERSONAS.PER_ID,
											PAGO_SEMANAL_IND,
											GRU_ID
								 	FROM PERSONAS_GRUPOS
								 	JOIN PERSONAS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
								 	WHERE GRU_ID = ?";
					$values_personas = array($row['GRU_ID']);
					$consulta_personas = $db->prepare($sql_personas);
					$pagos_arr = array();
					$ahorros_arr = array();
					
					$pagos_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);
					$ahorros_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);

					$totales_abonado = 0;
					$totales_ahorrado = 0;
					$totales_recuperado = 0;
					$totales_deudas = 0;

					$select = '<div class="cont-sel-'.$row['GRU_ID'].' display-none">';

					try {
						
						$consulta_personas->execute($values_personas);
						$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
						$ahorros_arr[0] = '-';
						foreach ($result_personas as $row_personas) {
							$select .= '<option value="'.$row_personas['PER_ID'].'">'.$row_personas['PER_NOMBRE'].'</option>';
							$lnk_desglosados = 'pagos-desglosados.php?per_id='.$row_personas['PER_ID'].'&gru_id='.$row['GRU_ID'].'';
							$json['pagos'] .= '<tr>
												<td align="center" rowspan="2"><a href="'.$this->printLink($module, "cambios", $lnk_desglosados).'" target="_blank">'.$row_personas['PER_NOMBRE'].'</a></td>
												<td align="center" rowspan="2">'
													.$row_personas['PAGO_SEMANAL_IND'].'
													<input type="hidden" class="form-control pago_semanal_ind" id="pago_semanal_ind_'.$row_personas['PER_ID'].'" value="'.$row_personas['PAGO_SEMANAL_IND'].'" data-id='.$row_personas['PER_ID'].'>
												</td>
												<td align="center">P</td>';

							$pagos_arr[0] += $row_personas['PAGO_SEMANAL_IND'];					

							$sql_pagos = "SELECT * FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  ORDER BY PI_FECHA ASC";
							$values_pagos = array($row_personas['PER_ID'],
												  $row['GRU_ID']);
							$consulta_pagos = $db->prepare($sql_pagos);	

							$p = 1;

							try {
								
								$consulta_pagos->execute($values_pagos);
								$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);
								$count_pagos = $consulta_pagos->rowCount();
								$json['total_pagos'][$row_personas['PER_ID']] = $count_pagos;

								$row_ahorro = '<tr>
												<td>A</td>';

								$total_abonado = 0;	
								$suma_ahorro = 0;			

								foreach ($result_pagos as $row_pagos) {
									//Aquí deben de desglosarse también los pagos**

									$pago = ($row_pagos['PI_REC'] == 0 ? $row_pagos['PI_PAGO'] : 0);

									$sql_desglosado = "SELECT * FROM
														(
															(SELECT PD_FECHA as FECHA,
																	PD_MONTO as MONTO,
																	'D' as DoR,
																	'0' as AD_ID,
																	PD_ID as ID_D
															FROM PAGOS_DESGLOSADOS
															WHERE PI_ID = ?)
															UNION
															(SELECT PR_FECHA as FECHA,
																	PR_MONTO as MONTO,
																	'R' as DoR,
																	AD_ID,
																	PR_ID as ID_D
															FROM PAGOS_RECUPERADOS
															WHERE PI_ID = ?)) 
														PAGOS
														ORDER BY FECHA ASC";
									$values_desglosado = array($row_pagos['PI_ID'],
															   $row_pagos['PI_ID']);
									$consulta_desglosado = $db->prepare($sql_desglosado);	

									try {
										
										$consulta_desglosado->execute($values_desglosado);
										$result_desglosado = $consulta_desglosado->fetchAll(PDO::FETCH_ASSOC);

										$hover_pago = "";
										$pago = 0;
										foreach ($result_desglosado as $row_desglosado) {
											$hover_pago .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";

											$pago += ($row_desglosado['DoR'] == 'R' ? 0 : $row_desglosado['MONTO']);

										}

									} catch (PDOException $e) {
										die($e->getMessage());
									}

									$color = ($row_pagos['PI_PAGO'] > 0 ? ($row_pagos['PI_PAGO'] == $row_pagos['PI_MONTO'] ? 'text-success' : 'text-warning') : '');

									$color = ($row_pagos['PI_REC'] == 2 ? 'text-info' : $color);


									$json['pagos'] .= '<td align="center"><span class="pop-hover '.$color.'" data-html="true" data-content="'.$hover_pago.'">'.$pago.'</span></td>';
									$row_ahorro .= '<td align="center">'.$row_pagos['PI_AHORRO'].'</td>';
									$total_abonado += $pago;
									$totales_abonado += $pago;
									$suma_ahorro += $row_pagos['PI_AHORRO'];
									$ahorros_arr[$p] += $row_pagos['PI_AHORRO'];
									$totales_ahorrado += $row_pagos['PI_AHORRO'];
									$pagos_arr[$p] += $pago;

									if($count_pagos < 12 && $p == $count_pagos) {
										$json['pagos'] .= '<td align="center">-</td>';
										$row_ahorro .= '<td align="center">-</td>';

										if($p == 10) {
											$json['pagos'] .= '<td align="center">-</td>';
											$row_ahorro .= '<td align="center">-</td>';
										}
									}


									$p++;
								}

								$row_ahorro .= '</tr>';	


							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Consulta los recuperados
							$sql_rec = "SELECT * FROM PAGOS_RECUPERADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_rec = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_rec = $db->prepare($sql_rec);	
							try {
								$consulta_rec->execute($values_rec);
								$result_rec = $consulta_rec->fetchAll(PDO::FETCH_ASSOC);
								$hover_rec = "";
								$recuperados = 0;
								foreach ($result_rec as $row_rec) {
									$hover_rec .= "<span class='".($row_rec['AD_ID'] > 0 ? 'text-purple' : '')."'>".date("d/m/Y",strtotime($row_rec["PR_FECHA"]))." - ".$row_rec['PR_MONTO']."<br>";
									$recuperados += $row_rec['PR_MONTO'];
									$totales_recuperado += $row_rec['PR_MONTO'];
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Deuda REAL
							$sql_deuda = "SELECT SUM(PI_PENDIENTE) as deuda
										  FROM PAGOS_INDIVIDUALES 
										  WHERE PER_ID = ? 
										  AND GRU_ID = ?
										  AND PI_FECHA < CURRENT_DATE";
							$values_deuda = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_deuda = $db->prepare($sql_deuda);	
							try {
								$consulta_deuda->execute($values_deuda);
								$row_deuda = $consulta_deuda->fetch(PDO::FETCH_ASSOC);
							} catch (PDOException $e) {
								die($e->getMessage());
							}

							$totales_deudas += $row_deuda['deuda'];

							//Consulta Ahorro desglosado
							$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS 
									   WHERE PER_ID = ? 
									   AND GRU_ID = ?";
							$values_ad = array($row_personas['PER_ID'],
											   $row['GRU_ID']);	
							$consulta_ad = $db->prepare($sql_ad);	
							try {
								$consulta_ad->execute($values_ad);
								$result_ad = $consulta_ad->fetchAll(PDO::FETCH_ASSOC);
								$hover_ad = "";
								foreach ($result_ad as $row_ad) {
									$hover_ad .= "<span class='".($row_ad['AD_VALIDO'] == 0 ? 'crossed' : '')."'>".date("d/m/Y",strtotime($row_ad["AD_FECHA"]))." - ".$row_ad['AD_CANTIDAD']."</span><br>";
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							$inpt_pago = '<input class="form-control" id="pago_'.$row_personas['PER_ID'].'" name="pago['.$row_personas['PER_ID'].']">';
							$inpt_ahorro = '<input class="form-control" id="ahorro_'.$row_personas['PER_ID'].'" name="ahorro['.$row_personas['PER_ID'].']">';
							$trans_ahorro = '<span class="fa-stack pop-hover transferir-ahorro" data-content="Transferir Ahorro" data-per="'.$row_personas['PER_ID'].'" data-gru="'.$row['GRU_ID'].'">
													  <i class="fa fa-circle fa-stack-2x"></i>
													  <i class="fa fa-dollar fa-stack-1x fa-inverse"></i>
													</span>';


							$json['pagos'] .= '<td align="center" rowspan="2">'.$total_abonado.'</td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_pago).'
											   </td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_ahorro).'
											   </td>
											   <td align="center" rowspan="2">
											   		<span class="pop-hover" data-html="true" data-content="'.$hover_ad.'">'
											   			.$suma_ahorro.
											   		'</span><br>
											   		'.$this->printLink($module, "cambios", $trans_ahorro).'
											   	</td>
											   <td align="center" rowspan="2"><span class="pop-hover" data-html="true" data-content="'.$hover_rec.'">'.$recuperados.'</span></td>
											   <td align="center" rowspan="2">'.$row_deuda['deuda'].'</td>';


							$json['pagos'] .= '</tr>';

							$json['pagos'] .= $row_ahorro;

						}

						$select .='</div>';


				 	} catch (PDOException $e) {
				 		die($e->getMessage());
				 	}		 	


					$json['pagos'] .= '					</tbody>
														<tfoot>
															<tr>
																<td>Total de Pagos</td>
																<td align="center">'.$pagos_arr[0].'</td>
																<td></td>';

					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$pagos_arr[$i].'</td>';
					}

					$btn_guardar = '<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-success"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button>
													</a>';
					$btn_autocomplete = '<a class="autocomplete" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-info"><i class="fa fa-sort-numeric-desc"></i>Autocompletar</button>
													</a>';	
					$btn_reload = '<a class="reload" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-light-grey"><i class="fa fa-refresh"></i>Recargar Grupo</button>
													</a>';																

					$json['pagos'].=							'<td align="center">'.$totales_abonado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">'.$totales_recuperado.'</td>
																<td align="center">'.$totales_deudas.'</td>
															</tr>
															<tr>
																<td>Total de Ahorros</td>
																<td align="center">'.$ahorros_arr[0].'</td>
																<td></td>';

					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$ahorros_arr[$i].'</td>';
					}

					$json['pagos'].=				'			<td align="center">'.$totales_ahorrado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">-</td>
																<td align="center">-</td>
															</tr>
													</tfoot>
													</table>
													'.$this->printLink($module, "cambios", $btn_guardar).'
													'.$this->printLink($module, "cambios", $btn_autocomplete).'
													'.$this->printLink($module, "cambios", $btn_reload).'
													'.$select.'
												</div>
											</div>
											</form>
										</div>';									


					}

					$inicio_pags = (ceil($pagina/20) * 20) - 19;
					$max_pags = $inicio_pags + 19;
					$fin_pags = ($max_pags > $cant_pags ? $cant_pags : $max_pags);

					$pag = '<nav><ul class="pagination">';

					if($pagina != 1) {
						$pag .= '<li>
									<a href="#" class="paginator" aria-label="Anterior" data-pag="'.($pagina-1).'">
										<span aria-hidden="true">&laquo;</span>
									</a>
								</li>';
					}

					for ($i=$inicio_pags; $i <= $fin_pags; $i++) { 
						$pag .= '<li'.($i == $pagina ? ' class="active disabled"' : '').'>
									<a href="#" class="paginator" data-pag="'.$i.'">'.$i.'</a>
								</li>';
					}

					if($pagina != $cant_pags) {
						$pag .= '<li>
									<a href="#" class="paginator" aria-label="Siguiente" data-pag="'.($pagina+1).'">
										<span aria-hidden="true">&raquo;</span>
									</a>
								</li>';
					}				

					$pag.=	'</ul></nav>';

					$json['paginas'] = $pag;


				} else {
					$json['pagos'] = "<h1>No se encontraron grupos</h1>";
				}


			} catch (PDOException $e) {
				die($e->getMessage());
			}

		}

		echo json_encode($json);

	}

	function filterClients() {
		$json = array();
		$json['pagos'] = "";

		global $module;

		if(isset($_POST['cliente']) && $_POST['cliente'] > 0) {

			//Obtiene todos los grupos (aunque estén inactivos) del Promotor
			$db = $this->_conexion;
			$sql_grupos = "SELECT GRUPOS.GRU_ID,
								  GRU_FECHA_ENTREGA,
								  GRU_FECHA_FINAL,
								  PAGO_SEMANAL,
								  GRU_MONTO_TOTAL,
								  GRU_RECREDITO,
								  GRU_VIGENTE,
								  GRU_PLAZO,
								  GRU_REESTRUCTURA,
								  SIU_NOMBRE
							FROM GRUPOS
                            JOIN PERSONAS_GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
                            LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
							WHERE PER_ID = ?
							ORDER BY GRU_ID DESC";

			$values_grupos = array($_POST['cliente']);
			$consulta_grupos = $db->prepare($sql_grupos);


			//RESUMEN GENERAL DE PROMOTOR
			/*$sql_promotor_total = "SELECT SUM(GRU_MONTO_TOTAL) as total
								   FROM GRUPOS
								   WHERE SIU_ID = ?";

			$values_promotor_total = array($_POST['promotor']);
			$consulta_promotor_total= $db->prepare($sql_promotor_total);
			$consulta_promotor_total->execute($values_promotor_total);
			$row_promotor_total = $consulta_promotor_total->fetch(PDO::FETCH_ASSOC);

			$sql_promotor_pend = "SELECT SUM(PI_PENDIENTE) as pendiente
							 	  FROM PAGOS_INDIVIDUALES
							 	  JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_INDIVIDUALES.GRU_ID
							 	  WHERE SIU_ID = ?
							 	  AND PI_FECHA < CURRENT_DATE";

			$values_promotor_pend = array($_POST['promotor']);
			$consulta_promotor_pend= $db->prepare($sql_promotor_pend);
			$consulta_promotor_pend->execute($values_promotor_pend);
			$row_promotor_pend = $consulta_promotor_pend->fetch(PDO::FETCH_ASSOC);

			$json['pagos'] = '<table class="table">
								<thead>
									<tr>
										<th>Colocación Total Histórica</th>
										<th>Deuda Total Histórica</th>
										<th>% Total Histórica</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td align="center">'.$row_promotor_total['total'].'</td>
										<td align="center">'.$row_promotor_pend['pendiente'].'</td>
										<td align="center">'.number_format((100 / $row_promotor_total['total'] * $row_promotor_pend['pendiente']), 2).'%</td>
									</tr>
								</tbody>
							  </table>';*/




			try {
				$consulta_grupos->execute($values_grupos);

				if ($consulta_grupos->rowCount()) {
					$result_grupos = $consulta_grupos->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_grupos as $row) {

						$sql_tabla_pagos = "SELECT TP_FECHA
											FROM TABLA_PAGOS
											WHERE GRU_ID = ?
											ORDER BY TP_FECHA ASC";
						$value_tabla_pagos = array($row['GRU_ID']);	
						$consulta_tabla_pagos = $db->prepare($sql_tabla_pagos);	
						$consulta_tabla_pagos->execute($value_tabla_pagos);	
						$result_tabla_pagos = $consulta_tabla_pagos->fetchAll(PDO::FETCH_ASSOC);

						$color = 'primary';
						if($row['GRU_VIGENTE'] == 0) {
							$color = 'gray';
						} else if($row['GRU_REESTRUCTURA'] == 1) {
							$color = 'orange';
						} else if($row['GRU_RECREDITO'] != 0) {
							$color = 'purple';
						}
						
						$json['pagos'] .= '<div class="col-md-12 div-'.$row['GRU_ID'].'">
											<form class="form-'.$row['GRU_ID'].'">
											<div class="box border '.$color.'">
												<input type="hidden" class="form-control" id="grupo_'.$row['GRU_ID'].'" name="grupo" value="'.$row['GRU_ID'].'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
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
																<td align="center" colspan="2"><b>Promotor:</b> '.$row['SIU_NOMBRE'].'</td>
															</tr>
															<tr>
																<td align="center"><b>Fecha de Apertura:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
																<td align="center"><b>Fecha de Vencimiento:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_FINAL"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Crédito Grupal Autorizado: </b> $'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
																<td align="center"><b>Pago Semanal Grupal: </b> $'.number_format($row['PAGO_SEMANAL'], 2).'</td>
															</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>Nombre</th>
																<th>Pago Semanal</th>
																<th></th>';
						$num_pagos = 0;										

						foreach ($result_tabla_pagos as $row_tabla_pagos) {
							$num_pagos++;
							$json['pagos'] .= '<th><span class="pop-hover" data-content="'.(isset($row_tabla_pagos['TP_FECHA']) ? date("d/m/Y",strtotime($row_tabla_pagos["TP_FECHA"])) : '-').'">'.$num_pagos.'</span></th>';
						}

						if(count($result_tabla_pagos) < 12) {
							$i_x = count($result_tabla_pagos) +1;
							for ($i=$i_x; $i < ($row['GRU_PLAZO'] + 1); $i++) { 
								$json['pagos'] .= '<th><span class="pop-hover" data-content="-">'.$i.'</span></th>';
							}
						}

						$json['pagos'].=						'<th>Total Abonado</th>
																<th>Pago Semanal</th>
																<th>Ahorro</th>
																<th>Suma Ahorro</th>
																<th>Total Recup.</th>
																<th>Deuda Real</th>
															</tr>
														</thead>
														<tbody>';


					$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
											PERSONAS.PER_ID,
											PAGO_SEMANAL_IND,
											GRU_ID
								 	FROM PERSONAS_GRUPOS
								 	JOIN PERSONAS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
								 	WHERE GRU_ID = ?";
					$values_personas = array($row['GRU_ID']);
					$consulta_personas = $db->prepare($sql_personas);
					$pagos_arr = array();
					$ahorros_arr = array();

					$pagos_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);
					$ahorros_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);

					$totales_abonado = 0;
					$totales_ahorrado = 0;
					$totales_recuperado = 0;
					$totales_deudas = 0;

					$select = '<div class="cont-sel-'.$row['GRU_ID'].' display-none">';

					try {
						
						$consulta_personas->execute($values_personas);
						$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
						$ahorros_arr[0] = '-';
						foreach ($result_personas as $row_personas) {
							$select .= '<option value="'.$row_personas['PER_ID'].'">'.$row_personas['PER_NOMBRE'].'</option>';
							$lnk_desglosados = 'pagos-desglosados.php?per_id='.$row_personas['PER_ID'].'&gru_id='.$row['GRU_ID'];
							$json['pagos'] .= '<tr>
												<td align="center" rowspan="2"><a href="'.$this->printLink($module, "cambios", $lnk_desglosados).'" target="_blank">'.$row_personas['PER_NOMBRE'].'</a></td>
												<td align="center" rowspan="2">'
													.$row_personas['PAGO_SEMANAL_IND'].'
													<input type="hidden" class="form-control pago_semanal_ind" id="pago_semanal_ind_'.$row_personas['PER_ID'].'" value="'.$row_personas['PAGO_SEMANAL_IND'].'" data-id='.$row_personas['PER_ID'].'>
												</td>
												<td align="center">P</td>';

							$pagos_arr[0] += $row_personas['PAGO_SEMANAL_IND'];					

							$sql_pagos = "SELECT * FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  ORDER BY PI_FECHA ASC";
							$values_pagos = array($row_personas['PER_ID'],
												  $row['GRU_ID']);
							$consulta_pagos = $db->prepare($sql_pagos);	

							$p = 1;

							try {
								
								$consulta_pagos->execute($values_pagos);
								$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);
								$count_pagos = $consulta_pagos->rowCount();
								$json['total_pagos'][$row_personas['PER_ID']] = $count_pagos;

								$row_ahorro = '<tr>
												<td>A</td>';

								$total_abonado = 0;	
								$suma_ahorro = 0;			

								foreach ($result_pagos as $row_pagos) {
									//Aquí deben de desglosarse también los pagos**

									$pago = ($row_pagos['PI_REC'] == 0 ? $row_pagos['PI_PAGO'] : 0);

									$sql_desglosado = "SELECT * FROM
														(
															(SELECT PD_FECHA as FECHA,
																	PD_MONTO as MONTO,
																	'D' as DoR,
																	'0' as AD_ID,
																	PD_ID as ID_D
															FROM PAGOS_DESGLOSADOS
															WHERE PI_ID = ?)
															UNION
															(SELECT PR_FECHA as FECHA,
																	PR_MONTO as MONTO,
																	'R' as DoR,
																	AD_ID,
																	PR_ID as ID_D
															FROM PAGOS_RECUPERADOS
															WHERE PI_ID = ?))
														PAGOS
														ORDER BY FECHA ASC";
									$values_desglosado = array($row_pagos['PI_ID'],
															   $row_pagos['PI_ID']);
									$consulta_desglosado = $db->prepare($sql_desglosado);	

									try {
										
										$consulta_desglosado->execute($values_desglosado);
										$result_desglosado = $consulta_desglosado->fetchAll(PDO::FETCH_ASSOC);

										$hover_pago = "";
										$pago = 0;
										foreach ($result_desglosado as $row_desglosado) {
											$hover_pago .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";

											$pago += ($row_desglosado['DoR'] == 'R' ? 0 : $row_desglosado['MONTO']);

										}

									} catch (PDOException $e) {
										die($e->getMessage());
									}

									$color = ($row_pagos['PI_PAGO'] > 0 ? ($row_pagos['PI_PAGO'] == $row_pagos['PI_MONTO'] ? 'text-success' : 'text-warning') : '');

									$color = ($row_pagos['PI_REC'] == 2 ? 'text-info' : $color);


									$json['pagos'] .= '<td align="center"><span class="pop-hover '.$color.'" data-html="true" data-content="'.$hover_pago.'">'.$pago.'</span></td>';
									$row_ahorro .= '<td align="center">'.$row_pagos['PI_AHORRO'].'</td>';
									$total_abonado += $pago;
									$totales_abonado += $pago;
									$suma_ahorro += $row_pagos['PI_AHORRO'];
									$ahorros_arr[$p] += $row_pagos['PI_AHORRO'];
									$totales_ahorrado += $row_pagos['PI_AHORRO'];
									$pagos_arr[$p] += $pago;

									if($count_pagos < 12 && $p == $count_pagos) {
										$json['pagos'] .= '<td align="center">-</td>';
										$row_ahorro .= '<td align="center">-</td>';

										if($p == 10) {
											$json['pagos'] .= '<td align="center">-</td>';
											$row_ahorro .= '<td align="center">-</td>';
										}
									}

									$p++;
								}

								$row_ahorro .= '</tr>';	


							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Consulta los recuperados
							$sql_rec = "SELECT * FROM PAGOS_RECUPERADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_rec = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_rec = $db->prepare($sql_rec);	
							try {
								$consulta_rec->execute($values_rec);
								$result_rec = $consulta_rec->fetchAll(PDO::FETCH_ASSOC);
								$hover_rec = "";
								$recuperados = 0;
								foreach ($result_rec as $row_rec) {
									$hover_rec .= "<span class='".($row_rec['AD_ID'] > 0 ? 'text-purple' : '')."'>".date("d/m/Y",strtotime($row_rec["PR_FECHA"]))." - ".$row_rec['PR_MONTO']."<br>";
									$recuperados += $row_rec['PR_MONTO'];
									$totales_recuperado += $row_rec['PR_MONTO'];
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Deuda REAL
							$sql_deuda = "SELECT SUM(PI_PENDIENTE) as deuda
										  FROM PAGOS_INDIVIDUALES 
										  WHERE PER_ID = ? 
										  AND GRU_ID = ?
										  AND PI_FECHA < CURRENT_DATE";
							$values_deuda = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_deuda = $db->prepare($sql_deuda);	
							try {
								$consulta_deuda->execute($values_deuda);
								$row_deuda = $consulta_deuda->fetch(PDO::FETCH_ASSOC);
							} catch (PDOException $e) {
								die($e->getMessage());
							}	

							//Consulta Ahorro desglosado
							$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS 
									   WHERE PER_ID = ? 
									   AND GRU_ID = ?";
							$values_ad = array($row_personas['PER_ID'],
											   $row['GRU_ID']);	
							$consulta_ad = $db->prepare($sql_ad);	
							try {
								$consulta_ad->execute($values_ad);
								$result_ad = $consulta_ad->fetchAll(PDO::FETCH_ASSOC);
								$hover_ad = "";
								foreach ($result_ad as $row_ad) {
									$hover_ad .= "<span class='".($row_ad['AD_VALIDO'] == 0 ? 'crossed' : '')."'>".date("d/m/Y",strtotime($row_ad["AD_FECHA"]))." - ".$row_ad['AD_CANTIDAD']."</span><br>";
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							$totales_deudas += $row_deuda['deuda'];

							$inpt_pago = '<input class="form-control" id="pago_'.$row_personas['PER_ID'].'" name="pago['.$row_personas['PER_ID'].']">';
							$inpt_ahorro = '<input class="form-control" id="ahorro_'.$row_personas['PER_ID'].'" name="ahorro['.$row_personas['PER_ID'].']">';
							$trans_ahorro = '<span class="fa-stack pop-hover transferir-ahorro" data-content="Transferir Ahorro" data-per="'.$row_personas['PER_ID'].'" data-gru="'.$row['GRU_ID'].'">
													  <i class="fa fa-circle fa-stack-2x"></i>
													  <i class="fa fa-dollar fa-stack-1x fa-inverse"></i>
													</span>';

							$json['pagos'] .= '<td align="center" rowspan="2">'.$total_abonado.'</td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_pago).'
											   </td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_ahorro).'
											   </td>
											   <td align="center" rowspan="2">
											   		<span class="pop-hover" data-html="true" data-content="'.$hover_ad.'">'
											   			.$suma_ahorro.
											   		'</span><br>
											   		'.$this->printLink($module, "cambios", $trans_ahorro).'
											   	</td>
											   <td align="center" rowspan="2"><span class="pop-hover" data-html="true" data-content="'.$hover_rec.'">'.$recuperados.'</span></td>
											   <td align="center" rowspan="2">'.$row_deuda['deuda'].'</td>';


							$json['pagos'] .= '</tr>';

							$json['pagos'] .= $row_ahorro;

						}

						$select .='</div>';


				 	} catch (PDOException $e) {
				 		die($e->getMessage());
				 	}		 	


					$json['pagos'] .= '					</tbody>
														<tfoot>
															<tr>
																<td>Total de Pagos</td>
																<td align="center">'.$pagos_arr[0].'</td>
																<td></td>';
																
					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$pagos_arr[$i].'</td>';
					}

					$btn_guardar = '<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-success"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button>
													</a>';
					$btn_autocomplete = '<a class="autocomplete" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-info"><i class="fa fa-sort-numeric-desc"></i>Autocompletar</button>
													</a>';
					$btn_reload = '<a class="reload" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-light-grey"><i class="fa fa-refresh"></i>Recargar Grupo</button>
													</a>';																	

					$json['pagos'].=							'<td align="center">'.$totales_abonado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">'.$totales_recuperado.'</td>
																<td align="center">'.$totales_deudas.'</td>
															</tr>
															<tr>
																<td>Total de Ahorros</td>
																<td align="center">'.$ahorros_arr[0].'</td>
																<td></td>';

					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$ahorros_arr[$i].'</td>';
					}


					$json['pagos'].=				'			<td align="center">'.$totales_ahorrado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">-</td>
																<td align="center">-</td>
															</tr>
														</tfoot>
													</table>
													'.$this->printLink($module, "cambios", $btn_guardar).'
													'.$this->printLink($module, "cambios", $btn_autocomplete).'
													'.$this->printLink($module, "cambios", $btn_reload).'
													'.$select.'
												</div>
											</div>
											</form>
										</div>';								


					}


				} else {
					$json['pagos'] = "<h1>No se encontraron grupos</h1>";
				}


			} catch (PDOException $e) {
				die($e->getMessage());
			}

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

	function fillPagosDesglosados() {
		$sql = "SELECT * FROM PAGOS_INDIVIDUALES
				WHERE PI_PAGO > 0";
		$db = $this->_conexion;
		$consulta = $db->prepare($sql);

		try {
			$consulta->execute();

			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {

				$sql_ind = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
														   PD_MONTO,
														   PD_AHORRO,
														   PER_ID,
														   GRU_ID,
														   PI_ID)
						  	VALUES (?, ?, ?, ?, ?, ? )";
				$values_ind = array($row['PI_FECHA_REG'],
									$row['PI_PAGO'],
									$row['PI_AHORRO'],
									$row['PER_ID'],
									$row['GRU_ID'],
									$row['PI_ID']);		
				$consulta_ind = $db->prepare($sql_ind);	
				$consulta_ind->execute($values_ind);	

			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		echo "LISTO!";


		//Query de ALejandro
		$query = "SELECT 	CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
							PER_TELEFONO,
							PER_CELULAR,
							ACTIVIDADES_ECONOMICAS.ACT_NOMBRE,
							SISTEMA_USUARIO.SIU_NOMBRE,
							PENDIENTE,
							FLOOR (PENDIENTE / PI_MONTO) as FALLOS
					FROM PERSONAS
					LEFT JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
					LEFT JOIN 
						(SELECT PER_ID, MAX(GRU_ID) as GRU_ID 
						 FROM PERSONAS_GRUPOS 
						 GROUP BY PER_ID) 
						PG ON PG.PER_ID = PERSONAS.PER_ID
					LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = PG.GRU_ID
					LEFT JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					LEFT JOIN 
						(SELECT PER_ID, SUM(PI_PENDIENTE) as PENDIENTE, PI_MONTO
						 FROM PAGOS_INDIVIDUALES 
						 WHERE PI_FECHA < CURRENT_DATE 
						 AND PI_PENDIENTE > 0 
						 GROUP BY PER_ID) 
						PI ON PI.PER_ID = PERSONAS.PER_ID
					WHERE PERSONAS.ACT_ID = 1";


		//Query todos los clientes
		$query = "SELECT 	CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
							PER_TELEFONO,
							PER_CELULAR,
							PG.GRU_ID
					FROM PERSONAS
					LEFT JOIN 
						(SELECT PER_ID, MAX(GRU_ID) as GRU_ID 
						 FROM PERSONAS_GRUPOS 
						 GROUP BY PER_ID) 
						PG ON PG.PER_ID = PERSONAS.PER_ID";			



		//Query de Edgar de Pagos
		$query2 = "SELECT * FROM
						(
							(SELECT GRUPOS.GRU_ID as GRUPO,
								  CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as NOMBRE,
								  PD_FECHA AS FECHA,
								  PD_MONTO AS PAGO,
								  'PAGO' as TIPO,
								  SIU_NOMBRE as PROMOTOR,
								  CONCAT ('G', (LPAD(GRUPOS.GRU_ID, 5, '0')), '-' ,(LPAD(PERSONAS.PER_ID, 5, '0'))) as CUENTA
							FROM PAGOS_DESGLOSADOS
							LEFT JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_DESGLOSADOS.PER_ID
							LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_DESGLOSADOS.GRU_ID
							LEFT JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
							WHERE PD_FECHA >= '2017-08-01'
							AND PD_FECHA <= CURRENT_DATE)
						UNION
							(SELECT GRUPOS.GRU_ID as GRUPO,
								  CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as NOMBRE,
								  PR_FECHA AS FECHA,
								  PR_MONTO AS PAGO,
								  'RECUPERADO' as TIPO,
								  SIU_NOMBRE as PROMOTOR,
								  CONCAT ('G', (LPAD(GRUPOS.GRU_ID, 5, '0')), '-' ,(LPAD(PERSONAS.PER_ID, 5, '0'))) as CUENTA
							FROM PAGOS_RECUPERADOS
							LEFT JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_RECUPERADOS.PER_ID
							LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_RECUPERADOS.GRU_ID
							LEFT JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
							WHERE PR_FECHA >= '2017-08-01'
							AND PR_FECHA <= CURRENT_DATE)
						) PAGOS
					ORDER BY FECHA ASC, GRUPO ASC";		


		//Query de Pagos a futuro
		$query3 = "SELECT * FROM 
						(

							(SELECT SIU_NOMBRE,
								  SUM(PI_PENDIENTE) as CANTIDAD
						   FROM PAGOS_INDIVIDUALES
						   LEFT JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_INDIVIDUALES.PER_ID
						   LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_INDIVIDUALES.GRU_ID
						   LEFT JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
						   WHERE PI_FECHA > CURRENT_DATE
						   GROUP BY SISTEMA_USUARIO.SIU_ID)

						UNION

							(SELECT SIU_NOMBRE,
									SUM(TPI_MONTO) as CANTIDAD
							FROM TABLA_PAGOS_IND
							LEFT JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.CRE_ID = TABLA_PAGOS_IND.CRE_ID
							LEFT JOIN PERSONAS ON PERSONAS.PER_ID = CREDITO_INDIVIDUAL.PER_ID
						   	LEFT JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
						   	WHERE TPI_FECHA > CURRENT_DATE
						   	GROUP BY SISTEMA_USUARIO.SIU_ID)

				   ) PAGOS_FUT";


		$query4 = "SELECT GRU_ID,
						  SUM(PI_PENDIENTE) as CANTIDAD
				   FROM PAGOS_INDIVIDUALES
				   WHERE PI_FECHA > CURRENT_DATE
				   GROUP BY GRU_ID";
		$query5 = "SELECT CRE_ID,
							SUM(TPI_FALTANTE) as CANTIDAD
					FROM TABLA_PAGOS_IND
				   	WHERE TPI_FECHA > CURRENT_DATE
				   	GROUP BY CRE_ID";	

		//Query Armando
		$query = "SELECT 	CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as NOMBRE,
							PER_MUNICIPIO as MUNICIPIO,
							ACTIVIDADES_ECONOMICAS.ACT_NOMBRE as ACTIVIDAD_ECONOMICA,
							PERSONAS_GRUPOS.GRU_ID as GRUPO,
							IF(GRU_RECREDITO > 0,'RECREDITO','NUEVO') as TIPO_CREDITO,
							SISTEMA_USUARIO.SIU_NOMBRE as PROMOTOR,
							MONTO_INDIVIDUAL as CREDITO,
							PENDIENTE,
							FLOOR (PENDIENTE / PI_MONTO) as FALLOS
					FROM PERSONAS
					LEFT JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
					LEFT JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
					LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					LEFT JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					LEFT JOIN 
						(SELECT PER_ID, SUM(PI_PENDIENTE) as PENDIENTE, PI_MONTO, GRU_ID
						 FROM PAGOS_INDIVIDUALES 
						 WHERE PI_FECHA < CURRENT_DATE 
						 AND PI_PENDIENTE > 0 
						 GROUP BY PER_ID, GRU_ID) 
						PI ON (PI.PER_ID = PERSONAS.PER_ID AND PI.GRU_ID = PERSONAS_GRUPOS.GRU_ID)
					WHERE PERSONAS.ACT_ID = 1";		   		   		   				



	}

	function savePagos() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "El Grupo se guardó con éxito.";
		$msg_extra = "";

		$db = $this->_conexion;
		$db->beginTransaction();
		$pago_efectuado = 0;
		$total_ahorro = 0;

		//Consulta los Pagos Individuales correspondientes a esa semana
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

		$sql = "SELECT * FROM PAGOS_INDIVIDUALES
				WHERE GRU_ID = ?
				AND PI_FECHA >= ?
				AND PI_FECHA <= ?";

		$values = array($_POST['grupo'],
						$fecha_inicio,
						$fecha_fin);
		$consulta = $db->prepare($sql);

		try {
			
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if($consulta->rowCount()) {
				foreach ($result as $row) {

					$pendiente = $row['PI_PENDIENTE'];
					$per_id = $row['PER_ID'];
					$pago = $_POST['pago'][$per_id];

					$ahorro_registrado = false;

					//Aún queda pago pendiente
					if($row['PI_PENDIENTE'] > 0 && $pago > 0 && $pago != '') {
						$deuda = $pago - $pendiente;
						$ahorro = $row['PI_AHORRO'] + $_POST['ahorro'][$per_id];

						if($deuda == 0 || $deuda > 0) {
							$pago_sql = $row['PI_MONTO'];
							$pendiente = 0;
							$pago = $deuda;
						} else if($deuda < 0) {
							$pago_sql = $row['PI_PAGO'] + $pago;
							$pendiente = abs($deuda);
							$pago = 0;
						}


						//Hace UPDATE del Pago Individual
						$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																  PI_AHORRO = ?,
																  PI_PENDIENTE = ?,
																  PI_FECHA_REG = ?
									WHERE PI_ID = ?";

						$values_pi = array($pago_sql,
											$ahorro,
											$pendiente,
											date("Y-m-d"),
											$row['PI_ID']);

						$consulta_pi = $db->prepare($sql_pi);
						try{
							$consulta_pi->execute($values_pi);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

						//Registra el pago
						$monto_pd = $_POST['pago'][$per_id] - $pago;
						$sql_pd = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
																  PD_MONTO,
																  PD_AHORRO,
																  PER_ID,
																  GRU_ID,
																  PI_ID)
								  	VALUES ( ?, ?, ?, ?, ?, ? )";
						$values_pd = array(date("Y-m-d"),
										   $monto_pd,
										   $_POST['ahorro'][$per_id],
										   $per_id,
										   $_POST['grupo'],
										   $row['PI_ID']);		
						$consulta_pd = $db->prepare($sql_pd);	
						try{
							$consulta_pd->execute($values_pd);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

					} 

					if(!$ahorro_registrado) {
						$ahorro = $row['PI_AHORRO'] + $_POST['ahorro'][$per_id];

						//Hace UPDATE del Pago Individual
						$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_AHORRO = ?
									WHERE PI_ID = ?";

						$values_pi = array($ahorro,
										   $row['PI_ID']);

						$consulta_pi = $db->prepare($sql_pi);
						try{
							$consulta_pi->execute($values_pi);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

						//Registra el Ahorro
						if($_POST['ahorro'][$per_id] > 0 && $_POST['ahorro'][$per_id] != '') {
							$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																		PER_ID,
																	  	GRU_ID,
																	  	PI_ID,
																	  	AD_CANTIDAD)
									  	VALUES ( ?, ?, ?, ?, ? )";
							$values_ad = array(date("Y-m-d"),
											   $per_id,
											   $_POST['grupo'],
											   $row['PI_ID'],
											   $_POST['ahorro'][$per_id]);		
							$consulta_ad = $db->prepare($sql_ad);	
							try{
								$consulta_ad->execute($values_ad);
								$ahorro_registrado = true;
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}
						}
					}

					//Si la deuda está saldada y aún hay saldo disponible, se va a deudas pasadas, PAGOS RECUPERADOS
					if($pago > 0) {

						$sql_pendientes = "SELECT * FROM PAGOS_INDIVIDUALES
										   WHERE PI_PENDIENTE > 0
										   AND PER_ID = ?
										   AND GRU_ID = ?
										   ORDER BY PI_FECHA ASC";
						$values_pendientes = array($row['PER_ID'],
												   $_POST['grupo']);
						$consulta_pendientes = $db->prepare($sql_pendientes);	

						try{
							$consulta_pendientes->execute($values_pendientes);
							$result_pendientes = $consulta_pendientes->fetchAll(PDO::FETCH_ASSOC);

							$n = 0;
							if(count($result_pendientes)) {
								while ($pago > 0 && count($result_pendientes) > $n) {

									$row_pendiente = $result_pendientes[$n];
									$pendiente = $row_pendiente['PI_PENDIENTE'];

									$deuda = $pago - $pendiente;
									if($deuda == 0 || $deuda > 0) {
										$pago_sql = $row_pendiente['PI_MONTO'];
										$pendiente = 0;
										$nuevo_pago = $deuda;
									} else if($deuda < 0) {
										$pago_sql = $row_pendiente['PI_PAGO'] + $pago;
										$pendiente = abs($deuda);
										$nuevo_pago = 0;
									}

									//Revisa si es Fecha Recuperado o Fecha Desglosado
									//Si es menor a la fecha actual = Recuperado
									//Si es mayor a la fecha actual = Desglosado
									$fecha_actual = date('Y-m-d');
									$rec = (strtotime($fecha_actual) < strtotime($row_pendiente['PI_FECHA']) ? 0 : 1);

									if(!$ahorro_registrado) {
										$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																				 PI_AHORRO = ?,
																				 PI_PENDIENTE = ?,
																				 PI_FECHA_REG = ?,
																				 PI_REC = ?
													WHERE PI_ID = ?";

										$ahorro = $row_pendiente['PI_AHORRO'] + $_POST['ahorro'][$per_id];			

										$values_pi = array($pago_sql,
														   $ahorro,
														   $pendiente,
														   date("Y-m-d"),
														   $rec,
														   $row_pendiente['PI_ID']);
									} else {
										//Hace UPDATE del Pago Individual
										$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																				 PI_PENDIENTE = ?,
																				 PI_FECHA_REG = ?,
																				 PI_REC = ?
													WHERE PI_ID = ?";

										$values_pi = array($pago_sql,
														   $pendiente,
														   date("Y-m-d"),
														   $rec,
														   $row_pendiente['PI_ID']);
									}

									$consulta_pi = $db->prepare($sql_pi);
									try{
										$consulta_pi->execute($values_pi);
									}catch(PDOException $e){
										$db->rollBack();
										die($e->getMessage());
										$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
									}

									//Registra el pago
									$monto_pd = $pago - $nuevo_pago;

									//Revisa si es Fecha Recuperado o Fecha Desglosado
									//Si es menor a la fecha actual = Recuperado
									//Si es mayor a la fecha actual = Desglosado
									if($rec == 1) {
										$sql_pd = "INSERT INTO PAGOS_RECUPERADOS (PR_FECHA,
																				  PR_MONTO,
																				  PER_ID,
																				  GRU_ID,
																				  PI_ID)
												  	VALUES ( ?, ?, ?, ?, ? )";
										$values_pd = array(date("Y-m-d"),
														   $monto_pd,
														   $per_id,
														   $_POST['grupo'],
														   $row_pendiente['PI_ID']);		
										$consulta_pd = $db->prepare($sql_pd);	
										try{
											$consulta_pd->execute($values_pd);
										}catch(PDOException $e){
											$db->rollBack();
											die($e->getMessage());
											$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
										}
									} else {
										$sql_pd = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
																				  PD_MONTO,
																				  PD_AHORRO,
																				  PER_ID,
																				  GRU_ID,
																				  PI_ID)
												  	VALUES ( ?, ?, ?, ?, ?, ? )";
										$values_pd = array(date("Y-m-d"),
														   $monto_pd,
														   $_POST['ahorro'][$per_id],
														   $per_id,
														   $_POST['grupo'],
														   $row_pendiente['PI_ID']);		
										$consulta_pd = $db->prepare($sql_pd);	
										try{
											$consulta_pd->execute($values_pd);
										}catch(PDOException $e){
											$db->rollBack();
											die($e->getMessage());
											$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
										}
									}

									//Registra el Ahorro
									if(!$ahorro_registrado) {
										if($_POST['ahorro'][$per_id] > 0 && $_POST['ahorro'][$per_id] != '') {
											$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																						PER_ID,
																					  	GRU_ID,
																					  	PI_ID,
																					  	AD_CANTIDAD)
													  	VALUES ( ?, ?, ?, ?, ? )";
											$values_ad = array(date("Y-m-d"),
															   $per_id,
															   $_POST['grupo'],
															   $row_pendiente['PI_ID'],
															   $_POST['ahorro'][$per_id]);		
											$consulta_ad = $db->prepare($sql_ad);	
											try{
												$consulta_ad->execute($values_ad);
												$ahorro_registrado = true;
											}catch(PDOException $e){
												$db->rollBack();
												die($e->getMessage());
												$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
											}
										}
									}

									$pago = $nuevo_pago;
									$n++;

								}
							} else {

								$sql_cliente = "SELECT PER_NOMBRE FROM PERSONAS
												WHERE PER_ID = ?";
								$values_cliente = array($per_id);		
								$consulta_cliente = $db->prepare($sql_cliente);	
								$consulta_cliente->execute($values_cliente);	

								$row_cliente = $consulta_cliente->fetch(PDO::FETCH_ASSOC);

								$msg_extra .= " El Cliente ".$row_cliente['PER_NOMBRE']." no tiene deuda y ya pagó esta semana.";
							}

						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}	

					}

				}

			} else {

				foreach ($_POST['pago'] as $per_id => $pago) {

					$ahorro_registrado = false;

					if($pago > 0 && $pago != '') {
						$sql_pendientes = "SELECT * FROM PAGOS_INDIVIDUALES
										   WHERE PI_PENDIENTE > 0
										   AND PER_ID = ?
										   AND GRU_ID = ?
										   ORDER BY PI_FECHA ASC";
						$values_pendientes = array($per_id,
												   $_POST['grupo']);
						$consulta_pendientes = $db->prepare($sql_pendientes);	

						try{
							$consulta_pendientes->execute($values_pendientes);
							$result_pendientes = $consulta_pendientes->fetchAll(PDO::FETCH_ASSOC);

							$n = 0;
							if(count($result_pendientes)) {
								while ($pago > 0 && count($result_pendientes) > $n) {

									$row_pendiente = $result_pendientes[$n];
									$pendiente = $row_pendiente['PI_PENDIENTE'];

									$deuda = $pago - $pendiente;
									if($deuda == 0 || $deuda > 0) {
										$pago_sql = $row_pendiente['PI_MONTO'];
										$pendiente = 0;
										$nuevo_pago = $deuda;
									} else if($deuda < 0) {
										$pago_sql = $row_pendiente['PI_PAGO'] + $pago;
										$pendiente = abs($deuda);
										$nuevo_pago = 0;
									}

									//Revisa si es Fecha Recuperado o Fecha Desglosado
									//Si es menor a la fecha actual = Recuperado
									//Si es mayor a la fecha actual = Desglosado
									$fecha_actual = date('Y-m-d');
									$rec = (strtotime($fecha_actual) < strtotime($row_pendiente['PI_FECHA']) ? 0 : 1);

									if(!$ahorro_registrado) {
										$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																				 PI_AHORRO = ?,
																				 PI_PENDIENTE = ?,
																				 PI_FECHA_REG = ?,
																				 PI_REC = ?
													WHERE PI_ID = ?";

										$ahorro = $row_pendiente['PI_AHORRO'] + $_POST['ahorro'][$per_id];			

										$values_pi = array($pago_sql,
														   $ahorro,
														   $pendiente,
														   date("Y-m-d"),
														   $rec,
														   $row_pendiente['PI_ID']);
									} else {
										//Hace UPDATE del Pago Individual
										$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																				 PI_PENDIENTE = ?,
																				 PI_FECHA_REG = ?,
																				 PI_REC = ?
													WHERE PI_ID = ?";

										$values_pi = array($pago_sql,
														   $pendiente,
														   date("Y-m-d"),
														   $rec,
														   $row_pendiente['PI_ID']);
									}

									$consulta_pi = $db->prepare($sql_pi);
									try{
										$consulta_pi->execute($values_pi);
									}catch(PDOException $e){
										$db->rollBack();
										die($e->getMessage().$row_pendiente['PI_ID']);
										$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
									}

									//Registra el pago
									$monto_pd = $pago - $nuevo_pago;
									//Revisa si es Fecha Recuperado o Fecha Desglosado
									//Si es menor a la fecha actual = Recuperado
									//Si es mayor a la fecha actual = Desglosado
									if($rec == 1) {
										$sql_pd = "INSERT INTO PAGOS_RECUPERADOS (PR_FECHA,
																				  PR_MONTO,
																				  PER_ID,
																				  GRU_ID,
																				  PI_ID)
												  	VALUES ( ?, ?, ?, ?, ? )";
										$values_pd = array(date("Y-m-d"),
														   $monto_pd,
														   $per_id,
														   $_POST['grupo'],
														   $row_pendiente['PI_ID']);		
										$consulta_pd = $db->prepare($sql_pd);	
										try{
											$consulta_pd->execute($values_pd);
										}catch(PDOException $e){
											$db->rollBack();
											die($e->getMessage());
											$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
										}
									} else {
										$sql_pd = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
																				  PD_MONTO,
																				  PD_AHORRO,
																				  PER_ID,
																				  GRU_ID,
																				  PI_ID)
												  	VALUES ( ?, ?, ?, ?, ?, ? )";
										$values_pd = array(date("Y-m-d"),
														   $monto_pd,
														   $_POST['ahorro'][$per_id],
														   $per_id,
														   $_POST['grupo'],
														   $row_pendiente['PI_ID']);		
										$consulta_pd = $db->prepare($sql_pd);	
										try{
											$consulta_pd->execute($values_pd);
										}catch(PDOException $e){
											$db->rollBack();
											die($e->getMessage().$row_pendiente['PI_ID']);
											$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
										}
									}

									//Registra el Ahorro
									if($_POST['ahorro'][$per_id] > 0 && $_POST['ahorro'][$per_id] != '') {
										$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																					PER_ID,
																				  	GRU_ID,
																				  	PI_ID,
																				  	AD_CANTIDAD)
												  	VALUES ( ?, ?, ?, ?, ? )";
										$values_ad = array(date("Y-m-d"),
														   $per_id,
														   $_POST['grupo'],
														   $row_pendiente['PI_ID'],
														   $_POST['ahorro'][$per_id]);		
										$consulta_ad = $db->prepare($sql_ad);	
										try{
											$consulta_ad->execute($values_ad);
											$ahorro_registrado = true;
										}catch(PDOException $e){
											$db->rollBack();
											die($e->getMessage());
											$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
										}
									}

									$pago = $nuevo_pago;
									$n++;

								}
							} else {
								$sql_cliente = "SELECT PER_NOMBRE FROM PERSONAS
												WHERE PER_ID = ?";
								$values_cliente = array($per_id);		
								$consulta_cliente = $db->prepare($sql_cliente);	
								$consulta_cliente->execute($values_cliente);	

								$row_cliente = $consulta_cliente->fetch(PDO::FETCH_ASSOC);

								$msg_extra .= " El Cliente ".$row_cliente['PER_NOMBRE']." no tiene deuda y ya pagó esta semana.";
							}

						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}
					}

					if(!$ahorro_registrado) {

						//Selecciona el pago individual más cercano a la fecha actual
						$sql_pi_a = "SELECT * FROM PAGOS_INDIVIDUALES
								     WHERE PI_FECHA < CURRENT_DATE
								     AND PER_ID = ?
								     AND GRU_ID = ?
								     ORDER BY PI_FECHA DESC";
						$values_pi_a = array($per_id,
											 $_POST['grupo']);
						$consulta_pi_a = $db->prepare($sql_pi_a);	
						$consulta_pi_a->execute($values_pi_a);	

						$row_pi_a = $consulta_pi_a->fetch(PDO::FETCH_ASSOC);


						$ahorro = $row_pi_a['PI_AHORRO'] + $_POST['ahorro'][$per_id];

						//Hace UPDATE del Pago Individual
						$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_AHORRO = ?
									WHERE PI_ID = ?";

						$values_pi = array($ahorro,
										   $row_pi_a['PI_ID']);

						$consulta_pi = $db->prepare($sql_pi);
						try{
							$consulta_pi->execute($values_pi);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

						//Registra el Ahorro
						if($_POST['ahorro'][$per_id] > 0 && $_POST['ahorro'][$per_id] != '') {
							$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																		PER_ID,
																	  	GRU_ID,
																	  	PI_ID,
																	  	AD_CANTIDAD)
									  	VALUES ( ?, ?, ?, ?, ? )";
							$values_ad = array(date("Y-m-d"),
											   $per_id,
											   $_POST['grupo'],
											   $row_pi_a['PI_ID'],
											   $_POST['ahorro'][$per_id]);		
							$consulta_ad = $db->prepare($sql_ad);	
							try{
								$consulta_ad->execute($values_ad);
								$ahorro_registrado = true;
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}
						}
					}
				}
			}


		} catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage().$sql_pendientes);
			$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
		}

		$json['msg'] .= $msg_extra;

		$db->commit();

		echo json_encode($json);
	}

	function reloadGroup() {
		$json = array();
		$json['pagos'] = "";

		global $module;

		if(isset($_POST['grupo'])) {

			//Obtiene todos los grupos (aunque estén inactivos) del Promotor
			$db = $this->_conexion;
			$sql_grupos = "SELECT GRU_ID,
								  GRU_FECHA_ENTREGA,
								  GRU_FECHA_FINAL,
								  PAGO_SEMANAL,
								  GRU_MONTO_TOTAL,
								  GRU_RECREDITO,
								  GRU_VIGENTE,
								  GRU_PLAZO
							FROM GRUPOS
							WHERE GRU_ID = ?
							ORDER BY GRU_ID DESC";

			$values_grupos = array($_POST['grupo']);
			$consulta_grupos = $db->prepare($sql_grupos);

			try {
				$consulta_grupos->execute($values_grupos);

				if ($consulta_grupos->rowCount()) {
					//$result_grupos = $consulta_grupos->fetchAll(PDO::FETCH_ASSOC);
					$row = $consulta_grupos->fetch(PDO::FETCH_ASSOC);

					//foreach ($result_grupos as $row) {

						$sql_tabla_pagos = "SELECT TP_FECHA
											FROM TABLA_PAGOS
											WHERE GRU_ID = ?
											ORDER BY TP_FECHA ASC";
						$value_tabla_pagos = array($row['GRU_ID']);	
						$consulta_tabla_pagos = $db->prepare($sql_tabla_pagos);	
						$consulta_tabla_pagos->execute($value_tabla_pagos);	
						$result_tabla_pagos = $consulta_tabla_pagos->fetchAll(PDO::FETCH_ASSOC);


						
						$json['pagos'] .= '<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha de Apertura:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
																<td align="center"><b>Fecha de Vencimiento:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_FINAL"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Crédito Grupal Autorizado: </b> $'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
																<td align="center"><b>Pago Semanal Grupal: </b> $'.number_format($row['PAGO_SEMANAL'], 2).'</td>
															</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>Nombre</th>
																<th>Pago Semanal</th>
																<th></th>';
						$num_pagos = 0;										

						foreach ($result_tabla_pagos as $row_tabla_pagos) {
							$num_pagos++;
							$json['pagos'] .= '<th><span class="pop-hover" data-content="'.(isset($row_tabla_pagos['TP_FECHA']) ? date("d/m/Y",strtotime($row_tabla_pagos["TP_FECHA"])) : '-').'">'.$num_pagos.'</span></th>';
						}

						if(count($result_tabla_pagos) < 12) {
							$i_x = count($result_tabla_pagos) +1;
							for ($i=$i_x; $i < ($row['GRU_PLAZO'] + 1); $i++) { 
								$json['pagos'] .= '<th><span class="pop-hover" data-content="-">'.$i.'</span></th>';
							}
						}

						$json['pagos'].=						'<th>Total Abonado</th>
																<th>Pago Semanal</th>
																<th>Ahorro</th>
																<th>Suma Ahorro</th>
																<th>Total Recup.</th>
																<th>Deuda Real</th>
															</tr>
														</thead>
														<tbody>';


					$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
											PERSONAS.PER_ID,
											PAGO_SEMANAL_IND,
											GRU_ID
								 	FROM PERSONAS_GRUPOS
								 	JOIN PERSONAS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
								 	WHERE GRU_ID = ?";
					$values_personas = array($row['GRU_ID']);
					$consulta_personas = $db->prepare($sql_personas);
					$pagos_arr = array();
					$ahorros_arr = array();
					
					$pagos_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);
					$ahorros_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);

					$totales_abonado = 0;
					$totales_ahorrado = 0;
					$totales_recuperado = 0;
					$totales_deudas = 0;

					$select = '<div class="cont-sel-'.$row['GRU_ID'].' display-none">';

					try {
						
						$consulta_personas->execute($values_personas);
						$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
						$ahorros_arr[0] = '-';

						foreach ($result_personas as $row_personas) {
							$select .= '<option value="'.$row_personas['PER_ID'].'">'.$row_personas['PER_NOMBRE'].'</option>';
							$lnk_desglosados = 'pagos-desglosados.php?per_id='.$row_personas['PER_ID'].'&gru_id='.$row['GRU_ID'];
							$json['pagos'] .= '<tr>
												<td align="center" rowspan="2"><a href="'.$this->printLink($module, "cambios", $lnk_desglosados).'" target="_blank">'.$row_personas['PER_NOMBRE'].'</a></td>
												<td align="center" rowspan="2">'
													.$row_personas['PAGO_SEMANAL_IND'].'
													<input type="hidden" class="form-control pago_semanal_ind" id="pago_semanal_ind_'.$row_personas['PER_ID'].'" value="'.$row_personas['PAGO_SEMANAL_IND'].'" data-id='.$row_personas['PER_ID'].'>
												</td>
												<td align="center">P</td>';

							$pagos_arr[0] += $row_personas['PAGO_SEMANAL_IND'];					

							$sql_pagos = "SELECT * FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  ORDER BY PI_FECHA ASC";
							$values_pagos = array($row_personas['PER_ID'],
												  $row['GRU_ID']);
							$consulta_pagos = $db->prepare($sql_pagos);	

							$p = 1;

							try {
								
								$consulta_pagos->execute($values_pagos);
								$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);
								$count_pagos = $consulta_pagos->rowCount();
								$json['total_pagos'][$row_personas['PER_ID']] = $count_pagos;

								$row_ahorro = '<tr>
												<td>A</td>';

								$total_abonado = 0;	
								$suma_ahorro = 0;			

								foreach ($result_pagos as $row_pagos) {
									//Aquí deben de desglosarse también los pagos**

									$pago = ($row_pagos['PI_REC'] == 0 ? $row_pagos['PI_PAGO'] : 0);

									$sql_desglosado = "SELECT * FROM
														(
															(SELECT PD_FECHA as FECHA,
																	PD_MONTO as MONTO,
																	'D' as DoR,
																	'0' as AD_ID,
																	PD_ID as ID_D
															FROM PAGOS_DESGLOSADOS
															WHERE PI_ID = ?)
															UNION
															(SELECT PR_FECHA as FECHA,
																	PR_MONTO as MONTO,
																	'R' as DoR,
																	AD_ID,
																	PR_ID as ID_D
															FROM PAGOS_RECUPERADOS
															WHERE PI_ID = ?)) 
														PAGOS
														ORDER BY FECHA ASC";
									$values_desglosado = array($row_pagos['PI_ID'],
															   $row_pagos['PI_ID']);
									$consulta_desglosado = $db->prepare($sql_desglosado);	

									try {
										
										$consulta_desglosado->execute($values_desglosado);
										$result_desglosado = $consulta_desglosado->fetchAll(PDO::FETCH_ASSOC);

										$hover_pago = "";
										$pago = 0;
										foreach ($result_desglosado as $row_desglosado) {
											$hover_pago .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";

											$pago += ($row_desglosado['DoR'] == 'R' ? 0 : $row_desglosado['MONTO']);

										}

									} catch (PDOException $e) {
										die($e->getMessage());
									}

									$color = ($row_pagos['PI_PAGO'] > 0 ? ($row_pagos['PI_PAGO'] == $row_pagos['PI_MONTO'] ? 'text-success' : 'text-warning') : '');

									$color = ($row_pagos['PI_REC'] == 2 ? 'text-info' : $color);


									$json['pagos'] .= '<td align="center"><span class="pop-hover '.$color.'" data-html="true" data-content="'.$hover_pago.'">'.$pago.'</span></td>';
									$row_ahorro .= '<td align="center">'.$row_pagos['PI_AHORRO'].'</td>';
									$total_abonado += $pago;
									$totales_abonado += $pago;
									$suma_ahorro += $row_pagos['PI_AHORRO'];
									$ahorros_arr[$p] += $row_pagos['PI_AHORRO'];
									$totales_ahorrado += $row_pagos['PI_AHORRO'];
									$pagos_arr[$p] += $pago;

									if($count_pagos < 12 && $p == $count_pagos) {
										$json['pagos'] .= '<td align="center">-</td>';
										$row_ahorro .= '<td align="center">-</td>';

										if($p == 10) {
											$json['pagos'] .= '<td align="center">-</td>';
											$row_ahorro .= '<td align="center">-</td>';
										}
									}

									$p++;
								}

								$row_ahorro .= '</tr>';	


							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Consulta los recuperados
							$sql_rec = "SELECT * FROM PAGOS_RECUPERADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_rec = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_rec = $db->prepare($sql_rec);	
							try {
								$consulta_rec->execute($values_rec);
								$result_rec = $consulta_rec->fetchAll(PDO::FETCH_ASSOC);
								$hover_rec = "";
								$recuperados = 0;
								foreach ($result_rec as $row_rec) {
									$hover_rec .= date("d/m/Y",strtotime($row_rec["PR_FECHA"]))." - ".$row_rec['PR_MONTO']."<br>";
									$recuperados += $row_rec['PR_MONTO'];
									$totales_recuperado += $row_rec['PR_MONTO'];
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Deuda REAL
							$sql_deuda = "SELECT SUM(PI_PENDIENTE) as deuda
										  FROM PAGOS_INDIVIDUALES 
										  WHERE PER_ID = ? 
										  AND GRU_ID = ?
										  AND PI_FECHA < CURRENT_DATE";
							$values_deuda = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_deuda = $db->prepare($sql_deuda);	
							try {
								$consulta_deuda->execute($values_deuda);
								$row_deuda = $consulta_deuda->fetch(PDO::FETCH_ASSOC);
							} catch (PDOException $e) {
								die($e->getMessage());
							}	

							$totales_deudas += $row_deuda['deuda'];

							//Consulta Ahorro desglosado
							$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS 
									   WHERE PER_ID = ? 
									   AND GRU_ID = ?";
							$values_ad = array($row_personas['PER_ID'],
											   $row['GRU_ID']);	
							$consulta_ad = $db->prepare($sql_ad);	
							try {
								$consulta_ad->execute($values_ad);
								$result_ad = $consulta_ad->fetchAll(PDO::FETCH_ASSOC);
								$hover_ad = "";
								foreach ($result_ad as $row_ad) {
									$hover_ad .= "<span class='".($row_ad['AD_VALIDO'] == 0 ? 'crossed' : '')."'>".date("d/m/Y",strtotime($row_ad["AD_FECHA"]))." - ".$row_ad['AD_CANTIDAD']."</span><br>";
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							$inpt_pago = '<input class="form-control" id="pago_'.$row_personas['PER_ID'].'" name="pago['.$row_personas['PER_ID'].']">';
							$inpt_ahorro = '<input class="form-control" id="ahorro_'.$row_personas['PER_ID'].'" name="ahorro['.$row_personas['PER_ID'].']">';
							$trans_ahorro = '<span class="fa-stack pop-hover transferir-ahorro" data-content="Transferir Ahorro" data-per="'.$row_personas['PER_ID'].'" data-gru="'.$row['GRU_ID'].'">
													  <i class="fa fa-circle fa-stack-2x"></i>
													  <i class="fa fa-dollar fa-stack-1x fa-inverse"></i>
													</span>';


							$json['pagos'] .= '<td align="center" rowspan="2">'.$total_abonado.'</td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_pago).'
											   </td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_ahorro).'
											   </td>
											   <td align="center" rowspan="2">
											   		<span class="pop-hover" data-html="true" data-content="'.$hover_ad.'">'
											   			.$suma_ahorro.
											   		'</span><br>
											   		'.$this->printLink($module, "cambios", $trans_ahorro).'
											   	</td>
											   <td align="center" rowspan="2"><span class="pop-hover" data-html="true" data-content="'.$hover_rec.'">'.$recuperados.'</span></td>
											   <td align="center" rowspan="2">'.$row_deuda['deuda'].'</td>';


							$json['pagos'] .= '</tr>';

							$json['pagos'] .= $row_ahorro;

						}

						$select .='</div>';


				 	} catch (PDOException $e) {
				 		die($e->getMessage());
				 	}		 	


					$json['pagos'] .= '					</tbody>
														<tfoot>
															<tr>
																<td>Total de Pagos</td>
																<td align="center">'.$pagos_arr[0].'</td>
																<td></td>';

					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$pagos_arr[$i].'</td>';
					}

					$btn_guardar = '<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-success"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button>
													</a>';
					$btn_autocomplete = '<a class="autocomplete" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-info"><i class="fa fa-sort-numeric-desc"></i>Autocompletar</button>
													</a>';	
					$btn_reload = '<a class="reload" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-light-grey"><i class="fa fa-refresh"></i>Recargar Grupo</button>
													</a>';																						

					$json['pagos'].=							'<td align="center">'.$totales_abonado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">'.$totales_recuperado.'</td>
																<td align="center">'.$totales_deudas.'</td>
															</tr>
															<tr>
																<td>Total de Ahorros</td>
																<td align="center">'.$ahorros_arr[0].'</td>
																<td></td>';
					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$ahorros_arr[$i].'</td>';
					}


					$json['pagos'].='							<td align="center">'.$totales_ahorrado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">-</td>
																<td align="center">-</td>
															</tr>
														</tfoot>
													</table>
													'.$this->printLink($module, "cambios", $btn_guardar).'
													'.$this->printLink($module, "cambios", $btn_autocomplete).'
													'.$this->printLink($module, "cambios", $btn_reload).'
													'.$select.'
												</div>
											</div>
											</form>
										</div>';										


					//}


				} /* else {
					$json['pagos'] = "<h1>No se encontraron grupos</h1>";
				}*/


			} catch (PDOException $e) {
				die($e->getMessage());
			}

		}

		echo json_encode($json);
	}

	function correctPendientes() {
		
		$sql = "SELECT * FROM PAGOS_INDIVIDUALES
				WHERE PI_PENDIENTE > PI_MONTO";
		$db = $this->_conexion;
		$consulta = $db->prepare($sql);

		try {
			$consulta->execute();

			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {


				$per_id = $row['PER_ID'];
				$gru_id = $row['GRU_ID'];
				$pendiente = $row['PI_PENDIENTE'];

				echo "p_id = ".$per_id." g_id = ".$gru_id." pend = ".$pendiente."<br>";

				$sql_pend = "SELECT * FROM PAGOS_INDIVIDUALES
							 WHERE PI_PAGO = 0
							 AND PI_PENDIENTE = 0
							 AND PER_ID = ?
							 AND GRU_ID = ?
							 ORDER BY PI_FECHA ASC";
				$values_pend = array($per_id,
									 $gru_id);	
				$consulta_pend = $db->prepare($sql_pend);
				
				try {
					$consulta_pend->execute($values_pend);
					$result_pend = $consulta_pend->fetchAll(PDO::FETCH_ASSOC);

					$n = 0;

					echo "n = ".$n." count = ".count($result_pend)."<br>";
					while ($pendiente > 0 && (count($result_pend) > $n)) {
						$row_pend = $result_pend[$n];
						echo "monto = ".$row_pend['PI_MONTO']."<br>";
						if($pendiente >= $row_pend['PI_MONTO']) {
							$sql_up = "UPDATE PAGOS_INDIVIDUALES 
									   SET PI_PENDIENTE = PI_MONTO
				    				   WHERE PI_ID = ? ";

				    		$values_up = array($row_pend['PI_ID']);
				    		$consulta_up = $db->prepare($sql_up);
				    		try {
				    			$consulta_up->execute($values_up);
				    		} catch (PDOException $e) {
				    			die($e->getMessage());
				    		}

				    		$pendiente = $pendiente - $row_pend['PI_MONTO'];
				    		echo "nuevo pendiente = ".$pendiente."<br>";
						}
						$n++;
					}
									 		 	
		 		 } catch (PDOException $e) {
		 		 	die($e->getMessage());
		 		 }	

	 		 	$sql_up2 = "UPDATE PAGOS_INDIVIDUALES 
						   SET PI_PENDIENTE = ?
	    				   WHERE PI_ID = ? ";

	    		$values_up2 = array($pendiente,
	    							$row['PI_ID']);
	    		$consulta_up2 = $db->prepare($sql_up2);
	    		try {
	    			$consulta_up2->execute($values_up2);
	    		} catch (PDOException $e) {
	    			die($e->getMessage());
	    		}

	    		echo '<br>';	

			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		echo "LISTO!";

	}

	function nuevoAhorro() {
		
		$sql = "SELECT * FROM PAGOS_INDIVIDUALES
				WHERE PI_AHORRO > 0";
		$db = $this->_conexion;
		$consulta = $db->prepare($sql);

		try {
			$consulta->execute();

			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {

				$sql_ind = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
															 PER_ID,
															 GRU_ID,
															 PI_ID,
															 AD_CANTIDAD)
						  	VALUES (?, ?, ?, ?, ? )";
				$values_ind = array($row['PI_FECHA_REG'],
									$row['PER_ID'],
									$row['GRU_ID'],
									$row['PI_ID'],
									$row['PI_AHORRO']);		
				$consulta_ind = $db->prepare($sql_ind);	
				$consulta_ind->execute($values_ind);	

			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		echo "LISTO!";

	}

	function transferirAhorro() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Se realizó la transferencia con éxito.";
		$msg_extra = "";

		$db = $this->_conexion;
		$db->beginTransaction();

		$per_id = $_POST['per_id'];
		$gru_id = $_POST['gru_id'];
		$destino = $_POST['destino'];


		//Obtenemos todos los Ahorros
		$sql_ahorros = "SELECT * FROM AHORROS_DESGLOSADOS
						WHERE PER_ID = ?
						AND GRU_ID = ?
						AND AD_VALIDO = 1";
		$values_ahorros = array($per_id,
								$gru_id);
		$consulta_ahorros = $db->prepare($sql_ahorros);	

		try {
			$consulta_ahorros->execute($values_ahorros);
			$result_ahorros = $consulta_ahorros->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result_ahorros as $row_ahorros) {
				
				$pago = $row_ahorros['AD_CANTIDAD'];
				$ahorro_fila = $row_ahorros['AD_CANTIDAD'];
				$cambio_ahorro = false;

				$sql_pendientes = "SELECT * FROM PAGOS_INDIVIDUALES
								   WHERE PI_FECHA < CURRENT_DATE
								   AND PI_PENDIENTE > 0
								   AND PER_ID = ?
								   AND GRU_ID = ?";
				$values_pendientes = array($destino,
										   $gru_id);
				$consulta_pendientes = $db->prepare($sql_pendientes);	

				try{
					$consulta_pendientes->execute($values_pendientes);
					$result_pendientes = $consulta_pendientes->fetchAll(PDO::FETCH_ASSOC);

					$n = 0;
					if(count($result_pendientes)) {
						while ($pago > 0 && count($result_pendientes) > $n) {

							$row_pendiente = $result_pendientes[$n];
							$pendiente = $row_pendiente['PI_PENDIENTE'];

							$deuda = $pago - $pendiente;
							if($deuda == 0 || $deuda > 0) {
								$pago_sql = $row_pendiente['PI_MONTO'];
								$pendiente = 0;
								$nuevo_pago = $deuda;
							} else if($deuda < 0) {
								$pago_sql = $row_pendiente['PI_PAGO'] + $pago;
								$pendiente = abs($deuda);
								$nuevo_pago = 0;
							}

							//Actualiza ese pago individual
							$sql_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																	 PI_PENDIENTE = ?,
																	 PI_FECHA_REG = ?,
																	 PI_REC = 1
										WHERE PI_ID = ?";		

							$values_pi = array($pago_sql,
											   $pendiente,
											   date("Y-m-d"),
											   $row_pendiente['PI_ID']);
							

							$consulta_pi = $db->prepare($sql_pi);
							try{
								$consulta_pi->execute($values_pi);
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}

							//Consulta el PAGO IND correspondente
							$sql_pi3 = "SELECT PI_AHORRO
										FROM PAGOS_INDIVIDUALES
										WHERE PI_ID = ?";
							$values_pi3 = array($row_ahorros['PI_ID']);
							
							$consulta_pi3 = $db->prepare($sql_pi3);
							$consulta_pi3->execute($values_pi3);
							$row_pi3 = $consulta_pi3->fetch(PDO::FETCH_ASSOC);			

							//Actualiza el ahorro del PI que corresponde
							$sql_pi2 = "UPDATE PAGOS_INDIVIDUALES SET PI_AHORRO = ?
										WHERE PI_ID = ?";

							$ahorro = $row_pi3['PI_AHORRO'] - ($pago - $nuevo_pago);			

							$values_pi2 = array($ahorro,
											   $row_ahorros['PI_ID']);
							

							$consulta_pi2 = $db->prepare($sql_pi2);
							try{
								$consulta_pi2->execute($values_pi2);
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}


							//Registra el nuevo ahorro
							$monto_pd = $pago - $nuevo_pago;
							$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																		PER_ID,
																	  	GRU_ID,
																	  	PI_ID,
																	  	AD_CANTIDAD,
																	  	AD_VALIDO)
									  	VALUES ( ?, ?, ?, ?, ?, 0 )";
							$values_ad = array($row_ahorros['AD_FECHA'],
											   $per_id,
											   $gru_id,
											   $row_ahorros['PI_ID'],
											   $monto_pd);		
							$consulta_ad = $db->prepare($sql_ad);	
							try{
								$consulta_ad->execute($values_ad);
								$last_id = $this->last_id();
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}

							//Registra el pago
							$sql_pd = "INSERT INTO PAGOS_RECUPERADOS (PR_FECHA,
																	  PR_MONTO,
																	  PER_ID,
																	  GRU_ID,
																	  PI_ID,
																	  AD_ID)
									  	VALUES ( ?, ?, ?, ?, ?, ? )";
							$values_pd = array(date("Y-m-d"),
											   $monto_pd,
											   $destino,
											   $gru_id,
											   $row_pendiente['PI_ID'],
											   $last_id);		
							$consulta_pd = $db->prepare($sql_pd);	
							try{
								$consulta_pd->execute($values_pd);
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}

							$pago = $nuevo_pago;
							$n++;

						}

						//Eliminamos el registro actual de Ahorro Desglosado
						try{
							$consulta = $db->prepare("DELETE FROM AHORROS_DESGLOSADOS WHERE AD_ID = :valor");
							$consulta->bindParam(':valor', $row_ahorros['AD_ID']);
							$consulta->execute();
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

						if($pago > 0 ) {
							//Dejamos lo que sobró de ahorro como AHORRO
							$sql_ad = "INSERT INTO AHORROS_DESGLOSADOS (AD_FECHA,
																		PER_ID,
																	  	GRU_ID,
																	  	PI_ID,
																	  	AD_CANTIDAD)
									  	VALUES ( ?, ?, ?, ?, ? )";
							$values_ad = array($row_ahorros['AD_FECHA'],
											   $per_id,
											   $gru_id,
											   $row_ahorros['PI_ID'],
											   $pago);		
							$consulta_ad = $db->prepare($sql_ad);	
							try{
								$consulta_ad->execute($values_ad);
							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}
						}

					}

				}catch(PDOException $e){
					$db->rollBack();
					die($e->getMessage());
					$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
				}



			}


		} catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
			$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
		}


		$db->commit();

		echo json_encode($json);
		
	}

	function pagosFiniquitos() {
		$sql_recreditos = "SELECT * FROM GRUPOS
						   WHERE GRU_RECREDITO > 99";
		$db = $this->_conexion;
		$db->beginTransaction();
		$consulta_recreditos = $db->prepare($sql_recreditos);

		try {
			$consulta_recreditos->execute();
			$result_recreditos = $consulta_recreditos->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result_recreditos as $row_recreditos) {

				//Verifica TODAS las personas que se encuentran en ese grupo
				$sql_personas = "SELECT * FROM PERSONAS_GRUPOS
								 WHERE GRU_ID = ?";
				$values_personas = array($row_recreditos['GRU_RECREDITO']);
				$consulta_personas = $db->prepare($sql_personas);	

				try {
					
					$consulta_personas->execute($values_personas);
					$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_personas as $row_personas) {

						//Verifica el GRUPO ANTERIOR en el que estuvo esa persona
						$sql_max_grupo = "SELECT MAX(GRU_ID) as MAX_GRUPO
										  FROM PERSONAS_GRUPOS 
										  WHERE PER_ID = ? 
										  AND GRU_ID < ?";
						$values_max_grupo = array($row_personas['PER_ID'],
												  $row_personas['GRU_ID']);
						$consulta_max_grupo = $db->prepare($sql_max_grupo);

						try {
							
							$consulta_max_grupo->execute($values_max_grupo);
							$row_max_grupo = $consulta_max_grupo->fetch(PDO::FETCH_ASSOC);

							if ($consulta_max_grupo->rowCount()) {
								//Selecciona el PAGO MAXIMO que se dio
								$sql_max = "SELECT *
											FROM PAGOS_INDIVIDUALES 
											WHERE PER_ID = ? 
											AND GRU_ID = ?
											ORDER BY PI_NUM DESC";
								$values_max = array($row_personas['PER_ID'],
													$row_max_grupo['MAX_GRUPO']);
								$consulta_max = $db->prepare($sql_max);

								try {
									
									$consulta_max->execute($values_max);

									//Toma el último pago
									$row_max = $consulta_max->fetch(PDO::FETCH_ASSOC);

									//Si tiene menos de 12
									if($row_max['PI_NUM'] != 12) {

										$max = $row_max['MAX'] +1;
										$fecha = strtotime ( '+1 week' , strtotime ( $row_max['PI_FECHA'] ) ) ;
										$fecha = date("Y-m-d", $fecha);

										for ($i=$max; $i < 13; $i++) { 
											
											//AGREGA LOS PAGOS QUE NO SE VEÍAN
											$sql_pi = "INSERT INTO PAGOS_INDIVIDUALES (PI_FECHA, 
																						PER_ID,
																						GRU_ID,
																						PI_MONTO,
																						PI_PAGO,
																						PI_PENDIENTE,
																						PI_AHORRO,
																						PI_NUM,
																						PI_REC)
									  					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
									  		$values_pi = array($fecha,
									  						   $row_max['PER_ID'],
									  						   $row_max['GRU_ID'],
									  						   $row_max['PI_MONTO'],
									  						   $row_max['PI_MONTO'],
									  						   0,
									  						   0,
									  						   $i,
									  						   2);
									  		$consulta_pi = $db->prepare($sql_pi);

									  		try {
									  			
									  			$consulta_pi->execute($values_pi);

									  		} catch (PDOException $e) {
									  			$db->rollBack();
												die($e->getMessage());
									  		}

									  		//Agrega a Pago Finiquitado uno
									  		$last_id = $this->last_id();
									  		$sql_pf = "INSERT INTO PAGOS_FINIQUITADOS (PF_FECHA,
									  												   PF_MONTO,
									  												   PER_ID,
									  												   GRU_ID,
									  												   PI_ID)
									  					VALUES(?, ?, ?, ?, ?)";
									  		$values_pf = array($fecha,
									  						   $row_max['PI_MONTO'],
									  						   $row_max['PER_ID'],
									  						   $row_max['GRU_ID'],
									  						   $last_id);
									  		$consulta_pf = $db->prepare($sql_pf);

									  		try {
									  			
									  			$consulta_pf->execute($values_pf);

									  		} catch (PDOException $e) {
									  			$db->rollBack();
												die($e->getMessage());
									  		}				   			

									  		$fecha = strtotime ( '+1 week' , strtotime ( $fecha ) ) ;
											$fecha = date("Y-m-d", $fecha);			

										}
									}
								

								} catch (PDOException $e) {
									$db->rollBack();
									die($e->getMessage());
								}
							}


						} catch (PDOException $e) {
							$db->rollBack();
							die($e->getMessage());
						}

					}


				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}			 

			}

		} catch (PDOException $e) {
			$db->rollBack();
			die($e->getMessage());
		}

		$db->commit();

		echo "LISTO!";				   

	}

	function filterByGroup() {
		$json = array();
		$json['pagos'] = "";

		global $module;

		if(isset($_POST['grupo']) && $_POST['grupo'] > 0) {

			//Obtiene todos los grupos (aunque estén inactivos) del Promotor
			$db = $this->_conexion;
			$sql_grupos = "SELECT GRUPOS.GRU_ID,
								  GRU_FECHA_ENTREGA,
								  GRU_FECHA_FINAL,
								  PAGO_SEMANAL,
								  GRU_MONTO_TOTAL,
								  GRU_RECREDITO,
								  GRU_VIGENTE,
								  GRU_PLAZO,
								  GRU_REESTRUCTURA,
								  SIU_NOMBRE
							FROM GRUPOS
                            LEFT JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
							WHERE GRUPOS.GRU_ID = ?";

			$values_grupos = array($_POST['grupo']);
			$consulta_grupos = $db->prepare($sql_grupos);


			//RESUMEN GENERAL DE PROMOTOR
			/*$sql_promotor_total = "SELECT SUM(GRU_MONTO_TOTAL) as total
								   FROM GRUPOS
								   WHERE SIU_ID = ?";

			$values_promotor_total = array($_POST['promotor']);
			$consulta_promotor_total= $db->prepare($sql_promotor_total);
			$consulta_promotor_total->execute($values_promotor_total);
			$row_promotor_total = $consulta_promotor_total->fetch(PDO::FETCH_ASSOC);

			$sql_promotor_pend = "SELECT SUM(PI_PENDIENTE) as pendiente
							 	  FROM PAGOS_INDIVIDUALES
							 	  JOIN GRUPOS ON GRUPOS.GRU_ID = PAGOS_INDIVIDUALES.GRU_ID
							 	  WHERE SIU_ID = ?
							 	  AND PI_FECHA < CURRENT_DATE";

			$values_promotor_pend = array($_POST['promotor']);
			$consulta_promotor_pend= $db->prepare($sql_promotor_pend);
			$consulta_promotor_pend->execute($values_promotor_pend);
			$row_promotor_pend = $consulta_promotor_pend->fetch(PDO::FETCH_ASSOC);

			$json['pagos'] = '<table class="table">
								<thead>
									<tr>
										<th>Colocación Total Histórica</th>
										<th>Deuda Total Histórica</th>
										<th>% Total Histórica</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td align="center">'.$row_promotor_total['total'].'</td>
										<td align="center">'.$row_promotor_pend['pendiente'].'</td>
										<td align="center">'.number_format((100 / $row_promotor_total['total'] * $row_promotor_pend['pendiente']), 2).'%</td>
									</tr>
								</tbody>
							  </table>';*/




			try {
				$consulta_grupos->execute($values_grupos);

				if ($consulta_grupos->rowCount()) {
					$result_grupos = $consulta_grupos->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_grupos as $row) {

						$sql_tabla_pagos = "SELECT TP_FECHA
											FROM TABLA_PAGOS
											WHERE GRU_ID = ?
											ORDER BY TP_FECHA ASC";
						$value_tabla_pagos = array($row['GRU_ID']);	
						$consulta_tabla_pagos = $db->prepare($sql_tabla_pagos);	
						$consulta_tabla_pagos->execute($value_tabla_pagos);	
						$result_tabla_pagos = $consulta_tabla_pagos->fetchAll(PDO::FETCH_ASSOC);

						$color = 'primary';
						if($row['GRU_VIGENTE'] == 0) {
							$color = 'gray';
						} else if($row['GRU_REESTRUCTURA'] == 1) {
							$color = 'orange';
						} else if($row['GRU_RECREDITO'] != 0) {
							$color = 'purple';
						}
						
						$json['pagos'] .= '<div class="col-md-12 div-'.$row['GRU_ID'].'">
											<form class="form-'.$row['GRU_ID'].'">
											<div class="box border '.$color.'">
												<input type="hidden" class="form-control" id="grupo_'.$row['GRU_ID'].'" name="grupo" value="'.$row['GRU_ID'].'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
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
																<td align="center" colspan="2"><b>Promotor:</b> '.$row['SIU_NOMBRE'].'</td>
															</tr>
															<tr>
																<td align="center"><b>Fecha de Apertura:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
																<td align="center"><b>Fecha de Vencimiento:</b> '.date("d/m/Y",strtotime($row["GRU_FECHA_FINAL"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Crédito Grupal Autorizado: </b> $'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
																<td align="center"><b>Pago Semanal Grupal: </b> $'.number_format($row['PAGO_SEMANAL'], 2).'</td>
															</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>Nombre</th>
																<th>Pago Semanal</th>
																<th></th>';
						$num_pagos = 0;										

						foreach ($result_tabla_pagos as $row_tabla_pagos) {
							$num_pagos++;
							$json['pagos'] .= '<th><span class="pop-hover" data-content="'.(isset($row_tabla_pagos['TP_FECHA']) ? date("d/m/Y",strtotime($row_tabla_pagos["TP_FECHA"])) : '-').'">'.$num_pagos.'</span></th>';
						}

						if(count($result_tabla_pagos) < 12) {
							$i_x = count($result_tabla_pagos) +1;
							for ($i=$i_x; $i < ($row['GRU_PLAZO'] + 1); $i++) { 
								$json['pagos'] .= '<th><span class="pop-hover" data-content="-">'.$i.'</span></th>';
							}
						}

						$json['pagos'].=						'<th>Total Abonado</th>
																<th>Pago Semanal</th>
																<th>Ahorro</th>
																<th>Suma Ahorro</th>
																<th>Total Recup.</th>
																<th>Deuda Real</th>
															</tr>
														</thead>
														<tbody>';


					$sql_personas = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
											PERSONAS.PER_ID,
											PAGO_SEMANAL_IND,
											GRU_ID
								 	FROM PERSONAS_GRUPOS
								 	JOIN PERSONAS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
								 	WHERE GRU_ID = ?";
					$values_personas = array($row['GRU_ID']);
					$consulta_personas = $db->prepare($sql_personas);
					$pagos_arr = array();
					$ahorros_arr = array();

					$pagos_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);
					$ahorros_arr = array_fill(0, ($row['GRU_PLAZO']+1), 0);

					$totales_abonado = 0;
					$totales_ahorrado = 0;
					$totales_recuperado = 0;
					$totales_deudas = 0;

					$select = '<div class="cont-sel-'.$row['GRU_ID'].' display-none">';

					try {
						
						$consulta_personas->execute($values_personas);
						$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
						$ahorros_arr[0] = '-';
 						foreach ($result_personas as $row_personas) {
							$select .= '<option value="'.$row_personas['PER_ID'].'">'.$row_personas['PER_NOMBRE'].'</option>';
							$lnk_desglosados = 'pagos-desglosados.php?per_id='.$row_personas['PER_ID'].'&gru_id='.$row['GRU_ID'];
							$json['pagos'] .= '<tr>
												<td align="center" rowspan="2"><a href="'.$this->printLink($module, "cambios", $lnk_desglosados).'" target="_blank">'.$row_personas['PER_NOMBRE'].'</a></td>
												<td align="center" rowspan="2">'
													.$row_personas['PAGO_SEMANAL_IND'].'
													<input type="hidden" class="form-control pago_semanal_ind" id="pago_semanal_ind_'.$row_personas['PER_ID'].'" value="'.$row_personas['PAGO_SEMANAL_IND'].'" data-id='.$row_personas['PER_ID'].'>
												</td>
												<td align="center">P</td>';

							$pagos_arr[0] += $row_personas['PAGO_SEMANAL_IND'];					

							$sql_pagos = "SELECT * FROM PAGOS_INDIVIDUALES
										  WHERE PER_ID = ?
										  AND GRU_ID = ?
										  ORDER BY PI_FECHA ASC";
							$values_pagos = array($row_personas['PER_ID'],
												  $row['GRU_ID']);
							$consulta_pagos = $db->prepare($sql_pagos);	

							$p = 1;

							try {
								
								$consulta_pagos->execute($values_pagos);
								$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);
								$count_pagos = $consulta_pagos->rowCount();
								$json['total_pagos'][$row_personas['PER_ID']] = $count_pagos;

								$row_ahorro = '<tr>
												<td>A</td>';

								$total_abonado = 0;	
								$suma_ahorro = 0;			

								foreach ($result_pagos as $row_pagos) {
									//Aquí deben de desglosarse también los pagos**

									$pago = ($row_pagos['PI_REC'] == 0 ? $row_pagos['PI_PAGO'] : 0);

									$sql_desglosado = "SELECT * FROM
														(
															(SELECT PD_FECHA as FECHA,
																	PD_MONTO as MONTO,
																	'D' as DoR,
																	'0' as AD_ID,
																	PD_ID as ID_D
															FROM PAGOS_DESGLOSADOS
															WHERE PI_ID = ?)
															UNION
															(SELECT PR_FECHA as FECHA,
																	PR_MONTO as MONTO,
																	'R' as DoR,
																	AD_ID,
																	PR_ID as ID_D
															FROM PAGOS_RECUPERADOS
															WHERE PI_ID = ?))
														PAGOS
														ORDER BY FECHA ASC";
									$values_desglosado = array($row_pagos['PI_ID'],
															   $row_pagos['PI_ID']);
									$consulta_desglosado = $db->prepare($sql_desglosado);	

									try {
										
										$consulta_desglosado->execute($values_desglosado);
										$result_desglosado = $consulta_desglosado->fetchAll(PDO::FETCH_ASSOC);

										$hover_pago = "";
										$pago = 0;
										foreach ($result_desglosado as $row_desglosado) {
											$hover_pago .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";

											$pago += ($row_desglosado['DoR'] == 'R' ? 0 : $row_desglosado['MONTO']);

										}

									} catch (PDOException $e) {
										die($e->getMessage());
									}

									$color = ($row_pagos['PI_PAGO'] > 0 ? ($row_pagos['PI_PAGO'] == $row_pagos['PI_MONTO'] ? 'text-success' : 'text-warning') : '');

									$color = ($row_pagos['PI_REC'] == 2 ? 'text-info' : $color);


									$json['pagos'] .= '<td align="center"><span class="pop-hover '.$color.'" data-html="true" data-content="'.$hover_pago.'">'.$pago.'</span></td>';
									$row_ahorro .= '<td align="center">'.$row_pagos['PI_AHORRO'].'</td>';
									$total_abonado += $pago;
									$totales_abonado += $pago;
									$suma_ahorro += $row_pagos['PI_AHORRO'];
									$ahorros_arr[$p] += $row_pagos['PI_AHORRO'];
									$totales_ahorrado += $row_pagos['PI_AHORRO'];
									$pagos_arr[$p] += $pago;

									if($count_pagos < 12 && $p == $count_pagos) {
										$json['pagos'] .= '<td align="center">-</td>';
										$row_ahorro .= '<td align="center">-</td>';

										if($p == 10) {
											$json['pagos'] .= '<td align="center">-</td>';
											$row_ahorro .= '<td align="center">-</td>';
										}
									}

									$p++;
								}

								$row_ahorro .= '</tr>';	


							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Consulta los recuperados
							$sql_rec = "SELECT * FROM PAGOS_RECUPERADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_rec = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_rec = $db->prepare($sql_rec);	
							try {
								$consulta_rec->execute($values_rec);
								$result_rec = $consulta_rec->fetchAll(PDO::FETCH_ASSOC);
								$hover_rec = "";
								$recuperados = 0;
								foreach ($result_rec as $row_rec) {
									$hover_rec .= "<span class='".($row_rec['AD_ID'] > 0 ? 'text-purple' : '')."'>".date("d/m/Y",strtotime($row_rec["PR_FECHA"]))." - ".$row_rec['PR_MONTO']."<br>";
									$recuperados += $row_rec['PR_MONTO'];
									$totales_recuperado += $row_rec['PR_MONTO'];
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							//Deuda REAL
							$sql_deuda = "SELECT SUM(PI_PENDIENTE) as deuda
										  FROM PAGOS_INDIVIDUALES 
										  WHERE PER_ID = ? 
										  AND GRU_ID = ?
										  AND PI_FECHA < CURRENT_DATE";
							$values_deuda = array($row_personas['PER_ID'],
												$row['GRU_ID']);	
							$consulta_deuda = $db->prepare($sql_deuda);	
							try {
								$consulta_deuda->execute($values_deuda);
								$row_deuda = $consulta_deuda->fetch(PDO::FETCH_ASSOC);
							} catch (PDOException $e) {
								die($e->getMessage());
							}	

							//Consulta Ahorro desglosado
							$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS 
									   WHERE PER_ID = ? 
									   AND GRU_ID = ?";
							$values_ad = array($row_personas['PER_ID'],
											   $row['GRU_ID']);	
							$consulta_ad = $db->prepare($sql_ad);	
							try {
								$consulta_ad->execute($values_ad);
								$result_ad = $consulta_ad->fetchAll(PDO::FETCH_ASSOC);
								$hover_ad = "";
								foreach ($result_ad as $row_ad) {
									$hover_ad .= "<span class='".($row_ad['AD_VALIDO'] == 0 ? 'crossed' : '')."'>".date("d/m/Y",strtotime($row_ad["AD_FECHA"]))." - ".$row_ad['AD_CANTIDAD']."</span><br>";
								}

							} catch (PDOException $e) {
								die($e->getMessage());
							}

							$totales_deudas += $row_deuda['deuda'];

							$inpt_pago = '<input class="form-control" id="pago_'.$row_personas['PER_ID'].'" name="pago['.$row_personas['PER_ID'].']">';
							$inpt_ahorro = '<input class="form-control" id="ahorro_'.$row_personas['PER_ID'].'" name="ahorro['.$row_personas['PER_ID'].']">';
							$trans_ahorro = '<span class="fa-stack pop-hover transferir-ahorro" data-content="Transferir Ahorro" data-per="'.$row_personas['PER_ID'].'" data-gru="'.$row['GRU_ID'].'">
													  <i class="fa fa-circle fa-stack-2x"></i>
													  <i class="fa fa-dollar fa-stack-1x fa-inverse"></i>
													</span>';

							$json['pagos'] .= '<td align="center" rowspan="2">'.$total_abonado.'</td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_pago).'
											   </td>
											   <td align="center" rowspan="2">
											   		'.$this->printLink($module, "cambios", $inpt_ahorro).'
											   </td>
											   <td align="center" rowspan="2">
											   		<span class="pop-hover" data-html="true" data-content="'.$hover_ad.'">'
											   			.$suma_ahorro.
											   		'</span><br>
											   		'.$this->printLink($module, "cambios", $trans_ahorro).'
											   	</td>
											   <td align="center" rowspan="2"><span class="pop-hover" data-html="true" data-content="'.$hover_rec.'">'.$recuperados.'</span></td>
											   <td align="center" rowspan="2">'.$row_deuda['deuda'].'</td>';


							$json['pagos'] .= '</tr>';

							$json['pagos'] .= $row_ahorro;

						}

						$select .='</div>';


				 	} catch (PDOException $e) {
				 		die($e->getMessage());
				 	}		 	


					$json['pagos'] .= '					</tbody>
														<tfoot>
															<tr>
																<td>Total de Pagos</td>
																<td align="center">'.$pagos_arr[0].'</td>
																<td></td>';
																
					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$pagos_arr[$i].'</td>';
					}

					$btn_guardar = '<a class="guardar" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-success"><i class="fa fa-save"></i>Guardar Pagos Grupo '.$row['GRU_ID'].'</button>
													</a>';
					$btn_autocomplete = '<a class="autocomplete" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-info"><i class="fa fa-sort-numeric-desc"></i>Autocompletar</button>
													</a>';	

					$btn_reload = '<a class="reload" href="#" data-id="'.$row['GRU_ID'].'">
														<button class="btn btn-light-grey"><i class="fa fa-refresh"></i>Recargar Grupo</button>
													</a>';																						

					$json['pagos'].=							'<td align="center">'.$totales_abonado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">'.$totales_recuperado.'</td>
																<td align="center">'.$totales_deudas.'</td>
															</tr>
															<tr>
																<td>Total de Ahorros</td>
																<td align="center">'.$ahorros_arr[0].'</td>
																<td></td>';

					for ($i=1; $i < $row['GRU_PLAZO']+1; $i++) { 
						$json['pagos'].= 						'<td align="center">'.$ahorros_arr[$i].'</td>';
					}



					$json['pagos'].=				'			<td align="center">'.$totales_ahorrado.'</td>
																<td></td>
																<td></td>
																<td align="center">'.$totales_ahorrado.'</td>
																<td align="center">-</td>
																<td align="center">-</td>
															</tr>
														</tfoot>
													</table>
													'.$this->printLink($module, "cambios", $btn_guardar).'
													'.$this->printLink($module, "cambios", $btn_autocomplete).'
													'.$this->printLink($module, "cambios", $btn_reload).'
													'.$select.'
												</div>
											</div>
											</form>
										</div>';								


					}


				} else {
					$json['pagos'] = "<h1>No se encontraron grupos</h1>";
				}


			} catch (PDOException $e) {
				die($e->getMessage());
			}

		}

		echo json_encode($json);
	}

	function pagosRecreditados() {
		$sql = "SELECT * FROM PAGOS_INDIVIDUALES
				WHERE PI_REC = 2";
		$db = $this->_conexion;
		$consulta = $db->prepare($sql);

		try {
			$consulta->execute();

			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				
				$sql_up = "UPDATE PAGOS_DESGLOSADOS SET PD_RECREDITO = 1 WHERE PI_ID = ?";
				$values_up = array($row['PI_ID']);	
				$consulta_up = $db->prepare($sql_up);
				$consulta_up->execute($values_up);

			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		echo "LISTO!";
	}

	function getPagosPersonales() {
		$json = array();
		$json['error'] = false;
		$json['pagos'] = "";
		$json['msg'] = '';
		$json['nombre'] = '';

		global $module;

		if(isset($_POST['gru_id']) && $_POST['gru_id'] > 0 && isset($_POST['per_id']) && $_POST['per_id'] > 0) {

			$json['pagos'] .= '<form class="form-cliente">
								<table class="table table-bordered table-striped table-hover">
									<thead>
										<tr>
											<th align="center">PAGO CORRESPONDIENTE</th>
											<th align="center">PAGO DESGLOSADO</th>
											<th align="center">AHORRO DESGLOSADO</th>
											<th align="center">TOTALES</th>
										</tr>
									</thead>
									<tbody>';
			$db = $this->_conexion;
			$sql_pagos = "SELECT PAGOS_INDIVIDUALES.*,
								 CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE
						  FROM PAGOS_INDIVIDUALES
						  JOIN PERSONAS ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
						  WHERE PAGOS_INDIVIDUALES.PER_ID = ?
						  AND GRU_ID = ?
						  ORDER BY PI_FECHA ASC";
			$values_pagos = array($_POST['per_id'],
								  $_POST['gru_id']);
			$consulta_pagos = $db->prepare($sql_pagos);	
			try {
				$consulta_pagos->execute($values_pagos);
				if($consulta_pagos->rowCount()) {

					$result_pagos = $consulta_pagos->fetchAll(PDO::FETCH_ASSOC);
					$json['nombre'] = $result_pagos[0]['PER_NOMBRE'];
					foreach ($result_pagos as $row_pagos) {
						$json['pagos'] .= '<tr>
											<td align="center">
												<b>'
													.$row_pagos['PI_NUM'].' <br>'
													.date("d/m/Y",strtotime($row_pagos["PI_FECHA"])).' <br>'
													.'$'.number_format($row_pagos['PI_MONTO'], 2).
												'</b>
											</td>
											<td>';

						//PAGOS DESGLOSADOS
						$sql_desglosado = "SELECT * FROM
											(
												(SELECT PD_FECHA as FECHA,
														PD_MONTO as MONTO,
														'D' as DoR,
														'0' as AD_ID,
														PD_RECREDITO,
														PD_ID as ID_D
												FROM PAGOS_DESGLOSADOS
												WHERE PI_ID = ?)
												UNION
												(SELECT PR_FECHA as FECHA,
														PR_MONTO as MONTO,
														'R' as DoR,
														AD_ID,
														'0' as PD_RECREDITO,
														PR_ID as ID_D
												FROM PAGOS_RECUPERADOS
												WHERE PI_ID = ?))
											PAGOS
											ORDER BY FECHA ASC";
						$values_desglosado = array($row_pagos['PI_ID'],
												   $row_pagos['PI_ID']);
						$consulta_desglosado = $db->prepare($sql_desglosado);	

						try {
							
							$consulta_desglosado->execute($values_desglosado);
							$result_desglosado = $consulta_desglosado->fetchAll(PDO::FETCH_ASSOC);

							$pago = 0;
							foreach ($result_desglosado as $row_desglosado) {

								if($row_desglosado['AD_ID'] == 0 ){
									$json['pagos'] .= '<div class="form-group">
														<div class="col-sm-4">
															<input id="fecha-'.$row_desglosado['ID_D'].'-'.$row_desglosado['DoR'].'" 
																   name="fecha_'.$row_desglosado['DoR'].'['.$row_desglosado['ID_D'].']"
																   value="'.date("d/m/Y",strtotime($row_desglosado["FECHA"])).'"
																   class="form-control fecha">	   	   
														</div>
														<div class="col-sm-4">
															<input id="monto-'.$row_desglosado['ID_D'].'-'.$row_desglosado['DoR'].'" 
																   name="monto_'.$row_desglosado['DoR'].'['.$row_desglosado['ID_D'].']"
																   value="'.$row_desglosado['MONTO'].'"
																   class="form-control">
														</div>
														<div class="col-sm-4">
															<select id="tipo-'.$row_desglosado['ID_D'].'-'.$row_desglosado['DoR'].'" 
																	name="tipo_'.$row_desglosado['DoR'].'['.$row_desglosado['ID_D'].']"
																	class="form-control">
																<option value="D" '.($row_desglosado['DoR'] == 'D' ? 'selected' : '').'>Pago</option>
																<option value="R" '.($row_desglosado['DoR'] == 'R' ? 'selected' : '').'>Recuperado</option>
															</select>
														</div>
													  </div>';
								} else if($row_desglosado['PD_RECREDITO'] > 0) {
									$json['pagos'] .= "<span class='text-info'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";
								} else {
									$sql_ahorro = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE
												   FROM PERSONAS
												   JOIN AHORROS_DESGLOSADOS ON AHORROS_DESGLOSADOS.PER_ID = PERSONAS.PER_ID
												   WHERE AD_ID = ?";
									$values_ahorro = array($row_desglosado['AD_ID']);		
									$consulta_ahorro = $db->prepare($sql_ahorro);	 
									try {
									  	$consulta_ahorro->execute($values_ahorro);
										$row_ahorro = $consulta_ahorro->fetch(PDO::FETCH_ASSOC);

										$json['pagos'] .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']." - Ahorro de:".$row_ahorro['PER_NOMBRE']."</span><br>";


									} catch (PDOException $e) {
									  	die($e->getMessage());
									}  
								}

								/*$json['pagos'] .= "<span class='".($row_desglosado['DoR'] == 'R' ? ($row_desglosado['AD_ID'] > 0 ? 'text-purple' : 'text-muted') : '')."'>".date("d/m/Y",strtotime($row_desglosado["FECHA"]))." - ".$row_desglosado['MONTO']."</span><br>";*/

								$pago += $row_desglosado['MONTO'];

							}

							$json['pagos'].= '</td>
											  <td>';

						} catch (PDOException $e) {
							die($e->getMessage());
						}

						//AHORROS DESGLOSADOS
						$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS 
								   WHERE PI_ID = ?";
						$values_ad = array($row_pagos['PI_ID']);	
						$consulta_ad = $db->prepare($sql_ad);	
						try {
							$consulta_ad->execute($values_ad);
							$result_ad = $consulta_ad->fetchAll(PDO::FETCH_ASSOC);
							$ahorro = 0;
							foreach ($result_ad as $row_ad) {

								if($row_ad['AD_VALIDO'] == 0){
									$json['pagos'] .= "<span class='".($row_ad['AD_VALIDO'] == 0 ? 'crossed' : '')."'>".date("d/m/Y",strtotime($row_ad["AD_FECHA"]))." - ".$row_ad['AD_CANTIDAD']."</span><br>";
								} else {
									$json['pagos'] .= '<div class="form-group">
														<div class="col-sm-6">
															<input id="fecha-'.$row_ad['AD_ID'].'-ahorro" 
																   name="fecha_ahorro['.$row_ad['AD_ID'].']"
																   value="'.date("d/m/Y",strtotime($row_ad["AD_FECHA"])).'"
																   class="form-control fecha">	   	   
														</div>
														<div class="col-sm-6">
															<input id="ahorro-'.$row_ad['AD_ID'].'" 
															   name="ahorro['.$row_ad['AD_ID'].']"
															   value="'.$row_ad['AD_CANTIDAD'].'"
															   class="form-control">
														</div>
													</div>';
									$ahorro += ($row_ad['AD_VALIDO'] == 0 ? 0 : $row_ad['AD_CANTIDAD']);
								}
								
							}

						} catch (PDOException $e) {
							die($e->getMessage());
						}

						$json['pagos'].= '</td>
										  <td>
										  		<b>
										  			PAGOS: '.(number_format($pago, 2)).'<br>
										  			AHORROS:'.(number_format($ahorro, 2)).'
										  		</b>';

						$json['pagos'] .= '</tr>';

					}

				} else {
					$json['error'] = true;
					$json['msg'] = "Cliente / Grupo inválido";
				}
			} catch (PDOException $e) {
				die($e->getMessage());
			}

			$json['pagos'] .= '		</tbody>
								</table>
							</form>';

		} else {
			$json['error'] = true;
			$json['msg'] = "Cliente / Grupo inválido";
		}

		echo json_encode($json);
	}

	function savePagosPersonales() {
		$json = array();
		$json['error'] = false;

		$db = $this->_conexion;
		$db->beginTransaction();

		//PAGOS DESGLOSADOS
		if(isset($_POST['monto_D'])) {
			foreach ($_POST['monto_D'] as $key => $monto) {

				//Obtenemos todos los datos del Pago Desglosado
				$sql_desglosado = "SELECT * FROM PAGOS_DESGLOSADOS
								   WHERE PD_ID = ?";
				$values_desglosado = array($key);
				$consulta_desglosado = $db->prepare($sql_desglosado);	
				try {
					$consulta_desglosado->execute($values_desglosado);
					$row_desglosado = $consulta_desglosado->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Obtenemos los datos del Pago Individual al que corresponde
				$sql_pi = "SELECT * FROM PAGOS_INDIVIDUALES
						   WHERE PI_ID = ?";
				$values_pi = array($row_desglosado['PI_ID']);
				$consulta_pi = $db->prepare($sql_pi);	
				try {
					$consulta_pi->execute($values_pi);
					$row_pi = $consulta_pi->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Eliminamos el Pago Desglosado del Pago Individual 
				$nuevo_individual = $row_pi['PI_PAGO'] - $row_desglosado['PD_MONTO'];
				$nuevo_pendiente = $row_pi['PI_PENDIENTE'] + $row_desglosado['PD_MONTO'];

				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_D'][$key])));

				//Si el monto es mayor a 0
				if($monto > 0) {
					//Sigue siendo pago a tiempo?
					if($_POST['tipo_D'][$key] == 'D') {
						//Actualiza el pago
						$sql_pago = "UPDATE PAGOS_DESGLOSADOS SET PD_FECHA = ?,
																  PD_MONTO = ?
							    	 WHERE PD_ID = ? ";
						$values_pago = array($fecha,
										   	 $monto,
										   	 $key);	
						$consulta_pago = $db->prepare($sql_pago);
						try {
							$consulta_pago->execute($values_pago);
						} catch (PDOException $e) {
							$db->rollBack();
						 	die($e->getMessage());
						}

						$nuevo_individual += $monto;
						$nuevo_pendiente -= $monto;

					} else {
						//Ahora es Pago recuperado -> Eliminamos el Pago Desglosado
						try{
							$consulta_del = $db->prepare("DELETE FROM PAGOS_DESGLOSADOS WHERE PD_ID = :valor");
							$consulta_del->bindParam(':valor', $row_desglosado['PD_ID']);
							$consulta_del->execute();
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
						}

						//Hacemos un registro de Pago Recuperado
						$sql_pr = "INSERT INTO PAGOS_RECUPERADOS (PR_FECHA,
																  PR_MONTO,
																  PER_ID,
																  GRU_ID,
																  PI_ID)
								  	VALUES ( ?, ?, ?, ?, ? )";
						$values_pr = array($fecha,
										   $monto,
										   $row_desglosado['PER_ID'],
										   $row_desglosado['GRU_ID'],
										   $row_desglosado['PI_ID']);		
						$consulta_pr = $db->prepare($sql_pr);	
						try{
							$consulta_pr->execute($values_pr);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
						}

						$nuevo_individual += $monto;
						$nuevo_pendiente -= $monto;

					}
				} else {
					//Si el monto es 0 -> se elimina ese registro
					try{
						$consulta_del = $db->prepare("DELETE FROM PAGOS_DESGLOSADOS WHERE PD_ID = :valor");
						$consulta_del->bindParam(':valor', $row_desglosado['PD_ID']);
						$consulta_del->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
					}
				}

				//Actualiza el Pago Individual
				$sql_up = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
														 PI_PENDIENTE = ?
					    WHERE PI_ID = ? ";
				$values_up = array($nuevo_individual,
								   $nuevo_pendiente,
								   $row_pi['PI_ID']);	
				$consulta_up = $db->prepare($sql_up);
				try {
					$consulta_up->execute($values_up);
				} catch (PDOException $e) {
					$db->rollBack();
				 	die($e->getMessage());
				}


			}
		}

		//PAGOS RECUPERADOS
		if(isset($_POST['monto_R'])) {
			foreach ($_POST['monto_R'] as $key => $monto) {

				//Obtenemos todos los datos del Pago Desglosado
				$sql_recuperado = "SELECT * FROM PAGOS_RECUPERADOS
								   WHERE PR_ID = ?";
				$values_recuperado = array($key);
				$consulta_recuperado = $db->prepare($sql_recuperado);	
				try {
					$consulta_recuperado->execute($values_recuperado);
					$row_recuperado = $consulta_recuperado->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Obtenemos los datos del Pago Individual al que corresponde
				$sql_pi = "SELECT * FROM PAGOS_INDIVIDUALES
						   WHERE PI_ID = ?";
				$values_pi = array($row_recuperado['PI_ID']);
				$consulta_pi = $db->prepare($sql_pi);	
				try {
					$consulta_pi->execute($values_pi);
					$row_pi = $consulta_pi->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Eliminamos el Pago Desglosado del Pago Individual 
				$nuevo_individual = $row_pi['PI_PAGO'] - $row_recuperado['PR_MONTO'];
				$nuevo_pendiente = $row_pi['PI_PENDIENTE'] + $row_recuperado['PR_MONTO'];

				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_R'][$key])));

				//Si el monto es mayor a 0
				if($monto > 0) {
					//Sigue siendo pago a tiempo?
					if($_POST['tipo_R'][$key] == 'R') {
						//Actualiza el pago
						$sql_pago = "UPDATE PAGOS_RECUPERADOS SET PR_FECHA = ?,
																  PR_MONTO = ?
							    	 WHERE PR_ID = ? ";
						$values_pago = array($fecha,
										   	 $monto,
										   	 $key);	
						$consulta_pago = $db->prepare($sql_pago);
						try {
							$consulta_pago->execute($values_pago);
						} catch (PDOException $e) {
							$db->rollBack();
						 	die($e->getMessage());
						}

						$nuevo_individual += $monto;
						$nuevo_pendiente -= $monto;

					} else {
						//Ahora es Pago recuperado -> Eliminamos el Pago Desglosado
						try{
							$consulta_del = $db->prepare("DELETE FROM PAGOS_RECUPERADOS WHERE PR_ID = :valor");
							$consulta_del->bindParam(':valor', $row_recuperado['PR_ID']);
							$consulta_del->execute();
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
						}

						//Hacemos un registro de Pago Recuperado
						$sql_pr = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
																  PD_MONTO,
																  PER_ID,
																  GRU_ID,
																  PI_ID)
								  	VALUES ( ?, ?, ?, ?, ? )";
						$values_pr = array($fecha,
										   $monto,
										   $row_recuperado['PER_ID'],
										   $row_recuperado['GRU_ID'],
										   $row_recuperado['PI_ID']);		
						$consulta_pr = $db->prepare($sql_pr);	
						try{
							$consulta_pr->execute($values_pr);
						}catch(PDOException $e){
							$db->rollBack();
							die($e->getMessage());
						}

						$nuevo_individual += $monto;
						$nuevo_pendiente -= $monto;

					}
				} else {
					//Si el monto es 0 -> se elimina ese registro
					try{
						$consulta_del = $db->prepare("DELETE FROM PAGOS_RECUPERADOS WHERE PR_ID = :valor");
						$consulta_del->bindParam(':valor', $row_recuperado['PR_ID']);
						$consulta_del->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
					}
				}

				//Actualiza el Pago Individual
				$sql_up = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
														 PI_PENDIENTE = ?
					    WHERE PI_ID = ? ";
				$values_up = array($nuevo_individual,
								   $nuevo_pendiente,
								   $row_pi['PI_ID']);	
				$consulta_up = $db->prepare($sql_up);
				try {
					$consulta_up->execute($values_up);
				} catch (PDOException $e) {
					$db->rollBack();
				 	die($e->getMessage());
				}


			}
		}

		//AHORROS DESGLOSADOS
		if(isset($_POST['ahorro'])) {
			foreach ($_POST['ahorro'] as $key => $monto) {

				//Obtenemos todos los datos del Pago Desglosado
				$sql_ad = "SELECT * FROM AHORROS_DESGLOSADOS
						   WHERE AD_ID = ?";
				$values_ad = array($key);
				$consulta_ad = $db->prepare($sql_ad);	
				try {
					$consulta_ad->execute($values_ad);
					$row_ad = $consulta_ad->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Obtenemos los datos del Pago Individual al que corresponde
				$sql_pi = "SELECT * FROM PAGOS_INDIVIDUALES
						   WHERE PI_ID = ?";
				$values_pi = array($row_ad['PI_ID']);
				$consulta_pi = $db->prepare($sql_pi);	
				try {
					$consulta_pi->execute($values_pi);
					$row_pi = $consulta_pi->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					$db->rollBack();
					die($e->getMessage());
				}

				//Eliminamos el Pago Desglosado del Pago Individual 
				$nuevo_ahorro = $row_pi['PI_AHORRO'] - $row_ad['AD_CANTIDAD'];

				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_ahorro'][$key])));

				//Si el monto es mayor a 0
				if($monto > 0) {
					//Actualiza el ahorro
					$sql_pago = "UPDATE AHORROS_DESGLOSADOS SET AD_FECHA = ?,
															  	AD_CANTIDAD = ?
						    	 WHERE AD_ID = ? ";
					$values_pago = array($fecha,
									   	 $monto,
									   	 $key);	
					$consulta_pago = $db->prepare($sql_pago);
					try {
						$consulta_pago->execute($values_pago);
					} catch (PDOException $e) {
						$db->rollBack();
					 	die($e->getMessage());
					}

					$nuevo_ahorro += $monto;
				} else {
					//Si el monto es 0 -> se elimina ese registro
					try{
						$consulta_del = $db->prepare("DELETE FROM AHORROS_DESGLOSADOS WHERE AD_ID = :valor");
						$consulta_del->bindParam(':valor', $row_ad['AD_ID']);
						$consulta_del->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
					}
				}

				//Actualiza el Pago Individual
				$sql_up = "UPDATE PAGOS_INDIVIDUALES SET PI_AHORRO = ?
					       WHERE PI_ID = ? ";
				$values_up = array($nuevo_ahorro,
								   $row_pi['PI_ID']);	
				$consulta_up = $db->prepare($sql_up);
				try {
					$consulta_up->execute($values_up);
				} catch (PDOException $e) {
					$db->rollBack();
				 	die($e->getMessage());
				}


			}
		}

		$json['msg'] = "Los pagos se guardaron con éxito.";
		$db->commit();

		echo json_encode($json);
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
		case "getClientes":
			$libs->getClientes();
			break;		
		case "filterGroups":
			$libs->filterGroups();
			break;	
		case "filterClients":
			$libs->filterClients();
			break;		
		case "showClients":
			$libs->showClients();
			break;	
		case "fillPagosDesglosados":
			$libs->fillPagosDesglosados();
			break;
		case "correctPendientes":
			$libs->correctPendientes();
			break;	
		case "savePagos":
			$libs->savePagos();
			break;
		case "reloadGroup":
			$libs->reloadGroup();
			break;	
		case 'nuevoAhorro':
			$libs->nuevoAhorro();
			break;	
		case 'transferirAhorro':
			$libs->transferirAhorro();
			break;
		case 'pagosFiniquitos':
			$libs->pagosFiniquitos();
			break;	
		case 'filterByGroup':
			$libs->filterByGroup();
			break;
		case 'pagosRecreditados':
			$libs->pagosRecreditados();
			break;
		case 'getPagosPersonales':
			$libs->getPagosPersonales();
			break;	
		case 'savePagosPersonales':
			$libs->savePagosPersonales();
			break;																		
	}
}

?>