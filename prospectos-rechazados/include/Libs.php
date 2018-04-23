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
		$aColumns = array(	"PERSONAS.PER_NOMBRE",
							"PERSONAS.PER_EMAIL",
							"",
							"PERSONAS.MONTO_SOLICITADO");
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
							"SISTEMA_USUARIO.SIU_EMAIL");
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
						PERSONAS.PER_ID,
						CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as nombre,
						PERSONAS.PER_TELEFONO,
						PERSONAS.PER_DIRECCION,
						PERSONAS.MONTO_SOLICITADO,
						PERSONAS.RAZON_RECHAZO
					FROM PERSONAS
					WHERE PERSONAS.STATUS = '2'";
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
				$aRow[] = $row["PER_DIRECCION"];
				$aRow[] = $row["PER_TELEFONO"];
				$aRow[] = $row["MONTO_SOLICITADO"];
				$aRow[] = $row["RAZON_RECHAZO"];


				//Botones
				$params_editar = array(	"link"		=>	"../prospectos/cambios.php?id=".$row['PER_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(8, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['PER_ID'],
										"extras"	=>	"data-name='".$row["nombre"]."'");
				$btn_borrar = $this->printButton(8, "baja", $params_borrar);

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
	 * @author: Cynthia Castillo 
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
				$consulta = $this->_conexion->prepare("UPDATE PERSONAS SET(STATUS = -1) WHERE PER_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Prospecto fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Prospecto elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
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
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
						FROM PERSONAS
						WHERE PER_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$result['PER_FECHA'] = date("d/m/Y",strtotime($result['PER_FECHA']));
					$json['prospecto'] = $result;
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
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2013-12-27
	 * 
	 * 
	 * Guarda el perfil de un usuario
	 */
	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Los campos con asterisco son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("fecha",
							  "nombre",
							  "direccion",
							  "telefono",
							  "antiguedad",
							  "ingreso_promedio",
							  "monto_solicitado",
							  "proyecto_inversion");

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]) {
				if($this->is_empty($valor) && in_array($clave, $obligatorios)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json["valor"] = $valor;
				} else if( ($clave == "ingreso_promedio" || $clave == "monto_solicitado") && !is_numeric($valor) && !$this->is_empty($valor)) {
					$json['error'] = true;
					$json["focus"] = $clave;
					$json["valor"] = $valor;
					$json['msg'] = "Favor de ingresar una cifra válida.";
				} else if($clave == "email" && !$this->isEmail($valor) && !$this->is_empty($valor)) {
					$json['error'] = true;
					$json["focus"] = $clave;
					$json["valor"] = $valor;
					$json['msg'] = "Favor de ingresar un correo electrónico válido.";
				}	
			}
		}

		//Verifica que si hicieron check a la casilla de que vive con otro, especifique con quién
		//Y si quieren rechazar desde un principio, que den la razón de rechazo
		if(!$json['error']) {
			if(isset($_POST['check_vive_otro']) && $this->is_empty($_POST['vive_otros'])) {
				$json['error'] = true;
				$json["focus"] = "vive_otros";
				$json["valor"] = $_POST['vive_otros'];
				$json['msg'] = "Favor de especificar 'Otro'.";
			}

			if(isset($_POST['rechazar']) && $this->is_empty($_POST['razon_rechazo'])) {
				$json['error'] = true;
				$json["focus"] = "razon_rechazo";
				$json["valor"] = $_POST['razon_rechazo'];
				$json['msg'] = "Favor de especificar la razón de rechazo.";
			}
		}


		if(!$json['error']) {
			/*Verifica con quién vive*/
			$vive_padres = 0;
			$vive_conyugue = 0;
			$vive_hijos = 0;
			$vive_hermanos = 0;
			$vive_otros = "";

			if(isset($_POST['vive_padres'])) {
				$vive_padres = 1;
			}

			if(isset($_POST['vive_conyugue'])) {
				$vive_conyugue = 1;
			}

			if(isset($_POST['vive_hijos'])) {
				$vive_hijos = 1;
			}

			if(isset($_POST['vive_hermanos'])) {
				$vive_hermanos = 1;
			}

			if(isset($_POST['check_vive_otro'])) {
				$vive_otros = $_POST['vive_otros'];
			}


			/*Verifica dependientes económicos*/
			$depende_padres = 0;
			$depende_conyugue = 0;
			$depende_hijos = 0;
			$depende_hermanos = 0;
			$depende_otros = 0;

			if(isset($_POST['depende_padres'])) {
				$depende_padres = 1;
			}

			if(isset($_POST['depende_conyugue'])) {
				$depende_conyugue = 1;
			}

			if(isset($_POST['depende_hijos'])) {
				$depende_hijos = 1;
			}

			if(isset($_POST['depende_hermanos'])) {
				$depende_hermanos = 1;
			}

			if(isset($_POST['depende_otros'])) {
				$depende_otros = 1;
			}

			/*Verifica si tiene otra actividad económica*/
			if($_POST['actividades'] == 0 && $this->is_empty($_POST['act_otro'])) {
				$json['error'] = true;
				$json["focus"] = "act_otro";
				$json["valor"] = $_POST['act_otro'];
				$json['msg'] = "Favor de especificar la Actividad Económica.";
			}

			/*Verifica su vivienda*/
			$vivienda = 1;
			if($_POST['vivienda'] == "Rentada") {
				$vivienda = 0;
			}

			/*Verifica si tiene otros Préstamos*/
			if(!$this->is_empty($_POST['prestamo_otro_1']) && 
			  ($this->is_empty($_POST['prestamos_pago_1']) || 
			  !is_numeric($_POST['prestamos_pago_1']))) {
				$json['error'] = true;
				$json["focus"] = "prestamos_pago_1";
				$json["valor"] = $_POST['prestamos_pago_1'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
			}

			if(!$this->is_empty($_POST['prestamo_otro_2']) && 
			  ($this->is_empty($_POST['prestamos_pago_2']) || 
			  !is_numeric($_POST['prestamos_pago_2']))) {
				$json['error'] = true;
				$json["focus"] = "prestamos_pago_2";
				$json["valor"] = $_POST['prestamos_pago_2'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
			}

			/*Verifica si tiene otros Ingresos*/
			if(!$this->is_empty($_POST['ingreso_adicional_1']) && 
			  ($this->is_empty($_POST['ingreso_monto_1']) || 
			  !is_numeric($_POST['ingreso_monto_1']))) {
				$json['error'] = true;
				$json["focus"] = "ingreso_monto_1";
				$json["valor"] = $_POST['ingreso_monto_1'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
			}

			if(!$this->is_empty($_POST['ingreso_adicional_2']) && 
			  ($this->is_empty($_POST['ingreso_monto_2']) || 
			  !is_numeric($_POST['ingreso_monto_2']))) {
				$json['error'] = true;
				$json["focus"] = "ingreso_monto_2";
				$json["valor"] = $_POST['ingreso_monto_2'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
			}

			if(!$this->is_empty($_POST['ingreso_adicional_3']) && 
			  ($this->is_empty($_POST['ingreso_monto_3']) || 
			  !is_numeric($_POST['ingreso_monto_3']))) {
				$json['error'] = true;
				$json["focus"] = "ingreso_monto_3";
				$json["valor"] = $_POST['ingreso_monto_3'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
			}


			/*CORRESPONDIENTE AL ARCHIVO IFE*/
			if(isset($_FILES['ife']['name']) && $_FILES['ife']['name'] != "") {
				$filename_ife = $_FILES['ife']['name'];
				$ext_ife = pathinfo($filename_ife, PATHINFO_EXTENSION);
			} else if(!isset($_POST['id'])) {
				$json['error'] = true;
				$json["focus"] = "ife";
				$json['msg'] = "Favor de ingresar el documento correspondiente a la IFE.";
			}

			/*CORRESPONDIENTE AL ARCHIVO COMPROBANTE DE DOMICILIO*/
			if(isset($_FILES['comprobante_domicilio']['name']) && $_FILES['comprobante_domicilio']['name'] != "") {
				$filename_cd = $_FILES['comprobante_domicilio']['name'];
				$ext_cd = pathinfo($filename_cd, PATHINFO_EXTENSION);
			} else if(!isset($_POST['id'])) {
				$json['error'] = true;
				$json["focus"] = "comprobante_domicilio";
				$json['msg'] = "Favor de ingresar el documento correspondiente al Comprobante de Domicilio.";
			}

			/*Verifica si está rechazado desde un principio*/
			$rechazar = 0;
			if(isset($_POST['depende_conyugue'])) {
				$rechazar = 2;
			}

			if(!$json['error']) {

				//Formato correcto para fecha
				$fecha = date("Y-d-m",strtotime($_POST['fecha']));

				if(isset($_POST['id'])) {
				 	//Query de update sin nuevo contrato
					$sql = "UPDATE PERSONAS SET PER_FECHA = ?,
												  PER_NOMBRE = ?,
												  PER_DIRECCION = ?,
												  PER_EMAIL = ?,
												  PER_TELEFONO = ?,
												  PER_CELULAR = ?,
												  PER_FACEBOOK = ?,
												  MONTO_SOLICITADO = ?,
												  VIVE_PADRES = ?,
												  VIVE_CONYUGUE = ?,
												  VIVE_HIJOS = ?,
												  VIVE_HERMANOS = ?,
												  VIVE_OTROS = ?,
												  DEPENDE_PADRES = ?,
												  DEPENDE_PADRES_COMMENT = ?,
												  DEPENDE_CONYUGUE = ?,
												  DEPENDE_CONYUGUE_COMMENT = ?,
												  DEPENDE_HIJOS = ?,
												  DEPENDE_HIJOS_COMMENT = ?,
												  DEPENDE_HERMANOS = ?,
												  DEPENDE_HERMANOS_COMMENT = ?,
												  DEPENDE_OTROS = ?,
												  DEPENDE_OTROS_COMMENT = ?,
												  ACT_ID = ?,
												  ACT_OTRO = ?,
												  ACT_ANTIGUEDAD = ?,
												  INGRESO_SEMANAL = ?,
												  INGRESO_ADICIONAL_1 = ?,
												  INGRESO_MONTO_1 = ?,
												  INGRESO_ADICIONAL_2 = ?,
												  INGRESO_MONTO_2 = ?,
												  INGRESO_ADICIONAL_3 = ?,
												  INGRESO_MONTO_3 = ?,
												  VIVIENDA = ?,
												  VIVIENDA_GASTO = ?,
												  PRESTAMO_OTRO_1 = ?,
												  PRESTAMO_PAGO_1 = ?,
												  PRESTAMO_OTRO_2 = ?,
												  PRESTAMO_PAGO_2 = ?,
												  PROYECTO_INVERSION = ?,
												  REFERENCIA_NOMBRE_1 = ?,
												  REFERENCIA_RELACION_1 = ?,
												  REFERENCIA_TELEFONO_1 = ?,
												  REFERENCIA_NOMBRE_2 = ?,
												  REFERENCIA_RELACION_2 = ?,
												  REFERENCIA_TELEFONO_2 = ?,
												  REFERENCIA_NOMBRE_3 = ?,
												  REFERENCIA_RELACION_3 = ?,
												  REFERENCIA_TELEFONO_3 = ?,
												  REFERENCIA_NOMBRE_4 = ?,
												  REFERENCIA_RELACION_4 = ?,
												  REFERENCIA_TELEFONO_4 = ?,
												  GARANTIA_BIEN_1 = ?,
												  GARANTIA_MODELO_1 = ?,
												  GARANTIA_DESCRIPCION_1 = ?,
												  GARANTIA_BIEN_2 = ?,
												  GARANTIA_MODELO_2 = ?,
												  GARANTIA_DESCRIPCION_2 = ?,
												  GARANTIA_BIEN_3 = ?,
												  GARANTIA_MODELO_3 = ?,
												  GARANTIA_DESCRIPCION_3 = ?,
												  COMENTARIOS = ?,
												  STATUS = ?,
												  RAZON_RECHAZO = ?
							WHERE PER_ID = ?";
					$values = array($fecha,
									$_POST['nombre'],
									$_POST['direccion'],
									$_POST['email'],
									$_POST['telefono'],
									$_POST['celular'],
									$_POST['facebook'],
									$_POST['monto_solicitado'],
									$vive_padres,
									$vive_conyugue,
									$vive_hijos,
									$vive_hermanos,
									$vive_otros,
									$depende_padres,
									$_POST['depende_comment_padres'],
									$depende_conyugue,
									$_POST['depende_comment_conyugue'],
									$depende_hijos,
									$_POST['depende_comment_hijos'],
									$depende_hermanos,
									$_POST['depende_comment_hermanos'],
									$depende_otros,
									$_POST['depende_comment_otros'],
									$_POST['actividades'],
									$_POST['act_otro'],
									$_POST['antiguedad'],
									$_POST['ingreso_promedio'],
									$_POST['ingreso_adicional_1'],
									$_POST['ingreso_monto_1'],
									$_POST['ingreso_adicional_2'],
									$_POST['ingreso_monto_2'],
									$_POST['ingreso_adicional_3'],
									$_POST['ingreso_monto_3'],
									$vivienda,
									$_POST['vivienda_gasto'],
									$_POST['prestamo_otro_1'],
									$_POST['prestamos_pago_1'],
									$_POST['prestamo_otro_2'],
									$_POST['prestamos_pago_2'],
									$_POST['proyecto_inversion'],
									$_POST['referencia_nombre_1'],
									$_POST['referencia_relacion_1'],
									$_POST['referencia_telefono_1'],
									$_POST['referencia_nombre_2'],
									$_POST['referencia_relacion_2'],
									$_POST['referencia_telefono_2'],
									$_POST['referencia_nombre_3'],
									$_POST['referencia_relacion_3'],
									$_POST['referencia_telefono_3'],
									$_POST['referencia_nombre_4'],
									$_POST['referencia_relacion_4'],
									$_POST['referencia_telefono_4'],
									$_POST['garantia_bien_1'],
									$_POST['garantia_modelo_1'],
									$_POST['garantia_descripcion_1'],
									$_POST['garantia_bien_2'],
									$_POST['garantia_modelo_2'],
									$_POST['garantia_descripcion_2'],
									$_POST['garantia_bien_3'],
									$_POST['garantia_modelo_3'],
									$_POST['garantia_descripcion_3'],
									$_POST['comentarios'],
									$rechazar,
									$_POST['razon_rechazo'],
									$_POST['id']);

				} else {
					//INSERT
					$sql = "INSERT INTO PERSONAS (PER_FECHA,
												  PER_NOMBRE,
												  PER_DIRECCION,
												  PER_EMAIL,
												  PER_TELEFONO,
												  PER_CELULAR,
												  PER_FACEBOOK,
												  MONTO_SOLICITADO,
												  VIVE_PADRES,
												  VIVE_CONYUGUE,
												  VIVE_HIJOS,
												  VIVE_HERMANOS,
												  VIVE_OTROS,
												  DEPENDE_PADRES,
												  DEPENDE_PADRES_COMMENT,
												  DEPENDE_CONYUGUE,
												  DEPENDE_CONYUGUE_COMMENT,
												  DEPENDE_HIJOS,
												  DEPENDE_HIJOS_COMMENT,
												  DEPENDE_HERMANOS,
												  DEPENDE_HERMANOS_COMMENT,
												  DEPENDE_OTROS,
												  DEPENDE_OTROS_COMMENT,
												  ACT_ID,
												  ACT_OTRO,
												  ACT_ANTIGUEDAD,
												  INGRESO_SEMANAL,
												  INGRESO_ADICIONAL_1,
												  INGRESO_MONTO_1,
												  INGRESO_ADICIONAL_2,
												  INGRESO_MONTO_2,
												  INGRESO_ADICIONAL_3,
												  INGRESO_MONTO_3,
												  VIVIENDA,
												  VIVIENDA_GASTO,
												  PRESTAMO_OTRO_1,
												  PRESTAMO_PAGO_1,
												  PRESTAMO_OTRO_2,
												  PRESTAMO_PAGO_2,
												  PROYECTO_INVERSION,
												  REFERENCIA_NOMBRE_1,
												  REFERENCIA_RELACION_1,
												  REFERENCIA_TELEFONO_1,
												  REFERENCIA_NOMBRE_2,
												  REFERENCIA_RELACION_2,
												  REFERENCIA_TELEFONO_2,
												  REFERENCIA_NOMBRE_3,
												  REFERENCIA_RELACION_3,
												  REFERENCIA_TELEFONO_3,
												  REFERENCIA_NOMBRE_4,
												  REFERENCIA_RELACION_4,
												  REFERENCIA_TELEFONO_4,
												  GARANTIA_BIEN_1,
												  GARANTIA_MODELO_1,
												  GARANTIA_DESCRIPCION_1,
												  GARANTIA_BIEN_2,
												  GARANTIA_MODELO_2,
												  GARANTIA_DESCRIPCION_2,
												  GARANTIA_BIEN_3,
												  GARANTIA_MODELO_3,
												  GARANTIA_DESCRIPCION_3,
												  COMENTARIOS,
												  STATUS,
												  RAZON_RECHAZO)
												VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?)";
					$values = array($fecha,
									$_POST['nombre'],
									$_POST['direccion'],
									$_POST['email'],
									$_POST['telefono'],
									$_POST['celular'],
									$_POST['facebook'],
									$_POST['monto_solicitado'],
									$vive_padres,
									$vive_conyugue,
									$vive_hijos,
									$vive_hermanos,
									$vive_otros,
									$depende_padres,
									$_POST['depende_comment_padres'],
									$depende_conyugue,
									$_POST['depende_comment_conyugue'],
									$depende_hijos,
									$_POST['depende_comment_hijos'],
									$depende_hermanos,
									$_POST['depende_comment_hermanos'],
									$depende_otros,
									$_POST['depende_comment_otros'],
									$_POST['actividades'],
									$_POST['act_otro'],
									$_POST['antiguedad'],
									$_POST['ingreso_promedio'],
									$_POST['ingreso_adicional_1'],
									$_POST['ingreso_monto_1'],
									$_POST['ingreso_adicional_2'],
									$_POST['ingreso_monto_2'],
									$_POST['ingreso_adicional_3'],
									$_POST['ingreso_monto_3'],
									$vivienda,
									$_POST['vivienda_gasto'],
									$_POST['prestamo_otro_1'],
									$_POST['prestamos_pago_1'],
									$_POST['prestamo_otro_2'],
									$_POST['prestamos_pago_2'],
									$_POST['proyecto_inversion'],
									$_POST['referencia_nombre_1'],
									$_POST['referencia_relacion_1'],
									$_POST['referencia_telefono_1'],
									$_POST['referencia_nombre_2'],
									$_POST['referencia_relacion_2'],
									$_POST['referencia_telefono_2'],
									$_POST['referencia_nombre_3'],
									$_POST['referencia_relacion_3'],
									$_POST['referencia_telefono_3'],
									$_POST['referencia_nombre_4'],
									$_POST['referencia_relacion_4'],
									$_POST['referencia_telefono_4'],
									$_POST['garantia_bien_1'],
									$_POST['garantia_modelo_1'],
									$_POST['garantia_descripcion_1'],
									$_POST['garantia_bien_2'],
									$_POST['garantia_modelo_2'],
									$_POST['garantia_descripcion_2'],
									$_POST['garantia_bien_3'],
									$_POST['garantia_modelo_3'],
									$_POST['garantia_descripcion_3'],
									$_POST['comentarios'],
									$rechazar,
									$_POST['razon_rechazo']);

				}

				$db = $this->_conexion;
				$consulta = $db->prepare($sql);

				try {
					$consulta->execute($values);

					$json['msg'] = "El Prospecto se guardó con éxito.";

					if(isset($_POST['id'])) {
						$last_id = $_POST['id'];

					} else {
						$last_id = $this->last_id();
					}

					if(isset($_FILES['ife']['name']) && $_FILES['ife']['name'] != "") {
						$ife = $last_id.".".$ext_ife;
						if(!move_uploaded_file($_FILES["ife"]["tmp_name"], $ruta."documentos/ife/".$ife)){
							$json['error'] = true;
							$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
						} else {
							//Update del archivo
							$sql_ife = "UPDATE PERSONAS SET IFE = ? WHERE PER_ID = ?";
							$values_ife = array($ife,
												$last_id);
							$consulta_ife = $db->prepare($sql_ife);
							$consulta_ife->execute($values_ife);

							$json['msg'] = "El Prospecto se guardó con éxito.";
						}
					}

					if(isset($_FILES['comprobante_domicilio']['name']) && $_FILES['comprobante_domicilio']['name'] != "") {
						$comprobante_domicilio = $last_id.".".$ext_cd;
						if(!move_uploaded_file($_FILES["comprobante_domicilio"]["tmp_name"], $ruta."documentos/domicilio/".$comprobante_domicilio)){
							$json['error'] = true;
							$json['msg'] = "Error al subir archivo. Inténtelo de nuevo más tarde.";
						} else {
							//Update del archivo
							$sql_cd = "UPDATE PERSONAS SET COMPROBANTE_DOMICILIO = ? WHERE PER_ID = ?";
							$values_cd = array($comprobante_domicilio,
											   $last_id);
							$consulta_cd = $db->prepare($sql_cd);
							$consulta_cd->execute($values_cd);

							$json['msg'] = "El Prospecto se guardó con éxito.";
						}
					}

				} catch(PDOException $e) {
					$json["error"] = true;
					$json["msg"] = $e->getMessage();
					$dbgMsg = "--SQL: ".$sql.(isset($values)?"\n--Values: ".print_r($values):"");
					die($e->getMessage().$dbgMsg);
				}
			}

		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo
	 * @version: 0.1 2015-01-21
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Regresa el Select correspondiente a las actividades
	 */
	function getActivities() {
		$json = array();
		$json["error"] = false;
		$json["select"] = '<select id="actividades" name="actividades" class="form-control">';

		$sql = "SELECT ACT_ID, ACT_NOMBRE FROM ACTIVIDADES_ECONOMICAS";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if($consulta->rowCount()) {
				foreach ($puntero as $row) {
					$json["select"] .= '<option value="'.$row['ACT_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['ACT_ID'] ? 'selected' : '' : '').' >'.$row['ACT_NOMBRE'].'</option>';
				}
			}

			$json["select"] .= '<option value="0" '.(isset($_POST['id']) ? $_POST['id'] == 0 ? 'selected' : '' : '').' >Otra</option>';
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}

		$json["select"] .= '</select>';
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
		case "getActivities":
			$libs->getActivities();
			break;		
	}
}

?>