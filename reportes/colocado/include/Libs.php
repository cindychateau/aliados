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

	function getColocacion() {
		$json = array();
		$json['error'] = false;
		$json['table'] = '';

		$db = $this->_conexion;
		$sql = "SELECT SUM(GRU_MONTO_TOTAL) as MONTO_COLOCADO
				FROM GRUPOS
				WHERE GRU_FECHA_ENTREGA >= ?
				AND GRU_FECHA_ENTREGA < ?";
		$consulta = $db->prepare($sql);	

		//INDIVIDUALES
		$sql_ind = "SELECT SUM(CRE_MONTO_TOTAL) as CRE_MONTO_TOTAL
					FROM CREDITO_INDIVIDUAL
					WHERE CRE_FECHA_ENTREGA >= ?
					AND CRE_FECHA_ENTREGA < ?";
		$consulta_ind = $db->prepare($sql_ind);				

		$fecha = '2015-10-01';
		$fecha_actual = date("Y-m-d");

		$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th>MES</th>
											<th>MONTO TOTAL COLOCADO</th>
										</tr>
									</thead>
									<tbody>';		

		do {
			$mes = date('m', strtotime($fecha));
			$ano = date('Y', strtotime($fecha));

			$mes_palabras = $this->getMonthWord($mes);

			//Query compara con un mes después
			$fecha1 = strtotime ( '+1 month' , strtotime ($fecha));
			$fecha1 = date ('Y-m-d',$fecha1);

			$values = array($fecha,
							$fecha1);

			try {
				$consulta->execute($values);
				$consulta_ind->execute($values);

				$row = $consulta->fetch(PDO::FETCH_ASSOC);
				$row_ind = $consulta_ind->fetch(PDO::FETCH_ASSOC);

				$json['table'] .= '<tr>
										<td align="center"><a href="mes.php?mes='.$mes.'&anio='.$ano.'">'.$mes_palabras.' '.$ano.'</a></td>
										<td align="center">$'.number_format($row['MONTO_COLOCADO'] + $row_ind['CRE_MONTO_TOTAL'], 2).'</td>
									</tr>';

			} catch (PDOException $e) {
				die($e->getMessage());
			}

			$fecha = $fecha1;


		} while (date('Y-m', strtotime($fecha)) <= date('Y-m', strtotime($fecha_actual)));	

		$json['table'] .= '</tbody>
								</table>';


		echo json_encode($json);
	}

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

	function getMes() {
		$json = array();
		$json['error'] = false;

		if(isset($_POST['mes']) && isset($_POST['anio'])) {

			$json['mes'] = $this->getMonthWord($_POST['mes']);

			$query_date = $_POST['anio'].'-'.$_POST['mes'].'-01';
			$fecha = date('Y-m-01', strtotime($query_date));

			$fecha1 = strtotime ( '+1 month' , strtotime ($fecha));
			$fecha1 = date ('Y-m-d',$fecha1);


			$db = $this->_conexion;
			$sql = "SELECT GRU_ID,
						   GRU_FECHA_ENTREGA,
						   GRU_MONTO_TOTAL,
						   SIU_NOMBRE
					FROM GRUPOS
					JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					WHERE GRU_FECHA_ENTREGA >= ?
					AND GRU_FECHA_ENTREGA < ?
					UNION
					SELECT CONCAT('IND ', CRE_ID) as GRU_ID,
				    	   CRE_FECHA_ENTREGA as GRU_FECHA_ENTREGA,
				    	   CRE_MONTO_TOTAL as GRU_MONTO_TOTAL,
				    	   SIU_NOMBRE
				    FROM CREDITO_INDIVIDUAL
				    JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
				    WHERE CRE_FECHA_ENTREGA >= ?
					AND CRE_FECHA_ENTREGA < ?
					ORDER BY GRU_FECHA_ENTREGA ASC";
			$values = array($fecha,
							$fecha1);

			$values2 = array($fecha,
							$fecha1,
							$fecha,
							$fecha1);

			$consulta = $db->prepare($sql);

			$sql_total = "SELECT SUM(GRU_MONTO_TOTAL) as MONTO_COLOCADO
						  FROM GRUPOS
						  WHERE GRU_FECHA_ENTREGA >= ?
						  AND GRU_FECHA_ENTREGA < ?";
			$consulta_total = $db->prepare($sql_total);		

			//INDIVIDUALES
			$sql_ind = "SELECT SUM(CRE_MONTO_TOTAL) as CRE_MONTO_TOTAL
						FROM CREDITO_INDIVIDUAL
						WHERE CRE_FECHA_ENTREGA >= ?
						AND CRE_FECHA_ENTREGA < ?";
			$consulta_ind = $db->prepare($sql_ind);		  

			try {
				$consulta->execute($values2);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$consulta_total->execute($values);
				$row_total = $consulta_total->fetch(PDO::FETCH_ASSOC);

				$consulta_ind->execute($values);
				$row_ind = $consulta_ind->fetch(PDO::FETCH_ASSOC);

				$json['table'] = '<table class="table table-striped">
									<thead>
										<tr>
											<th>GRUPO</th>
											<th>FECHA INICIAL</th>
											<th>PROMOTOR</th>
											<th>MONTO COLOCADO</th>
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
										<td align="center">
											<b>$'.number_format($row_total['MONTO_COLOCADO'] + $row_ind['CRE_MONTO_TOTAL'], 2).'</b>
										</td>
									</tr>';
				foreach ($result as $row) {

					$json['table'] .= '<tr>
											<td align="center">'.$row['GRU_ID'].'</td>
											<td align="center">'.date("d/m/Y",strtotime($row['GRU_FECHA_ENTREGA'])).'</td>
											<td align="center">'.$row['SIU_NOMBRE'].'</td>
											<td align="center">$'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
											
										</tr>';

				}

				$json['table'] .= '</tbody>
								</table>';

			} catch (PDOException $e) {
				die($e->getMessage());
			}
					
		}


		/*$sql = "SELECT PER_FECHA as 'Fecha de Elaboración',
				PERSONAS_GRUPOS.GRU_ID as 'No. de Grupo',
				PERSONAS.PER_ID,
				PER_NOMBRE as Nombre,
				PER_FECHA_NAC as 'Fecha de Nacimiento',
				PER_EDO_CIVIL as 'Estado Civil',
				IFE_NUM as 'Clave Elector IFE',
				PER_DIRECCION as 'Dirección',
				PER_NUM as '#',
				PER_COLONIA as 'Colonia',
				PER_MUNICIPIO as 'Municipio',
				PER_ESTADO as 'Estado',
				PER_TELEFONO as 'Teléfono',
				PER_CELULAR as 'Celular',
				MONTO_INDIVIDUAL as 'Monto de Préstamo',
				(PAGO_SEMANAL_IND * GRU_PLAZO) as 'Monto a Pagar',
				GRU_FECHA_ENTREGA as 'Fecha Apertura Crédito',
				GARANTIA_BIEN_1 as 'Garantía',
				SIU_NOMBRE as 'Promotora',
				IF (GRU_RECREDITO = 0, 'Crédito', 'Recrédito') as 'Tipo de Crédito'
				FROM `PERSONAS`
				JOIN PERSONAS_GRUPOS ON PERSONAS_GRUPOS.PER_ID = PERSONAS.PER_ID
				JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
				JOIN SISTEMA_USUARIO on SISTEMA_USUARIO.SIU_ID = GRUPOS.SIU_ID
				UNION
				SELECT PER_FECHA as 'Fecha de Elaboración',
				CONCAT('IND ', CRE_ID) as 'No. de Grupo',
				PERSONAS.PER_ID,
				PER_NOMBRE as Nombre,
				PER_FECHA_NAC as 'Fecha de Nacimiento',
				PER_EDO_CIVIL as 'Estado Civil',
				IFE_NUM as 'Clave Elector IFE',
				PER_DIRECCION as 'Dirección',
				PER_NUM as '#',
				PER_COLONIA as 'Colonia',
				PER_MUNICIPIO as 'Municipio',
				PER_ESTADO as 'Estado',
				PER_TELEFONO as 'Teléfono',
				PER_CELULAR as 'Celular',
				CRE_MONTO_TOTAL as 'Monto de Préstamo',
				(CRE_PAGO_SEMANAL * CRE_PLAZO) as 'Monto a Pagar',
				CRE_FECHA_ENTREGA as 'Fecha Apertura Crédito',
				GARANTIA_BIEN_1 as 'Garantía',
				SIU_NOMBRE as 'Promotora',
				'Individual' as 'Tipo de Crédito'
				FROM PERSONAS
				JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
				JOIN SISTEMA_USUARIO ON SISTEMA_USUARIO.SIU_ID = CREDITO_INDIVIDUAL.SIU_ID
				ORDER BY Nombre ASC";*/

		echo json_encode($json);
	}

}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getColocacion": 
			$libs->getColocacion();
			break;	
		case "getMes": 
			$libs->getMes();
			break;						
	}
}

?>