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

$module = 7;

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

		$obligatorios = array("fecha",
							  "plazo",
							  "tasa",
							  "fecha_entrega",
							  "fecha_inicial",
							  "comision_p",
							  "monto_otorgado",
							  "pago",
							  "monto_total",
							  "monto_total_entregar",
							  "pago_total_semanal",
							  "domicilio",
							  "num_ext",
							  "colonia",
							  "municipio",
							  "estado",
							  "cp",
							  "promotor");

		$excepciones = array("cli_id",
							 "monto_individual",
							 "ahorro_d",
							 "comision_d",
							 "monto_otorgar",
							 "pago_semanal",
							 "orden");

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
			} else if(count($_POST['cli_id']) < 1 || count($_POST['cli_id']) > 15) {
				$json["error"] = true;
				$json["focus"] = "clientes";
				$json["msg"] = "Deben de ser al menos 7 personas en el Grupo y máximo 15.";
			}
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

				//$ahorro_p = $_POST['ahorro_p'] / 100;

				$recredito = 0;
				if(isset($_POST['recredito'])) {
					$recredito = $_POST['grupo_rec'];
				}

				$transferencia = 0;
				if(isset($_POST['transferencia'])) {
					$transferencia = 1;
				}

				$reestructura = 0;
				if(isset($_POST['reestructura'])) {
					$reestructura = 1;
				}

				$comision_p = $_POST['comision_p'] / 100;

				/*$pago_capital = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes = $pago_capital * $tasa;
				$pago_semanal = $pago_capital + $pago_interes;*/


				//Verifica Presidenta / Tesorera / Secretaria
				$presidenta = 0;
				if(isset($_POST['presidenta'])) {
					$presidenta = $_POST['presidenta'];
				}

				$tesorera = 0;
				if(isset($_POST['tesorera'])) {
					$tesorera = $_POST['tesorera'];
				}

				$secretaria = 0;
				if(isset($_POST['secretaria'])) {
					$secretaria = $_POST['secretaria'];
				}

				$pago_capital_total = $_POST['monto_total'] / $_POST['plazo'];
				$pago_interes_total = $pago_capital_total * $tasa;
				$pago_semanal_total = $pago_capital_total + $pago_interes_total;

				$values = array($fecha,
								$_POST["plazo"],
								$tasa,
								$fecha_inicial,
								$fecha_entrega,
								$recredito,
								$reestructura,
								$comision_p,
								$_POST['domicilio'],
								$_POST['num_ext'],
								$_POST['num_int'],
								$_POST['colonia'],
								$_POST['municipio'],
								$_POST['estado'],
								$_POST['cp'],
								$_POST['promotor'],
								$_POST['monto_total'],
								$_POST['monto_total_entregar'],
								$pago_capital_total,
								$pago_interes_total,
								$pago_semanal_total,
								$transferencia,
								$presidenta,
								$tesorera,
								$secretaria);


				if(isset($_POST['id'])) {
					$sql = "UPDATE GRUPOS SET GRU_FECHA = ?, 
											GRU_PLAZO = ?,
											GRU_TASA = ?,
											GRU_FECHA_INICIAL = ?,
											GRU_FECHA_ENTREGA = ?,
											GRU_RECREDITO = ?,
											GRU_REESTRUCTURA = ?,
											GRU_COMISION_P = ?,
											GRU_DOMICILIO = ?,
											GRU_NUM_EXT = ?,
											GRU_NUM_INT = ?,
											GRU_COLONIA = ?,
											GRU_MUNICIPIO = ?,
											GRU_ESTADO = ?,
											GRU_CP = ?,
											SIU_ID = ?,
											GRU_MONTO_TOTAL = ?,
											GRU_MONTO_TOTAL_ENTREGAR = ?,
											PAGO_CAPITAL = ?,
											PAGO_INTERES = ?,
											PAGO_SEMANAL = ?,
											GRU_TRANSFERENCIA = ?,
											GRU_PRESI = ?,
											GRU_TESOR = ?,
											GRU_SECRE = ?
							WHERE GRU_ID = ?";
					$values[]= $_POST['id'];

				} else {
					$sql = "INSERT INTO GRUPOS (GRU_FECHA, 
												GRU_PLAZO,
												GRU_TASA,
												GRU_FECHA_INICIAL,
												GRU_FECHA_ENTREGA,
												GRU_RECREDITO,
												GRU_REESTRUCTURA,
												GRU_COMISION_P,
												GRU_DOMICILIO,
												GRU_NUM_EXT,
												GRU_NUM_INT,
												GRU_COLONIA,
												GRU_MUNICIPIO,
												GRU_ESTADO,
												GRU_CP,
												SIU_ID,
												GRU_MONTO_TOTAL,
												GRU_MONTO_TOTAL_ENTREGAR,
												PAGO_CAPITAL,
												PAGO_INTERES,
												PAGO_SEMANAL,
												GRU_TRANSFERENCIA,
												GRU_PRESI,
												GRU_TESOR,
												GRU_SECRE)
								  	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
								  			?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
								  			?, ?, ?, ?, ? )";
				}

				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$last_id = $_POST['id'];

					//Elimina registros de PERSONAS_GRUPOS
					try{
						$consulta_pg = $db->prepare("DELETE FROM PERSONAS_GRUPOS WHERE GRU_ID = :valor");
						$consulta_pg->bindParam(':valor', $_POST['id']);
						$consulta_pg->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Elimina registros de TABLA_PAGOS
					try{
						$consulta_pa = $db->prepare("DELETE FROM TABLA_PAGOS WHERE GRU_ID = :valor");
						$consulta_pa->bindParam(':valor', $_POST['id']);
						$consulta_pa->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Elimina registros de FINIQUITO
					try{
						$consulta_fi = $db->prepare("DELETE FROM FINIQUITO_RECREDITO WHERE GRU_ID = :valor");
						$consulta_fi->bindParam(':valor', $_POST['id']);
						$consulta_fi->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Elimina registros de PAGOS_INDIVIDUALES
					try{
						$consulta_pi = $db->prepare("DELETE FROM PAGOS_INDIVIDUALES WHERE GRU_ID = :valor");
						$consulta_pi->bindParam(':valor', $_POST['id']);
						$consulta_pi->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

				} else {
					$last_id = $this->last_id();
				}

				$pago_capital_total = 0;
				$pago_interes_total = 0;
				$pago_semanal_total = 0;

				$personas_rechazadas = "";
				$p_rechazo = false;

				asort($_POST['orden']);

				foreach ($_POST['orden'] as $num => $orden) {
					$id_cliente = $_POST['cli_id'][$num];
					/*$sql_sl = "SELECT CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
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
					if($row_sl['MAXIMO_PAGAR'] > $_POST['monto_individual'][$num]) {	*/
						$pago_capital = (float) $_POST['monto_individual'][$num] / $_POST['plazo'];
						$pago_semanal = (float) $_POST['pago_semanal'][$num];
						//die($pago_semanal." ".$_POST['pago_semanal'][$num]);
						$pago_interes = $pago_semanal - $pago_capital;

						$pago_capital_total += $pago_capital;
						$pago_interes_total += $pago_interes;
						$pago_semanal_total += $pago_semanal;

						$sql_cg = "INSERT INTO PERSONAS_GRUPOS (PER_ID,
																GRU_ID,
																MONTO_INDIVIDUAL,
																COMAP_D,
																MONTO_OTORGAR,
																PAGO_CAPITAL_IND,
																PAGO_INTERES_IND,
																PAGO_SEMANAL_IND)
											VALUES (?, ?, ?,
													?, ?, ?,
													?, ?)";
						$values_cg = array($id_cliente,
										   $last_id,
										   $_POST['monto_individual'][$num],
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


						$fecha = $fecha_inicial;
						//Insert de Pagos Individuales
						for ($i=0; $i < $_POST['plazo']; $i++) {

							$sql_ind = "INSERT INTO PAGOS_INDIVIDUALES (PI_FECHA, 
																		PER_ID,
																		GRU_ID,
																		PI_MONTO,
																		PI_PAGO,
																		PI_PENDIENTE,
																		PI_AHORRO,
																		PI_NUM)
									  	VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
							$values_ind = array($fecha,
												$id_cliente,
												$last_id,
												$pago_semanal,
												0,
												$pago_semanal,
												0,
												($i+1));		
							$consulta_ind = $this->_conexion->prepare($sql_ind);	
							$consulta_ind->execute($values_ind);	


							//Nueva Fecha
							$fecha_ = strtotime($fecha);
							$fecha = strtotime('+1 week', $fecha_);
							$fecha = date("Y-m-d", $fecha);	
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

					/*} else {
						$p_rechazo = true;

						//UPDATE de personas rechazadas
						$sql_rechazo = "UPDATE PERSONAS SET STATUS=2,
															RAZON_RECHAZO = ?
								   		WHERE PER_ID = ?";
						$values_rechazo = array("El Monto del Cliente excede el límite autorizado".$_POST['monto_individual'][$num]." en el Grupo ".$last_id,
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

					}*/

					
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
														   GRU_ID,
														   TP_FALTANTE)
								  VALUES (?, ?, ?, ?)";
					$values_pagos = array($fecha,
										  $pago_semanal_total,
										  $last_id,
										  $pago_semanal_total);	
					$consulta_pagos = $db->prepare($sql_pagos);

					try {
						$consulta_pagos->execute($values_pagos);
					} catch (PDOException $e) {
						$db->rollBack();
						$dbgMsg = isset($debug)?"--SQL: ".$sql_pagos.(isset($values_pagos)?"\n--Values: ".print_r($values_pagos):""):"";
						die($e->getMessage().$dbgMsg);
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Si es el último pago
					if($i == $_POST['plazo']-1) {
						$sql_in = "UPDATE GRUPOS SET GRU_FECHA_FINAL = ?
									WHERE GRU_ID = ?";

						$values_in = array($fecha,
										   $last_id);
						$consulta2 = $db->prepare($sql_in);

						try {
							$consulta2->execute($values_in);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = isset($debug)?"--SQL: ".$sql_in.(isset($values_in)?"\n--Values: ".print_r($values_in):""):"";
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}
					}

					//Nueva Fecha
					$fecha_ = strtotime($fecha);
					$fecha = strtotime('+1 week', $fecha_);
					$fecha = date("Y-m-d", $fecha);

				}	


				/*Si es recrédito entonces debe de verificar en qué semana se está 
				realizando para cambiar el Flujo Semanal*/
				if($recredito != 0) {
					/*Busca en Tabla de Pagos*/

					//Primer día de la semana
					//$fecha_actual = date("Y-m-d");
					//$lunes = date("Y-m-d", $this->last_monday($fecha_actual)); 
					$lunes = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_entrega'])));

					//Verifica todos los grupos que serán afectados
					$sql_grupos = "SELECT PERSONAS_GRUPOS.GRU_ID 
								   FROM PERSONAS_GRUPOS
								   JOIN GRUPOS ON PERSONAS_GRUPOS.GRU_ID = GRUPOS.GRU_ID
								   WHERE PERSONAS_GRUPOS.GRU_ID != ?
								   AND GRU_VIGENTE = 1 AND (";
					$values_grupos = array($last_id);			   
					foreach ($_POST['cli_id'] as $num => $id_cliente) {
						$sql_grupos .= " PER_ID = ? OR";
						$values_grupos[]= $id_cliente;
					}

					$sql_grupos = trim($sql_grupos, "OR");

					$sql_grupos.=") GROUP BY PERSONAS_GRUPOS.GRU_ID";

					$consulta_grupos = $db->prepare($sql_grupos);

					try {
						$consulta_grupos->execute($values_grupos);
					} catch (PDOException $e) {
						$db->rollBack();
						$dbgMsg = "--SQL: ".$sql_grupos.(isset($values_grupos)?"\n--Values: ".print_r($values_grupos):"");
						die($e->getMessage().$dbgMsg);
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}  

					$result_grupos = $consulta_grupos->fetchAll(PDO::FETCH_ASSOC);

					$grupos_rec = array();
					foreach ($result_grupos as $row_grupos) {
						$grupos_rec[]= $row_grupos['GRU_ID'];
					}

					//Tabla de Pagos lo pone como que están recreditando
					if(count($grupos_rec) > 0) {
						$sql_up_tp = "UPDATE TABLA_PAGOS SET TP_PAGADO_REC = 1
									  WHERE TP_FECHA >= ?
									  AND (";
						$values_up_tp = array($lunes);			  
						foreach ($grupos_rec as $grupo_rec) {
							$sql_up_tp.=" GRU_ID = ? OR";
							$values_up_tp[]=$grupo_rec;
						}			

						$sql_up_tp = trim($sql_up_tp, "OR");
						$sql_up_tp.=")";  

						$consulta_up_tp = $db->prepare($sql_up_tp);

						try {
							$consulta_up_tp->execute($values_up_tp);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = "--SQL: ".$sql_up_tp.(isset($values_up_tp)?"\n--Values: ".print_r($values_up_tp):"");
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}
					}

					if(count($grupos_rec) > 0) {
						//Busca los nuevos pagos semanales del grupo
						$cantidad = array();
						$semanas = array();

						/*Ingresa registro en FINIQUITO_RECREDITO*/
						//Cuánto dinero se finiquita ? 
						$total_finiquito = 0;
						foreach ($_POST['cli_id'] as $num => $id_cliente) {
							//En vez de FINIQUITAR a los del grupo, se les va a abonar el pago
							$sql_pi = "SELECT * FROM PAGOS_INDIVIDUALES 
									   WHERE PI_FECHA >= ?
									   AND PER_ID = ?
									   AND (";
							$values_pi = array($lunes,
											   $id_cliente);
							foreach ($grupos_rec as $grupo_rec) {
								$sql_pi.=" GRU_ID = ? OR";
								$values_pi[]=$grupo_rec;
							}
							
							$sql_pi = trim($sql_pi, "OR");
							$sql_pi.=")";

							$consulta_pi = $db->prepare($sql_pi);
							$consulta_pi->execute($values_pi);	
							$result_pi = $consulta_pi->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result_pi as $row_pi) {
								if($row_pi['PI_PAGO'] != $row_pi['PI_MONTO']) {
									//Uno por uno actualizamos los pagos individuales
									$sql_up_pi = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
																			 PI_PENDIENTE = 0,
																			 PI_FECHA_REG = ?,
																			 PI_REC = 2
											   	  WHERE PI_ID = ?";
									$values_up_pi = array($row_pi['PI_MONTO'],
														  $lunes,
														  $row_pi['PI_ID']);

									$consulta_up_pi = $db->prepare($sql_up_pi);
									$consulta_up_pi->execute($values_up_pi);

									$pago_desglosado = $row_pi['PI_MONTO'] - $row_pi['PI_PAGO'];

									//Agrega pago desglosado
									$sql_pd = "INSERT INTO PAGOS_DESGLOSADOS (PD_FECHA,
																			   PD_MONTO,
																			   PD_AHORRO,
																			   PER_ID,
																			   GRU_ID,
																			   PI_ID,
																			   PD_RECREDITO)
												  	  VALUES ( ?, ?, ?, ?, ?, ?, ? )";
									$values_pd = array($lunes,
													  $pago_desglosado,
													  0,
													  $row_pi['PER_ID'],
													  $row_pi['GRU_ID'],
													  $row_pi['PI_ID'],
													  1);
									$consulta_pd = $db->prepare($sql_pd);	
									try {
										$consulta_pd->execute($values_pd);
									} catch (PDOException $e) {
										$db->rollBack();
										$dbgMsg = "--SQL: ".$sql_pd.(isset($values_pd)?"\n--Values: ".print_r($values_pd):"");
										die($e->getMessage().$dbgMsg);
										$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
									}
								}

							}

						}
					}
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
		$json["tabla"] = "";

		if(isset($_POST['id'])){
			try {
				$db = $this->_conexion;
				$sql = "SELECT * FROM GRUPOS WHERE GRU_ID = :valor";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$puntero = $consulta->fetch(PDO::FETCH_ASSOC);
					$json = array_merge($json, $puntero);
					$json['GRU_FECHA'] = date("d/m/Y",strtotime($puntero['GRU_FECHA']));
					$json['GRU_FECHA_INICIAL'] = date("d/m/Y",strtotime($puntero['GRU_FECHA_INICIAL']));
					$json['GRU_FECHA_ENTREGA'] = date("d/m/Y",strtotime($puntero['GRU_FECHA_ENTREGA']));
					$json['GRU_TASA'] = $puntero['GRU_TASA'] * 100;
					//$json['GRU_AHORRO_P'] = $puntero['GRU_AHORRO_P'] * 100;
					$json['GRU_COMISION_P'] = $puntero['GRU_COMISION_P'] * 100;

					$presidenta = $puntero['GRU_PRESI'];
					$tesorera = $puntero['GRU_TESOR'];
					$secretaria = $puntero['GRU_SECRE'];

					$sql_cli = "SELECT PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO,
								   MONTO_INDIVIDUAL,
								   AHORRO_D,
								   MONTO_OTORGAR,
								   COMAP_D,
								   PAGO_SEMANAL_IND
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
							WHERE PERSONAS_GRUPOS.GRU_ID = :grupo";
					$consulta_cli = $db->prepare($sql_cli);
					$consulta_cli->bindParam(':grupo', $_POST['id']);
					try {
						$consulta_cli->execute();	
						$puntero = $consulta_cli->fetchAll(PDO::FETCH_ASSOC);
						$num = 0;
						foreach ($puntero as $row) {
							$num++;
							$json['ids_clientes'][] = $row['PER_ID'];

							$sql_pendiente ="SELECT SUM(PI_PENDIENTE) as PENDIENTE
											FROM PAGOS_INDIVIDUALES
											WHERE PER_ID = :persona
											AND GRU_ID != :grupo"; 
							$consulta_pendiente = $db->prepare($sql_pendiente);
							$consulta_pendiente->bindParam(':persona', $row['PER_ID']);	
							$consulta_pendiente->bindParam(':grupo', $_POST['id']);	
							$consulta_pendiente->execute();	
							$row_pendiente = $consulta_pendiente->fetch(PDO::FETCH_ASSOC);

							$sql_grupo ="SELECT GRU_ID
											FROM PERSONAS_GRUPOS
											WHERE PER_ID = :persona
											AND GRU_ID != :grupo
											ORDER BY GRU_ID DESC
								  			LIMIT 1"; 
							$consulta_grupo = $db->prepare($sql_grupo);
							$consulta_grupo->bindParam(':persona', $row['PER_ID']);	
							$consulta_grupo->bindParam(':grupo', $_POST['id']);	
							$consulta_grupo->execute();	
							$row_grupo = $consulta_grupo->fetch(PDO::FETCH_ASSOC);			

							$json["tabla"] .= '<tr id="row_'.$num.'">
												<td>
													<label class="radio">
														<div class="radio">
															<span class="">
																<input type="radio" class="uniform" id="presidenta_'.$num.'" name="presidenta" value="'.$row['PER_ID'].'" '.($presidenta == $row['PER_ID'] ? 'checked = "checked"' : '').'>P
															</span>
														</div> 
													</label>
													<label class="radio">
														<div class="radio">
															<span class="">
																<input type="radio" class="uniform" id="secretaria_'.$num.'" name="secretaria" value="'.$row['PER_ID'].'" '.($secretaria == $row['PER_ID'] ? 'checked = "checked"' : '').'>S
															</span>
														</div> 
													</label>
													<label class="radio">
														<div class="radio">
															<span class="">
																<input type="radio" class="uniform" id="tesorera_'.$num.'" name="tesorera" value="'.$row['PER_ID'].'" '.($tesorera == $row['PER_ID'] ? 'checked = "checked"' : '').'>T
															</span>
														</div> 
													</label>
												</td>
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
													'.$row_pendiente['PENDIENTE'].'
												</td>
												<td align="center">
													'.$row_grupo['GRU_ID'].'
												</td>
												<td align="center">
													<input id="monto_individual_'.$num.'" name="monto_individual['.$num.']" type="text" class="form-control monto_individual" data-id="'.$num.'" value="'.$row['MONTO_INDIVIDUAL'].'">
												</td>
												<td align="center">
													<input id="comision_d_'.$num.'" name="comision_d['.$num.']" type="text" class="form-control comision_d" data-id="'.$num.'" readonly="readonly" value="'.$row['COMAP_D'].'">
												</td>
												<td align="center">
													<input id="monto_otorgar_'.$num.'" name="monto_otorgar['.$num.']" type="text" class="form-control monto_otorgar" data-id="'.$num.'" readonly="readonly" value="'.$row['MONTO_OTORGAR'].'">
												</td>
												<td align="center">
													<input id="pago_semanal_'.$num.'" name="pago_semanal['.$num.']" type="text" class="form-control pago_semanal" data-id="'.$num.'" readonly="readonly" value="'.$row['PAGO_SEMANAL_IND'].'">
												</td>
												<td align="center">
													<input id="orden_'.$num.'" name="orden['.$num.']" type="text" class="form-control" data-id="'.$num.'" value="'.$num.'">
												</td>
												<td align="center" class="cont-button">
													<a class="eliminar-cl" href="#" data-id="'.$num.'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>
												</td>
											</tr>';
						} 
					} catch (PDOException $e) {
						die($e->getMessage().$dbgMsg);
					}	

				} else {
					$json['error'] = true;
				} 
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		} else {
			$json['error'] = true;
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
			$sql = "SELECT SUM(PI_PENDIENTE) as PENDIENTE,
						   PERSONAS.PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_CELULAR,
						   MONTO_SOLICITADO
					FROM PERSONAS 
                    LEFT JOIN (SELECT PI_PENDIENTE, PER_ID FROM PAGOS_INDIVIDUALES WHERE PI_FECHA < CURRENT_DATE) PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
					WHERE (STATUS != -1
					AND STATUS != 2)
					AND CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) LIKE '%".$term."%'";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $term);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					//Revisa el último grupo
					$sql_grupo = "SELECT GRU_ID
								  FROM PERSONAS_GRUPOS
								  WHERE PER_ID = ?
								  ORDER BY GRU_ID DESC
								  LIMIT 1";
					$consulta_grupo = $db->prepare($sql_grupo);
					$values_grupo = array($row['PER_ID']);
					$consulta_grupo->execute($values_grupo);
					$row_grupo = $consulta_grupo->fetch(PDO::FETCH_ASSOC);

					$persona['id'] = $row['PER_ID'];
					$persona['name'] = $row['PER_NOMBRE'];
					$persona['value'] = $row['PER_NOMBRE'];
					$persona['address'] = $row['PER_DIRECCION']." ".$row['PER_NUM']." ".$row['PER_COLONIA'];
					$persona['phone'] = $row['PER_CELULAR'];
					$persona['money'] = $row['MONTO_SOLICITADO'];
					$persona['pend'] = is_null($row['PENDIENTE']) ? 0 : $row['PENDIENTE'] ;
					$persona['grupo'] = $row_grupo['GRU_ID'];
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
		$json['total'] = 0;
		global $module;	

		try {
			$db = $this->_conexion;
			$sql = "SELECT GRU_ID,
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
					ORDER BY GRU_ID DESC, GRU_FECHA DESC";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$json['total'] += $row['GRU_MONTO_TOTAL'];
					$editar = '<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-pencil"></i>
													</a>';
					$editar_promotor = '<a href="#" class="editar-promotor" data-id="'.$row['GRU_ID'].'"><i class="fa fa-pencil"></i></a>';								
					$json['content'] .= '<div class="col-md-6">
										<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>													
													<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-file-text-o"></i>
													</a>
													'.$this->printLink($module, "cambios", $editar).'
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
																'.$this->printLink($module, "cambios", $editar_promotor).'
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
					ORDER BY CRE_FECHA DESC, CRE_ID DESC";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$json['total'] += $row['CRE_MONTO_TOTAL'];
					$editar = '<a href="cambios-ind.php?id='.$row['CRE_ID'].'" class="edit" data-id="'.$row['CRE_ID'].'">
														<i class="fa fa-pencil"></i>
													</a>';
					$editar_promotor = '<a href="#" class="editar-promotor" data-id="'.$row['CRE_ID'].'"><i class="fa fa-pencil"></i></a>';								
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
													'.$this->printLink($module, "cambios", $editar).'
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
																'.$this->printLink($module, "cambios", $editar_promotor).'
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

		$json['total'] = number_format($json['total']);
		
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-02
	 * 
	 * Impresión de los Grupos en página de inicio
	 */
	function printGroupsPr() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";
		$json['promotor'] = "";

		if(isset($_POST['id'])) {
			$siu_id = $_POST['id'];		

			try {
				$db = $this->_conexion;
				$sql = "SELECT GRU_ID,
							   GRU_FECHA,
							   GRU_MONTO_TOTAL,
							   GRU_PLAZO,
							   GRU_TASA,
							   SIU_NOMBRE,
							   GRU_RECREDITO,
							   GRU_PRESI,
							   GRU_TESOR,
							   GRU_SECRE
						FROM GRUPOS
						JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID 
						WHERE GRUPOS.SIU_ID = :valor
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $siu_id);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$editar = '<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-pencil"></i>
														</a>';
						$json['promotor'] = $row['SIU_NOMBRE'];
						$json['content'] .= '<div class="col-md-4">
											<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary').'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														'.$this->printLink($module, "cambios", $editar).'
													</div>
												</div>
												<div class="box-body" style="display:none;">
													
													<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["GRU_FECHA"])).'</td>
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
										   PAGO_SEMANAL_IND
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
									WHERE PERSONAS_GRUPOS.GRU_ID = :valor";
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

		} else {
			$json['error'] = true;
		}

		echo json_encode($json);
	}	

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-03
	 * 
	 * Select de Promotores
	 */
	function getPromotores() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="promotor" name="promotor" class="form-control">';

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
				/*$sql_cli = "SELECT PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
							WHERE GRU_ID = :grupo";*/
				$sql_cli = "SELECT SUM(PI_PENDIENTE) as PENDIENTE,
								   PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_NUM,
								   PER_COLONIA,
								   PER_CELULAR,
								   MONTO_SOLICITADO
							FROM PERSONAS 
		                    LEFT JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
		                    WHERE GRU_ID = :grupo
		                    GROUP BY PER_ID";			
				$consulta_cli = $db->prepare($sql_cli);
				$consulta_cli->bindParam(':grupo', $_POST['id']);
				try {
					$consulta_cli->execute();	
					$puntero = $consulta_cli->fetchAll(PDO::FETCH_ASSOC);
					$num = 0;
					foreach ($puntero as $row) {
						$num++;
						$json['ids_clientes'][] = $row['PER_ID'];

						$sql_grupo = "SELECT GRU_ID
									  FROM PERSONAS_GRUPOS
									  WHERE PER_ID = ?
									  ORDER BY GRU_ID DESC
									  LIMIT 1";
						$consulta_grupo = $db->prepare($sql_grupo);
						$values_grupo = array($row['PER_ID']);
						$consulta_grupo->execute($values_grupo);
						$row_grupo = $consulta_grupo->fetch(PDO::FETCH_ASSOC);

						$json["tabla"] .= '<tr id="row_'.$num.'">
											<td>
												<label class="radio">
													<div class="radio">
														<span class="">
															<input type="radio" class="uniform" id="presidenta_'.$num.'" name="presidenta" value="'.$row['PER_ID'].'">P
														</span>
													</div> 
												</label>
												<label class="radio">
													<div class="radio">
														<span class="">
															<input type="radio" class="uniform" id="secretaria_'.$num.'" name="secretaria" value="'.$row['PER_ID'].'">S
														</span>
													</div> 
												</label>
												<label class="radio">
													<div class="radio">
														<span class="">
															<input type="radio" class="uniform" id="tesorera_'.$num.'" name="tesorera" value="'.$row['PER_ID'].'">T
														</span>
													</div> 
												</label>
											</td>
											<td align="center">
												'.$row['PER_NOMBRE'].'
												<input type="text" id="cli_id_'.$num.'" name="cli_id['.$num.']" data-id="'.$num.'" value="'.$row['PER_ID'].'" style="display:none;">
											</td>
											<td align="center">
												'.$row['PER_DIRECCION']." ".$row['PER_NUM']." ".$row['PER_COLONIA'].'
											</td>
											<td align="center">
												'.$row['PER_CELULAR'].'
											</td>
											<td align="center">
												'.$row['MONTO_SOLICITADO'].'
											</td>
											<td align="center">
												'.$row['PENDIENTE'].'
											</td>
											<td align="center">
												'.$row_grupo['GRU_ID'].'
											</td>
											<td align="center">
												<input id="monto_individual_'.$num.'" name="monto_individual['.$num.']" type="text" class="form-control monto_individual" data-id="'.$num.'">
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
												<input id="orden_'.$num.'" name="orden['.$num.']" type="text" class="form-control" data-id="'.$num.'" value="'.$num.'">
											</td>
											<td align="center" class="cont-button">
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

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-27
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Guarda Clientes
	 */
	function saveIndividual() {
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("fecha",
							  "plazo",
							  "tasa",
							  "fecha_entrega",
							  "fecha_inicial",
							  "ahorro_di",
							  "comision_p",
							  "monto_otorgado",
							  "domicilio",
							  "promotor");

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
				$json["msg"] = "Deben de ser 1 persona en el Crédito Individual.";
			} else if(count($_POST['cli_id']) != 1 ) {
				$json["error"] = true;
				$json["focus"] = "clientes";
				$json["msg"] = "Deben de ser 1 persona en el Crédito Individual.";
			}
		}

		if(!$json["error"]) {
			try {
				$db = $this->_conexion;
				$db->beginTransaction();

				//$fecha = date("Y-m-d",strtotime($_POST['fecha']));
				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
				$fecha_inicial = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_inicial'])));
				$fecha_entrega = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_entrega'])));

				//$personas = implode("|", $_POST['cli_id']);

				$tasa = $_POST['tasa'] / 100;

				//$ahorro_p = $_POST['ahorro_p'] / 100;

				$recredito = 0;
				if(isset($_POST['recredito'])) {
					$recredito = $_POST['grupo_rec'];
				}

				$comision_p = $_POST['comision_p'] / 100;

				/*$pago_capital = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes = $pago_capital * $tasa;
				$pago_semanal = $pago_capital + $pago_interes;*/
				//$pago_semanal_total = (($_POST['monto_individual']*($tasa * $_POST['plazo'])) + $_POST['monto_individual']) / $_POST['plazo'];
				$pago_semanal_total = $_POST['pago_semanal'];
				$pago_capital_total = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes_total = $pago_semanal_total - $pago_capital_total;

				$values = array($fecha,
								$_POST['cli_id'],
								$_POST["plazo"],
								$tasa,
								$fecha_inicial,
								$fecha_entrega,
								$_POST['ahorro_di'],
								$_POST['ahorro_di'],
								$recredito,
								$comision_p,
								$_POST['comision_d'],
								$_POST['domicilio'],
								$_POST['promotor'],
								$_POST['monto_individual'],
								$_POST['monto_otorgar'],
								$pago_capital_total,
								$pago_interes_total,
								$pago_semanal_total);


				if(isset($_POST['id'])) {
					$sql = "UPDATE CREDITO_INDIVIDUAL SET CRE_FECHA = ?, 
														PER_ID = ?,
														CRE_PLAZO = ?,
														CRE_TASA = ?,
														CRE_FECHA_INICIAL = ?,
														CRE_FECHA_ENTREGA = ?,
														CRE_AHORRO_P = ?,
														CRE_AHORRO_D = ?,
														CRE_RECREDITO = ?,
														CRE_COMISION_P = ?,
														CRE_COMISION_D = ?,
														CRE_DOMICILIO = ?,
														SIU_ID = ?,
														CRE_MONTO_TOTAL = ?,
														CRE_MONTO_ENTREGAR = ?,
														CRE_PAGO_CAPITAL = ?,
														CRE_PAGO_INTERES = ?,
														CRE_PAGO_SEMANAL = ?
							WHERE CRE_ID = ?";
					$values[]= $_POST['id'];

				} else {
					$sql = "INSERT INTO CREDITO_INDIVIDUAL (CRE_FECHA,
															PER_ID,
															CRE_PLAZO,
															CRE_TASA,
															CRE_FECHA_INICIAL,
															CRE_FECHA_ENTREGA,
															CRE_AHORRO_P,
															CRE_AHORRO_D,
															CRE_RECREDITO,
															CRE_COMISION_P,
															CRE_COMISION_D,
															CRE_DOMICILIO,
															SIU_ID,
															CRE_MONTO_TOTAL,
															CRE_MONTO_ENTREGAR,
															CRE_PAGO_CAPITAL,
															CRE_PAGO_INTERES,
															CRE_PAGO_SEMANAL)
								  	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,
								  			?, ?, ?, ?, ?, ?, ?, ?, ?)";
				}

				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$last_id = $_POST['id'];

					//Elimina registros de TABLA_PAGOS
					try{
						$consulta_pa = $db->prepare("DELETE FROM TABLA_PAGOS_IND WHERE CRE_ID = :valor");
						$consulta_pa->bindParam(':valor', $_POST['id']);
						$consulta_pa->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

				} else {
					$last_id = $this->last_id();
				}	

				//TABLA DE PAGOS
				$fecha = $fecha_inicial;

				for ($i=0; $i < $_POST['plazo']; $i++) { //Plazo
					$sql_pagos = "INSERT INTO TABLA_PAGOS_IND (TPI_FECHA,
															  TPI_MONTO,
															  TPI_FALTANTE,
															  CRE_ID)
								  VALUES (?, ?, ?, ?)";
					$values_pagos = array($fecha,
										  $pago_semanal_total,
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

				//Update de la Fecha del Último Pago
				$sql_up = "UPDATE CREDITO_INDIVIDUAL SET CRE_FECHA_FINAL = ?
						   WHERE CRE_ID = ?";
				$values_up = array($fecha,
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

				$db->commit();
				$json['msg'] = "El Crédito Individual se ha guardado con éxito.";	   

			} catch (PDOException $e) {
				$db->rollBack();
				$dbgMsg = isset($debug)?"--SQL: ".$sql.(isset($values)?"\n--Values: ".print_r($values):""):"";
				die($e->getMessage().$dbgMsg);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}
		}
		
		echo json_encode($json);
	}


	function filterGroups() {
		$json = array();
		global $module;

		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";		
		$json['total'] = 0;


		$promotor = "";
		if($_POST['promotor'] != 0) {
			$promotor = " AND SISTEMA_USUARIO.SIU_ID = ".$_POST['promotor']." ";
		}

		$fechas = "";
		$fechas_2 = "";
		if($_POST['fecha_1'] != '' && $_POST['fecha_2'] != '') {
			//Cambio de Formato
			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

			$fechas = " AND GRU_FECHA_ENTREGA >= '".$fecha_1."' AND GRU_FECHA_ENTREGA <= '".$fecha_2."' ";
			$fechas_2 = " AND CRE_FECHA_ENTREGA >= '".$fecha_1."' AND CRE_FECHA_ENTREGA <= '".$fecha_2."' ";
		}

		//if($_POST['tipo'] == 1 || $_POST['tipo'] == 2 || $_POST['tipo'] == 0 || $_POST['tipo'] == 4) {
		if (in_array(0, $_POST['tipo']) || in_array(1, $_POST['tipo']) || in_array(2, $_POST['tipo']) || in_array(4, $_POST['tipo'])) {
			try {

				$tipo = [];
				if(in_array(1, $_POST['tipo'])) {
					$tipo[]="(GRU_RECREDITO = 0 AND GRU_REESTRUCTURA = 0) ";
				} 

				if(in_array(2, $_POST['tipo'])) {
					$tipo []= "GRU_RECREDITO != 0 ";
				} 

				if(in_array(4, $_POST['tipo'])) {
					$tipo[]= "GRU_REESTRUCTURA = 1 ";
				}

				$tipo = implode(' OR ', $tipo);


				$db = $this->_conexion;
				$sql = "SELECT GRU_ID,
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
						WHERE GRU_VIGENTE = 1 ".$promotor." AND (".$tipo.") ".$fechas."
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";
				//die($sql);		
				$consulta = $db->prepare($sql);
				$consulta->execute();
				//die($sql);
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['total'] += $row['GRU_MONTO_TOTAL'];
						$editar = '<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
										<i class="fa fa-pencil"></i>
									</a>';
						$editar_promotor = '<a href="#" class="editar-promotor" data-id="'.$row['GRU_ID'].'"><i class="fa fa-pencil"></i></a>';			
						$json['content'] .= '<div class="col-md-6">
											<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														'.$this->printLink($module, "cambios", $editar).'
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
																	'.$this->printLink($module, "cambios", $editar_promotor).'
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
													<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'" target=_blank>'.$per['PER_NOMBRE'].'</a></td>
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

		if(in_array(3, $_POST['tipo']) || in_array(0, $_POST['tipo'])) {
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
						WHERE CRE_VIGENTE = 1 ".$promotor." ".$fechas_2."
						ORDER BY CRE_FECHA DESC, CRE_ID DESC";
				//die($sql);			
				$consulta = $db->prepare($sql);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['total'] += $row['CRE_MONTO_TOTAL'];
						$editar = '<a href="cambios-ind.php?id='.$row['CRE_ID'].'" class="edit" data-id="'.$row['CRE_ID'].'">
										<i class="fa fa-pencil"></i>
									</a>';
						$editar_promotor = '<a href="#" class="editar-promotor" data-id="'.$row['CRE_ID'].'"><i class="fa fa-pencil"></i></a>';			
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
														'.$this->printLink($module, "cambios", $editar).'
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
																	'.$this->printLink($module, "cambios", $editar_promotor).'
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
															<td align="center">'.$row['PER_CELULAR'].'</td>
															<td align="center">$'.$row['CRE_PAGO_SEMANAL'].'</td>
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

		$json['total'] = number_format($json['total']);

		echo json_encode($json);
	}


	function showIndividual() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["tabla"] = "";

		if(isset($_POST['id'])){
			try {
				$db = $this->_conexion;
				$sql = "SELECT * FROM CREDITO_INDIVIDUAL WHERE CRE_ID = :valor";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$puntero = $consulta->fetch(PDO::FETCH_ASSOC);
					$json = array_merge($json, $puntero);
					$json['CRE_FECHA'] = date("d/m/Y",strtotime($puntero['CRE_FECHA']));
					$json['CRE_FECHA_INICIAL'] = date("d/m/Y",strtotime($puntero['CRE_FECHA_INICIAL']));
					$json['CRE_FECHA_ENTREGA'] = date("d/m/Y",strtotime($puntero['CRE_FECHA_ENTREGA']));
					$json['CRE_TASA'] = $puntero['CRE_TASA'] * 100;
					//$json['CRE_AHORRO_P'] = $puntero['CRE_AHORRO_P'] * 100;
					$json['CRE_COMISION_P'] = $puntero['CRE_COMISION_P'] * 100;

					$sql_cli = "SELECT PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO,
								   CRE_MONTO_ENTREGAR,
								   CRE_COMISION_D,
								   CRE_PAGO_SEMANAL,
								   CRE_AHORRO_D,
								   CRE_MONTO_TOTAL
							FROM PERSONAS
							JOIN CREDITO_INDIVIDUAL ON PERSONAS.PER_ID = CREDITO_INDIVIDUAL.PER_ID
							WHERE CRE_ID = :cred";
					$consulta_cli = $db->prepare($sql_cli);
					$consulta_cli->bindParam(':cred', $_POST['id']);
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
													<input type="text" id="cli_id_'.$num.'" name="cli_id" data-id="'.$num.'" value="'.$row['PER_ID'].'" style="display:none;">
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
													<input id="monto_individual_'.$num.'" name="monto_individual" type="text" class="form-control monto_individual" data-id="'.$num.'" value="'.$row['CRE_MONTO_TOTAL'].'">
												</td>
												<td align="center">
													<input id="ahorro_d_'.$num.'" name="ahorro_d" type="text" class="form-control ahorro_d" data-id="'.$num.'" readonly="readonly" value="'.$row['CRE_AHORRO_D'].'">
												</td>
												<td align="center">
													<input id="comision_d_'.$num.'" name="comision_d" type="text" class="form-control comision_d" data-id="'.$num.'" readonly="readonly" value="'.$row['CRE_COMISION_D'].'">
												</td>
												<td align="center">
													<input id="monto_otorgar_'.$num.'" name="monto_otorgar" type="text" class="form-control monto_otorgar" data-id="'.$num.'" readonly="readonly" value="'.$row['CRE_MONTO_ENTREGAR'].'">
												</td>
												<td align="center">
													<input id="pago_semanal_'.$num.'" name="pago_semanal" type="text" class="form-control pago_semanal" data-id="'.$num.'" readonly="readonly" value="'.$row['CRE_PAGO_SEMANAL'].'">
												</td>
												<td align="center" class="cont-button">
													<a class="eliminar-cl" href="#" data-id="'.$num.'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>
												</td>
											</tr>';
						} 
					} catch (PDOException $e) {
						die($e->getMessage());
					}	

				} else {
					$json['error'] = true;
				} 
				
			} catch (PDOException $e) {
				die($e->getMessage());
				
			}
		} else {
			$json['error'] = true;
		}

		echo json_encode($json);
	}

	function fecha_final() {
		try {
			$sql = "SELECT GRUPOS.GRU_ID, MAX(TP_FECHA) as FECHA
							FROM TABLA_PAGOS
							JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
							WHERE GRU_ID > 290
							GROUP BY GRUPOS.GRU_ID";
			$db = $this->_conexion;
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);	
				foreach ($puntero as $row) {
					$sql_in = "UPDATE GRUPOS SET GRU_FECHA_FINAL = ?
								WHERE GRU_ID = ?";

					$values = array($row['FECHA'],
									$row['GRU_ID']);
					$consulta2 = $db->prepare($sql_in);
					$consulta2->execute($values);

				}

			}

		} catch (PDOException $e) {
			die($e->getMessage());
			
		}	

	}

	function editPromotor() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = '';
		if(isset($_POST['promotor1']) && isset($_POST['grupo'])){
			$sql_in = "UPDATE GRUPOS SET SIU_ID = ?
					   WHERE GRU_ID = ?";
			$db = $this->_conexion;
			$values = array($_POST['promotor1'],
							$_POST['grupo']);
			$consulta2 = $db->prepare($sql_in);
			$consulta2->execute($values);
			$json['msg'] = "El Promotor fue actualizado con éxito.";
			$json['error'] = false;
		} else {	
			$json['error'] = true;	
			$json['msg'] = "Grupo / Promotor inválido, inténtelo de nuevo.";
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
		case "printTable":
			$libs->printTable();
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
		case "printGroupsPr":
			$libs->printGroupsPr();
			break;	
		case "getPromotores":
			$libs->getPromotores();
			break;
		case "getPromotores2":
			$libs->getPromotores2();
			break;	
		case "getRecredito":
			$libs->getRecredito();
			break;	
		case "saveIndividual":
			$libs->saveIndividual();
			break;
		case "filterGroups":
			$libs->filterGroups();
			break;
		case "showIndividual":
			$libs->showIndividual();
			break;	
		case "fecha_final":
			$libs->fecha_final();
			break;	
		case "editPromotor":
			$libs->editPromotor();
			break;												
	}
}
?>