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
		$sql_t_clientes = "SELECT COUNT(PER_ID) AS clientes
						   FROM PERSONAS
						   WHERE STATUS != 2
						   AND STATUS != -1";
		$consulta_t_clientes = $this->_conexion->prepare($sql_t_clientes);

		try {
			$consulta_t_clientes->execute();
			$row_t_clientes = $consulta_t_clientes->fetch(PDO::FETCH_ASSOC);
			$json['total_clientes'] = $row_t_clientes['clientes'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}				   
		/*END: TOTAL DE CLIENTES*/

		/*BG: TOTAL DE CLIENTES ACTIVOS*/
		$sql_clientes = "SELECT COUNT(PERSONAS_GRUPOS.PER_ID) as clientes
						 FROM PERSONAS_GRUPOS
						 JOIN GRUPOS ON PERSONAS_GRUPOS.GRU_ID = GRUPOS.GRU_ID
						 WHERE GRU_VIGENTE = 1";
		$consulta_clientes = $this->_conexion->prepare($sql_clientes);

		try {
			$consulta_clientes->execute();
			$row_clientes = $consulta_clientes->fetch(PDO::FETCH_ASSOC);
			$json['clientes_activos'] = $row_clientes['clientes'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE CLIENTES ACTIVOS*/

		/*BG: TOTAL DE GRUPOS*/
		$sql_t_grupos = "SELECT COUNT(GRU_ID) AS grupos
					     FROM GRUPOS";
		$consulta_t_grupos = $this->_conexion->prepare($sql_t_grupos);

		try {
			$consulta_t_grupos->execute();
			$row_t_grupos = $consulta_t_grupos->fetch(PDO::FETCH_ASSOC);
			$json['total_grupos'] = $row_t_grupos['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE GRUPOS*/

		/*BG: TOTAL DE GRUPOS ACTIVOS*/
		$sql_grupos = "SELECT COUNT(GRU_ID) AS grupos
					   FROM GRUPOS
					   WHERE GRU_VIGENTE = 1";
		$consulta_grupos = $this->_conexion->prepare($sql_grupos);

		try {
			$consulta_grupos->execute();
			$row_grupos = $consulta_grupos->fetch(PDO::FETCH_ASSOC);
			$json['grupos_activos'] = $row_grupos['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: TOTAL DE GRUPOS ACTIVOS*/

		/*BG: PRÉSTAMOS PENDIENTES DE ENTREGAR*/
		$sql_grupos_pendientes = "SELECT COUNT(GRU_ID) AS grupos
								  FROM GRUPOS
								  WHERE DATE(GRU_FECHA_ENTREGA) > CURDATE() ";
		$consulta_grupos_pendientes = $this->_conexion->prepare($sql_grupos_pendientes);

		try {
			$consulta_grupos_pendientes->execute();
			$row_grupos_pendientes = $consulta_grupos_pendientes->fetch(PDO::FETCH_ASSOC);
			$json['pendientes_entregar'] = $row_grupos_pendientes['grupos'];
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: PRÉSTAMOS PENDIENTES DE ENTREGAR*/

		/*BG: SALDO BRUTO*/
		$sql_saldo_bruto = "SELECT SUM(GRU_MONTO_TOTAL_ENTREGAR) AS saldo
					      	FROM GRUPOS";
		$consulta_saldo_bruto = $this->_conexion->prepare($sql_saldo_bruto);

		try {
			$consulta_saldo_bruto->execute();
			$row_saldo_bruto = $consulta_saldo_bruto->fetch(PDO::FETCH_ASSOC);
			$json['saldo_bruto'] = number_format($row_saldo_bruto['saldo']);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: SALDO BRUTO*/

		/*BG: SALDO PROMEDIO*/
		$sql_promedio = "SELECT AVG(MONTO_INDIVIDUAL) AS promedio
					     FROM PERSONAS_GRUPOS";
		$consulta_promedio = $this->_conexion->prepare($sql_promedio);

		try {
			$consulta_promedio->execute();
			$row_promedio = $consulta_promedio->fetch(PDO::FETCH_ASSOC);
			$json['saldo_promedio'] = number_format($row_promedio['promedio']);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: SALDO PROMEDIO*/

		/*BG: GANANCIAS POR INTERES*/
		$sql_ganancias = "SELECT SUM(PAGO_INTERES*GRU_PLAZO) AS ganancias
					      FROM GRUPOS";
		$consulta_ganancias = $this->_conexion->prepare($sql_ganancias);

		try {
			$consulta_ganancias->execute();
			$row_ganancias = $consulta_ganancias->fetch(PDO::FETCH_ASSOC);
			$json['ganancias'] = number_format($row_ganancias['ganancias']);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: GANANCIAS POR INTERES*/

		/*BG: CARTERA HISTORICA*/
		$sql_cartera_historica = "SELECT SUM(GRU_MONTO_TOTAL) AS cartera
					      	   FROM GRUPOS";
		$consulta_cartera_historica = $this->_conexion->prepare($sql_cartera_historica);

		try {
			$consulta_cartera_historica->execute();
			$row_cartera_historica = $consulta_cartera_historica->fetch(PDO::FETCH_ASSOC);
			$json['cartera_historica'] = number_format($row_cartera_historica['cartera']);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: CARTERA HISTORICA*/

		/*BG: CARTERA ACTIVA*/
		$sql_cartera_activa = "SELECT SUM(GRU_MONTO_TOTAL) AS cartera
					      	   	  FROM GRUPOS
					      	   	  WHERE GRU_VIGENTE = 1";
		$consulta_cartera_activa = $this->_conexion->prepare($sql_cartera_activa);

		try {
			$consulta_cartera_activa->execute();
			$row_cartera_activa = $consulta_cartera_activa->fetch(PDO::FETCH_ASSOC);
			$json['cartera_activa'] = number_format($row_cartera_activa['cartera']);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
		/*END: CARTERA HISTORICA*/

		/*BG: RIESGO > 7 DIAS*/
		$fecha = date("Y-m-d");

		$fecha = strtotime ( '-2 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$fin_str = strtotime('next sunday', $inicio_str);
		$fecha_fin = date("Y-m-d", $fin_str);

		$sql_7_dias = "SELECT SUM(PI_PENDIENTE) as pendiente
					   FROM PAGOS_INDIVIDUALES
					   WHERE PI_FECHA >= ?
					   AND PI_FECHA <= ?";
		$values_7_dias = array($fecha_inicio,
								$fecha_fin);	
		$consulta_7_dias = $this->_conexion->prepare($sql_7_dias);	
		try {
			$consulta_7_dias->execute($values_7_dias);
			$row_7_dias = $consulta_7_dias->fetch(PDO::FETCH_ASSOC);
			$json['riesgo_7'] = number_format($row_7_dias['pendiente']);		
			$json['inicio_7'] = $fecha_inicio;
			$json['fin_7'] = $fecha_fin;
			$json['fecha_7'] = 	date("d/m/Y", $inicio_str) ." - ". date("d/m/Y", $fin_str);	
			$json['per_7'] = number_format(100/$row_cartera_historica['cartera']*$row_7_dias['pendiente'], 2);   	
	   	} catch (PDOException $e) {
	   		die($e->getMessage());
	   	}							   
		/*END: RIESGO > 7 DIAS*/

		/*BG: RIESGO > 15 DIAS*/
		$fecha = date("Y-m-d");

		$fecha = strtotime ( '-4 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		/*$fin_str = strtotime('next sunday', $inicio_str);
		$fin_str = strtotime('next sunday', $fin_str);
		$fecha_fin = date("Y-m-d", $fin_str);*/

		$sql_15_dias = "SELECT SUM(PI_PENDIENTE) as pendiente
					   FROM PAGOS_INDIVIDUALES
					   WHERE PI_FECHA >= ?
					   AND PI_FECHA <= ?";
		$values_15_dias = array($fecha_inicio,
								$fecha_fin);	
		$consulta_15_dias = $this->_conexion->prepare($sql_15_dias);	
		try {
			$consulta_15_dias->execute($values_15_dias);
			$row_15_dias = $consulta_15_dias->fetch(PDO::FETCH_ASSOC);
			$json['riesgo_15'] = number_format($row_15_dias['pendiente']);		
			$json['inicio_15'] = $fecha_inicio;
			$json['fin_15'] = $fecha_fin;	
			$json['fecha_15'] = 	date("d/m/Y", $inicio_str) ." - ". date("d/m/Y", $fin_str);	
			$json['per_15'] = number_format(100/$row_cartera_historica['cartera']*$row_15_dias['pendiente'], 2);	   	
	   	} catch (PDOException $e) {
	   		die($e->getMessage());
	   	}							   
		/*END: RIESGO > 15 DIAS*/

		/*BG: RIESGO > 30 DIAS*/
		$fecha = date("Y-m-d");

		$fecha = strtotime ( '-8 week' , strtotime ( $fecha ) ) ;
		$fecha = date ( 'Y-m-j' , $fecha );
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		/*$fin_str = strtotime('next sunday', $inicio_str);
		$fin_str = strtotime('next sunday', $fin_str);
		$fin_str = strtotime('next sunday', $fin_str);
		$fin_str = strtotime('next sunday', $fin_str);
		$fecha_fin = date("Y-m-d", $fin_str);*/

		$sql_30_dias = "SELECT SUM(PI_PENDIENTE) as pendiente
					   FROM PAGOS_INDIVIDUALES
					   WHERE PI_FECHA >= ?
					   AND PI_FECHA <= ?";
		$values_30_dias = array($fecha_inicio,
								$fecha_fin);	
		$consulta_30_dias = $this->_conexion->prepare($sql_30_dias);	
		try {
			$consulta_30_dias->execute($values_30_dias);
			$row_30_dias = $consulta_30_dias->fetch(PDO::FETCH_ASSOC);
			$json['riesgo_30'] = number_format($row_30_dias['pendiente']);		
			$json['inicio_30'] = $fecha_inicio;
			$json['fin_30'] = $fecha_fin;	
			$json['fecha_30'] = date("d/m/Y", $inicio_str) ." - ". date("d/m/Y", $fin_str);	
			$json['per_30'] = number_format(100/$row_cartera_historica['cartera']*$row_30_dias['pendiente'], 2);		   	
	   	} catch (PDOException $e) {
	   		die($e->getMessage());
	   	}							   
		/*END: RIESGO > 30 DIAS*/


		/*BG: RIESGO > 45 DIAS*/
		$sql_90_dias = "SELECT SUM(PI_PENDIENTE) as pendiente
					   FROM PAGOS_INDIVIDUALES
					   WHERE PI_FECHA >= ?
					   AND PI_FECHA < ?";
		$values_90_dias = array($fecha_inicio,
								$fecha_fin);	
		$consulta_90_dias = $this->_conexion->prepare($sql_90_dias);	
		try {
			$consulta_90_dias->execute($values_90_dias);
			$row_90_dias = $consulta_90_dias->fetch(PDO::FETCH_ASSOC);
			$json['riesgo_90'] = number_format($row_90_dias['pendiente']);	
			$json['fin_90'] = $fecha_fin;	
			$json['fecha_90'] = ">".date("d/m/Y", $inicio_str);		
			$json['per_90'] = number_format(100/$row_cartera_historica['cartera']*$row_90_dias['pendiente'], 2);	   	
	   	} catch (PDOException $e) {
	   		die($e->getMessage());
	   	}							   
		/*END: RIESGO > 90 DIAS*/


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
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getMonth":
			$libs->getMonth();
			break;			
		case "getWeek":
			$libs->getWeek();
			break;
		case "getFlujo":
			$libs->getFlujo();
			break;
		case "getInfo":
			$libs->getInfo();
			break;	
	}
}

?>