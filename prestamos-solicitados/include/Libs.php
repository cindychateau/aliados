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
		$aColumns = array("PS_FECHA",
						  "PS_ACREDITANTE",
						  "PS_MONTO_TOTAL",
						  "",
						  "",
						  "",
						  "PS_INTERESES_PAGO");
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
		$aColumns = array("PS_ACREDITANTE");
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
		$sqlQuery = "SELECT *
					 FROM PRESTAMOS_SOLICITADOS
					 WHERE PS_VIGENTE = 1";
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
				$aRow[] = date("d/m/Y",strtotime($row["PS_FECHA"]));
				$aRow[] = $row["PS_ACREDITANTE"];
				$aRow[] = "$".number_format($row['PS_MONTO_TOTAL'], 2);
				$aRow[] = $row["PS_PLAZO"];
				$aRow[] = ($row["PS_TASA"]*100)."%";


				$frecuencia = "Mensual";
				switch ($row["PS_FRECUENCIA_PAGOS"]) {
					case 0:
						$frecuencia = "Mensual";
						break;
					case 1:
						$frecuencia = "Semanal";
						break;
					case 2:
						$frecuencia = "Anual";
						break;		
				}

				$aRow[] = $frecuencia;
				$aRow[] = "$".number_format($row['PS_INTERESES_PAGO'], 2);

				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['PS_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(17, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['PS_ID']);
				$btn_borrar = $this->printButton(17, "baja", $params_borrar);

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
				$db = $this->_conexion;
				$db->beginTransaction();
				$consulta = $db->prepare("DELETE FROM PRESTAMOS_SOLICITADOS WHERE PS_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					try{
						$consulta_del = $db->prepare("DELETE FROM PAGOS_PRESTAMOS WHERE PS_ID = :valor");
						$consulta_del->bindParam(':valor', $_POST['id']);
						$consulta_del->execute();
						$json['msg'] = "El Fondeo fue eliminado con éxito.";
						$json['error'] = false;
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
					}
				} else{
					$db->rollBack();
					$json['error'] = true;
					$json['msg'] = "El Fondeo elegido no pudo ser eliminado.";
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
		$json['fecha'] = "";
		$json['acreditante'] = "";
		$json['monto'] = "";
		$json['tasa'] = "";
		$json['plazo'] = "";
		$json['frecuencia'] = "";
		$json['fecha_pago'] = "";
		$json['monto_pagos'] = "";
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
						FROM PRESTAMOS_SOLICITADOS
						WHERE PS_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$json['fecha'] = date("d/m/Y",strtotime($row["PS_FECHA"]));
					$json['acreditante'] = $row['PS_ACREDITANTE'];
					$json['monto'] = $row['PS_MONTO_TOTAL'];
					$json['tasa'] = $row['PS_TASA']*100;
					$json['plazo'] = $row['PS_PLAZO'];
					$json['frecuencia'] = $row['PS_FRECUENCIA_PAGOS'];
					$json['fecha_pago'] = date("d/m/Y",strtotime($row["PS_PAGO_INICIAL"]));
					$json['monto_pagos'] = $row['PS_MONTO_PAGOS'];
					$json['intereses'] = $row['PS_INTERESES_PAGO'];
						
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
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";
		$error_msg = NULL;

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor))) {
					$json["error"] = true;
					$json["focus"] = $clave;	
				} else if($clave == "monto"  && !is_numeric($valor)) {
					$json["error"] = true;
					$json["focus"] = $clave;
					$json["msg"] = "El Monto ingresado no es válido.";
				}
			}
		}

		if(!$json["error"]) {
			$db = $this->_conexion;
			$db->beginTransaction();

			//Modifica formato de fecha
			$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
			$fecha_pago = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['primer-pago'])));
			$tasa = $_POST['tasa']/100;
			$values = array($fecha,
							$_POST["acreditante"],
							$tasa,
							$_POST['plazo'],
							$_POST['monto'],
							$_POST['intereses'],
							$fecha_pago,
							$_POST['frecuencia']);

			if(isset($_POST['id'])) { //UPDATE
				
				$sql = "UPDATE PRESTAMOS_SOLICITADOS SET PS_FECHA = ?,
												  		 PS_ACREDITANTE = ?,
												  		 PS_TASA = ?,
												  		 PS_PLAZO = ?,
												  		 PS_MONTO_TOTAL = ?,
												  		 PS_INTERESES_PAGO = ?,
												  		 PS_PAGO_INICIAL = ?,
												  		 PS_FRECUENCIA_PAGOS = ?
						WHERE PS_ID = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO PRESTAMOS_SOLICITADOS (PS_FECHA,
														   PS_ACREDITANTE,
														   PS_TASA,
														   PS_PLAZO,
														   PS_MONTO_TOTAL,
														   PS_INTERESES_PAGO,
														   PS_PAGO_INICIAL,
														   PS_FRECUENCIA_PAGOS) 
						VALUES( ?, ?, ?, ?,
								?, ?, ?, ? )";
			}

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

				/*BG: TABLA PAGOS*/
				if(isset($_POST['id'])) {
					$last_id = $_POST['id'];

					//Si ya existían Pagos registrados
					try{
						$consulta_del = $db->prepare("DELETE FROM PAGOS_PRESTAMOS WHERE PS_ID = :valor");
						$consulta_del->bindParam(':valor', $_POST['id']);
						$consulta_del->execute();
					}catch(PDOException $e){
						$db->rollBack();
						die($e->getMessage());
					}
				} else {
					$last_id = $this->last_id();
				}

				//Da de alta los Pagos
				for ($i=0; $i < $_POST['plazo']; $i++) { 
					$sql_pagos = "INSERT INTO PAGOS_PRESTAMOS (PS_ID,
															  PP_FECHA,
															  PP_MONTO,
															  PP_NUM_PAGO)
												VALUES (?, ?, ?, ?)";
					$values_pagos = array($last_id,
										  $fecha_pago,
										  $_POST['intereses'],
										  ($i+1));	
					$consulta_pagos = $db->prepare($sql_pagos);

					try {
						$consulta_pagos->execute($values_pagos);
					} catch (PDOException $e) {
						$db->rollBack();
						die($e->getMessage());
					}

					//Nueva Fecha
					$fecha_ = strtotime($fecha_pago);
					if($_POST['frecuencia'] == 0) {
						$fecha_pago = strtotime('+1 month', $fecha_);
					} else if($_POST['frecuencia'] == 1){
						$fecha_pago = strtotime('+1 week', $fecha_);
					} else if($_POST['frecuencia'] == 2){
						$fecha_pago = strtotime('+1 year', $fecha_);
					}
					
					$fecha_pago = date("Y-m-d", $fecha_pago);
				}

				/*END: TABLA PAGOS*/

				$db->commit();
				$json['msg'] = "El Préstamo fue guardado con éxito.";

			} catch(PDOException $e) {
				$db->rollBack();
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}
	
	function showAcreditantes() {
		$acreditantes = array();
		$term = trim($_GET['term']); //retrieve the search term that autocomplete sends
		try {
			$db = $this->_conexion;
			$sql = "SELECT PS_ACREDITANTE
					FROM PRESTAMOS_SOLICITADOS 
					WHERE PS_ACREDITANTE LIKE '%".$term."%'
					GROUP BY LOWER(PS_ACREDITANTE)";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $term);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$acreditantes[] = $row['PS_ACREDITANTE'];
				}

			} 
			
		} catch (PDOException $e) {
			die($e->getMessage());
			
		}
		echo json_encode($acreditantes);
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
		case "saveRecord":
			$libs->saveRecord();
			break;	
		case "showAcreditantes":
			$libs->showAcreditantes();
			break;			
	}
}

?>