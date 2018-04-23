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
	 * @version: 0.1 2014-11-27
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Guarda Clientes
	 */
	function saveRecord() {
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";
		$debug = true;

		$obligatorios = array("fecha",
							  "plazo",
							  "tasa",
							  "fecha_entrega",
							  "fecha_inicial",
							  "ahorro_p",
							  "comision_p",
							  "monto_total",
							  "monto_total_entregar",
							  "pago_total_semanal",
							  "domicilio");

		$excepciones = array("cli_id",
							 "monto_individual",
							 "ahorro_d",
							 "comision_d",
							 "monto_otorgar",
							 "pago_semanal");

		//VALIDACIÓN
		foreach($_POST as $clave => $valor){
			if(!$json["error"] && !in_array($clave, $excepciones) ){
				if($this->is_empty($valor) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
				}
			}
		}

		if(!$json['error']) {
			//Revisa las personas en el grupo
			if(!isset($_POST['cli_id'])) {
				$json["error"] = true;
				$json["focus"] = "clientes";
				$json["msg"] = "Deben de ser al menos 7 personas en el Grupo.";
			}/* else if(count($_POST['cli_id']) < 3 || count($_POST['cli_id']) > 13) {
				$json["error"] = true;
				$json["focus"] = "clientes";
				$json["msg"] = "Deben de ser al menos 7 personas en el Grupo y máximo 13.";
			}*/
		}

		if(!$json["error"]) {
			try {
				$db = $this->_conexion;
				$db->beginTransaction();

				//$fecha = date("Y-m-d",strtotime($_POST['fecha']));
				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
				$fecha_inicial = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_inicial'])));
				$fecha_entrega = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_entrega'])));

				$personas = implode("|", $_POST['cli_id']);

				$tasa = $_POST['tasa'] / 100;

				$ahorro_p = $_POST['ahorro_p'] / 100;

				$recredito = 0;
				if(isset($_POST['recredito'])) {
					$recredito = 1;
				}

				$comision_p = $_POST['comision_p'] / 100;

				/*$pago_capital = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes = $pago_capital * $tasa;
				$pago_semanal = $pago_capital + $pago_interes;*/

				$pago_capital_total = $_POST['monto_total'] / $_POST['plazo'];
				$pago_interes_total = $pago_capital_total * $tasa;
				$pago_semanal_total = $pago_capital_total + $pago_interes_total;

				if(!isset($_SESSION)){
					@session_start();
				}
				
				//Se prepara la consulta
				$siu_id = $_SESSION["mp"]["userid"];

				$values = array($fecha,
								$_POST["plazo"],
								$tasa,
								$fecha_inicial,
								$fecha_entrega,
								$ahorro_p,
								$recredito,
								$comision_p,
								$_POST['domicilio'],
								$siu_id,
								$_POST['monto_total'],
								$_POST['monto_total_entregar'],
								$pago_capital_total,
								$pago_interes_total,
								$pago_semanal_total);


				if(isset($_POST['id'])) {
					$sql = "UPDATE CLIENTE  SET CLI_NOMBRE=?, 
												CLI_APELLIDO_PATERNO=?,
												CLI_APELLIDO_MATERNO=?,
												CLI_EMAIL=?,
												CLI_MOVIL=?
											WHERE CLI_ID = ?";
					$values[]= $_POST['id'];

				} else {
					$sql = "INSERT INTO GRUPOS (GRU_FECHA, 
												GRU_PLAZO,
												GRU_TASA,
												GRU_FECHA_INICIAL,
												GRU_FECHA_ENTREGA,
												GRU_AHORRO_P,
												GRU_RECREDITO,
												GRU_COMISION_P,
												GRU_DOMICILIO,
												SIU_ID,
												GRU_MONTO_TOTAL,
												GRU_MONTO_TOTAL_ENTREGAR,
												PAGO_CAPITAL,
												PAGO_INTERES,
												PAGO_SEMANAL)
								  	VALUES (?, ?, ?, ?, ?, ?, ?,
								  			?, ?, ?, ?, ?, ?, ?, ?)";
				}

				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$last_id = $_POST['id'];

				} else {
					$last_id = $this->last_id();
				}

				$pago_capital_total = 0;
				$pago_interes_total = 0;
				$pago_semanal_total = 0;

				$personas_rechazadas = "";
				$p_rechazo = false;

				foreach ($_POST['cli_id'] as $num => $id_cliente) {

					$sql_sl = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
									  MAXIMO_PAGAR,
									  STATUS
								FROM PERSONAS 
								WHERE PER_ID = :valor";
					$consulta_sl = $db->prepare($sql_sl);
					$consulta_sl->bindParam(':valor', $id_cliente);
					$consulta_sl->execute();

					$row_sl = $consulta_sl->fetch(PDO::FETCH_ASSOC);

					//Verifica que pueda pagar el monto que se le está asignando
					//if($row_sl['MAXIMO_PAGAR'] > $_POST['monto_individual'][$num] || $row_sl['STATUS'] == 1 || $row_sl['STATUS'] == 2) {
					if($row_sl['MAXIMO_PAGAR'] > $_POST['monto_individual'][$num]) {
						$pago_capital = (float)$_POST['monto_individual'][$num] / $_POST['plazo'];
						$pago_semanal = (float)$_POST['pago_semanal'][$num];
						$pago_interes = $pago_semanal - $pago_capital;

						$pago_capital_total += $pago_capital;
						$pago_interes_total += $pago_interes;
						$pago_semanal_total += $pago_semanal;

						$sql_cg = "INSERT INTO PERSONAS_GRUPOS (PER_ID,
																GRU_ID,
																MONTO_INDIVIDUAL,
																AHORRO_D,
																COMAP_D,
																MONTO_OTORGAR,
																PAGO_CAPITAL_IND,
																PAGO_INTERES_IND,
																PAGO_SEMANAL_IND)
											VALUES (?, ?, ?,
													?, ?, ?,
													?, ?, ?)";
						$values_cg = array($id_cliente,
										   $last_id,
										   $_POST['monto_individual'][$num],
										   $_POST['ahorro_d'][$num],
										   $_POST['comision_d'][$num],
										   $_POST['monto_otorgar'][$num],
										   $pago_capital,
										   $pago_interes,
										   $pago_semanal);	

						$consulta_cg = $db->prepare($sql_cg);	
						
						try {
							$consulta_cg->execute($values_cg);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = isset($debug)?"--SQL: ".$sql_cg.(isset($values_cg)?"\n--Values: ".print_r($values_cg):""):"";
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}			   						

						$sql_cl = "UPDATE PERSONAS SET STATUS = 1
												WHERE PER_ID = ?";
						$values_cl = array($id_cliente);
						$consulta_cl = $db->prepare($sql_cl);

						try {
							$consulta_cl->execute($values_cl);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = isset($debug)?"--SQL: ".$sql_cl.(isset($values_cl)?"\n--Values: ".print_r($values_up):""):"";
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}
					} else {
						$p_rechazo = true;

						//UPDATE de personas rechazadas
						$sql_rechazo = "UPDATE PERSONAS SET STATUS=2,
															RAZON_RECHAZO = ?
								   		WHERE PER_ID = ?";
						$values_rechazo = array("El Monto del Cliente excede el límite autorizado. El promotor asignó ".$_POST['monto_individual'][$num]." en el Grupo ".$last_id,
												$id_cliente);	
						$consulta_rechazo = $db->prepare($sql_rechazo);

						try {
							$consulta_rechazo->execute($values_rechazo);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = isset($debug)?"--SQL: ".$sql_rechazo.(isset($values_rechazo)?"\n--Values: ".print_r($values_rechazo):""):"";
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}

						$personas_rechazadas.= "<br>".$row_sl['PER_NOMBRE'];

					}

					
				}

				//UPDATE a los montos totales, para que no haya errores de centavos
				$sql_up = "UPDATE GRUPOS  SET PAGO_CAPITAL=?,
											  PAGO_INTERES=?,
											  PAGO_SEMANAL=?
											WHERE GRU_ID = ?";
				$values_up = array($pago_capital_total,
								   $pago_interes_total,
								   $pago_semanal_total,
								   $last_id);	
				$consulta_up = $db->prepare($sql_up);

				try {
					$consulta_up->execute($values_up);
				} catch (PDOException $e) {
					$db->rollBack();
					$dbgMsg = isset($debug)?"--SQL: ".$sql_up.(isset($values_up)?"\n--Values: ".print_r($values_up):""):"";
					die($e->getMessage().$dbgMsg);
					$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
				}


				//TABLA DE PAGOS
				$fecha = $fecha_inicial;

				for ($i=0; $i < $_POST['plazo']; $i++) { //Plazo
					$sql_pagos = "INSERT INTO TABLA_PAGOS (TP_FECHA,
														   TP_MONTO,
														   GRU_ID)
												VALUES (?, ?, ?)";
					$values_pagos = array($fecha,
										  $pago_semanal_total,
										  $last_id);	
					$consulta_pagos = $db->prepare($sql_pagos);

					try {
						$consulta_pagos->execute($values_pagos);
					} catch (PDOException $e) {
						$db->rollBack();
						$dbgMsg = isset($debug)?"--SQL: ".$sql_pagos.(isset($values_pagos)?"\n--Values: ".print_r($values_pagos):""):"";
						die($e->getMessage().$dbgMsg);
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Nueva Fecha
					$fecha_ = strtotime($fecha);
					$fecha = strtotime('+1 week', $fecha_);
					$fecha = date("Y-m-d", $fecha);

				}			   						


				$db->commit();
				$json['msg'] = "El Grupo se ha guardado con éxito.". ($p_rechazo ? "<br>Las siguientes personas no califican para el préstamo asignado:".$personas_rechazadas : '');	   

			} catch (PDOException $e) {
				$db->rollBack();
				$dbgMsg = isset($debug)?"--SQL: ".$sql.(isset($values)?"\n--Values: ".print_r($values):""):"";
				die($e->getMessage().$dbgMsg);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}
		}
		
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-28
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Borra Clientes (y sus teléfonos) de la Base de Datos
	 */
	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";

		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM CLIENTE WHERE CLI_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Cliente fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Cliente elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				if ($e->getCode() == '23000') {
        			$json['error'] = true;
					$json['msg'] = "El Cliente elegido no pudo ser eliminado. Verifique que no tenga un crédito relacionada.";
				} else
					die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-28
	 * 
	 * @param '$id'		int. 	ID de Cliente
	 * 
	 * Metodo que regresa los datos de Cliente en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";

		if(isset($_POST['id'])){
			try {
				$db = $this->_conexion;
				$sql = "SELECT * FROM CLIENTE WHERE CLI_ID = :valor";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$json = array_merge($json, $puntero[0]);
				} else {
					$json['error'] = true;
				} 
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		}

		echo json_encode($json);

	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-29
	 * 
	 * Metodo que regresa los datos de Prospecto
	 */
	function showClients() {
		$personas = array();
		$term = trim($_GET['term']); //retrieve the search term that autocomplete sends
		try {
			$db = $this->_conexion;
			$sql = "SELECT PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_CELULAR,
						   MONTO_SOLICITADO
					FROM PERSONAS 
					WHERE (STATUS != -1 
					AND STATUS != 2)
					AND PER_NOMBRE LIKE '%".$term."%'";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $term);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$persona['id'] = $row['PER_ID'];
					$persona['name'] = $row['PER_NOMBRE'];
					$persona['value'] = $row['PER_NOMBRE'];
					$persona['address'] = $row['PER_DIRECCION'];
					$persona['phone'] = $row['PER_CELULAR'];
					$persona['money'] = $row['MONTO_SOLICITADO'];
					$personas[] = $persona;
				}

			} 
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($personas);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-02
	 * 
	 * Impresión de los Grupos en página de inicio
	 */
	function printGroups() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";

		if(!isset($_SESSION)){
			@session_start();
		}
		
		//Se prepara la consulta
		$siu_id = $_SESSION["mp"]["userid"];		

		try {
			$db = $this->_conexion;
			/*$sql = "SELECT GRU_ID,
						   GRU_FECHA,
						   GRU_MONTO_TOTAL,
						   GRU_PLAZO,
						   GRU_TASA,
						   GRU_RECREDITO
					FROM GRUPOS 
					WHERE SIU_ID = :valor
					ORDER BY GRU_FECHA DESC, GRU_ID DESC";*/
			$sql = 'SELECT GRU_ID,
						   GRU_FECHA_ENTREGA,
						   GRU_MONTO_TOTAL,
						   GRU_PLAZO,
						   GRU_TASA,
						   SIU_NOMBRE,
						   GRUPOS.SIU_ID,
						   GRU_RECREDITO,
						   GRU_REESTRUCTURA,
						   GRU_PRESI,
						   GRU_TESOR,
						   GRU_SECRE
					FROM GRUPOS 
					JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					WHERE GRU_VIGENTE = 1
					AND SISTEMA_USUARIO.SIU_ID = :valor
					ORDER BY GRU_ID DESC, GRU_FECHA DESC';		
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $siu_id);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$json['content'] .= '<div class="col-md-6">
										<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
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
															<td align="center"><b>Fecha Entrega</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Monto</b></td>
															<td align="center">$'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Plazo</b></td>
															<td align="center">'.$row['GRU_PLAZO'].'</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Tasa</b></td>
															<td align="center">'.($row['GRU_TASA']*100).'%</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Promotor</b></td>
															<td align="center">
																<a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a>
															</td>
													  	</tr>
													  	'.($row['GRU_RECREDITO'] != 0 ?

													  		'<tr>
																<td align="center"><b>Recrédito</b></td>
																<td align="center">'.$row['GRU_RECREDITO'].'</td>
														  	</tr>'


													  		: '').'
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th></th>
															<th>Acreditado</th>
															<th>Celular</th>
															<th>Préstamo Otorgado</th>
															<th>Pago Sem.</th>
														</tr>
													</thead>
													<tbody>';

					$num = 1;
					$sql_per = "SELECT PERSONAS.PER_ID,
									   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
									   STATUS,
									   MONTO_INDIVIDUAL,
									   PAGO_SEMANAL_IND,
									   PER_CELULAR
								FROM PERSONAS
								JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								WHERE PERSONAS_GRUPOS.GRU_ID = :valor";
					/*$sql_per = 'SELECT PERSONAS.PER_ID,
									   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
									   STATUS,
									   MONTO_INDIVIDUAL,
									   PAGO_SEMANAL_IND,
									   PER_CELULAR
								FROM PERSONAS
								JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								WHERE PERSONAS_GRUPOS.GRU_ID = :valor';			*/
					$consulta_per = $db->prepare($sql_per);
					$consulta_per->bindParam(':valor', $row['GRU_ID']);
					$consulta_per->execute();
					$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result_per as $per) {

						$presidenta = ($row['GRU_PRESI'] == $per['PER_ID'] ? 'P' : '');
						$tesorera = ($row['GRU_TESOR'] == $per['PER_ID'] ? 'T' : '');
						$secretaria = ($row['GRU_SECRE'] == $per['PER_ID'] ? 'S' : '');

						$json['content'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center">'.$presidenta.$tesorera.$secretaria.'</td>
												<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'" target="_blank">'.$per['PER_NOMBRE'].'</a></td>
												<td align="center">'.$per['PER_CELULAR'].'</td>
												<td align="center">$'.$per['MONTO_INDIVIDUAL'].'</td>
												<td align="center">$'.$per['PAGO_SEMANAL_IND'].'</td>
											</tr>';
						$num++;
					}

					$json['content'] .='			</tbody>
												  </table>
											</div>
										</div>
										</div>';
				}

			} else {
				$json['content'] = "<h1 style='text-align:center;'>No se encuentran grupos vigentes</h1>";
			}
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		//INDIVIDUALES
		try {
			$db = $this->_conexion;
			$sql = "SELECT CRE_ID,
						   CRE_FECHA,
						   CRE_FECHA_ENTREGA,
						   CRE_MONTO_TOTAL,
						   CRE_PLAZO,
						   CRE_TASA,
						   SIU_NOMBRE,
						   CREDITO_INDIVIDUAL.PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   STATUS,
						   CRE_PAGO_SEMANAL,
						   CREDITO_INDIVIDUAL.SIU_ID,
						   PER_CELULAR
					FROM CREDITO_INDIVIDUAL 
					JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
					LEFT JOIN PERSONAS ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					WHERE CRE_VIGENTE = 1
					AND SISTEMA_USUARIO.SIU_ID = :valor
					ORDER BY CRE_FECHA DESC, CRE_ID DESC";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $siu_id);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {							
					$json['content'] .= '<div class="col-md-6">
										<div class="box border blue">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Individual '.$row['CRE_ID'].'</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>
													<a target="_blank" href="include/contrato-ind.php?id='.$row['CRE_ID'].'" class="contrato" data-id="'.$row['CRE_ID'].'">
														<i class="fa fa-file-text-o"></i>
													</a>
												</div>
											</div>
											<div class="box-body" style="display:none;">
												
												<table class="table table-striped general-info" data-id="'.$row['CRE_ID'].'">
													<tbody>
														<tr>
															<td align="center"><b>Fecha</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["CRE_FECHA_ENTREGA"])).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Monto</b></td>
															<td align="center">$'.number_format($row['CRE_MONTO_TOTAL'], 2).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Plazo</b></td>
															<td align="center">'.$row['CRE_PLAZO'].'</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Tasa</b></td>
															<td align="center">'.($row['CRE_TASA']*100).'%</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Promotor</b></td>
															<td align="center">
																<a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a> 
															</td>
													  	</tr>
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['CRE_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th>Acreditado</th>
															<th>Celular</th>
															<th>Pago Sem.</th>
														</tr>
													</thead>
													<tbody>
													<tr>
														<td align="center">1</td>
														<td align="center"><a data-id="'.$row['PER_ID'].'" href="../prospectos/cambios.php?id='.$row['PER_ID'].'&status='.$row['STATUS'].'" target="_blank">'.$row['PER_NOMBRE'].'</a></td>
														<td align="center">$'.$row['PER_CELULAR'].'</td>
														<td align="center">'.$row['CRE_PAGO_SEMANAL'].'</td>
													</tr>';

					$json['content'] .='			</tbody>
												  </table>
											</div>
										</div>
										</div>';
				}

			}
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-05-16
	 * 
	 * Personas de Recrédito
	 */	
	function getRecredito() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Grupo no válido.";
		$json['ids_clientes'] = array();
		$json['tabla'] = "";

		if(!isset($_POST['id']) || $this->is_empty($_POST['id'])) {
			$json['error'] = true;
			$json['msg'] = "Grupo no válido 1.";
		} else {
			$db = $this->_conexion;
			$sql = "SELECT GRU_ID FROM GRUPOS WHERE GRU_ID = :valor";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $_POST['id']);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$sql_cli = "SELECT PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
							WHERE GRU_ID = :grupo";
				$consulta_cli = $db->prepare($sql_cli);
				$consulta_cli->bindParam(':grupo', $_POST['id']);
				try {
					$consulta_cli->execute();	
					$puntero = $consulta_cli->fetchAll(PDO::FETCH_ASSOC);
					$num = 0;
					foreach ($puntero as $row) {
						$num++;
						$json['ids_clientes'][] = $row['PER_ID'];
						$json["tabla"] .= '<tr id="row_'.$num.'">
											<td align="center">
												'.$row['PER_NOMBRE'].'
												<input type="text" id="cli_id_'.$num.'" name="cli_id['.$num.']" data-id="'.$num.'" value="'.$row['PER_ID'].'" style="display:none;">
											</td>
											<td align="center">
												'.$row['PER_DIRECCION'].'
											</td>
											<td align="center">
												'.$row['PER_CELULAR'].'
											</td>
											<td align="center">
												'.$row['MONTO_SOLICITADO'].'
											</td>
											<td align="center">
												<input id="monto_individual_'.$num.'" name="monto_individual['.$num.']" type="text" class="form-control monto_individual" data-id="'.$num.'">
											</td>
											<td align="center">
												<input id="ahorro_d_'.$num.'" name="ahorro_d['.$num.']" type="text" class="form-control ahorro_d" data-id="'.$num.'" readonly="readonly">
											</td>
											<td align="center">
												<input id="comision_d_'.$num.'" name="comision_d['.$num.']" type="text" class="form-control comision_d" data-id="'.$num.'" readonly="readonly">
											</td>
											<td align="center">
												<input id="monto_otorgar_'.$num.'" name="monto_otorgar['.$num.']" type="text" class="form-control monto_otorgar" data-id="'.$num.'" readonly="readonly">
											</td>
											<td align="center">
												<input id="pago_semanal_'.$num.'" name="pago_semanal['.$num.']" type="text" class="form-control pago_semanal" data-id="'.$num.'" readonly="readonly">
											</td>
											<td align="center">
												<a class="eliminar-cl" href="#" data-id="'.$num.'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>
											</td>
										</tr>';
					}

				} catch (PDOException $e) {
					$json["error"] = true;
					$json['msg'] = "Grupo no válido 3.";
				}	

			} else {
				$json['error'] = true;
				$json['msg'] = "Grupo no válido 2.";
			} 
		}

		echo json_encode($json);
	}

	function filterGroups() {
		$json = array();
		$fechas = "";
		$fechas_2 = "";
		if($_POST['fecha_1'] != '' && $_POST['fecha_2'] != '') {
			//Cambio de Formato
			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

			$fechas = " AND GRU_FECHA_ENTREGA >= '".$fecha_1."' AND GRU_FECHA_ENTREGA <= '".$fecha_2."' ";
			$fechas_2 = " AND CRE_FECHA_ENTREGA >= '".$fecha_1."' AND CRE_FECHA_ENTREGA <= '".$fecha_2."' ";
		}

		if($_POST['tipo'] == 1 || $_POST['tipo'] == 2 || $_POST['tipo'] == 0 || $_POST['tipo'] == 4) {
			$tipo = "";
			if($_POST['tipo'] == 1) {
				$tipo = " AND GRU_RECREDITO = 0 AND GRU_REESTRUCTURA = 0 ";
			} else if($_POST['tipo'] == 2) {
				$tipo = " AND GRU_RECREDITO != 0 ";
			} else if($_POST['tipo'] == 4) {
				$tipo = " AND GRU_REESTRUCTURA = 1 ";
			}

			if(!isset($_SESSION)){
				@session_start();
			}
			
			//Se prepara la consulta
			$siu_id = $_SESSION["mp"]["userid"];		

			try {
				$db = $this->_conexion;
				/*$sql = "SELECT GRU_ID,
							   GRU_FECHA,
							   GRU_MONTO_TOTAL,
							   GRU_PLAZO,
							   GRU_TASA,
							   GRU_RECREDITO
						FROM GRUPOS 
						WHERE SIU_ID = :valor
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";*/
				$sql = 'SELECT GRU_ID,
							   GRU_FECHA_ENTREGA,
							   GRU_MONTO_TOTAL,
							   GRU_PLAZO,
							   GRU_TASA,
							   SIU_NOMBRE,
							   GRUPOS.SIU_ID,
							   GRU_RECREDITO,
							   GRU_REESTRUCTURA,
							   GRU_PRESI,
							   GRU_TESOR,
							   GRU_SECRE
						FROM GRUPOS 
						JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
						WHERE GRU_VIGENTE = 1
						AND SISTEMA_USUARIO.SIU_ID = :valor '.$tipo.' '.$fechas.'
						ORDER BY GRU_ID DESC, GRU_FECHA DESC';		
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $siu_id);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['content'] .= '<div class="col-md-6">
											<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
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
																<td align="center"><b>Fecha Entrega</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Monto</b></td>
																<td align="center">$'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Plazo</b></td>
																<td align="center">'.$row['GRU_PLAZO'].'</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Tasa</b></td>
																<td align="center">'.($row['GRU_TASA']*100).'%</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Promotor</b></td>
																<td align="center">
																	<a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a>
																</td>
														  	</tr>
														  	'.($row['GRU_RECREDITO'] != 0 ?

														  		'<tr>
																	<td align="center"><b>Recrédito</b></td>
																	<td align="center">'.$row['GRU_RECREDITO'].'</td>
															  	</tr>'


														  		: '').'
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>#</th>
																<th></th>
																<th>Acreditado</th>
																<th>Celular</th>
																<th>Préstamo Otorgado</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>';

						$num = 1;
						$sql_per = "SELECT PERSONAS.PER_ID,
										   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
										   STATUS,
										   MONTO_INDIVIDUAL,
										   PAGO_SEMANAL_IND,
										   PER_CELULAR
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
									WHERE PERSONAS_GRUPOS.GRU_ID = :valor";
						/*$sql_per = 'SELECT PERSONAS.PER_ID,
										   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
										   STATUS,
										   MONTO_INDIVIDUAL,
										   PAGO_SEMANAL_IND,
										   PER_CELULAR
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
									WHERE PERSONAS_GRUPOS.GRU_ID = :valor';			*/
						$consulta_per = $db->prepare($sql_per);
						$consulta_per->bindParam(':valor', $row['GRU_ID']);
						$consulta_per->execute();
						$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result_per as $per) {

							$presidenta = ($row['GRU_PRESI'] == $per['PER_ID'] ? 'P' : '');
							$tesorera = ($row['GRU_TESOR'] == $per['PER_ID'] ? 'T' : '');
							$secretaria = ($row['GRU_SECRE'] == $per['PER_ID'] ? 'S' : '');

							$json['content'] .= '<tr>
													<td align="center">'.$num.'</td>
													<td align="center">'.$presidenta.$tesorera.$secretaria.'</td>
													<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'" target="_blank">'.$per['PER_NOMBRE'].'</a></td>
													<td align="center">'.$per['PER_CELULAR'].'</td>
													<td align="center">$'.$per['MONTO_INDIVIDUAL'].'</td>
													<td align="center">$'.$per['PAGO_SEMANAL_IND'].'</td>
												</tr>';
							$num++;
						}

						$json['content'] .='			</tbody>
													  </table>
												</div>
											</div>
											</div>';
					}

				} else {
					$json['content'] = "<h1 style='text-align:center;'>No se encuentran grupos vigentes</h1>";
				}
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}


		}

		if($_POST['tipo'] == 3 || $_POST['tipo'] == 0) {

			//INDIVIDUALES
			try {
				$db = $this->_conexion;
				$sql = "SELECT CRE_ID,
							   CRE_FECHA,
							   CRE_FECHA_ENTREGA,
							   CRE_MONTO_TOTAL,
							   CRE_PLAZO,
							   CRE_TASA,
							   SIU_NOMBRE,
							   CREDITO_INDIVIDUAL.PER_ID,
							   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
							   STATUS,
							   CRE_PAGO_SEMANAL,
							   CREDITO_INDIVIDUAL.SIU_ID,
							   PER_CELULAR
						FROM CREDITO_INDIVIDUAL 
						JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
						LEFT JOIN PERSONAS ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
						WHERE CRE_VIGENTE = 1
						AND SISTEMA_USUARIO.SIU_ID = :valor ".$fechas_2."
						ORDER BY CRE_FECHA DESC, CRE_ID DESC";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $siu_id);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {							
						$json['content'] .= '<div class="col-md-6">
											<div class="box border blue">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Individual '.$row['CRE_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato-ind.php?id='.$row['CRE_ID'].'" class="contrato" data-id="'.$row['CRE_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
													</div>
												</div>
												<div class="box-body" style="display:none;">
													
													<table class="table table-striped general-info" data-id="'.$row['CRE_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["CRE_FECHA_ENTREGA"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Monto</b></td>
																<td align="center">$'.number_format($row['CRE_MONTO_TOTAL'], 2).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Plazo</b></td>
																<td align="center">'.$row['CRE_PLAZO'].'</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Tasa</b></td>
																<td align="center">'.($row['CRE_TASA']*100).'%</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Promotor</b></td>
																<td align="center">
																	<a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a> 
																</td>
														  	</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['CRE_ID'].'">
														<thead>
															<tr>
																<th>#</th>
																<th>Acreditado</th>
																<th>Celular</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>
														<tr>
															<td align="center">1</td>
															<td align="center"><a data-id="'.$row['PER_ID'].'" href="../prospectos/cambios.php?id='.$row['PER_ID'].'&status='.$row['STATUS'].'" target="_blank">'.$row['PER_NOMBRE'].'</a></td>
															<td align="center">$'.$row['PER_CELULAR'].'</td>
															<td align="center">'.$row['CRE_PAGO_SEMANAL'].'</td>
														</tr>';

						$json['content'] .='			</tbody>
													  </table>
												</div>
											</div>
											</div>';
					}

				}
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}

		}

		echo json_encode($json);
	}
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "saveRecord":
			$libs->saveRecord();
			break;
		case "deleteRecord":
			$libs->deleteRecord();
			break;
		case "showRecord":
			$libs->showRecord();
			break;
		case "showClients":
			$libs->showClients();
			break;	
		case "printGroups":
			$libs->printGroups();
			break;
		case "filterGroups":
			$libs->filterGroups();
			break;		
		case "getRecredito":
			$libs->getRecredito();
			break;						
	}
}
?>