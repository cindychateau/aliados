<?php

$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
	$ruta .= "../";
}

//Se incluye la clase Common
include_once("Common.php");

class Libs extends Common {

	function getGrupos() {
		$json = array();
		$json['grupos'] = "";

		if (!isset($_SESSION)) {
			@session_start();
		}

		if($_SESSION["mp"]["userprofile"] == 1 || $_SESSION["mp"]["userprofile"] == 2) {

			$sql_total = "SELECT SUM(GRU_MONTO_TOTAL) as GRU_MONTO_TOTAL
						  FROM GRUPOS";
			$consulta_total = $this->_conexion->prepare($sql_total);

			$sql_total2 = "SELECT SUM(CRE_MONTO_TOTAL) as CRE_MONTO_TOTAL
						  FROM CREDITO_INDIVIDUAL";
			$consulta_total2 = $this->_conexion->prepare($sql_total2);			  

			try {
				$consulta_total->execute();
				$consulta_total2->execute();

				$puntero_t = $consulta_total->fetchAll(PDO::FETCH_ASSOC);
				$row_2 = $consulta_total2->fetch(PDO::FETCH_ASSOC);

				if($consulta_total->rowCount()) {
					foreach ($puntero_t as $row_t) {
						$json['grupos'].= '<tr>
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
													<b>$'.number_format($row_t['GRU_MONTO_TOTAL'] + $row_2['CRE_MONTO_TOTAL'], 2).'</b>
												</td>
												<td align="center">
													-
												</td>
											</tr>';	
					}
				}
				
			} catch(PDOException $e) {
				die($e->getMessage());
				$json["error"] = true;
			}



			$sql = "SELECT GRU_ID,
						   GRU_FECHA,
						   GRU_FECHA_ENTREGA,
						   GRUPOS.SIU_ID,
						   SIU_NOMBRE,
						   GRU_MONTO_TOTAL,
						   PAGO_SEMANAL
				    FROM GRUPOS
				    JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
				    UNION
				    SELECT CONCAT('IND ', CRE_ID) as GRU_ID,
				    	   CRE_FECHA as GRU_FECHA,
				    	   CRE_FECHA_ENTREGA as GRU_FECHA_ENTREGA,
				    	   CREDITO_INDIVIDUAL.SIU_ID,
				    	   SIU_NOMBRE,
				    	   CRE_MONTO_TOTAL as GRU_MONTO_TOTAL,
				    	   CRE_PAGO_SEMANAL as PAGO_SEMANAL
				    FROM CREDITO_INDIVIDUAL
				    JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
				    ORDER BY GRU_FECHA DESC";
			$consulta = $this->_conexion->prepare($sql);
			try {
				$consulta->execute();
				$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

				if($consulta->rowCount()) {
					foreach ($puntero as $row) {

						$date = ($row['GRU_FECHA_ENTREGA'] == '0000-00-00' ? date("d/m/Y",strtotime($row['GRU_FECHA'])) : date("d/m/Y",strtotime($row['GRU_FECHA_ENTREGA'])));

						$json['grupos'].= '<tr>
												<td align="center">
													'.$date.'
												</td>
												<td align="center">
													'.$row['GRU_ID'].'
												</td>
												<td align="center">
													'.$row['SIU_NOMBRE'].'
												</td>
												<td align="center">
													$'.number_format($row['GRU_MONTO_TOTAL'], 2).'
												</td>
												<td align="center">
													$'.number_format($row['PAGO_SEMANAL'], 2).'
												</td>
											</tr>';	
					}
				}
				
			} catch(PDOException $e) {
				die($e->getMessage());
				$json["error"] = true;
			}
		}

		echo json_encode($json);
	}

}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getGrupos":
			$libs->getGrupos();
			break;		
	}
}

?>