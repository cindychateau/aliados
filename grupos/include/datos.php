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
					   GRU_COMISION_P
				FROM GRUPOS
				WHERE GRU_ID = :valor";
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
					   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
					   PER_DIRECCION
				FROM PERSONAS
				JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
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
				FROM TABLA_PAGOS
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