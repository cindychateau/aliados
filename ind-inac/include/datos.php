<?php

include_once($ruta."include/Common.php");

class Datos extends Common {

	function getGroup($id) {
		$db = $this->_conexion;
		$sql = "SELECT GRU_FECHA,
					   GRU_FECHA_ENTREGA, 
					   GRU_DOMICILIO,
					   GRU_FECHA_INICIAL,
					   GRU_MONTO_TOTAL,
					   GRU_PLAZO,
					   GRU_TASA,
					   GRU_COMISION_P,
					   GRU_RECREDITO,
					   SIU_NOMBRE,
					   SIU_DIRECCION
				FROM GRUPOS
				JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
				WHERE GRUPOS.GRU_ID = :valor";
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
					   PER_DIRECCION,
					   PER_NUM,
					   PER_COLONIA,
					   PER_MUNICIPIO,
					   PER_ESTADO,
					   PER_CP,
					   IFE_NUM,
					   MONTO_INDIVIDUAL,
					   PAGO_SEMANAL_IND
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