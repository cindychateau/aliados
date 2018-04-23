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
	}
}

?>