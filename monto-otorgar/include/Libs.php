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
				$sql = "SELECT TP_ID,
							   TP_FECHA,
							   TP_MONTO,
							   TABLA_PAGOS.GRU_ID,
							   GRU_RECREDITO,
							   GRU_PLAZO
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
							   GRU_PLAZO
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
														</tr>
														<tr>
															<td align="center"><b>Pago Semanal Efectuado</b></td>
															<td align="center">$<span class="pago-semanal-efectuado-'.$row['GRU_ID'].'">0.00</span></td>
														</tr>
														<tr>
															<td align="center"><b>Total Ahorro</b></td>
															<td align="center">$<span class="total-ahorro-'.$row['GRU_ID'].'">0.00</span></td>
														</tr>
														<tr>
															<td align="center"><b>Total Pagado</b></td>
															<td align="center">$<span class="total-pagado-'.$row['GRU_ID'].'">0.00</span></td>
														</tr>
														<tr>
															<td align="center"><b>Total Faltante</b></td>
															<td align="center">$<span class="total-faltante-'.$row['GRU_ID'].' danger">'.number_format($row['TP_MONTO'], 2).'</span></td>
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
										PAGO_SEMANAL_IND
								 FROM PERSONAS_GRUPOS
								 JOIN PERSONAS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								 WHERE GRU_ID = ?";
				$values_personas = array($row['GRU_ID']);
				$consulta_personas = $this->_conexion->prepare($sql_personas);	
				try {
					$consulta_personas->execute($values_personas);
					$result_personas = $consulta_personas->fetchAll(PDO::FETCH_ASSOC);
					$num = 0;
					foreach ($result_personas as $row_personas) {
						$num++;
						$num2++;
						$json['pagos'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center">'.$row_personas['PER_NOMBRE'].'</td>
												<td align="center">$'.$row_personas['MONTO_INDIVIDUAL'].'</td>
												<td align="center">$<span class="semanal_'.$num2.'">'.$row_personas['PAGO_SEMANAL_IND'].'</span></td>
												<td align="center">
													<input class="form-control pago dinero_'.$num2.'  pago_'.$row['GRU_ID'].'" type="text" id="pago_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'">
												</td>
												<td align="center">
													<input class="form-control ahorro dinero_'.$num2.'  ahorro_'.$row['GRU_ID'].'" type="text" id="ahorro_'.$row_personas['PER_ID'].'_'.$row['GRU_ID'].'" data-id="'.$row_personas['PER_ID'].'" data-group="'.$row['GRU_ID'].'" data-num="'.$num2.'">
												</td>
												<td align="center">
													$<span class="pago_individual_'.$num2.'">0.00</span>
												</td>
												<td align="center" class="status_'.$num2.'">
													<i class="fa fa-times"></i>
												</td>
												<td align="center" class="danger falt_ind_'.$num2.'">
													$<span class="faltante_individual_'.$num2.'">'.$row_personas['PAGO_SEMANAL_IND'].'</span>
												</td>
												<td align="center">
													<textarea class="form-control" value="1" id="'.$row_personas['PER_ID'].'"></textarea>
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
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$json['inicio'] = $fecha_inicio;
		$fin_str = strtotime('next sunday', $inicio_str);
		$fecha_fin = date("Y-m-d", $fin_str);
		$json['fin'] = $fecha_fin;
		$sql = "SELECT SUM(GRU_MONTO_TOTAL) as monto, 
					   GRUPOS.SIU_ID,
					   SIU_NOMBRE
				FROM GRUPOS
				JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
				WHERE GRU_FECHA_ENTREGA >= ?
				AND GRU_FECHA_ENTREGA <= ?
                GROUP BY GRUPOS.SIU_ID";

		$values = array($fecha_inicio,
						$fecha_fin);

		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

			$json['totales'] .= '<table class="table table-striped">
									<thead>
										<tr>
											<td></td>
											<td align="center"><b>Promotor</b></td>
											<td align="center"><b>Monto Total</b></td>
										</tr>
									</thead>
									<tbody>';
			$expansiones = 0;
			foreach ($result as $row) {
				$json['totales'] .= '<tr style="background: #E7E7E7">
										<td  align="center" class="expandir tck" data-id="'.$expansiones.'"><i class="fa fa-chevron-down"></i></td>
										<td align="center">'.$row['SIU_NOMBRE'].'</td>
										<td align="center">$'.number_format($row['monto'], 2).'</td>
									</tr>';


				$sql_prom = "SELECT GRU_MONTO_TOTAL,
									GRU_ID
							FROM GRUPOS
							WHERE GRU_FECHA_ENTREGA >= ?
							AND GRU_FECHA_ENTREGA <= ?
							AND SIU_ID = ?";
				$value_prom = array($fecha_inicio,
									$fecha_fin,
									$row['SIU_ID']);
				$consulta_prom = $this->_conexion->prepare($sql_prom);	
				
				try {
					$consulta_prom->execute($value_prom);
					$result_prom = $consulta_prom->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result_prom as $row_prom) {
						$json['totales'] .= "<tr class='zone-".$expansiones."' style='display:none;'>
												<td></td>
												<td align='center'>Préstamo de Grupo ".$row_prom['GRU_ID']."</td>
												<td align='center'>$".number_format($row_prom['GRU_MONTO_TOTAL'], 2)."</td>
											 </tr>";
					}

				} catch (PDOException $e) {
					die($e->getMessage());
				}	

				$expansiones++;						

			}

			$json['totales'] .= '</tbody>
							</table>';
		} catch (PDOException $e) {
		 	die($e->getMessage());
		}	

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
	}
}

?>