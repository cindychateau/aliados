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

class Libs extends Common {

	function showChart1() {
		$json = array();
		$json['error'] = false;
		
		/*CANTIDAD DE PERSONAS POR ACT ECON*/
		$json['actividad'] = array();
		$json['ticks'] = array();
		$sql = "SELECT COUNT(PER_ID) Total_Personas, 
						ACT_NOMBRE,
						PERSONAS.ACT_ID
				FROM PERSONAS 
				JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
				GROUP BY (PERSONAS.ACT_ID) 
				ORDER BY Total_Personas DESC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['actividad'][] = [$i, intval($row['Total_Personas'])];
				$json['ticks'][] = [$i, '<a href="actividad.php?act_id='.$row['ACT_ID'].'">'.$row['ACT_NOMBRE'].'</a>'];
				$i++;
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		/*PROMEDIO CRÉDITO POR ACT ECON*/
		$json['actividad_avg'] = array();
		$json['ticks_avg'] = array();
		$sql = "SELECT AVG(MONTO_INDIVIDUAL) as Promedio, 
					   ACT_NOMBRE
				FROM PERSONAS
				JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
				JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
				GROUP BY (PERSONAS.ACT_ID)
				ORDER BY Promedio DESC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['actividad_avg'][] = [$i, intval($row['Promedio'])];
				$json['ticks_avg'][] = [$i, $row['ACT_NOMBRE']];
				$i++;
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		/*PAGOS PEND POR ACT ECON*/
		$json['actividad_pend'] = array();
		$json['ticks_pend'] = array();
		$sql = "SELECT SUM(PI_PENDIENTE) Total_mora, 
					   ACT_NOMBRE, 
					   PERSONAS.ACT_ID
						FROM PERSONAS
				JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
				JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
				WHERE PI_FECHA < CURRENT_DATE
				AND PI_PENDIENTE > -1
				GROUP BY (PERSONAS.ACT_ID) 
				ORDER BY Total_mora DESC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['actividad_pend'][] = [$i, intval($row['Total_mora'])];
				$json['ticks_pend'][] = [$i, '<a href="pendiente.php?act_id='.$row['ACT_ID'].'">'.$row['ACT_NOMBRE'].'</a>'];
				$i++;
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}		

		echo json_encode($json);
	}

	function getPendiente() {
		$json = array();
		$json['error'] = false;

		if(isset($_POST['id'])) {
			$db = $this->_conexion;
			$sql = "SELECT PI_PENDIENTE, 
						   ACT_NOMBRE, 
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PI_FECHA,
						   GRU_ID
					FROM PERSONAS
					JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
					JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
					WHERE PI_FECHA < CURRENT_DATE
					AND PERSONAS.ACT_ID = ?
					AND PI_PENDIENTE > 0
					ORDER BY PERSONAS.PER_ID, GRU_ID";
			$values = array($_POST['id']);
			$consulta = $db->prepare($sql);

			$sql_total = "SELECT SUM(PI_PENDIENTE) as SUMA
						  FROM PERSONAS
						  JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
						  JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
						  WHERE PI_FECHA < CURRENT_DATE
						  AND PERSONAS.ACT_ID = ?
						  AND PI_PENDIENTE > 0";
			$consulta_total = $db->prepare($sql_total);			  

			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$consulta_total->execute($values);
				$row_total = $consulta_total->fetch(PDO::FETCH_ASSOC);

				$json['actividad'] = $result[0]['ACT_NOMBRE'];
				$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th>NOMBRE</th>
											<th>PAGO PENDIENTE</th>
											<th>GRUPO</th>
											<th>FECHA</th>
										</tr>
									</thead>
									<tbody>
									<tr>
										<td align="center">
											-
										</td>
										<td align="center">
											<b>$'.number_format($row_total['SUMA'], 2).'</b>
										</td>
										<td align="center">
											-
										</td>
										<td align="center">
											-
										</td>
									</tr>';
				foreach ($result as $row) {

					$json['table'] .= '<tr>
											<td align="center">'.$row['PER_NOMBRE'].'</td>
											<td align="center">$'.number_format($row['PI_PENDIENTE'], 2).'</td>
											<td align="center">'.$row['GRU_ID'].'</td>
											<td align="center">'.date("d/m/Y",strtotime($row['PI_FECHA'])).'</td>
										</tr>';

				}

				$json['table'] .= '</tbody>
								</table>';

			} catch (PDOException $e) {
				die($e->getMessage());
			}
					
		}

		echo json_encode($json);
	}

	function getActividad() {
		$json = array();
		$json['error'] = false;

		if(isset($_POST['id'])) {
			$db = $this->_conexion;
			$sql = "SELECT ACT_NOMBRE, 
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PERSONAS.PER_ID,
						   MONTO_INDIVIDUAL,
						   PG.GRU_ID,
						   GRUPOS.GRU_FECHA,
						   PER_CELULAR,
						   GRUPOS.SIU_ID,
						   SISTEMA_USUARIO.SIU_NOMBRE
					FROM PERSONAS
					JOIN ACTIVIDADES_ECONOMICAS ON ACTIVIDADES_ECONOMICAS.ACT_ID = PERSONAS.ACT_ID
					JOIN (SELECT * FROM PERSONAS_GRUPOS ORDER BY PG_ID DESC) PG ON PERSONAS.PER_ID = PG.PER_ID
					JOIN GRUPOS ON GRUPOS.GRU_ID = PG.GRU_ID
					JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					AND PERSONAS.ACT_ID = ?
					GROUP BY PERSONAS.PER_ID
					ORDER BY PER_NOMBRE, PG.GRU_ID DESC";
			$values = array($_POST['id']);
			$consulta = $db->prepare($sql);		  

			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$json['actividad'] = $result[0]['ACT_NOMBRE'];
				$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th>#</th>
											<th>NOMBRE</th>
											<th>PRÉSTAMO OTORGADO</th>
											<th>TELÉFONO</th>
											<th>PROMOTOR</th>
											<th>FECHA</th>
											<th>ATRASO</th>
											<th># FALLOS</th>
										</tr>
									</thead>
									<tbody>';
				$n = 0;					
				foreach ($result as $row) {
					$n++;

					$sql_pend = "SELECT SUM(PI_PENDIENTE) as PENDIENTE,
										PI_MONTO
								 FROM PAGOS_INDIVIDUALES
								 WHERE PER_ID = ?
								 AND PI_FECHA < CURRENT_DATE
								 AND PI_PENDIENTE > 0";

					$values_pend = array($row['PER_ID']);	
					$consulta_pend = $db->prepare($sql_pend);	
					$consulta_pend->execute($values_pend);
					$row_pend = $consulta_pend->fetch(PDO::FETCH_ASSOC);

					$num_atrasos = 0;
					if($row_pend['PENDIENTE'] != 0)
						$num_atrasos = floor($row_pend['PENDIENTE'] / $row_pend['PI_MONTO']);


					$json['table'] .= '<tr>
											<td align="center">'.$n.'</td>
											<td align="center"><a target="_blank" href="../../buscador/info.php?id='.$row['PER_ID'].'">'.$row['PER_NOMBRE'].'</a></td>
											<td align="center">'.$row['MONTO_INDIVIDUAL'].'</td>
											<td align="center">'.$row['PER_CELULAR'].'</td>
											<td align="center">'.$row['SIU_NOMBRE'].'</td>
											<td align="center">'.date("d/m/Y",strtotime($row['GRU_FECHA'])).'</td>
											<td align="center"><a href="pendiente_persona.php?id='.$row['PER_ID'].'">$'.number_format($row_pend['PENDIENTE'], 2).'</a></td>
											<td align="center">'.$num_atrasos.'</td>
										</tr>';

				}

				$json['table'] .= '</tbody>
								</table>';

			} catch (PDOException $e) {
				die($e->getMessage());
			}
					
		}

		echo json_encode($json);
	}


	function getPendientePersona() {
		$json = array();
		$json['error'] = false;

		if(isset($_POST['id'])) {
			$db = $this->_conexion;
			$sql = "SELECT PI_PENDIENTE,
						   PI_FECHA,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   GRU_ID
					FROM PAGOS_INDIVIDUALES
					JOIN PERSONAS ON PERSONAS.PER_ID = PAGOS_INDIVIDUALES.PER_ID
					WHERE PAGOS_INDIVIDUALES.PER_ID = ?
					AND PI_PENDIENTE > 0
					AND PI_FECHA < CURRENT_DATE";
			$values = array($_POST['id']);
			$consulta = $db->prepare($sql);	

			try {
				
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$json['persona'] = $result[0]['PER_NOMBRE'];

				$json['table'] = '<small>*Los datos a continuación pueden ser modificados en ABONAR PAGOS POR PERSONA</small>
								<table class="table table-striped">
									<thead>
										<tr>
											<th>#</th>
											<th>FECHA</th>
											<th>GRUPO</th>
											<th>PAGO PENDIENTE</th>
										</tr>
									</thead>
									<tbody>';

				$n = 0;					
				foreach ($result as $row) {
					$n++;

					$json['table'] .= '<tr>
											<td align="center">'.$n.'</td>
											<td align="center">'.date("d/m/Y",strtotime($row['PI_FECHA'])).'</td>
											<td align="center">'.$row['GRU_ID'].'</td>
											<td align="center">$'.number_format($row['PI_PENDIENTE'], 2).'</td>
										</tr>';

				}



			} catch (PDOException $e) {
				die($e->getMessage());
			}



		}

		echo json_encode($json);
	}


}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "showChart1": 
			$libs->showChart1();
			break;	
		case "getPendiente": 
			$libs->getPendiente();
			break;	
		case "getActividad": 
			$libs->getActividad();
			break;	
		case "getPendientePersona": 
			$libs->getPendientePersona();
			break;								
	}
}

?>