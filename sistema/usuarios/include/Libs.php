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
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 *  
	 * Imprime la tabla de registros de perfil de usuarios EXCEPTUANDO 'daemon'
	 */
	function printTable() {
		/*
		 * Limites: Se generan los limites para la consulta.
		 */
		$sLimit = "";
		
		if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != "-1") {
			$sLimit = "LIMIT ".intval($_GET['iDisplayStart']).",".intval($_GET['iDisplayLength']);
		}
		
		/*
		 * Ordenación: Se genera la ordenación para la consulta.
		 */
		//Matriz con los Nombres de las columnas disponibles para ordenar. Se permite colocar los Alias de las columnas.
		$aColumns = array(	"SISTEMA_USUARIO.SIU_NOMBRE",
							"SISTEMA_USUARIO.SIU_EMAIL",
							"SISTEMA_USUARIO_PERFIL.SUP_NOMBRE");
		$sOrder = "";
		
		if (isset($_GET['iSortCol_0'])) {
			$sOrder = " ORDER BY  ";
			
			for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
				
				if ($_GET['bSortable_'.intval($_GET['iSortCol_'.$i])] == "true") {
					$sOrder.= " ".$aColumns[intval($_GET['iSortCol_'.$i]) - 1]." ".($_GET['sSortDir_'.$i]==="asc" ? "asc" : "desc") .", ";
				}
			}
			$sOrder = substr_replace($sOrder, "", -2);
			
			if ($sOrder == " ORDER BY") {
				$sOrder = "";
			}
		}

		/*
		 * Búsquedas: Genera el filtro WHERE para la consulta.
		 */
		//Matriz con los Nombres de los campos disponibles para realizar filtros. No se deben colocar los Alias de las columnas.
		$aColumns = array(	"SISTEMA_USUARIO.SIU_NOMBRE",
							"SISTEMA_USUARIO.SIU_EMAIL",
							"SISTEMA_USUARIO_PERFIL.SUP_NOMBRE");
		$sWhere = "";
		
		//Búsquedas en todos los campos.
		if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = " AND (";
			$countWords = 1;
			
			for ($i = 0; $i < count($aColumns); $i++) {
				
				if ($_GET['sSearch'][0] == '"' && substr($_GET['sSearch'], -1) == '"') {
					//Si el texto está encerrado con comillas busca todas las palabras juntas.
					$sWhere .= ($countWords > 1 ? " OR " : "")." ".$aColumns[$i]." LIKE '%".(str_replace('"', "",  $_GET['sSearch']) )."%' ";
				}
				else{
					//Si el texto no está encerrado con comillas busca palabra por palabra.
					$explodeWord = explode(" ", $_GET['sSearch']);
					
					foreach ($explodeWord as $word) {
						
						if ($word != "") {
							$sWhere .= ($countWords > 1 ? " OR " : "")." ".$aColumns[$i]." LIKE '%".( $word )."%' ";
							$countWords++;
						}
					}
				}
				$countWords++;
			}
			$sWhere .= ")";
		}

		//Búsquedas por campo.
		for ($i = 0; $i < count($aColumns); $i++) {
			
			if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '') {
				
				//Filtros Especiales
				if ($i == 4 && $_GET['sSearch_'.$i] != "") {
					
					//Filtro especial utilizado para la columna Sexo
					if($_GET['sSearch_'.$i] != "0"){
						$sWhere .= " AND ".$aColumns[$i]." = '".($_GET['sSearch_'.$i])."' ";
					}
				}
				//Filtros NO Especiales
				else {
					if ($_GET['sSearch_'.$i][0] == '"' && substr($_GET['sSearch_'.$i], -1) == '"') {
						//Si el texto está encerrado con comillas busca todas las palabras juntas.
						$sWhere .= " AND ".$aColumns[$i]." LIKE '%".(str_replace('"', "", $_GET['sSearch_'.$i]))."%' ";
					}
					else {
						$explodeWord = explode(" ", $_GET['sSearch_'.$i]);
						
						foreach ($explodeWord as $word) {
							
							if ($word != "") {
								$sWhere .= " AND ".$aColumns[$i]." LIKE '%".($word)."%' ";
							}
						}
					}
				}
			}
		}

		/*
		 * Query principal
		 */
		$sqlQuery = "SELECT
						SISTEMA_USUARIO.SIU_ID,
						SISTEMA_USUARIO.SIU_NOMBRE as nombre,
						SISTEMA_USUARIO.SIU_EMAIL,
						SISTEMA_USUARIO_PERFIL.SUP_NOMBRE
					FROM SISTEMA_USUARIO
					LEFT JOIN SISTEMA_USUARIO_PERFIL ON SISTEMA_USUARIO_PERFIL.SUP_ID = SISTEMA_USUARIO.SUP_ID
					WHERE SISTEMA_USUARIO_PERFIL.SUP_ID <> '1'
						AND SISTEMA_USUARIO.SIU_RECUPERACION = '0'";
		$sqlQueryFiltered = $sqlQuery." ".$sWhere." ".$sOrder." ".$sLimit;
		
		//Se prepara la consulta de extración de datos
		$sqlFinalCounter = $this->_conexion->prepare($sqlQuery);
		$consulta = $this->_conexion->prepare($sqlQueryFiltered);

		//echo $sqlQueryFiltered;

		//Se ejecuta la consulta
		try {
			$sqlFinalCounter->execute();
			$finalCounter = $sqlFinalCounter->rowCount();
			
			$consulta->execute();
			
			//Se imprime la tabla
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			
			/* Se calcula la cantidad de registros */
			$iFilteredTotal = $consulta->rowCount();
			
			/*
			* Salida de Datos
			*/
			$output = array(
					"sEcho" => intval($_GET['sEcho']),
					"iTotalRecords" => $iFilteredTotal,
					"iTotalDisplayRecords" => $finalCounter,
					"aaData" => array()
					);
			$counter = 0;
			
			foreach ($puntero as $row) {
				$counter++;
				$aRow = array();
				$aRow[] = $counter;
				$aRow[] = $row["nombre"];
				$aRow[] = $row["SIU_EMAIL"];
				$aRow[] = $row["SUP_NOMBRE"];


				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['SIU_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(2, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['SIU_ID'],
										"extras"	=>	"data-name='".$row["nombre"]."'");
				$btn_borrar = $this->printButton(2, "baja", $params_borrar);

				$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}
			echo json_encode($output);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Metodo que borra una fila de la BD
	 */
	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM SISTEMA_USUARIO WHERE SIU_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Usuario fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Usuario elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-13
	 * 
	 * @param '$id'		int. 	ID de perfil de usuario
	 * 
	 * Metodo que imprime la tabla de permisos de un perfil de usuario en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		$json['nombre'] = "";
		$json['apellido'] = "";
		$json['email'] = "";
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT 
						SIU_NOMBRE,
						SIU_EMAIL, 
						SUP_ID,
						SIU_DIRECCION
					FROM SISTEMA_USUARIO
					WHERE SIU_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row = $result[0];

					$json['nombre'] = $row['SIU_NOMBRE'];
					$json['email'] = $row['SIU_EMAIL'];
					$json['profile'] = $row['SUP_ID'];
					$json['direccion'] = $row['SIU_DIRECCION'];
						
				} else {
					$json['error'] = true;
				}

			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2014-01-02
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Metodo que regresa un select con las máquinas existentes
	 */
	function showProfiles() {
		$json = array();
		$json["error"] = false;
		$json["select"] = '<select id="perfil" name="perfil" class="form-control">';
		$consulta = $this->_conexion->prepare("SELECT SUP_ID, SUP_NOMBRE FROM SISTEMA_USUARIO_PERFIL WHERE SUP_ID <> '1'");
		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['SUP_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SUP_ID'] ? 'selected' : '' : '').' >'.$row['SUP_NOMBRE'].'</option>';
			}
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}
		$json["select"] .= '</select>';
		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 * 
	 * 
	 * Guarda el perfil de un usuario
	 */
	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";
		$error_msg = NULL;

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if( ($clave == 'pswd' | $clave == 'conf-pswd') && isset($_POST['id'])) {
					//NO HAY ERROR
				} else if($this->is_empty(trim($valor)) && $clave != "direccion") {
					$json["error"] = true;
					$json["focus"] = $clave;	
				} else if($clave == "email"  && !$this->isEmail($valor)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json["msg"] = "E-mail inválido. Favor de ingresarlo nuevamente.";
				}
			}
		}

		/*Valida que el correo no esté ingresado*/
		//Checks email
		$sql = "SELECT SIU_ID FROM SISTEMA_USUARIO WHERE SIU_EMAIL = ? ";
		$params = array($_POST['email']);
		$consulta = $this->_conexion->prepare($sql);
		$consulta->execute($params);
		$resultMail = $consulta->fetchAll(PDO::FETCH_ASSOC);

		if(!$json["error"] && $consulta->rowCount() > 0 && !isset($_POST['id'])){
			$json["error"] = true;
			$json["focus"] = "mail";
			$error_msg = 1;
		}

		/*Valida contraseña*/
		//Se valida la estructura de la contraseña
		if (!$json["error"] && strlen($_POST['pswd']) > 6) {
			if(!$json["error"] && isset($_POST['pswd']) && (!$this->is_empty(trim($_POST['pswd'])) | !$this->is_empty(trim($_POST['conf-pswd'])) ) ){
				$foundNumber = false;
				$foundChars = false;
				$numbers = "1234567890";
				$chars = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
				$password = $_POST['pswd'];
			
				for($i = 0; $i < strlen($password); $i++){
					
					if(!$foundNumber && strpos($numbers, $password[$i]) !== false){
						$foundNumber = true;
					}
				}
				
				for($i = 0; $i < strlen($password); $i++){
					
					if(!$foundChars && strpos($chars, $password[$i]) !== false){
						$foundChars = true;
					}
				}
				
				if(!$foundNumber){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos un número (0-9)";
					$json["focus"] = "pswd";
				}
				elseif(!$foundChars){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos una letra mayúscula (A-Z)";
					$json["focus"] = "pswd";
				}
				elseif($password != $_POST['conf-pswd']){
					$json["msg"] = "Las contraseñas no coinciden";
					$json["error"] = true;
					$json["focus"] = "pswd";
				}
			}
		} else {
				//$json["error"] = true;

				if($error_msg == 1){
					$json["error"] = true;
					$json["msg"] = "El correo electrónico ingresado ya ha sido registrado anteriormente.";
					$json["focus"] = "email";
				}else if (strlen($_POST['pswd'])<=6 && !isset($_POST['id'])){
					$json["error"] = true;
					$json["msg"] = "La contraseña debe contener al menos siete caracteres.";
					$json["focus"] = "pswd";
				}
		
		}

		if(!$json["error"]) {
			if(isset($_POST['id'])) { //UPDATE
				//Si tiene password
				if(!$this->is_empty(trim($_POST['pswd']))) {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE = ?,
															SIU_EMAIL = ?,
															SIU_PASSWORD = ?,
															SUP_ID = ?,
															SIU_DIRECCION = ?
														WHERE SIU_ID = ?";

					$pass_encr = $this->encrypt($_POST['pswd']);

					$values = array($_POST["nombre"],
									$_POST["email"],
									$pass_encr,
									$_POST["perfil"],
									$_POST['direccion'],
									$_POST['id']);							
				} else {
					$sql_user = "UPDATE SISTEMA_USUARIO SET SIU_NOMBRE = ?,
															SIU_EMAIL = ?,
															SUP_ID = ?,
															SIU_DIRECCION = ?
														WHERE SIU_ID = ?";

					$values = array($_POST["nombre"],
									$_POST["email"],
									$_POST["perfil"],
									$_POST['direccion'],
									$_POST['id']);
				}

			} else { //INSERCION
				$sql_user = "INSERT INTO SISTEMA_USUARIO (SIU_NOMBRE,
													SIU_EMAIL,
													SIU_PASSWORD,
													SUP_ID,
													SIU_DIRECCION) 
												VALUES( ?, ?, ?, ?, ? )";

				$pass_encr = $this->encrypt($_POST['pswd']);

				$values = array($_POST["nombre"],
								$_POST["email"],
								$pass_encr,
								$_POST["perfil"],
								$_POST['direccion']);
			}

			$consulta = $this->_conexion->prepare($sql_user);

			try {
				$consulta->execute($values);
				$json["valid"] = true;

				//Manda mail si se registra
				if(!isset($_POST['id'])) {
					$json["msg"] = "El Usuario registrado con éxito. En breve recibirá un correo electrónico de confirmación";
					//Extrae las Variables de Sistema (Título y Cuerpo del correo de confirmación)
					$sql = "SELECT * 
							FROM SISTEMA_VARIABLE 
							WHERE SIV_VARIABLE LIKE '%nuevo usuario email cuerpo%' 
							OR SIV_VARIABLE LIKE '%nuevo usuario email titulo%'
							OR SIV_VARIABLE LIKE '%baseurl%'
							ORDER BY SIV_ID";

					$consulta = $this->_conexion->prepare($sql);
					$consulta->bindParam(':valor', $_POST['id']);
					$consulta->execute();
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

					$row_url = $result[0];
					$row_body = $result[1];
					$row_title = $result[2];

					require_once($ruta."include/Mail.php");

					$changes = array("[baseurl]", "[email]", "[password]");
					$new   = array($row_url['SIV_VALOR'], $_POST['email'], $_POST['pswd']);

					$body = str_replace($changes, $new, $row_body['SIV_VALOR']);


					//Correo para el usuario
					$Mail = new Mail($row_title["SIV_VALOR"]);
					$Mail->addMail($_POST['email'], $_POST['nombre']);
					$cuerpo_mensaje = "<tr><td>
								<br><br>
								".$body."
							</td></tr>";
					$Mail->content($cuerpo_mensaje);
					
					//Envía el correo
					$Mail->send();	
				} else {
					$json["msg"] = "Los cambios del Usuario han sido realizados exitosamente.";
				}

			} catch(PDOException $e) {
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

		echo json_encode($json);
	}

	function countPassword() {
		$json = array();
		$json['num'] = 0;

		$sqlQuery = "SELECT
						SISTEMA_USUARIO.SIU_ID,
						SISTEMA_USUARIO.SIU_NOMBRE as nombre,
						SISTEMA_USUARIO.SIU_EMAIL,
						SISTEMA_USUARIO_PERFIL.SUP_NOMBRE
					FROM SISTEMA_USUARIO
					LEFT JOIN SISTEMA_USUARIO_PERFIL ON SISTEMA_USUARIO_PERFIL.SUP_ID = SISTEMA_USUARIO.SUP_ID
					WHERE SISTEMA_USUARIO_PERFIL.SUP_ID <> '1'
						AND SISTEMA_USUARIO.SIU_RECUPERACION = '1'";
		
		//Se prepara la consulta de extración de datos
		$sqlFinalCounter = $this->_conexion->prepare($sqlQuery);

		//Se ejecuta la consulta
		$sqlFinalCounter->execute();
		$json['num'] = $sqlFinalCounter->rowCount();

		echo json_encode($json);
	}
	
	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "printTable":
			$libs->printTable();
			break;
		case "printTablePass":
			$libs->printTablePass();
			break;	
		case "deleteRecord":
			$libs->deleteRecord();
			break;
		case "showRecord":
			$libs->showRecord();
			break;
		case "showProfiles":
			$libs->showProfiles();
			break;	
		case "saveRecord":
			$libs->saveRecord();
			break;
		case "newPassword":
			$libs->newPassword();
			break;
		case "countPassword":		
			$libs->countPassword();
			break;
		case "noticiaVista":		
			$libs->noticiaVista();
			break;		
	}
}

?>