<?php
require_once("Core.php");

class Login extends Core
{
	/*
	* @author: Alfonso Gómez <hector.gomez@metodika.mx>
	* @version: 0.1 2013-12-26
	* log_in: relaciona un correo electrónico a un password
	* returns: un array en JSON con un valor auth verdadero o falso, msg de éxito o error y credenciales solicitadas
	* Si se da true a $log guarda el usuarioNombre en SESSION
	*/
	public function log_in($log = false) {
		$json = array();
		$json['msg'] = "";
		$json['auth'] = false;

		$credentials = array('SIU_NOMBRE','SIU_ID', 'SUP_ID');

		$cred = isset($_POST['credentials'])?$_POST['credentials']:array();
		$credentials = array_merge($credentials, $cred);
		//array_unique($credentials);

		if (!isset($_POST['email']) || !$this->isEmail($_POST['email'])) {
			$json['msg'] = "Correo electrónico y/o contraseña no válidos.";
		}
		if (!isset($_POST['password']) || empty($_POST['password'])) {
			$json['msg'] = "Correo electrónico y/o contraseña no válidos.";
		}

		if (empty($json['msg'])) {
			$email = $_POST['email'];
			$password = $_POST['password'];
			$result = $this->authUser(array('table'=>'SISTEMA_USUARIO','SIU_EMAIL'=>$email,'SIU_PASSWORD'=>$password),$credentials);
			$json['auth'] = $result['userAuth'];
			$json['credentials'] = isset($result['credentials'])?$result['credentials']:array();
			$json['msg'] = $json['auth']?"Conectado exitosamente.":"Correo electrónico y/o contraseña no válidos.";
			if ($log && $json['auth']) {
				session_start();
				$_SESSION["mp"]["username"] = $json['credentials']['SIU_NOMBRE'];
				$_SESSION["mp"]["userid"] = $json['credentials']['SIU_ID'];
				$_SESSION["mp"]["userprofile"] = $json['credentials']['SUP_ID'];


				/*Agregar Grupos Inactivos*/
				/*$sql = "SELECT GRUPOS.GRU_ID, 
							   GRUPOS.GRU_VIGENTE, 
							   MAX(TP_FECHA) as FECHA
						FROM TABLA_PAGOS
						JOIN GRUPOS ON GRUPOS.GRU_ID = TABLA_PAGOS.GRU_ID
						WHERE GRUPOS.GRU_VIGENTE = 1
						GROUP BY GRUPOS.GRU_ID";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				if ($consulta->rowCount() > 0) {
					foreach ($result as $row) {
						$today = date('d-m-Y', strtotime("+1 week"));
						$fecha = date("Y-m-d",strtotime($row["FECHA"]));
						if($today >= $fecha ) {
							$sql_updt = "UPDATE GRUPOS SET GRU_VIGENTE = 0
									 	 WHERE GRU_ID = ?";
							$values = array($row['GRU_ID']);
							$consulta_updt = $this->_conexion->prepare($sql_updt);
							try {
								$consulta_updt->execute($values);
							} catch(PDOException $e) {
								die($e->getMessage());
							}			 	 
						}
					}
				}	*/

				$sql = "SELECT GRU_ID,
							   GRU_FECHA_FINAL
						FROM GRUPOS
						WHERE GRU_VIGENTE = 1
						AND GRU_FECHA_FINAL < (CURRENT_DATE - INTERVAL 1 WEEK)";
				$consulta = $this->_conexion->prepare($sql);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				if ($consulta->rowCount() > 0) {
					foreach ($result as $row) {
						$sql_updt = "UPDATE GRUPOS SET GRU_VIGENTE = 0
								 	 WHERE GRU_ID = ?";
						$values = array($row['GRU_ID']);
						$consulta_updt = $this->_conexion->prepare($sql_updt);
						try {
							$consulta_updt->execute($values);
						} catch(PDOException $e) {
							die($e->getMessage());
						}
					}
				}

				$sql = "SELECT CRE_ID,
							   CRE_FECHA_FINAL
						FROM CREDITO_INDIVIDUAL
						WHERE CRE_VIGENTE = 1
						AND CRE_FECHA_FINAL < (CURRENT_DATE - INTERVAL 1 WEEK)";
				$consulta = $this->_conexion->prepare($sql);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				if ($consulta->rowCount() > 0) {
					foreach ($result as $row) {
						$sql_updt = "UPDATE CREDITO_INDIVIDUAL SET CRE_VIGENTE = 0
								 	 WHERE CRE_ID = ?";
						$values = array($row['CRE_ID']);
						$consulta_updt = $this->_conexion->prepare($sql_updt);
						try {
							$consulta_updt->execute($values);
						} catch(PDOException $e) {
							die($e->getMessage());
						}
					}
				}		

			}
		}
		
		echo json_encode($json);
	}

	/*
	* @author: Alfonso Gómez <hector.gomez@metodika.mx>
	* @version: 0.1 2013-12-26
	* resetPassword: relaciona un correo electrónico a una cuenta y asigna 1 a recuperar la contraseña
	* returns: un array en JSON con un msg de éxito o error
	*/
	public function resetPassword() {
		$json = array();
		$json['msg'] = "";

		if (!isset($_POST['email']) || !$this->isEmail($_POST['email'])) {
			$json['msg'] = "El e-mail proporcionado no es válido.";
		}

		if (empty($json['msg'])) {
			if ($this->isInDb('SISTEMA_USUARIO','SIU_EMAIL',$_POST['email'])) {
				try{
				
					//Enviar correo.
					require_once("Mail.php");
					
					$new_pass = $this->getRandomWord();

					$body = "La nueva contraseña para ingresar a la Plataforma Financiera es: ".$new_pass." .";


					//Correo para el usuario
					$Mail = new Mail("Cambio de contraseña");
					$Mail->addMail($_POST['email']);
					$Mail->addMail("ca.castilloe@gmail.com");
					$cuerpo_mensaje = "<tr><td>
								<br><br>
								".$body."
							</td></tr>";
					$Mail->content($cuerpo_mensaje);


					//Registra la nueva contraseña en la DB
					try{
						$sql = "SELECT 
								SIU_ID
							FROM SISTEMA_USUARIO
							WHERE SIU_EMAIL = :valor";

						$consulta = $this->_conexion->prepare($sql);
						$consulta->bindParam(':valor', $_POST['email']);
						$consulta->execute();
						$row = $consulta->fetch(PDO::FETCH_ASSOC);

						$sql_updt = "UPDATE SISTEMA_USUARIO SET SIU_PASSWORD = ?
									 WHERE SIU_ID = ?";	
						$pass_encr = $this->encrypt($new_pass);		

						$values = array($pass_encr,
										$row['SIU_ID']);	

						$consulta = $this->_conexion->prepare($sql_updt);

						try {
							$consulta->execute($values);

							//Envía el correo
							$Mail->send();

							if ($consulta->rowCount()) {
								$json['msg'] = "Se ha solicitado el cambio de contraseña. En seguida recibirá un correo electrónico con su nueva contraseña.";
								$json['reset'] = true;

							}else {
								$json['msg'] = "Ya ha solicitado el cambio de contraseña. Espere a que su administrador se la proporcione.";
								$json['reset'] = false;
							}


						} catch(PDOException $e) {
							die($e->getMessage());
						}												

					}catch(PDOException $e){
						die($e->getMessage());
					}

				}catch(PDOException $e) {
					die($e->getMessage());
				}
			}else {
				$json['msg'] = "El e-mail no está registrado.";
				$json['reset'] = false;
			}


		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-26
	 * logout: Destruye la sesión actual y redirecciona al index (pantalla de Iniciar Sesión)
	 */
	public function logout() {
		session_start();
		session_destroy();
		header('Location: ../index.php');
	}

	function getRandomWord($len = 10) {
	    $word = array_merge(range('a', 'z'), range('A', 'Z'));
	    shuffle($word);
	    return substr(implode($word), 0, $len);
	}

}

if (isset($_GET['accion'])) {
	$login = new Login();
	switch ($_GET['accion']) {
		case 'login':
			$login->log_in(true);
			break;
		case 'credentials': 
			$login->log_in();
			break;
		case 'forgot':
			$login->resetPassword();
			break;
		case 'logout':
			$login->logout();
			break;
		default:
			die("Acción no definida");
			break;
	}

}
?>