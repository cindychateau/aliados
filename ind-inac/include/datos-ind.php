<?php

include_once($ruta."include/Common.php");

class Datos extends Common {

	function getGroup($id) {
		$db = $this->_conexion;
		$sql = "SELECT CRE_ID, 
					   CRE_DOMICILIO,
					   CRE_FECHA_INICIAL,
					   CRE_MONTO_TOTAL,
					   CRE_PLAZO,
					   CRE_TASA,
					   CRE_COMISION_P,
					   SIU_NOMBRE,
					   SIU_DIRECCION
				FROM CREDITO_INDIVIDUAL
				JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
				WHERE CREDITO_INDIVIDUAL.CRE_ID = :valor";
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
					   IFE_NUM
				FROM PERSONAS
				JOIN CREDITO_INDIVIDUAL ON PERSONAS.PER_ID = CREDITO_INDIVIDUAL.PER_ID
				WHERE CRE_ID = :valor";
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
				FROM TABLA_PAGOS_IND
				WHERE CRE_ID = :valor";
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