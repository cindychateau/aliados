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

		$obligatorios = array("fecha",
							  "plazo",
							  "tasa",
							  "fecha_entrega",
							  "fecha_inicial",
							  "ahorro_p",
							  "comision_p",
							  "monto_otorgado",
							  "pago",
							  "monto_total",
							  "monto_total_entregar",
							  "pago_total_semanal",
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
				$json["msg"] = "Deben de ser al menos 7 personas en el Grupo.";
			} else if(count($_POST['cli_id']) < 1 || count($_POST['cli_id']) > 13) {
				$json["error"] = true;
				$json["focus"] = "clientes";
				$json["msg"] = "Deben de ser al menos 7 personas en el Grupo y máximo 13.";
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

				$ahorro_p = $_POST['ahorro_p'] / 100;

				$recredito = 0;
				if(isset($_POST['recredito'])) {
					$recredito = $_POST['grupo_rec'];
				}

				$transferencia = 0;
				if(isset($_POST['transferencia'])) {
					$transferencia = 1;
				}

				$comision_p = $_POST['comision_p'] / 100;

				/*$pago_capital = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes = $pago_capital * $tasa;
				$pago_semanal = $pago_capital + $pago_interes;*/

				$pago_capital_total = $_POST['monto_total'] / $_POST['plazo'];
				$pago_interes_total = $pago_capital_total * $tasa;
				$pago_semanal_total = $pago_capital_total + $pago_interes_total;

				$values = array($fecha,
								$_POST["plazo"],
								$tasa,
								$fecha_inicial,
								$fecha_entrega,
								$ahorro_p,
								$recredito,
								$comision_p,
								$_POST['domicilio'],
								$_POST['promotor'],
								$_POST['monto_total'],
								$_POST['monto_total_entregar'],
								$pago_capital_total,
								$pago_interes_total,
								$pago_semanal_total,
								$transferencia);


				if(isset($_POST['id'])) {
					$sql = "UPDATE GRUPOS_B SET GRU_FECHA = ?, 
											GRU_PLAZO = ?,
											GRU_TASA = ?,
											GRU_FECHA_INICIAL = ?,
											GRU_FECHA_ENTREGA = ?,
											GRU_AHORRO_P = ?,
											GRU_RECREDITO = ?,
											GRU_COMISION_P = ?,
											GRU_DOMICILIO = ?,
											SIU_ID = ?,
											GRU_MONTO_TOTAL = ?,
											GRU_MONTO_TOTAL_ENTREGAR = ?,
											PAGO_CAPITAL = ?,
											PAGO_INTERES = ?,
											PAGO_SEMANAL = ?,
											GRU_TRANSFERENCIA = ?
							WHERE GRU_ID = ?";
					$values[]= $_POST['id'];

				} else {
					$sql = "INSERT INTO GRUPOS_B (GRU_FECHA, 
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
												PAGO_SEMANAL,
												GRU_TRANSFERENCIA)
								  	VALUES (?, ?, ?, ?, ?, ?, ?, ?,
								  			?, ?, ?, ?, ?, ?, ?, ?)";
				}

				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				if(isset($_POST['id'])) {
					$last_id = $_POST['id'];

					//Elimina registros de PERSONAS_GRUPOS_B
					try{
						$consulta_pg = $db->prepare("DELETE FROM PERSONAS_GRUPOS_B WHERE GRU_ID = :valor");
						$consulta_pg->bindParam(':valor', $_POST['id']);
						$consulta_pg->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
						$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
					}

					//Elimina registros de TABLA_PAGOS_B
					try{
						$consulta_pa = $db->prepare("DELETE FROM TABLA_PAGOS_B WHERE GRU_ID = :valor");
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

					//Elimina registros de PAGOS_INDIVIDUALES_B
					try{
						$consulta_pi = $db->prepare("DELETE FROM PAGOS_INDIVIDUALES_B WHERE GRU_ID = :valor");
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


				foreach ($_POST['cli_id'] as $num => $id_cliente) {

					$sql_sl = "SELECT PER_NOMBRE,
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
						$pago_capital = (float) $_POST['monto_individual'][$num] / $_POST['plazo'];
						$pago_semanal = (float) $_POST['pago_semanal'][$num];
						//die($pago_semanal." ".$_POST['pago_semanal'][$num]);
						$pago_interes = $pago_semanal - $pago_capital;

						$pago_capital_total += $pago_capital;
						$pago_interes_total += $pago_interes;
						$pago_semanal_total += $pago_semanal;

						$sql_cg = "INSERT INTO PERSONAS_GRUPOS_B (PER_ID,
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


						$fecha = $fecha_inicial;
						//Insert de Pagos Individuales
						for ($i=0; $i < $_POST['plazo']; $i++) {

							$sql_ind = "INSERT INTO PAGOS_INDIVIDUALES_B (PI_FECHA, 
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

					} else {
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

					}

					
				}

				//UPDATE a los montos totales, para que no haya errores de centavos
				$sql_up = "UPDATE GRUPOS_B  SET PAGO_CAPITAL=?,
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
					$sql_pagos = "INSERT INTO TABLA_PAGOS_B (TP_FECHA,
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
					$sql_grupos = "SELECT PERSONAS_GRUPOS_B.GRU_ID 
								   FROM PERSONAS_GRUPOS_B
								   JOIN GRUPOS_B ON PERSONAS_GRUPOS_B.GRU_ID = GRUPOS_B.GRU_ID
								   WHERE PERSONAS_GRUPOS_B.GRU_ID != ?
								   AND GRU_VIGENTE = 1 AND (";
					$values_grupos = array($last_id);			   
					foreach ($_POST['cli_id'] as $num => $id_cliente) {
						$sql_grupos .= " PER_ID = ? OR";
						$values_grupos[]= $id_cliente;
					}

					$sql_grupos = trim($sql_grupos, "OR");

					$sql_grupos.=") GROUP BY PERSONAS_GRUPOS_B.GRU_ID";

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

					if(count($grupos_rec) > 0) {
						//Busca los nuevos pagos semanales del grupo
						$cantidad = array();
						$semanas = array();
						foreach ($grupos_rec as $grupo_rec) {
							//Nuevo pago semanal excluyendo a las personas del recrédito
							$sql_cant = "SELECT SUM(PAGO_SEMANAL_IND) AS NUEVO_PAGO
										 FROM PERSONAS_GRUPOS_B
										 WHERE GRU_ID = ?
										 AND (";
							$values_cant = array($grupo_rec);
							
							foreach ($_POST['cli_id'] as $num => $id_cliente) {
								$sql_cant .= " PER_ID != ? AND";
								$values_cant[]= $id_cliente;
							}

							$sql_cant = trim($sql_cant, "AND");
							$sql_cant.=")";
							$consulta_cant = $db->prepare($sql_cant);

							try {
								$consulta_cant->execute($values_cant);
							} catch (PDOException $e) {
								$db->rollBack();
								$dbgMsg = "--SQL: ".$sql_cant.(isset($values_cant)?"\n--Values: ".print_r($values_cant):"");
								die($e->getMessage().$dbgMsg);
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}

							$result_cant = $consulta_cant->fetch(PDO::FETCH_ASSOC);

							//Calcula la cantidad de semanas que se van a pagar
							$sql_sem = "SELECT COUNT(TP_ID) as SEMANAS
										FROM TABLA_PAGOS_B
										WHERE TP_FECHA >= ?
										AND GRU_ID = ?";
							$values_sem = array($lunes,
												$grupo_rec);

							$consulta_sem = $db->prepare($sql_sem);

							try {
								$consulta_sem->execute($values_sem);
								$row_sem = $consulta_sem->fetch(PDO::FETCH_ASSOC);
								$semanas[$grupo_rec] = $row_sem['SEMANAS'];
							} catch (PDOException $e) {
								$db->rollBack();
								$dbgMsg = "--SQL: ".$sql_sem.(isset($values_sem)?"\n--Values: ".print_r($values_sem):"");
								die($e->getMessage().$dbgMsg);
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}	

							//Update de los pagos
							if(is_null($result_cant['NUEVO_PAGO']) OR $result_cant['NUEVO_PAGO'] == 0) {
								//Si no pago, se elimina el registro
								$sql_nuevo = "DELETE FROM TABLA_PAGOS_B
											  WHERE TP_FECHA >= ?
											  AND GRU_ID = ?";
								$values_nuevo = array($lunes,
													  $grupo_rec);
								$consulta_nuevo = $db->prepare($sql_nuevo);

								try {
									$consulta_nuevo->execute($values_nuevo);
								} catch (PDOException $e) {
									$db->rollBack();
									$dbgMsg = "--SQL: ".$sql_nuevo.(isset($values_nuevo)?"\n--Values: ".print_r($values_nuevo):"");
									die($e->getMessage().$dbgMsg);
									$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
								}					  			  
							} else {
								//Actualiza Tabla de Pagos
								$sql_nuevo = "UPDATE TABLA_PAGOS_B SET TP_MONTO = ?,
																	 TP_FALTANTE = ?
											  WHERE TP_FECHA >= ?
											  AND GRU_ID = ?";
								$values_nuevo = array($result_cant['NUEVO_PAGO'],
													  $result_cant['NUEVO_PAGO'],
													  $lunes,
													  $grupo_rec);	
								$consulta_nuevo = $db->prepare($sql_nuevo);

								try {
									$consulta_nuevo->execute($values_nuevo);
								} catch (PDOException $e) {
									$db->rollBack();
									$dbgMsg = "--SQL: ".$sql_nuevo.(isset($values_nuevo)?"\n--Values: ".print_r($values_nuevo):"");
									die($e->getMessage().$dbgMsg);
									$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
								}						  		  
							}

						}

						/*Ingresa registro en FINIQUITO_RECREDITO*/
						//Cuánto dinero se finiquita ? 
						$total_finiquito = 0;
						foreach ($_POST['cli_id'] as $num => $id_cliente) {
							$sql_fini = "SELECT PAGO_SEMANAL_IND,
												GRU_ID
										 FROM PERSONAS_GRUPOS_B
										 WHERE PER_ID = ?
										 AND (";
							$values_fini = array($id_cliente);

							foreach ($grupos_rec as $grupo_rec) {
								$sql_fini.=" GRU_ID = ? OR";
								$values_fini[]=$grupo_rec;
							}

							$sql_fini = trim($sql_fini, "OR");
							$sql_fini.=")";
							$consulta_fini = $db->prepare($sql_fini);

							try {
								$consulta_fini->execute($values_fini);
								$row_fini = $consulta_fini->fetch(PDO::FETCH_ASSOC);	

								$total_finiquito+= ($row_fini['PAGO_SEMANAL_IND']*$semanas[$row_fini['GRU_ID']]);

							} catch (PDOException $e) {
								$db->rollBack();
								$dbgMsg = "--SQL: ".$sql_fini.(isset($values_finiquito)?"\n--Values: ".print_r($values_finiquito):"");
								die($e->getMessage().$dbgMsg);
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}


							//Elimina los PAGOS_INDIVIDUALES_B de la persona que se recreditó
							try{
								$sql_pi = "DELETE FROM PAGOS_INDIVIDUALES_B 
										   WHERE PER_ID = ?
										   AND PI_FECHA >= ?
										   AND (";
								$values_pi = array($id_cliente,
												   $lunes);

								foreach ($grupos_rec as $grupo_rec) {
									$sql_pi.=" GRU_ID = ? OR";
									$values_pi[]=$grupo_rec;
								}

								$sql_pi = trim($sql_pi, "OR");
								$sql_pi.=")";

								$consulta_pi = $db->prepare($sql_pi);
								$consulta_pi->execute($values_pi);

							}catch(PDOException $e){
								$db->rollBack();
								die($e->getMessage());
								$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
							}


						}

						//Insert en Finiquito
						$sql_finiquito = "INSERT INTO FINIQUITO_RECREDITO (FIN_FECHA,
																		   FIN_CANTIDAD,
																		   GRU_ID,
																		   FIN_SEMANA)
									  	  VALUES (?, ?, ?, ?)";
						$semana_fini = 12-($semanas[$recredito]);
						$values_finiquito = array(date('Y-m-d'),
												  $total_finiquito,
												  $recredito,
												  $semana_fini);
						$consulta_finiquito = $db->prepare($sql_finiquito);	
						try {
							$consulta_finiquito->execute($values_finiquito);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = "--SQL: ".$sql_finiquito.(isset($values_finiquito)?"\n--Values: ".print_r($values_finiquito):"");
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
						}						  			  	  

						//Elimina registro de esa semana del Flujo Semanal
						$sql_fs= "DELETE FROM SALDO_ANTERIOR
									  WHERE SAL_FECHA >= ?";
						$values_fs = array($lunes);
						$consulta_fs = $db->prepare($sql_fs);

						try {
							$consulta_fs->execute($values_fs);
						} catch (PDOException $e) {
							$db->rollBack();
							$dbgMsg = "--SQL: ".$sql_fs.(isset($values_fs)?"\n--Values: ".print_r($values_fs):"");
							die($e->getMessage().$dbgMsg);
							$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
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
				$sql = "SELECT * FROM GRUPOS_B WHERE GRU_ID = :valor";
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
					$json['GRU_AHORRO_P'] = $puntero['GRU_AHORRO_P'] * 100;
					$json['GRU_COMISION_P'] = $puntero['GRU_COMISION_P'] * 100;

					$sql_cli = "SELECT PERSONAS.PER_ID,
								   PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO,
								   MONTO_INDIVIDUAL,
								   AHORRO_D,
								   MONTO_OTORGAR,
								   COMAP_D,
								   PAGO_SEMANAL_IND
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
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
													<input id="monto_individual_'.$num.'" name="monto_individual['.$num.']" type="text" class="form-control monto_individual" data-id="'.$num.'" value="'.$row['MONTO_INDIVIDUAL'].'">
												</td>
												<td align="center">
													<input id="ahorro_d_'.$num.'" name="ahorro_d['.$num.']" type="text" class="form-control ahorro_d" data-id="'.$num.'" readonly="readonly" value="'.$row['AHORRO_D'].'">
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
			$sql = "SELECT PER_ID,
						   PER_NOMBRE,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
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
					$persona['address'] = $row['PER_DIRECCION']." ".$row['PER_NUM']." ".$row['PER_COLONIA'];
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

		try {
			$db = $this->_conexion;
			$sql = "SELECT GRU_ID,
						   GRU_FECHA_ENTREGA,
						   GRU_MONTO_TOTAL,
						   GRU_PLAZO,
						   GRU_TASA,
						   SIU_NOMBRE,
						   GRUPOS_B.SIU_ID,
						   GRU_RECREDITO
					FROM GRUPOS_B 
					JOIN SISTEMA_USUARIO ON GRUPOS_B.SIU_ID = SISTEMA_USUARIO.SIU_ID
					WHERE GRU_VIGENTE = 1
					ORDER BY GRU_FECHA DESC, GRU_ID DESC";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$json['content'] .= '<div class="col-md-4">
										<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'grey' : 'light-grey').'">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>
													<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-file-text-o"></i>
													</a>
													<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-pencil"></i>
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
															<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
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
															<th>Acreditado</th>
															<th>Préstamo Otorgado</th>
															<th>Pago Sem.</th>
														</tr>
													</thead>
													<tbody>';

					$num = 1;
					$sql_per = "SELECT PERSONAS.PER_ID,
									   PER_NOMBRE,
									   STATUS,
									   MONTO_INDIVIDUAL,
									   PAGO_SEMANAL_IND
								FROM PERSONAS
								JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
								WHERE PERSONAS_GRUPOS_B.GRU_ID = :valor";
					$consulta_per = $db->prepare($sql_per);
					$consulta_per->bindParam(':valor', $row['GRU_ID']);
					$consulta_per->execute();
					$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result_per as $per) {
						$json['content'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'">'.$per['PER_NOMBRE'].'</a></td>
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
		/*try {
			$db = $this->_conexion;
			$sql = "SELECT CRE_ID,
						   CRE_FECHA,
						   CRE_MONTO_TOTAL,
						   CRE_PLAZO,
						   CRE_TASA,
						   SIU_NOMBRE,
						   CREDITO_INDIVIDUAL.PER_ID,
						   PER_NOMBRE,
						   STATUS,
						   CRE_PAGO_SEMANAL,
						   CREDITO_INDIVIDUAL.SIU_ID
					FROM CREDITO_INDIVIDUAL 
					JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
					JOIN PERSONAS ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					WHERE CRE_VIGENTE = 1
					ORDER BY CRE_FECHA DESC, CRE_ID DESC";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$json['content'] .= '<div class="col-md-4">
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
													<a href="cambios.php?id='.$row['CRE_ID'].'" class="edit" data-id="'.$row['CRE_ID'].'">
														<i class="fa fa-pencil"></i>
													</a>
												</div>
											</div>
											<div class="box-body" style="display:none;">
												
												<table class="table table-striped general-info" data-id="'.$row['CRE_ID'].'">
													<tbody>
														<tr>
															<td align="center"><b>Fecha</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["CRE_FECHA"])).'</td>
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
															<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
													  	</tr>
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['CRE_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th>Acreditado</th>
															<th>Pago Sem.</th>
														</tr>
													</thead>
													<tbody>
													<tr>
														<td align="center">1</td>
														<td align="center"><a data-id="'.$row['PER_ID'].'" href="../prospectos/cambios.php?id='.$row['PER_ID'].'&status='.$row['STATUS'].'">'.$row['PER_NOMBRE'].'</a></td>
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
			
		}*/

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
							   GRU_RECREDITO
						FROM GRUPOS_B
						JOIN SISTEMA_USUARIO ON GRUPOS_B.SIU_ID = SISTEMA_USUARIO.SIU_ID 
						WHERE GRUPOS_B.SIU_ID = :valor
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $siu_id);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['promotor'] = $row['SIU_NOMBRE'];
						$json['content'] .= '<div class="col-md-4">
											<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'grey' : 'light-grey').'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-pencil"></i>
														</a>
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
																<th>Acreditado</th>
																<th>Préstamo Otorgado</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>';
						$num = 1;
						$sql_per = "SELECT PERSONAS.PER_ID,
										   PER_NOMBRE,
										   STATUS,
										   MONTO_INDIVIDUAL,
										   PAGO_SEMANAL_IND
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
									WHERE PERSONAS_GRUPOS_B.GRU_ID = :valor";
						$consulta_per = $db->prepare($sql_per);
						$consulta_per->bindParam(':valor', $row['GRU_ID']);
						$consulta_per->execute();
						$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result_per as $per) {
							$json['content'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'">'.$per['PER_NOMBRE'].'</a></td>
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
			$sql = "SELECT GRU_ID FROM GRUPOS_B WHERE GRU_ID = :valor";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $_POST['id']);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$sql_cli = "SELECT PERSONAS.PER_ID,
								   PER_NOMBRE,
								   PER_DIRECCION,
								   PER_CELULAR,
								   MONTO_SOLICITADO
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
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
							  "ahorro_p",
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

				$ahorro_p = $_POST['ahorro_p'] / 100;

				$recredito = 0;
				if(isset($_POST['recredito'])) {
					$recredito = $_POST['grupo_rec'];
				}

				$comision_p = $_POST['comision_p'] / 100;

				/*$pago_capital = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes = $pago_capital * $tasa;
				$pago_semanal = $pago_capital + $pago_interes;*/

				$pago_capital_total = $_POST['monto_individual'] / $_POST['plazo'];
				$pago_interes_total = $pago_capital_total * $tasa;
				$pago_semanal_total = $pago_capital_total + $pago_interes_total;

				$values = array($fecha,
								$_POST['cli_id'],
								$_POST["plazo"],
								$tasa,
								$fecha_inicial,
								$fecha_entrega,
								$ahorro_p,
								$_POST['ahorro_d'],
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

					//Elimina registros de TABLA_PAGOS_B
					try{
						$consulta_pa = $db->prepare("DELETE FROM TABLA_PAGOS_B_IND WHERE CRE_ID = :valor");
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
					$sql_pagos = "INSERT INTO TABLA_PAGOS_B_IND (TPI_FECHA,
															  TPI_MONTO,
															  CRE_ID)
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

		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";		


		$promotor = "";
		if($_POST['promotor'] != 0) {
			$promotor = " AND SISTEMA_USUARIO.SIU_ID = ".$_POST['promotor']." ";
		}



		if($_POST['tipo'] == 1 || $_POST['tipo'] == 2 || $_POST['tipo'] == 0) {
			try {

				$tipo = "";
				if($_POST['tipo'] == 1) {
					$tipo = " AND GRU_RECREDITO = 0 ";
				} else if($_POST['tipo'] == 2) {
					$tipo = " AND GRU_RECREDITO != 0 ";
				}


				$db = $this->_conexion;
				$sql = "SELECT GRU_ID,
							   GRU_FECHA_ENTREGA,
							   GRU_MONTO_TOTAL,
							   GRU_PLAZO,
							   GRU_TASA,
							   SIU_NOMBRE,
							   GRUPOS_B.SIU_ID,
							   GRU_RECREDITO
						FROM GRUPOS_B 
						JOIN SISTEMA_USUARIO ON GRUPOS_B.SIU_ID = SISTEMA_USUARIO.SIU_ID
						WHERE GRU_VIGENTE = 1".$promotor." ".$tipo."
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";
				//die($sql);		
				$consulta = $db->prepare($sql);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['content'] .= '<div class="col-md-4">
											<div class="box border '.($row['GRU_RECREDITO'] != 0 ? 'grey' : 'light-grey').'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-pencil"></i>
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
																<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
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
																<th>Acreditado</th>
																<th>Préstamo Otorgado</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>';

						$num = 1;
						$sql_per = "SELECT PERSONAS.PER_ID,
										   PER_NOMBRE,
										   STATUS,
										   MONTO_INDIVIDUAL,
										   PAGO_SEMANAL_IND
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
									WHERE PERSONAS_GRUPOS_B.GRU_ID = :valor";
						$consulta_per = $db->prepare($sql_per);
						$consulta_per->bindParam(':valor', $row['GRU_ID']);
						$consulta_per->execute();
						$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result_per as $per) {
							$json['content'] .= '<tr>
													<td align="center">'.$num.'</td>
													<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'">'.$per['PER_NOMBRE'].'</a></td>
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

		/*if($_POST['tipo'] == 3 || $_POST['tipo'] == 0) {
			//INDIVIDUALES
			try {
				$db = $this->_conexion;
				$sql = "SELECT CRE_ID,
							   CRE_FECHA,
							   CRE_MONTO_TOTAL,
							   CRE_PLAZO,
							   CRE_TASA,
							   SIU_NOMBRE,
							   CREDITO_INDIVIDUAL.PER_ID,
							   PER_NOMBRE,
							   STATUS,
							   CRE_PAGO_SEMANAL,
							   CREDITO_INDIVIDUAL.SIU_ID
						FROM CREDITO_INDIVIDUAL 
						JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
						JOIN PERSONAS ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
						WHERE CRE_VIGENTE = 1 ".$promotor."
						ORDER BY CRE_FECHA DESC, CRE_ID DESC";
				//die($sql);			
				$consulta = $db->prepare($sql);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {
						$json['content'] .= '<div class="col-md-4">
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
														<a href="cambios.php?id='.$row['CRE_ID'].'" class="edit" data-id="'.$row['CRE_ID'].'">
															<i class="fa fa-pencil"></i>
														</a>
													</div>
												</div>
												<div class="box-body" style="display:none;">
													
													<table class="table table-striped general-info" data-id="'.$row['CRE_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["CRE_FECHA"])).'</td>
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
																<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
														  	</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['CRE_ID'].'">
														<thead>
															<tr>
																<th>#</th>
																<th>Acreditado</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>
														<tr>
															<td align="center">1</td>
															<td align="center"><a data-id="'.$row['PER_ID'].'" href="../prospectos/cambios.php?id='.$row['PER_ID'].'&status='.$row['STATUS'].'">'.$row['PER_NOMBRE'].'</a></td>
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
		}*/

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
	}
}
?>