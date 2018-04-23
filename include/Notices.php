<?php
require_once("Core.php");

class Notices extends Core
{
	public function noticiaVista() {
		if(!isset($_SESSION)){
			@session_start();
		}
		$json = array();
		$json['msg'] = "no hizo null";
		try {
			$query = "SELECT * FROM SISTEMA_NOTIFICACIONES WHERE SIN_ESTADO = 0 AND SIN_LIGA = :valor";//query para user
			$consulta = $this->_conexion->prepare($query);
			$consulta->bindParam(":valor",$_POST['liga']);
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($puntero as $row) {
				$usuarios = $row['SIN_USUARIOS'];
				$usuarios = explode("|", $usuarios);

				if(($key = array_search($_SESSION["mp"]["userid"], $usuarios)) !== false) {
				    unset($usuarios[$key]);
				}

				$usuarios_str = implode("|", $usuarios);
				$estado = 1;
				if(strlen($usuarios_str) > 0) {
					$estado = 0;
				}
				$query = "UPDATE SISTEMA_NOTIFICACIONES SET SIN_USUARIOS = :usuarios, 
															SIN_ESTADO = :estado
						  WHERE SIN_ID = :valor";
				$consulta = $this->_conexion->prepare($query);
				$consulta->bindParam(":usuarios",$usuarios_str);
				$consulta->bindParam(":estado",$estado);
				$consulta->bindParam(":valor",$row['SIN_ID']);
				$consulta->execute();
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
		}
		echo json_encode($json);
	}
	/*
	public function noticiaVista() {
		$json = array();
		$json['msg'] = "";
		try {
			$query = "UPDATE SISTEMA_MENSAJE SET SIM_ESTADO = 1 WHERE SIM_TIPO = :valor";
			$consulta = $this->_conexion->prepare($query);
				$consulta->bindParam(":valor",$_POST['id_t']);
				$consulta->execute();
				$json['msg'] = "Se realizo";
				
		} catch(PDOException $e) {
			die($e->getMessage());
		}		
		echo json_encode($json);
	}*/
}
if (isset($_GET['accion'])) {
	$notices = new Notices();
	switch ($_GET['accion']) {
		case 'vista':
			$notices->noticiaVista();
			break;
		default:
			die("Acción no definida");
			break;
	}
}
?>