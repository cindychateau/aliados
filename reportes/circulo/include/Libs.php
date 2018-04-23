<?php
/*
 *	Se identifica la ruta	
 */
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
require_once($ruta."include/PHPExcel/PHPExcel.php");

class Libs extends Common {

	function getMonthWord($month) {
		$mes = "Enero";
		switch ($month) {
			case 1:
				$mes = "Enero";
				break;
			case 2:
				$mes = "Febrero";
				break;
			case 3:
				$mes = "Marzo";
				break;
			case 4:
				$mes = "Abril";
				break;
			case 5:
				$mes = "Mayo";
				break;
			case 6:
				$mes = "Junio";
				break;
			case 7:
				$mes = "Julio";
				break;
			case 8:
				$mes = "Agosto";
				break;
			case 9:
				$mes = "Septiembre";
				break;
			case 10:
				$mes = "Octubre";
				break;
			case 11:
				$mes = "Noviembre";
				break;
			case 12:
				$mes = "Diciembre";
				break;											
		}

		return $mes;
	}

	function getCirculo() {
		$json = array();
		$json['table'] = '';

		if(isset($_POST['fecha_1']) && isset($_POST['fecha_2'])) {
			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

			$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th align="center">APELLIDO PATERNO</th>
											<th align="center">APELLIDO MATERNO</th>
											<th align="center">NOMBRE(S)</th>
											<th align="center">FECHA DE NACIMIENTO</th>
											<th align="center">RFC</th>
											<th align="center">CURP</th>
											<th align="center"># DE SEGURO SOCIAL</th>
											<th align="center">NACIONALIDAD</th>
											<th align="center">RESIDENCIA</th>
											<th align="center"># DE LICENCIA DE CONDUCIR</th>
											<th align="center">ESTADO CIVIL</th>
											<th align="center">SEXO</th>
											<th align="center">CLAVE ELECTORAL IFE</th>
											<th align="center"># DE DEPENDIENTES</th>
											<th align="center">TIPO DE PERSONA</th>
											<th align="center">DIRECCIÓN</th>
											<th align="center">COLONIA POBLACIÓN</th>
											<th align="center">DELEGACIÓN MUNICIPIO</th>
											<th align="center">CIUDAD</th>
											<th align="center">ESTADO</th>
											<th align="center">CP</th>
											<th align="center"># TELÉFONO</th>
											<th align="center">TIPO DOMICILIO</th>
											'.(isset($_POST['asentamiento']) ? '<th align="center">TIPO ASENTAMIENTO</th>': '').'
											<th align="center">CUENTA ACTUAL</th>
											<th align="center">PROMOTOR</th>
											<th align="center">TIPO RESPONSABILIDAD</th>
											<th align="center">TIPO CUENTA</th>
											<th align="center">TIPO CONTRATO</th>
											<th align="center">CLAVE UNIDAD MONETARIA</th>
											<th align="center">VALOR ACTIVO VALUACIÓN</th>
											<th align="center"># PAGOS</th>
											<th align="center">FRECUENCIA DE PAGOS</th>
											'.(isset($_POST['monto_pagar']) ? '<th align="center">MONTO PAGAR</th>': '').'
											<th align="center">FECHA APERTURA CUENTA</th>
											'.(isset($_POST['fecha_ultimo_pago']) ? '<th align="center">FECHA ÚLTIMO PAGO</th>': '').'
											<th align="center">FECHA ÚLTIMA COMPRA</th>
											<th align="center">FECHA CORTE</th>
											<th align="center">GARANTÍA</th>
											<th align="center">CRÉDITO MÁXIMO</th>
											'.(isset($_POST['saldo_actual']) ? '<th align="center">SALDO ACTUAL</th>': '').'
											<th align="center">LIMITE CRÉDITO</th>
											'.(isset($_POST['saldo_vencido']) ? '<th align="center">SALDO VENCIDO</th>': '').'
											'.(isset($_POST['num_pagos_vencidos']) ? '<th align="center"># PAGOS VENCIDOS</th>': '').'
											'.(isset($_POST['pago_actual']) ? '<th align="center">PAGO ACTUAL</th>': '').'
											'.(isset($_POST['total_pagos_rep']) ? '<th align="center">TOTAL DE PAGOS REPORTADOS</th>': '').'
											'.(isset($_POST['fecha_inc']) ? '<th align="center">FECHA PRIMER INCUMPLIMIENTO</th>': '').'
											'.(isset($_POST['monto_ultimo_pago']) ? '<th align="center">MONTO ÚLTIMO PAGO</th>': '').'
											'.(isset($_POST['total_abonado']) ? '<th align="center">TOTAL ABONADO</th>': '').'
											<th align="center">TOTAL RECUPERADO</th>
											<th align="center">PLAZO MESES</th>
											<th align="center">MONTO CRÉDITO ORIGINACIÓN</th>
											'.(isset($_POST['total_saldos_actuales']) ? '<th align="center">TOTAL SALDOS ACTUALES</th>': '').'
											'.(isset($_POST['total_saldos_vencidos']) ? '<th align="center">TOTAL SALDOS VENCIDOS</th>': '').'
											<th align="center">TOTAL ELEMENTOS NOMBRE REPORTADOS</th>
											<th align="center">TOTAL ELEMENTOS DIRECCIÓN REPORTADOS</th>
											<th align="center">TOTAL ELEMENTOS EMPLEO REPORTADOS</th>
											<th align="center">TOTAL ELEMENTOS CUENTA REPORTADOS</th>
											'.(isset($_POST['saldo_insoluto']) ? '<th align="center">SALDO INSOLUTO</th>': '').'
										</tr>
									</thead>
									<tbody>';


			$sql = "SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   PERSONAS_GRUPOS.GRU_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'M' AS RESPONSABILIDAD,
						   GRUPOS.GRU_PLAZO as PLAZO,
						   PERSONAS_GRUPOS.PAGO_SEMANAL_IND as PAGO_SEMANAL,
						   GRU_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   PERSONAS_GRUPOS.MONTO_INDIVIDUAL,
						   SIU_NOMBRE,
						   GRU_VIGENTE as VIGENTE
					FROM PERSONAS
					JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
					JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
					WHERE GRU_FECHA_ENTREGA >= ?
					AND GRU_FECHA_ENTREGA <= ?
					UNION
					SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   CRE_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'I' AS RESPONSABILIDAD,
						   CRE_PLAZO as PLAZO,
						   CRE_PAGO_SEMANAL as PAGO_SEMANAL,
						   CRE_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   CRE_MONTO_TOTAL as MONTO_INDIVIDUAL,
						   SIU_NOMBRE,
						   CRE_VIGENTE as VIGENTE
					FROM PERSONAS
					JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = CREDITO_INDIVIDUAL.SIU_ID
					WHERE CRE_FECHA_ENTREGA >= ?
					AND CRE_FECHA_ENTREGA <= ?";

			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));	
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));		

			$values = array($fecha_1,
							$fecha_2,
							$fecha_1,
							$fecha_2);	

			$db = $this->_conexion;
			$consulta = $db->prepare($sql);		  

			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				foreach ($result as $row) {

					//Residencia
					$residencia = ($row['VIVIENDA'] == 0 ? "2" : "1");

					//Edo. Civil
					$edo_civil = "S";
					if($row['PER_EDO_CIVIL'] == "Casado") {
						$edo_civil = "C";
					} else if($row['PER_EDO_CIVIL'] == "Divorciado") {
						$edo_civil = "D";
					} else if($row['PER_EDO_CIVIL'] == "Viudo") {
						$edo_civil = "V";
					}

					//Sexo
					$sexo = ($row['PER_GENERO'] == 'Masculino' ? "M" : "F");

					//Dependientes 
					$dependientes = $row['DEPENDE_PADRES'] + $row['DEPENDE_CONYUGUE'] + $row['DEPENDE_HIJOS'] + $row['DEPENDE_HERMANOS'] + $row['DEPENDE_OTROS'];

					//Obtiene el Asentamiento de la colonia
					$asentamiento = 7;
					$colonia = ($row['PER_COLONIA'] == '0' ? $row['PER_COLONIA_OTRA'] : $row['PER_COLONIA']);
					if(isset($_POST['asentamiento'])) {
						$sql_asent = "SELECT MUN_ASENTAMIENTO FROM MUNICIPIOS WHERE MUN_COLONIA = ?";
						$value_asent = array($colonia);
						$consulta_asent = $db->prepare($sql_asent);
						try {
							$consulta_asent->execute($value_asent);
							if($consulta_asent->rowCount() > 0) {
								$row_asent = $consulta_asent->fetch(PDO::FETCH_ASSOC);
								$asentamiento = $row_asent['MUN_ASENTAMIENTO'];
							} 
						} catch (PDOException $e) {
							die($e->getMessage().$sql_asent);
						}
					}

					//Cuenta Actual
					$tipo_cred = ($row['RESPONSABILIDAD'] == 'I' ? 'I' : 'G');
					$cuenta_act1 = str_pad($row['CUENT_ACT'], 5, "0", STR_PAD_LEFT);
					$cuenta_act2 = str_pad($row['CUENT_ACT2'], 6, "0", STR_PAD_LEFT);
					$cuenta_actual = $tipo_cred.$cuenta_act1."-".$cuenta_act2;

					//Obtiene la Fecha del Último Pago
					if(isset($_POST['fecha_ultimo_pago']) || isset($_POST['monto_ultimo_pago'])) {
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_ult = "SELECT TPI_FECHA as FECHA_ULTIMA,
											   TPI_PAGADO as ULTIMO_PAGO
										FROM TABLA_PAGOS_IND
										WHERE CRE_ID = ?
										AND TPI_PAGADO != 0
										ORDER BY TPI_FECHA DESC";
							$values_ult = array($row['CUENT_ACT']);						
						} else {
							$sql_ult = "SELECT PI_FECHA_REG as FECHA_ULTIMA,
											   PI_PAGO as ULTIMO_PAGO
										FROM PAGOS_INDIVIDUALES
										WHERE GRU_ID = ?
										AND PER_ID = ?
										AND PI_PAGO != 0
										ORDER BY PI_FECHA_REG DESC";
							$values_ult = array($row['CUENT_ACT'],
											$row['CUENT_ACT2']);						
						}
						
						$consulta_ult = $db->prepare($sql_ult);
						try {
							$consulta_ult->execute($values_ult);
							$row_ult = $consulta_ult->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage().$sql_ult);
						}
					}

					//Obtiene el Saldo Actual
					if(isset($_POST['total_saldos_actuales']) || isset($_POST['saldo_actual'])) {
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_actual = "SELECT SUM(TPI_FALTANTE) as SALDO_ACTUAL
										   FROM TABLA_PAGOS_IND
										   WHERE CRE_ID = ?";	
							$values_actual = array($row['CUENT_ACT']);			   		   
						} else {
							$sql_actual = "SELECT SUM(PI_PENDIENTE) as SALDO_ACTUAL
										   FROM PAGOS_INDIVIDUALES
										   WHERE GRU_ID = ?
										   AND PER_ID = ?";
							$values_actual = array($row['CUENT_ACT'],
											   $row['CUENT_ACT2']);			   
						}
						$consulta_actual = $db->prepare($sql_actual);
						try {
							$consulta_actual->execute($values_actual);
							$row_actual = $consulta_actual->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage().$sql_actual);
						}
					}

					//Obtiene el Saldo Vencido
					if(isset($_POST['monto_pagar']) || 
					   isset($_POST['total_saldos_vencidos']) || 
					   isset($_POST['saldo_vencido']) ||
					   isset($_POST['num_pagos_vencidos']) ||
					   isset($_POST['pago_actual'])) {
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_venc = "SELECT SUM(TPI_FALTANTE) as SALDO_VENCIDO
										 FROM TABLA_PAGOS_IND
										 WHERE CRE_ID = ?
										 AND TPI_FECHA < CURRENT_DATE";
							$values_venc = array($row['CUENT_ACT']);			 			   
						} else {
							$sql_venc = "SELECT SUM(PI_PENDIENTE) as SALDO_VENCIDO
										 FROM PAGOS_INDIVIDUALES
										 WHERE GRU_ID = ?
										 AND PER_ID = ?
										 AND PI_FECHA < CURRENT_DATE";
							$values_venc = array($row['CUENT_ACT'],
											 $row['CUENT_ACT2']);			 
						}
						
						$consulta_venc = $db->prepare($sql_venc);
						try {
							$consulta_venc->execute($values_venc);
							$row_venc = $consulta_venc->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage().$sql_venc);
						}

						//Obtiene # de Pagos Vencidos y Pago Actual
						//echo $row_venc['SALDO_VENCIDO']." ".$row['PAGO_SEMANAL']."<br>";
						$pag_act = "V";
						if($row_venc['SALDO_VENCIDO'] > 0) {
							$pag_venc = floor($row_venc['SALDO_VENCIDO'] / $row['PAGO_SEMANAL']);
							$pag_act = $pag_venc;
						}

						//Monto a Pagar
						//Si ya se pagaron las doce semanas en col MONTOAPAGAR que aparezca en 0
						$monto_pagar = $row['PAGO_SEMANAL'];
						if($row['VIGENTE'] == 0) {
							$monto_pagar = $row_venc['SALDO_VENCIDO'];
						}
					}
					

					//Obtiene Total de Pagos Reportados
					if(isset($_POST['total_pagos_rep'])){
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_tots = "SELECT SUM(TPI_PAGADO) as TOTAL_PAGADO
										 FROM TABLA_PAGOS_IND
										 WHERE CRE_ID = ?
										 AND TPI_FECHA < CURRENT_DATE";		
							$values_tots = array($row['CUENT_ACT']);			 	   
						} else {
							$sql_tots = "SELECT SUM(PI_PAGO) as TOTAL_PAGADO
										 FROM PAGOS_INDIVIDUALES
										 WHERE GRU_ID = ?
										 AND PER_ID = ?
										 AND PI_FECHA < CURRENT_DATE";
							$values_tots = array($row['CUENT_ACT'],
											 $row['CUENT_ACT2']);			 
						}
						
						$consulta_tots = $db->prepare($sql_tots);
						try {
							$consulta_tots->execute($values_tots);
							$row_tots = $consulta_tots->fetch(PDO::FETCH_ASSOC);
						} catch (PDOException $e) {
							die($e->getMessage().$sql_tots);
						}

						$total_pagos_rep = 0;
						if($row_tots['TOTAL_PAGADO'] > 0 ) {
							$total_pagos_rep = floor($row_tots['TOTAL_PAGADO'] / $row['PAGO_SEMANAL']);
						}
					}

					//Obtiene Fecha del Primer Incumplimiento
					if(isset($_POST['fecha_inc'])) {
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_fecha_inc = "SELECT TPI_FECHA as FECHA_INC
											  FROM TABLA_PAGOS_IND
											  WHERE CRE_ID = ?
											  AND TPI_FALTANTE != 0
											  AND TPI_FECHA < CURRENT_DATE";
							$values_fecha_inc = array($row['CUENT_ACT']);				  			   
						} else {
							$sql_fecha_inc = "SELECT PI_FECHA as FECHA_INC
											  FROM PAGOS_INDIVIDUALES
											  WHERE GRU_ID = ?
											  AND PER_ID = ?
											  AND PI_PENDIENTE != 0
											  AND PI_FECHA < CURRENT_DATE";
							$values_fecha_inc = array($row['CUENT_ACT'],
											 	  	  $row['CUENT_ACT2']);				  
						}
						
						$consulta_fecha_inc = $db->prepare($sql_fecha_inc);
						try {
							$consulta_fecha_inc->execute($values_fecha_inc);
							$fecha_inc = '1901-01-01';
							if($consulta_fecha_inc->rowCount() > 0) {
								$row_fecha_inc = $consulta_fecha_inc->fetch(PDO::FETCH_ASSOC);
								$fecha_inc = $row_fecha_inc['FECHA_INC'];
							}
							
						} catch (PDOException $e) {
							die($e->getMessage().$sql_fecha_inc);
						}
					}

					$plazo_meses = floor($row['PLAZO'] / 4);

					//Obtiene Total Abonado
					if(isset($_POST['total_abonado']) || isset($_POST['saldo_insoluto'])) {
						if($row['RESPONSABILIDAD'] == 'I') {
							$sql_abo = "SELECT SUM(TPI_PAGADO) as total_abonado
										FROM TABLA_PAGOS_IND 
										WHERE CRE_ID = ?";
							$values_abo = array($row['CUENT_ACT']);	
							$consulta_abo = $db->prepare($sql_abo);	
							try {
								$consulta_abo->execute($values_abo);
								$row_abo = $consulta_abo->fetch(PDO::FETCH_ASSOC);
								$total_abonado = $row_abo['total_abonado'];
							} catch (PDOException $e) {
								die($e->getMessage());
							}
						} else {
							$sql_abo = "SELECT SUM(PD_MONTO) as total_abonado
										FROM PAGOS_DESGLOSADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_abo = array($row['CUENT_ACT2'],
												$row['CUENT_ACT']);	
							$consulta_abo = $db->prepare($sql_abo);	
							try {
								$consulta_abo->execute($values_abo);
								$row_abo = $consulta_abo->fetch(PDO::FETCH_ASSOC);
								$total_abonado = $row_abo['total_abonado'];
							} catch (PDOException $e) {
								die($e->getMessage());
							}
						}
					}

					//Obtiene Total Recuperado
					$total_recuperado = 0;
					if(isset($_POST['total_recuperado']) || isset($_POST['saldo_insoluto'])) {
						if($row['RESPONSABILIDAD'] != 'I') {
							$sql_rec = "SELECT SUM(PR_MONTO) as total_recuperado
										FROM PAGOS_RECUPERADOS 
										WHERE PER_ID = ? 
										AND GRU_ID = ?";
							$values_rec = array($row['CUENT_ACT2'],
												$row['CUENT_ACT']);	
							$consulta_rec = $db->prepare($sql_rec);	
							try {
								$consulta_rec->execute($values_rec);
								$row_rec = $consulta_rec->fetch(PDO::FETCH_ASSOC);
								$total_recuperado = $row_rec['total_recuperado'];
							} catch (PDOException $e) {
								die($e->getMessage());
							}
						}
					}	

					//Saldo Insoluto -> (Crédito Otorgado) - (Pagos que ha realizado / 101 * 83 )
					if(isset($_POST['saldo_insoluto'])) {
						$saldo_insoluto = ($row['MONTO_INDIVIDUAL']) - (($total_abonado + $total_recuperado) / 101 * 83);
					}

					
					//GENERAMOS LA TABLA
					$json['table'] .= '<tr>
											<td align="center">'.$row['PER_APELLIDO_PAT'].'</td>
											<td align="center">'.$row['PER_APELLIDO_MAT'].'</td>
											<td align="center">'.$row['PER_NOMBRE'].'</td>
											<td align="center">'.date("d/m/Y",strtotime($row['PER_FECHA_NAC'])).'</td>
											<td align="center">'.$row['PER_RFC'].'</td>
											<td align="center">'.$row['PER_CURP'].'</td>
											<td align="center"></td>
											<td align="center">MX</td>
											<td align="center">'.$residencia.'</td>
											<td align="center"></td>
											<td align="center">'.$edo_civil.'</td>
											<td align="center">'.$sexo.'</td>
											<td align="center">'.$row['IFE_NUM'].'</td>
											<td align="center">'.$dependientes.'</td>
											<td align="center">PF</td>
											<td align="center">'.$row['PER_DIRECCION'].' #'.$row['PER_NUM'].'</td>
											<td align="center">'.$colonia.'</td>
											<td align="center">'.$row['PER_MUNICIPIO'].'</td>
											<td align="center">'.$row['PER_MUNICIPIO'].'</td>
											<td align="center">Nuevo León</td>
											<td align="center">'.$row['PER_CP'].'</td>
											<td align="center">'.$row['PER_CELULAR'].'</td>
											<td align="center">C</td>
											'.(isset($_POST['asentamiento']) ? '<td align="center">'.$asentamiento.'</td>': '').'
											<td align="center">'.$cuenta_actual.'</td>
											<td align="center">'.$row['SIU_NOMBRE'].'</td>
											<td align="center">'.$row['RESPONSABILIDAD'].'</td>
											<td align="center">F</td>
											<td align="center">PP</td>
											<td align="center">MX</td>
											<td align="center">'.($row['VIGENTE'] == 0 ? 'Inactivo' : 
																	(strtotime($row['FECHA_ENTREGA']) > strtotime(date("Y-m-d")) ? 'Pendiente' : 'Activo') 
																 ).'</td>
											<td align="center">'.$row['PLAZO'].'</td>
											<td align="center">S</td>
											'.(isset($_POST['monto_pagar']) ? '<td align="center">'.$monto_pagar.'</td>': '').'
											<td align="center">'.date("d/m/Y",strtotime($row['FECHA_ENTREGA'])).'</td>
											'.(isset($_POST['fecha_ultimo_pago']) ? '<td align="center">'.date("d/m/Y",strtotime($row_ult['FECHA_ULTIMA'])).'</td>': '').'
											<td align="center">'.date("d/m/Y",strtotime($row['FECHA_ENTREGA'])).'</td>
											<td align="center">'.date("d/m/Y").'</td>
											<td align="center">'.$row['GARANTIA_BIEN_1'].'</td>
											<td align="center">'.$row['MAXIMO_PAGAR'].'</td>
											'.(isset($_POST['saldo_actual']) ? '<td align="center">'.$row_actual['SALDO_ACTUAL'].'</td>': '').'
											<td align="center">'.$row['MAXIMO_PAGAR'].'</td>
											'.(isset($_POST['saldo_vencido']) ? '<td align="center">'.$row_venc['SALDO_VENCIDO'].'</td>': '').'
											'.(isset($_POST['num_pagos_vencidos']) ? '<td align="center">'.$pag_venc.'</td>': '').'
											'.(isset($_POST['pago_actual']) ? '<td align="center">'.$pag_act.'</td>': '').'
											'.(isset($_POST['total_pagos_rep']) ? '<td align="center">'.$total_pagos_rep.'</td>': '').'
											'.(isset($_POST['fecha_inc']) ? '<td align="center">'.date("d/m/Y",strtotime($fecha_inc)).'</td>': '').'
											'.(isset($_POST['monto_ultimo_pago']) ? '<td align="center">'.$row_ult['ULTIMO_PAGO'].'</td>': '').'
											'.(isset($_POST['total_abonado']) ? '<td align="center">'.$total_abonado.'</td>': '').'
											'.(isset($_POST['total_recuperado']) ? '<td align="center">'.$total_recuperado.'</td>': '').'
											<td align="center">'.$plazo_meses.'</td>
											<td align="center">'.$row['MONTO_INDIVIDUAL'].'</td>
											'.(isset($_POST['total_saldos_actuales']) ? '<td align="center">'.$row_actual['SALDO_ACTUAL'].'</td>': '').'
											'.(isset($_POST['total_saldos_vencidos']) ? '<td align="center">'.$row_venc['SALDO_VENCIDO'].'</td>': '').'
											<td align="center">1</td>
											<td align="center">1</td>
											<td align="center">1</td>
											<td align="center">1</td>
											'.(isset($_POST['saldo_insoluto']) ? '<td align="center">'.(number_format($saldo_insoluto, 2)).'</td>': '').'
									   </tr>';




				}

			} catch (PDOException $e) {
				die($e->getMessage().$sql);
			}


			$json['table'] .= '</tbody>
								</table>';						


		}

		echo json_encode($json);
	}

	function getExcel() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D",
						 "E",
						 "F",
						 "G",
						 "H",
						 "I",
						 "J",
						 "K",
						 "L",
						 "M",
						 "N",
						 "O",
						 "P",
						 "Q",
						 "R",
						 "S",
						 "T",
						 "U",
						 "V",
						 "W",
						 "X",
						 "Y",
						 "Z",
						 "AA",
						 "AB",
						 "AC",
						 "AD",
						 "AE",
						 "AF",
						 "AG",
						 "AH",
						 "AI",
						 "AJ",
						 "AK",
						 "AL",
						 "AM",
						 "AN",
						 "AO",
						 "AP",
						 "AQ",
						 "AR",
						 "AS",
						 "AT",
						 "AU",
						 "AV",
						 "AW",
						 "AX",
						 "AY",
						 "AZ",
						 "BA",
						 "BB",
						 "BC",
						 "BD",
						 "BE",
						 "BF",
						 "BG");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Grupo Propulsor de Microempresas del Norte")
					 ->setLastModifiedBy("Grupo Propulsor de Microempresas del Norte")
					 ->setTitle("Circulo de Credito")
					 ->setSubject("Circulo de Credito")
					 ->setDescription("Reporte de Circulo de Credito")
					 ->setKeywords("circulo credito");

		$styleArray = array(
				        'font' => array(
				            'bold' => true
				        ),
				        'alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );
		$styleArray2 = array('alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );		    			 

		//Hacemos más grande las columnas, bold la primera y text-center
		foreach ($columns as $column) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(20);
			$objPHPExcel->getActiveSheet()->getStyle($column."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($styleArray2);
		}

		$objPHPExcel->getActiveSheet()->getColumnDimension("AU")->setWidth(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BC")->setWidth(28);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BD")->setWidth(29);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BE")->setWidth(27.7);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BF")->setWidth(26.7);

		//$objPHPExcel->getStyle("M")->getNumberFormat()->setFormatCode('0'); 


		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'ApellidoPaterno')
		            ->setCellValue('B1', 'ApellidoMaterno')
		            ->setCellValue('C1', 'Nombres')
		            ->setCellValue('D1', 'FechaNacimiento')
		            ->setCellValue('E1', 'RFC')
		            ->setCellValue('F1', 'CURP')
		            ->setCellValue('G1', 'NumeroSeguridadSocial')
		            ->setCellValue('H1', 'Nacionalidad')
		            ->setCellValue('I1', 'Residencia')
		            ->setCellValue('J1', 'NumeroLicenciaConducir')
		            ->setCellValue('K1', 'EstadoCivil')
		            ->setCellValue('L1', 'Sexo')
		            ->setCellValue('M1', 'ClaveElectorIFE')
		            ->setCellValue('N1', 'NumeroDependientes')
		            ->setCellValue('O1', 'TipoPersona')
		            ->setCellValue('P1', 'Dirección')
		            ->setCellValue('Q1', 'ColoniaPoblacion')
		            ->setCellValue('R1', 'DelegacionMunicipio')
		            ->setCellValue('S1', 'Ciudad')
		            ->setCellValue('T1', 'Estado')
		            ->setCellValue('U1', 'CP')
		            ->setCellValue('V1', 'NumeroTelefono')
		            ->setCellValue('W1', 'TipoDomicilio')
		            ->setCellValue('X1', 'TipoAsentamiento')
		            ->setCellValue('Y1', 'CuentaActual')
		            ->setCellValue('Z1', 'Promotor')
		            ->setCellValue('AA1', 'TipoResponsabilidad')
		            ->setCellValue('AB1', 'TipoCuenta')
		            ->setCellValue('AC1', 'TipoContrato')
		            ->setCellValue('AD1', 'ClaveUnidadMonetaria')
		            ->setCellValue('AE1', 'ValorActivoValuacion')
		            ->setCellValue('AF1', 'NumeroPagos')
		            ->setCellValue('AG1', 'FrecuenciaPagos')
		            ->setCellValue('AH1', 'MontoPagar')
		            ->setCellValue('AI1', 'FechaAperturaCuenta')
		            ->setCellValue('AJ1', 'FechaUltimoPago')
		            ->setCellValue('AK1', 'FechaUltimaCompra')
		            ->setCellValue('AL1', 'FechaCorte')
		            ->setCellValue('AM1', 'Garantia')
		            ->setCellValue('AN1', 'CreditoMaximo')
		            ->setCellValue('AO1', 'SaldoActual')
		            ->setCellValue('AP1', 'LimiteCredito')
		            ->setCellValue('AQ1', 'SaldoVencido')
		            ->setCellValue('AR1', 'NumeroPagosVencidos')
		            ->setCellValue('AS1', 'PagoActual')
		            ->setCellValue('AT1', 'TotalPagosReportados')
		            ->setCellValue('AU1', 'FechaPrimerIncumplimiento')
		            ->setCellValue('AV1', 'MontoUltimoPago')
		            ->setCellValue('AW1', 'TotalAbonado')
		            ->setCellValue('AX1', 'TotalRecuperado')
		            ->setCellValue('AY1', 'PlazoMeses')
		            ->setCellValue('AZ1', 'MontoCreditoOriginacion')
		            ->setCellValue('BA1', 'TotalSaldosActuales')
		            ->setCellValue('BB1', 'TotalSaldosVencidos')
		            ->setCellValue('BC1', 'TotalElementosNombreReportados')
		            ->setCellValue('BD1', 'TotalElementosDireccionReportados')
		            ->setCellValue('BE1', 'TotalElementosEmpleoReportados')
		            ->setCellValue('BF1', 'TotalElementosCuentaReportados')
		            ->setCellValue('BG1', 'SaldoInsoluto');


		/*DATOS*/
		$sql = "SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   PERSONAS_GRUPOS.GRU_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'M' AS RESPONSABILIDAD,
						   GRUPOS.GRU_PLAZO as PLAZO,
						   PERSONAS_GRUPOS.PAGO_SEMANAL_IND as PAGO_SEMANAL,
						   GRU_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   PERSONAS_GRUPOS.MONTO_INDIVIDUAL,
						   SIU_NOMBRE,
						   GRU_VIGENTE as VIGENTE
					FROM PERSONAS
					JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
					JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
					WHERE GRU_FECHA_ENTREGA >= ?
					AND GRU_FECHA_ENTREGA <= ?
					UNION
					SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   CRE_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'I' AS RESPONSABILIDAD,
						   CRE_PLAZO as PLAZO,
						   CRE_PAGO_SEMANAL as PAGO_SEMANAL,
						   CRE_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   CRE_MONTO_TOTAL as MONTO_INDIVIDUAL,
						   SIU_NOMBRE,
						   CRE_VIGENTE as VIGENTE
					FROM PERSONAS
					JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = CREDITO_INDIVIDUAL.SIU_ID
					WHERE CRE_FECHA_ENTREGA >= ?
					AND CRE_FECHA_ENTREGA <= ?";

		$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));	
		$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));		

		$values = array($fecha_1,
						$fecha_2,
						$fecha_1,
						$fecha_2);	

		$db = $this->_conexion;
		$consulta = $db->prepare($sql);	

		$n = 2;

		try {
			
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result as $row) {

				//Residencia
				$residencia = ($row['VIVIENDA'] == 0 ? "2" : "1");

				//Edo. Civil
				$edo_civil = "S";
				if($row['PER_EDO_CIVIL'] == "Casado") {
					$edo_civil = "C";
				} else if($row['PER_EDO_CIVIL'] == "Divorciado") {
					$edo_civil = "D";
				} else if($row['PER_EDO_CIVIL'] == "Viudo") {
					$edo_civil = "V";
				}

				//Sexo
				$sexo = ($row['PER_GENERO'] == 'Masculino' ? "M" : "F");

				//Dependientes 
				$dependientes = $row['DEPENDE_PADRES'] + $row['DEPENDE_CONYUGUE'] + $row['DEPENDE_HIJOS'] + $row['DEPENDE_HERMANOS'] + $row['DEPENDE_OTROS'];

				//Obtiene el Asentamiento de la colonia
				$asentamiento = 7;
				$colonia = ($row['PER_COLONIA'] == '0' ? $row['PER_COLONIA_OTRA'] : $row['PER_COLONIA']);
				if(isset($_POST['asentamiento'])) {
					$sql_asent = "SELECT MUN_ASENTAMIENTO FROM MUNICIPIOS WHERE MUN_COLONIA = ?";
					$value_asent = array($colonia);
					$consulta_asent = $db->prepare($sql_asent);
					
					try {
						$consulta_asent->execute($value_asent);
						if($consulta_asent->rowCount() > 0) {
							$row_asent = $consulta_asent->fetch(PDO::FETCH_ASSOC);
							$asentamiento = $row_asent['MUN_ASENTAMIENTO'];
						} 
					} catch (PDOException $e) {
						die($e->getMessage().$sql_asent);
					}
				}

				//Cuenta Actual
				$tipo_cred = ($row['RESPONSABILIDAD'] == 'I' ? 'I' : 'G');
				$cuenta_act1 = str_pad($row['CUENT_ACT'], 5, "0", STR_PAD_LEFT);
				$cuenta_act2 = str_pad($row['CUENT_ACT2'], 6, "0", STR_PAD_LEFT);
				$cuenta_actual = $tipo_cred.$cuenta_act1."-".$cuenta_act2;

				//Obtiene la Fecha del Último Pago
				if(isset($_POST['fecha_ultimo_pago']) || isset($_POST['monto_ultimo_pago'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_ult = "SELECT TPI_FECHA as FECHA_ULTIMA,
										   TPI_PAGADO as ULTIMO_PAGO
									FROM TABLA_PAGOS_IND
									WHERE CRE_ID = ?
									AND TPI_PAGADO != 0
									ORDER BY TPI_FECHA DESC";
						$values_ult = array($row['CUENT_ACT']);						
					} else {
						$sql_ult = "SELECT PI_FECHA_REG as FECHA_ULTIMA,
										   PI_PAGO as ULTIMO_PAGO
									FROM PAGOS_INDIVIDUALES
									WHERE GRU_ID = ?
									AND PER_ID = ?
									AND PI_PAGO != 0
									ORDER BY PI_FECHA_REG DESC";
						$values_ult = array($row['CUENT_ACT'],
											$row['CUENT_ACT2']);						
					}
					
					$consulta_ult = $db->prepare($sql_ult);
					try {
						$consulta_ult->execute($values_ult);
						$row_ult = $consulta_ult->fetch(PDO::FETCH_ASSOC);
					} catch (PDOException $e) {
						die($e->getMessage().$sql_ult);
					}
				}

				//Obtiene el Saldo Actual
				if(isset($_POST['total_saldos_actuales']) || isset($_POST['saldo_actual'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_actual = "SELECT SUM(TPI_FALTANTE) as SALDO_ACTUAL
									   FROM TABLA_PAGOS_IND
									   WHERE CRE_ID = ?";	
						$values_actual = array($row['CUENT_ACT']);			   		   
					} else {
						$sql_actual = "SELECT SUM(PI_PENDIENTE) as SALDO_ACTUAL
									   FROM PAGOS_INDIVIDUALES
									   WHERE GRU_ID = ?
									   AND PER_ID = ?";
						$values_actual = array($row['CUENT_ACT'],
										   	   $row['CUENT_ACT2']);			   
					}
					$consulta_actual = $db->prepare($sql_actual);
					try {
						$consulta_actual->execute($values_actual);
						$row_actual = $consulta_actual->fetch(PDO::FETCH_ASSOC);
					} catch (PDOException $e) {
						die($e->getMessage().$sql_actual);
					}
				}

				//Obtiene el Saldo Vencido
				if(isset($_POST['monto_pagar']) || 
					   isset($_POST['total_saldos_vencidos']) || 
					   isset($_POST['saldo_vencido']) ||
					   isset($_POST['num_pagos_vencidos']) ||
					   isset($_POST['pago_actual'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_venc = "SELECT SUM(TPI_FALTANTE) as SALDO_VENCIDO
									 FROM TABLA_PAGOS_IND
									 WHERE CRE_ID = ?
									 AND TPI_FECHA < CURRENT_DATE";
						$values_venc = array($row['CUENT_ACT']);			 			   
					} else {
						$sql_venc = "SELECT SUM(PI_PENDIENTE) as SALDO_VENCIDO
									 FROM PAGOS_INDIVIDUALES
									 WHERE GRU_ID = ?
									 AND PER_ID = ?
									 AND PI_FECHA < CURRENT_DATE";
						$values_venc = array($row['CUENT_ACT'],
											 $row['CUENT_ACT2']);			 
					}
					
					$consulta_venc = $db->prepare($sql_venc);
					try {
						$consulta_venc->execute($values_venc);
						$row_venc = $consulta_venc->fetch(PDO::FETCH_ASSOC);
					} catch (PDOException $e) {
						die($e->getMessage().$sql_venc);
					}

					//Obtiene # de Pagos Vencidos y Pago Actual
					$pag_act = "V";
					if($row_venc['SALDO_VENCIDO'] > 0) {
						$pag_venc = floor($row_venc['SALDO_VENCIDO'] / $row['PAGO_SEMANAL']);
						$pag_act = $pag_venc;
					}

					//Monto a Pagar
					//Si ya se pagaron las doce semanas en col MONTOAPAGAR que aparezca en 0
					$monto_pagar = $row['PAGO_SEMANAL'];
					if($row['VIGENTE'] == 0) {
						$monto_pagar = $row_venc['SALDO_VENCIDO'];
					}
				}


				//Obtiene Total de Pagos Reportados
				if(isset($_POST['total_pagos_rep'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_tots = "SELECT SUM(TPI_PAGADO) as TOTAL_PAGADO
									 FROM TABLA_PAGOS_IND
									 WHERE CRE_ID = ?
									 AND TPI_FECHA < CURRENT_DATE";		
						$values_tots = array($row['CUENT_ACT']);			 	   
					} else {
						$sql_tots = "SELECT SUM(PI_PAGO) as TOTAL_PAGADO
									 FROM PAGOS_INDIVIDUALES
									 WHERE GRU_ID = ?
									 AND PER_ID = ?
									 AND PI_FECHA < CURRENT_DATE";
						$values_tots = array($row['CUENT_ACT'],
										 	 $row['CUENT_ACT2']);			 
					}
					
					$consulta_tots = $db->prepare($sql_tots);
					try {
						$consulta_tots->execute($values_tots);
						$row_tots = $consulta_tots->fetch(PDO::FETCH_ASSOC);
					} catch (PDOException $e) {
						die($e->getMessage().$sql_tots);
					}
					
					$total_pagos_rep = 0;
					if($row_tots['TOTAL_PAGADO'] > 0 ) {
						$total_pagos_rep = floor($row_tots['TOTAL_PAGADO'] / $row['PAGO_SEMANAL']);
					}
				}

				//Obtiene Fecha del Primer Incumplimiento
				if(isset($_POST['fecha_inc'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_fecha_inc = "SELECT TPI_FECHA as FECHA_INC
										  FROM TABLA_PAGOS_IND
										  WHERE CRE_ID = ?
										  AND TPI_FALTANTE != 0
										  AND TPI_FECHA < CURRENT_DATE";
						$values_fecha_inc = array($row['CUENT_ACT']);				  			   
					} else {
						$sql_fecha_inc = "SELECT PI_FECHA as FECHA_INC
										  FROM PAGOS_INDIVIDUALES
										  WHERE GRU_ID = ?
										  AND PER_ID = ?
										  AND PI_PENDIENTE != 0
										  AND PI_FECHA < CURRENT_DATE";
						$values_fecha_inc = array($row['CUENT_ACT'],
										 		  $row['CUENT_ACT2']);				  
					}
					
					$consulta_fecha_inc = $db->prepare($sql_fecha_inc);
					try {
						$consulta_fecha_inc->execute($values_fecha_inc);
						$fecha_inc = '1901-01-01';
						if($consulta_fecha_inc->rowCount() > 0) {
							$row_fecha_inc = $consulta_fecha_inc->fetch(PDO::FETCH_ASSOC);
							$fecha_inc = $row_fecha_inc['FECHA_INC'];
						}
						
					} catch (PDOException $e) {
						die($e->getMessage().$sql_fecha_inc);
					}
				}

				//Obtiene Total Abonado
				if(isset($_POST['total_abonado']) || isset($_POST['saldo_insoluto'])) {
					if($row['RESPONSABILIDAD'] == 'I') {
						$sql_abo = "SELECT SUM(TPI_PAGADO) as total_abonado
									FROM TABLA_PAGOS_IND 
									WHERE CRE_ID = ?";
						$values_abo = array($row['CUENT_ACT']);	
						$consulta_abo = $db->prepare($sql_abo);	
						try {
							$consulta_abo->execute($values_abo);
							$row_abo = $consulta_abo->fetch(PDO::FETCH_ASSOC);
							$total_abonado = $row_abo['total_abonado'];
						} catch (PDOException $e) {
							die($e->getMessage());
						}
					} else {
						$sql_abo = "SELECT SUM(PD_MONTO) as total_abonado
									FROM PAGOS_DESGLOSADOS 
									WHERE PER_ID = ? 
									AND GRU_ID = ?";
						$values_abo = array($row['CUENT_ACT2'],
											$row['CUENT_ACT']);	
						$consulta_abo = $db->prepare($sql_abo);	
						try {
							$consulta_abo->execute($values_abo);
							$row_abo = $consulta_abo->fetch(PDO::FETCH_ASSOC);
							$total_abonado = $row_abo['total_abonado'];
						} catch (PDOException $e) {
							die($e->getMessage());
						}
					}
				}
				

				//Obtiene Total Recuperado
				$total_recuperado = 0;
				if(isset($_POST['total_recuperado']) || isset($_POST['saldo_insoluto'])) {
					if($row['RESPONSABILIDAD'] != 'I') {
						$sql_rec = "SELECT SUM(PR_MONTO) as total_recuperado
									FROM PAGOS_RECUPERADOS 
									WHERE PER_ID = ? 
									AND GRU_ID = ?";
						$values_rec = array($row['CUENT_ACT2'],
											$row['CUENT_ACT']);	
						$consulta_rec = $db->prepare($sql_rec);	
						try {
							$consulta_rec->execute($values_rec);
							$row_rec = $consulta_rec->fetch(PDO::FETCH_ASSOC);
							$total_recuperado = $row_rec['total_recuperado'];
						} catch (PDOException $e) {
							die($e->getMessage());
						}
					}
				}


				$plazo_meses = floor($row['PLAZO'] / 4);

				$vigente = ($row['VIGENTE'] == 0 ? 'Inactivo' : (strtotime($row['FECHA_ENTREGA']) > strtotime(date("Y-m-d")) ? 'Pendiente' : 'Activo'));

				//Saldo Insoluto -> (Crédito Otorgado) - (Pagos que ha realizado / 101 * 83 )
				if(isset($_POST['saldo_insoluto'])) {
					$saldo_insoluto = ($row['MONTO_INDIVIDUAL']) - (($total_abonado + $total_recuperado) / 101 * 83);
				}

				//AGREGAMOS LA ROW
				$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A'.$n, $row['PER_APELLIDO_PAT'])
		            ->setCellValue('B'.$n, $row['PER_APELLIDO_MAT'])
		            ->setCellValue('C'.$n, $row['PER_NOMBRE'])
		            ->setCellValue('D'.$n, date("d/m/Y",strtotime($row['PER_FECHA_NAC'])))
		            ->setCellValue('E'.$n, $row['PER_RFC'])
		            ->setCellValue('F'.$n, $row['PER_CURP'])
		            ->setCellValue('G'.$n, '')
		            ->setCellValue('H'.$n, 'MX')
		            ->setCellValue('I'.$n, $residencia)
		            ->setCellValue('J'.$n, '')
		            ->setCellValue('K'.$n, $edo_civil)
		            ->setCellValue('L'.$n, $sexo)
		            ->setCellValue('M'.$n, $row['IFE_NUM'])
		            ->setCellValue('N'.$n, $dependientes)
		            ->setCellValue('O'.$n, 'PF')
		            ->setCellValue('P'.$n, $row['PER_DIRECCION']." #".$row['PER_NUM'])
		            ->setCellValue('Q'.$n, $colonia)
		            ->setCellValue('R'.$n, $row['PER_MUNICIPIO'])
		            ->setCellValue('S'.$n, $row['PER_MUNICIPIO'])
		            ->setCellValue('T'.$n, 'Nuevo León')
		            ->setCellValue('U'.$n, $row['PER_CP'])
		            ->setCellValue('V'.$n, $row['PER_CELULAR'])
		            ->setCellValue('W'.$n, 'C')
		            ->setCellValue('X'.$n, (isset($_POST['asentamiento']) ? $asentamiento : '-'))
		            ->setCellValue('Y'.$n, $cuenta_actual)
		            ->setCellValue('Z'.$n, $row['SIU_NOMBRE'])
		            ->setCellValue('AA'.$n, $row['RESPONSABILIDAD'])
		            ->setCellValue('AB'.$n, 'F')
		            ->setCellValue('AC'.$n, 'PP')
		            ->setCellValue('AD'.$n, 'MX')
		            ->setCellValue('AE'.$n, $vigente)
		            ->setCellValue('AF'.$n, $row['PLAZO'])
		            ->setCellValue('AG'.$n, 'S')
		            ->setCellValue('AH'.$n, (isset($_POST['monto_pagar']) ? $monto_pagar : '-'))
		            ->setCellValue('AI'.$n, date("d/m/Y",strtotime($row['FECHA_ENTREGA'])))
		            ->setCellValue('AJ'.$n, (isset($_POST['fecha_ultimo_pago']) ? date("d/m/Y",strtotime($row_ult['FECHA_ULTIMA'])) : "-"))
		            ->setCellValue('AK'.$n, date("d/m/Y",strtotime($row['FECHA_ENTREGA'])))
		            ->setCellValue('AL'.$n, date("d/m/Y"))
		            ->setCellValue('AM'.$n, $row['GARANTIA_BIEN_1'])
		            ->setCellValue('AN'.$n, $row['MAXIMO_PAGAR'])
		            ->setCellValue('AO'.$n, (isset($_POST['saldo_actual']) ? $row_actual['SALDO_ACTUAL'] : '-'))
		            ->setCellValue('AP'.$n, $row['MAXIMO_PAGAR'])
		            ->setCellValue('AQ'.$n, (isset($_POST['saldo_vencido']) ? $row_venc['SALDO_VENCIDO'] : '-'))
		            ->setCellValue('AR'.$n, (isset($_POST['num_pagos_vencidos']) ? $pag_venc : '-'))
		            ->setCellValue('AS'.$n, (isset($_POST['pago_actual']) ? $pag_act : '-'))
		            ->setCellValue('AT'.$n, (isset($_POST['total_pagos_rep']) ? $total_pagos_rep : '-'))
		            ->setCellValue('AU'.$n, (isset($_POST['fecha_inc']) ? date("d/m/Y",strtotime($fecha_inc)) : '-'))
		            ->setCellValue('AV'.$n, (isset($_POST['monto_ultimo_pago']) ? $row_ult['ULTIMO_PAGO'] : '-'))
		            ->setCellValue('AW'.$n, (isset($_POST['total_abonado']) ? $total_abonado : '-'))
		            ->setCellValue('AX'.$n, (isset($_POST['total_recuperado']) ? $total_recuperado : '-'))
		            ->setCellValue('AY'.$n, $plazo_meses)
		            ->setCellValue('AZ'.$n, $row['MONTO_INDIVIDUAL'])
		            ->setCellValue('BA'.$n, (isset($_POST['total_saldos_actuales']) ? $row_actual['SALDO_ACTUAL'] : '-'))
		            ->setCellValue('BB'.$n, (isset($_POST['total_saldos_vencidos']) ? $row_venc['SALDO_VENCIDO'] : '-'))
		            ->setCellValue('BC'.$n, '1')
		            ->setCellValue('BD'.$n, '1')
		            ->setCellValue('BE'.$n, '1')
		            ->setCellValue('BF'.$n, '1')
		            ->setCellValue('BG'.$n, (isset($_POST['saldo_insoluto']) ? (number_format($saldo_insoluto, 2)) : '-'));


		    	$n++;

			}

		} catch (PDOException $e) {
			die($e->getMessage().$sql);
		}
		

		$objPHPExcel->getActiveSheet()->setTitle('CirculoCredito');  
		$objPHPExcel->setActiveSheetIndex(0);    
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'circulo-credito.xlsx', __FILE__));      


		$json['completado'] = true;

		echo json_encode($json);
	}

	function getExcel2() {
		$json = array();
		$json['completado'] = false;

		$columns = array("A",
						 "B",
						 "C",
						 "D",
						 "E",
						 "F",
						 "G",
						 "H",
						 "I",
						 "J",
						 "K",
						 "L",
						 "M",
						 "N",
						 "O",
						 "P",
						 "Q",
						 "R",
						 "S",
						 "T",
						 "U",
						 "V",
						 "W",
						 "X",
						 "Y",
						 "Z",
						 "AA",
						 "AB",
						 "AC",
						 "AD",
						 "AE",
						 "AF",
						 "AG",
						 "AH",
						 "AI",
						 "AJ",
						 "AK",
						 "AL",
						 "AM",
						 "AN",
						 "AO",
						 "AP",
						 "AQ",
						 "AR",
						 "AS",
						 "AT",
						 "AU",
						 "AV",
						 "AW",
						 "AX",
						 "AY",
						 "AZ",
						 "BA",
						 "BB",
						 "BC");

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Grupo Propulsor de Microempresas del Norte")
					 ->setLastModifiedBy("Grupo Propulsor de Microempresas del Norte")
					 ->setTitle("Circulo de Credito")
					 ->setSubject("Circulo de Credito")
					 ->setDescription("Reporte de Circulo de Credito")
					 ->setKeywords("circulo credito");

		$styleArray = array(
				        'font' => array(
				            'bold' => true
				        ),
				        'alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );
		$styleArray2 = array('alignment' => array(
				            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				        )
				    );		    			 

		//Hacemos más grande las columnas, bold la primera y text-center
		foreach ($columns as $column) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(20);
			$objPHPExcel->getActiveSheet()->getStyle($column."1")->applyFromArray($styleArray);
			$objPHPExcel->getActiveSheet()->getStyle($column)->applyFromArray($styleArray2);
		}

		$objPHPExcel->getActiveSheet()->getColumnDimension("AT")->setWidth(22);
		$objPHPExcel->getActiveSheet()->getColumnDimension("AZ")->setWidth(28);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BA")->setWidth(29);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BB")->setWidth(27.7);
		$objPHPExcel->getActiveSheet()->getColumnDimension("BC")->setWidth(26.7);

		$objPHPExcel->getStyle("M")->getNumberFormat()->setFormatCode('0'); 


		$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A1', 'ApellidoPaterno')
		            ->setCellValue('B1', 'ApellidoMaterno')
		            ->setCellValue('C1', 'Nombres')
		            ->setCellValue('D1', 'FechaNacimiento')
		            ->setCellValue('E1', 'RFC')
		            ->setCellValue('F1', 'CURP')
		            ->setCellValue('G1', 'NumeroSeguridadSocial')
		            ->setCellValue('H1', 'Nacionalidad')
		            ->setCellValue('I1', 'Residencia')
		            ->setCellValue('J1', 'NumeroLicenciaConducir')
		            ->setCellValue('K1', 'EstadoCivil')
		            ->setCellValue('L1', 'Sexo')
		            ->setCellValue('M1', 'ClaveElectorIFE')
		            ->setCellValue('N1', 'NumeroDependientes')
		            ->setCellValue('O1', 'TipoPersona')
		            ->setCellValue('P1', 'Dirección')
		            ->setCellValue('Q1', 'ColoniaPoblacion')
		            ->setCellValue('R1', 'DelegacionMunicipio')
		            ->setCellValue('S1', 'Ciudad')
		            ->setCellValue('T1', 'Estado')
		            ->setCellValue('U1', 'CP')
		            ->setCellValue('V1', 'NumeroTelefono')
		            ->setCellValue('W1', 'TipoDomicilio')
		            ->setCellValue('X1', 'TipoAsentamiento')
		            ->setCellValue('Y1', 'CuentaActual')
		            ->setCellValue('Z1', 'TipoResponsabilidad')
		            ->setCellValue('AA1', 'TipoCuenta')
		            ->setCellValue('AB1', 'TipoContrato')
		            ->setCellValue('AC1', 'ClaveUnidadMonetaria')
		            ->setCellValue('AD1', 'ValorActivoValuacion')
		            ->setCellValue('AE1', 'NumeroPagos')
		            ->setCellValue('AF1', 'FrecuenciaPagos')
		            ->setCellValue('AG1', 'MontoPagar')
		            ->setCellValue('AH1', 'FechaAperturaCuenta')
		            ->setCellValue('AI1', 'FechaUltimoPago')
		            ->setCellValue('AJ1', 'FechaUltimaCompra')
		            ->setCellValue('AK1', 'FechaCorte')
		            ->setCellValue('AL1', 'Garantia')
		            ->setCellValue('AM1', 'CreditoMaximo')
		            ->setCellValue('AN1', 'SaldoActual')
		            ->setCellValue('AO1', 'LimiteCredito')
		            ->setCellValue('AP1', 'SaldoVencido')
		            ->setCellValue('AQ1', 'NumeroPagosVencidos')
		            ->setCellValue('AR1', 'PagoActual')
		            ->setCellValue('AS1', 'TotalPagosReportados')
		            ->setCellValue('AT1', 'FechaPrimerIncumplimiento')
		            ->setCellValue('AU1', 'MontoUltimoPago')
		            ->setCellValue('AV1', 'PlazoMeses')
		            ->setCellValue('AW1', 'MontoCreditoOriginacion')
		            ->setCellValue('AX1', 'TotalSaldosActuales')
		            ->setCellValue('AY1', 'TotalSaldosVencidos')
		            ->setCellValue('AZ1', 'TotalElementosNombreReportados')
		            ->setCellValue('BA1', 'TotalElementosDireccionReportados')
		            ->setCellValue('BB1', 'TotalElementosEmpleoReportados')
		            ->setCellValue('BC1', 'TotalElementosCuentaReportados');


		/*DATOS*/
		$sql = "SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   PERSONAS_GRUPOS.GRU_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'M' AS RESPONSABILIDAD,
						   GRUPOS.GRU_PLAZO as PLAZO,
						   PERSONAS_GRUPOS.PAGO_SEMANAL_IND as PAGO_SEMANAL,
						   GRU_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   PERSONAS_GRUPOS.MONTO_INDIVIDUAL
					FROM PERSONAS
					JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
					JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
					WHERE GRU_FECHA_ENTREGA >= ?
					AND GRU_FECHA_ENTREGA <= ?
					UNION
					SELECT PER_APELLIDO_PAT,
						   PER_APELLIDO_MAT,
						   PER_NOMBRE,
						   PER_FECHA_NAC,
						   PER_RFC,
						   PER_CURP,
						   VIVIENDA,
						   PER_EDO_CIVIL,
						   PER_GENERO,
						   IFE_NUM,
						   DEPENDE_PADRES,
						   DEPENDE_CONYUGUE,
						   DEPENDE_HIJOS,
						   DEPENDE_HERMANOS,
						   DEPENDE_OTROS,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_COLONIA_OTRA,
						   PER_MUNICIPIO,
						   PER_ESTADO,
						   PER_CP,
						   PER_CELULAR,
						   CRE_ID AS CUENT_ACT,
						   PERSONAS.PER_ID AS CUENT_ACT2,
						   'I' AS RESPONSABILIDAD,
						   CRE_PLAZO as PLAZO,
						   CRE_PAGO_SEMANAL as PAGO_SEMANAL,
						   CRE_FECHA_ENTREGA as FECHA_ENTREGA,
						   GARANTIA_BIEN_1,
						   MAXIMO_PAGAR,
						   CRE_MONTO_TOTAL as MONTO_INDIVIDUAL
					FROM PERSONAS
					JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					WHERE CRE_FECHA_ENTREGA >= ?
					AND CRE_FECHA_ENTREGA <= ?";

		$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));	
		$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));		

		$values = array($fecha_1,
						$fecha_2,
						$fecha_1,
						$fecha_2);	

		$db = $this->_conexion;
		$consulta = $db->prepare($sql);	

		$n = 2;

		try {
			
			$consulta->execute($values);
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result as $row) {

				//Residencia
				$residencia = ($row['VIVIENDA'] == 0 ? "2" : "1");

				//Edo. Civil
				$edo_civil = "S";
				if($row['PER_EDO_CIVIL'] == "Casado") {
					$edo_civil = "C";
				} else if($row['PER_EDO_CIVIL'] == "Divorciado") {
					$edo_civil = "D";
				} else if($row['PER_EDO_CIVIL'] == "Viudo") {
					$edo_civil = "V";
				}

				//Sexo
				$sexo = ($row['PER_GENERO'] == 'Masculino' ? "M" : "F");

				//Dependientes 
				$dependientes = $row['DEPENDE_PADRES'] + $row['DEPENDE_CONYUGUE'] + $row['DEPENDE_HIJOS'] + $row['DEPENDE_HERMANOS'] + $row['DEPENDE_OTROS'];

				//Obtiene el Asentamiento de la colonia
				$colonia = ($row['PER_COLONIA'] == '0' ? $row['PER_COLONIA_OTRA'] : $row['PER_COLONIA']);
				$sql_asent = "SELECT MUN_ASENTAMIENTO FROM MUNICIPIOS WHERE MUN_COLONIA = ?";
				$value_asent = array($colonia);
				$consulta_asent = $db->prepare($sql_asent);
				$asentamiento = 7;
				try {
					$consulta_asent->execute($value_asent);
					if($consulta_asent->rowCount() > 0) {
						$row_asent = $consulta_asent->fetch(PDO::FETCH_ASSOC);
						$asentamiento = $row_asent['MUN_ASENTAMIENTO'];
					} 
				} catch (PDOException $e) {
					die($e->getMessage().$sql_asent);
				}

				//Cuenta Actual
				$tipo_cred = ($row['RESPONSABILIDAD'] == 'I' ? 'I' : 'G');
				$cuenta_act1 = str_pad($row['CUENT_ACT'], 5, "0", STR_PAD_LEFT);
				$cuenta_act2 = str_pad($row['CUENT_ACT2'], 6, "0", STR_PAD_LEFT);
				$cuenta_actual = $tipo_cred.$cuenta_act1."-".$cuenta_act2;

				//Obtiene la Fecha del Último Pago
				if($row['RESPONSABILIDAD'] == 'I') {
					$sql_ult = "SELECT TPI_FECHA as FECHA_ULTIMA,
									   TPI_PAGADO as ULTIMO_PAGO
								FROM TABLA_PAGOS_IND
								WHERE CRE_ID = ?
								AND TPI_PAGADO != 0
								ORDER BY TPI_FECHA DESC";
					$values_ult = array($row['CUENT_ACT']);						
				} else {
					$sql_ult = "SELECT PI_FECHA_REG as FECHA_ULTIMA,
									   PI_PAGO as ULTIMO_PAGO
								FROM PAGOS_INDIVIDUALES
								WHERE GRU_ID = ?
								AND PER_ID = ?
								AND PI_PAGO != 0
								ORDER BY PI_FECHA_REG DESC";
					$values_ult = array($row['CUENT_ACT'],
										$row['CUENT_ACT2']);						
				}
				
				$consulta_ult = $db->prepare($sql_ult);
				try {
					$consulta_ult->execute($values_ult);
					$row_ult = $consulta_ult->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die($e->getMessage().$sql_ult);
				}

				//Obtiene el Saldo Actual
				if($row['RESPONSABILIDAD'] == 'I') {
					$sql_actual = "SELECT SUM(TPI_FALTANTE) as SALDO_ACTUAL
								   FROM TABLA_PAGOS_IND
								   WHERE CRE_ID = ?";	
					$values_actual = array($row['CUENT_ACT']);			   		   
				} else {
					$sql_actual = "SELECT SUM(PI_PENDIENTE) as SALDO_ACTUAL
								   FROM PAGOS_INDIVIDUALES
								   WHERE GRU_ID = ?
								   AND PER_ID = ?";
					$values_actual = array($row['CUENT_ACT'],
									   	   $row['CUENT_ACT2']);			   
				}
				$consulta_actual = $db->prepare($sql_actual);
				try {
					$consulta_actual->execute($values_actual);
					$row_actual = $consulta_actual->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die($e->getMessage().$sql_actual);
				}

				//Obtiene el Saldo Vencido
				if($row['RESPONSABILIDAD'] == 'I') {
					$sql_venc = "SELECT SUM(TPI_FALTANTE) as SALDO_VENCIDO
								 FROM TABLA_PAGOS_IND
								 WHERE CRE_ID = ?
								 AND TPI_FECHA < CURRENT_DATE";
					$values_venc = array($row['CUENT_ACT']);			 			   
				} else {
					$sql_venc = "SELECT SUM(PI_PENDIENTE) as SALDO_VENCIDO
								 FROM PAGOS_INDIVIDUALES
								 WHERE GRU_ID = ?
								 AND PER_ID = ?
								 AND PI_FECHA < CURRENT_DATE";
					$values_venc = array($row['CUENT_ACT'],
										 $row['CUENT_ACT2']);			 
				}
				
				$consulta_venc = $db->prepare($sql_venc);
				try {
					$consulta_venc->execute($values_venc);
					$row_venc = $consulta_venc->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die($e->getMessage().$sql_venc);
				}

				//Obtiene # de Pagos Vencidos y Pago Actual
				$pag_act = "V";
				if($row_venc['SALDO_VENCIDO'] > 0) {
					$pag_venc = floor($row_venc['SALDO_VENCIDO'] / $row['PAGO_SEMANAL']);
					$pag_act = $pag_venc;
				}

				//Obtiene Total de Pagos Reportados
				if($row['RESPONSABILIDAD'] == 'I') {
					$sql_tots = "SELECT SUM(TPI_PAGADO) as TOTAL_PAGADO
								 FROM TABLA_PAGOS_IND
								 WHERE CRE_ID = ?
								 AND TPI_FECHA < CURRENT_DATE";		
					$values_tots = array($row['CUENT_ACT']);			 	   
				} else {
					$sql_tots = "SELECT SUM(PI_PAGO) as TOTAL_PAGADO
								 FROM PAGOS_INDIVIDUALES
								 WHERE GRU_ID = ?
								 AND PER_ID = ?
								 AND PI_FECHA < CURRENT_DATE";
					$values_tots = array($row['CUENT_ACT'],
									 	 $row['CUENT_ACT2']);			 
				}
				
				$consulta_tots = $db->prepare($sql_tots);
				try {
					$consulta_tots->execute($values_tots);
					$row_tots = $consulta_tots->fetch(PDO::FETCH_ASSOC);
				} catch (PDOException $e) {
					die($e->getMessage().$sql_tots);
				}
				
				$total_pagos_rep = 0;
				if($row_tots['TOTAL_PAGADO'] > 0 ) {
					$total_pagos_rep = floor($row_tots['TOTAL_PAGADO'] / $row['PAGO_SEMANAL']);
				}

				//Obtiene Fecha del Primer Incumplimiento
				if($row['RESPONSABILIDAD'] == 'I') {
					$sql_fecha_inc = "SELECT TPI_FECHA as FECHA_INC
									  FROM TABLA_PAGOS_IND
									  WHERE CRE_ID = ?
									  AND TPI_FALTANTE != 0
									  AND TPI_FECHA < CURRENT_DATE";
					$values_fecha_inc = array($row['CUENT_ACT']);				  			   
				} else {
					$sql_fecha_inc = "SELECT PI_FECHA as FECHA_INC
									  FROM PAGOS_INDIVIDUALES
									  WHERE GRU_ID = ?
									  AND PER_ID = ?
									  AND PI_PENDIENTE != 0
									  AND PI_FECHA < CURRENT_DATE";
					$values_fecha_inc = array($row['CUENT_ACT'],
									 		  $row['CUENT_ACT2']);				  
				}
				
				$consulta_fecha_inc = $db->prepare($sql_fecha_inc);
				try {
					$consulta_fecha_inc->execute($values_fecha_inc);
					$fecha_inc = '1901-01-01';
					if($consulta_asent->rowCount() > 0) {
						$row_fecha_inc = $consulta_fecha_inc->fetch(PDO::FETCH_ASSOC);
						$fecha_inc = $row_fecha_inc['FECHA_INC'];
					}
					
				} catch (PDOException $e) {
					die($e->getMessage().$sql_fecha_inc);
				}

				$plazo_meses = floor($row['PLAZO'] / 4);

				//AGREGAMOS LA ROW
				$objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A'.$n, $row['PER_APELLIDO_PAT'])
		            ->setCellValue('B'.$n, $row['PER_APELLIDO_MAT'])
		            ->setCellValue('C'.$n, $row['PER_NOMBRE'])
		            ->setCellValue('D'.$n, date("d/m/Y",strtotime($row['PER_FECHA_NAC'])))
		            ->setCellValue('E'.$n, $row['PER_RFC'])
		            ->setCellValue('F'.$n, $row['PER_CURP'])
		            ->setCellValue('G'.$n, '')
		            ->setCellValue('H'.$n, 'MX')
		            ->setCellValue('I'.$n, $residencia)
		            ->setCellValue('J'.$n, '')
		            ->setCellValue('K'.$n, $edo_civil)
		            ->setCellValue('L'.$n, $sexo)
		            ->setCellValue('M'.$n, $row['IFE_NUM'])
		            ->setCellValue('N'.$n, $dependientes)
		            ->setCellValue('O'.$n, 'PF')
		            ->setCellValue('P'.$n, $row['PER_DIRECCION']." #".$row['PER_NUM'])
		            ->setCellValue('Q'.$n, $colonia)
		            ->setCellValue('R'.$n, $row['PER_MUNICIPIO'])
		            ->setCellValue('S'.$n, $row['PER_MUNICIPIO'])
		            ->setCellValue('T'.$n, 'Nuevo León')
		            ->setCellValue('U'.$n, $row['PER_CP'])
		            ->setCellValue('V'.$n, $row['PER_CELULAR'])
		            ->setCellValue('W'.$n, 'C')
		            ->setCellValue('X'.$n, $asentamiento)
		            ->setCellValue('Y'.$n, $cuenta_actual)
		            ->setCellValue('Z'.$n, $row['RESPONSABILIDAD'])
		            ->setCellValue('AA'.$n, 'F')
		            ->setCellValue('AB'.$n, 'PP')
		            ->setCellValue('AC'.$n, 'MX')
		            ->setCellValue('AD'.$n, '')
		            ->setCellValue('AE'.$n, $row['PLAZO'])
		            ->setCellValue('AF'.$n, 'S')
		            ->setCellValue('AG'.$n, $row['PAGO_SEMANAL'])
		            ->setCellValue('AH'.$n, date("d/m/Y",strtotime($row['FECHA_ENTREGA'])))
		            ->setCellValue('AI'.$n, date("d/m/Y",strtotime($row_ult['FECHA_ULTIMA'])))
		            ->setCellValue('AJ'.$n, date("d/m/Y",strtotime($row['FECHA_ENTREGA'])))
		            ->setCellValue('AK'.$n, date("d/m/Y"))
		            ->setCellValue('AL'.$n, $row['GARANTIA_BIEN_1'])
		            ->setCellValue('AM'.$n, $row['MAXIMO_PAGAR'])
		            ->setCellValue('AN'.$n, $row_actual['SALDO_ACTUAL'])
		            ->setCellValue('AO'.$n, $row['MAXIMO_PAGAR'])
		            ->setCellValue('AP'.$n, $row_venc['SALDO_VENCIDO'])
		            ->setCellValue('AQ'.$n, $pag_venc)
		            ->setCellValue('AR'.$n, $pag_act)
		            ->setCellValue('AS'.$n, $total_pagos_rep)
		            ->setCellValue('AT'.$n, date("d/m/Y",strtotime($fecha_inc)))
		            ->setCellValue('AU'.$n, $row_ult['ULTIMO_PAGO'])
		            ->setCellValue('AV'.$n, $plazo_meses)
		            ->setCellValue('AW'.$n, $row['MONTO_INDIVIDUAL'])
		            ->setCellValue('AX'.$n, $row_actual['SALDO_ACTUAL'])
		            ->setCellValue('AY'.$n, $row_venc['SALDO_VENCIDO'])
		            ->setCellValue('AZ'.$n, '1')
		            ->setCellValue('BA'.$n, '1')
		            ->setCellValue('BB'.$n, '1')
		            ->setCellValue('BC'.$n, '1');


		    	$n++;

			}

		} catch (PDOException $e) {
			die($e->getMessage().$sql);
		}
		

		$objPHPExcel->getActiveSheet()->setTitle('CirculoCredito');  
		$objPHPExcel->setActiveSheetIndex(0);    
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('Libs.php', 'circulo-credito2.xlsx', __FILE__));      


		$json['completado'] = true;

		echo json_encode($json);
	}

}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getCirculo": 
			$libs->getCirculo();
			break;
		case "getExcel": 
			$libs->getExcel();
			break;
		case "getExcel2": 
			$libs->getExcel2();
			break;									
	}
}

?>