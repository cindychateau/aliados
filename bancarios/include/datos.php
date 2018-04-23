<?php

include_once($ruta."include/Common.php");

class Datos extends Common {

	function getGroup($id) {
		$db = $this->_conexion;
		$sql = "SELECT GRU_FECHA, 
					   GRU_DOMICILIO,
					   GRU_FECHA_INICIAL,
					   GRU_MONTO_TOTAL,
					   GRU_PLAZO,
					   GRU_TASA,
					   GRU_COMISION_P,
					   GRU_RECREDITO,
					   SIU_NOMBRE,
					   SIU_DIRECCION
				FROM GRUPOS_B
				JOIN SISTEMA_USUARIO ON GRUPOS_B.SIU_ID = SISTEMA_USUARIO.SIU_ID
				WHERE GRUPOS_B.GRU_ID = :valor";
		$consulta = $db->prepare($sql);
		$consulta->bindParam(':valor', $id);

		try {
			$consulta->execute();
			$row = $consulta->fetch(PDO::FETCH_ASSOC);
			return $row;			
		} catch (PDOException $e) {
			die($e->getMessage());				
		}	
	}

	function getPersons($id_group) {
		$db = $this->_conexion;
		$sql = "SELECT PERSONAS.PER_ID, 
					   PER_NOMBRE,
					   PER_DIRECCION,
					   PER_NUM,
					   PER_COLONIA,
					   PER_MUNICIPIO,
					   PER_ESTADO,
					   PER_CP,
					   MONTO_INDIVIDUAL
				FROM PERSONAS
				JOIN PERSONAS_GRUPOS_B ON PERSONAS.PER_ID = PERSONAS_GRUPOS_B.PER_ID
				WHERE GRU_ID = :valor";
		$consulta = $db->prepare($sql);
		$consulta->bindParam(':valor', $id_group);

		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			return $result;			
		} catch (PDOException $e) {
			die($e->getMessage());				
		}
	}

	function getPagos($id) {
		$db = $this->_conexion;
		$sql = "SELECT *
				FROM TABLA_PAGOS_B
				WHERE GRU_ID = :valor";
		$consulta = $db->prepare($sql);
		$consulta->bindParam(':valor', $id);

		try {
			$consulta->execute();
			$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			return $result;			
		} catch (PDOException $e) {
			die($e->getMessage());				
		}	
	}
}

?>