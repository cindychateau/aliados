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

		/*CANTIDAD DE PERSONAS EN MUNICIPIO*/
		$json['municipio'] = array();
		$json['ticks'] = array();
		$sql = "SELECT COUNT(PER_ID) Total_Personas, 
					   PER_MUNICIPIO 
				FROM PERSONAS 
				GROUP BY (PER_MUNICIPIO) 
				ORDER BY PER_MUNICIPIO ASC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['municipio'][] = [$i, intval($row['Total_Personas'])];
				$json['ticks'][] = [$i, ($row['PER_MUNICIPIO'] == "" ? "No Especificado" : '<a href="municipio.php?muni='.strtoupper($row['PER_MUNICIPIO']).'">'.strtoupper($row['PER_MUNICIPIO']).'</a>')];
				$i++;
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		/*PROMEDIO DE CREDITO OTORGADO POR MUNICIPIO*/
		$json['municipio_avg'] = array();
		$json['ticks_avg'] = array();
		$sql = "SELECT AVG(MONTO_INDIVIDUAL) as Promedio, 
					   PER_MUNICIPIO
				FROM PERSONAS
				JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
				GROUP BY PER_MUNICIPIO
				ORDER BY PER_MUNICIPIO ASC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['municipio_avg'][] = [$i, intval($row['Promedio'])];
				$json['ticks_avg'][] = [$i, ($row['PER_MUNICIPIO'] == "" ? "No Especificado" : strtoupper($row['PER_MUNICIPIO']))];
				$i++;
			}

		} catch (PDOException $e) {
			die($e->getMessage());
		}

		/*PAGOS PEND POR MUNICIPIO*/
		$json['actividad_pend'] = array();
		$json['ticks_pend'] = array();
		$sql = "SELECT SUM(PI_PENDIENTE) Total_mora, 
					   PER_MUNICIPIO
				FROM PERSONAS
				JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
				WHERE PI_FECHA < CURRENT_DATE
				AND PI_PENDIENTE > -1
				GROUP BY PER_MUNICIPIO 
				ORDER BY Total_mora DESC";
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			$i = 1;
			foreach ($result as $row) {
				$json['actividad_pend'][] = [$i, intval($row['Total_mora'])];
				$json['ticks_pend'][] = [$i, ($row['PER_MUNICIPIO'] == "" ? "No Especificado" : '<a href="pendiente.php?muni='.strtoupper($row['PER_MUNICIPIO']).'">'.strtoupper($row['PER_MUNICIPIO']).'</a>')];
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

		if(isset($_POST['municipio'])) {
			$db = $this->_conexion;
			$sql = "SELECT PI_PENDIENTE, 
						   PER_MUNICIPIO, 
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PI_FECHA,
						   GRU_ID
					FROM PERSONAS
					JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
					WHERE PI_FECHA < CURRENT_DATE
					AND PER_MUNICIPIO = ?
					AND PI_PENDIENTE > 0
					ORDER BY PERSONAS.PER_ID, GRU_ID";
			$values = array($_POST['municipio']);
			$consulta = $db->prepare($sql);

			$sql_total = "SELECT SUM(PI_PENDIENTE) as SUMA
						  FROM PERSONAS
						  JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
						  WHERE PI_FECHA < CURRENT_DATE
						  AND PER_MUNICIPIO = ?
						  AND PI_PENDIENTE > 0";
			$consulta_total = $db->prepare($sql_total);			  

			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$consulta_total->execute($values);
				$row_total = $consulta_total->fetch(PDO::FETCH_ASSOC);

				$json['municipio'] = $result[0]['PER_MUNICIPIO'];
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

	function getMuni() {
		$json = array();
		$json['error'] = false;

		if(isset($_POST['municipio'])) {
			$db = $this->_conexion;
			$sql = "SELECT PER_MUNICIPIO, 
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PI_FECHA,
						   GRU_ID
					FROM PERSONAS
					LEFT JOIN PAGOS_INDIVIDUALES ON PAGOS_INDIVIDUALES.PER_ID = PERSONAS.PER_ID
					WHERE PER_MUNICIPIO = ?
					GROUP BY PERSONAS.PER_ID
					ORDER BY PERSONAS.PER_ID, GRU_ID";
			$values = array($_POST['municipio']);
			$consulta = $db->prepare($sql);		  

			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$json['municipio'] = $result[0]['PER_MUNICIPIO'];
				$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th>NOMBRE</th>
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
											-
										</td>
										<td align="center">
											-
										</td>
									</tr>';
				foreach ($result as $row) {

					$json['table'] .= '<tr>
											<td align="center">'.$row['PER_NOMBRE'].'</td>
											<td align="center">'.$row['GRU_ID'].'</td>
											<td align="center">'.(is_null($row['PI_FECHA'] ? '-' : date("d/m/Y",strtotime($row['PI_FECHA'])))).'</td>
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
		case "getMuni": 
			$libs->getMuni();
			break;							
	}
}

?>