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
		$aColumns = array("AP_FECHA",);
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
		$aColumns = array(	"AP_CONCEPTO");
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
		$sqlQuery = "SELECT AP_CONCEPTO, 
							SUM(AP_MONTO) as MONTO_TOTAL
					FROM APORTACIONES
					GROUP BY (AP_CONCEPTO)";
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
				$aRow[] = '<a href="concepto.php?c='.$row["AP_CONCEPTO"].'">'.$row["AP_CONCEPTO"].'</a>';
				$aRow[] = "$".number_format($row['MONTO_TOTAL'], 2);


				//Botones
				/*$params_editar = array(	"link"		=>	"cambios.php?id=".$row['AP_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(11, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['AP_ID']);
				$btn_borrar = $this->printButton(11, "baja", $params_borrar);*/

				//$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}
			echo json_encode($output);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	function printTableConcept() {
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
		$aColumns = array("",);
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
		$aColumns = array(	"");
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
		$sqlQuery = "SELECT MONTH(AP_FECHA) as MES, 
							YEAR(AP_FECHA) as ANIO, 
							SUM(AP_MONTO) as MONTO_TOTAL
					 FROM APORTACIONES
                     WHERE AP_CONCEPTO = '".$_GET['c']."'
					 GROUP BY MONTH(AP_FECHA), YEAR(AP_FECHA)
					 ORDER BY AP_FECHA DESC";
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
				$aRow[] = '<a href="mes.php?c='.$_GET['c'].'&m='.$row['MES'].'&y='.$row['ANIO'].'">'.$this->getMonthWord($row['MES'])." ".$row['ANIO'].'</a>';
				$aRow[] = "$".number_format($row['MONTO_TOTAL'], 2);


				//Botones
				/*$params_editar = array(	"link"		=>	"cambios.php?id=".$row['AP_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(11, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['AP_ID']);
				$btn_borrar = $this->printButton(11, "baja", $params_borrar);*/

				//$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}
			echo json_encode($output);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	function printTableMonth() {
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
		$aColumns = array("",);
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
		$aColumns = array(	"");
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
					 FROM APORTACIONES
                     WHERE AP_CONCEPTO = '".$_GET['c']."'
					 AND MONTH(AP_FECHA) = ".$_GET['m']."
					 AND YEAR(AP_FECHA) = ".$_GET['y'];
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
				$aRow[] = date("d/m/Y",strtotime($row["AP_FECHA"]));
				$aRow[] = "$".number_format($row['AP_MONTO'], 2);


				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['AP_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(11, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['AP_ID']);
				$btn_borrar = $this->printButton(11, "baja", $params_borrar);

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
				$consulta = $this->_conexion->prepare("DELETE FROM APORTACIONES WHERE AP_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "La Aportación fue eliminada con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "La Aportación elegido no pudo ser eliminada.";
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
				$sql = "SELECT *
						FROM APORTACIONES
						WHERE AP_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row = $result[0];

					$json['fecha'] = date("d/m/Y",strtotime($row["AP_FECHA"]));
					$json['concepto'] = $row['AP_CONCEPTO'];
					$json['monto'] = $row['AP_MONTO'];
						
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

			//Modifica formato de fecha
			$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
			$values = array($fecha,
							$_POST["concepto"],
							$_POST["monto"]);

			if(isset($_POST['id'])) { //UPDATE
				
				$sql = "UPDATE APORTACIONES SET AP_FECHA = ?,
										  		AP_CONCEPTO = ?,
										  		AP_MONTO = ?
						WHERE AP_ID = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO APORTACIONES (AP_FECHA,
												  AP_CONCEPTO,
												  AP_MONTO) 
						VALUES( ?, ?, ? )";
			}

			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				$json['msg'] = "La Aportación fue guardada con éxito.";
			} catch(PDOException $e) {
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

		echo json_encode($json);
	}

	function getMonthWord($month) {
		$mes = "Enero";
		switch ($month) {
			case 1:
				$mes = "Enero";
				break;
			case 2:
				$mes = "Febrero";
				break;
			case 3:
				$mes = "Marzo";
				break;
			case 4:
				$mes = "Abril";
				break;
			case 5:
				$mes = "Mayo";
				break;
			case 6:
				$mes = "Junio";
				break;
			case 7:
				$mes = "Julio";
				break;
			case 8:
				$mes = "Agosto";
				break;
			case 9:
				$mes = "Septiembre";
				break;
			case 10:
				$mes = "Octubre";
				break;
			case 11:
				$mes = "Noviembre";
				break;
			case 12:
				$mes = "Diciembre";
				break;											
		}

		return $mes;
	}
	
	function getMonth() {
		$json = array();
		$month = $_POST['mes'];

		$mes = "Enero";
		switch ($month) {
			case 1:
				$mes = "Enero";
				break;
			case 2:
				$mes = "Febrero";
				break;
			case 3:
				$mes = "Marzo";
				break;
			case 4:
				$mes = "Abril";
				break;
			case 5:
				$mes = "Mayo";
				break;
			case 6:
				$mes = "Junio";
				break;
			case 7:
				$mes = "Julio";
				break;
			case 8:
				$mes = "Agosto";
				break;
			case 9:
				$mes = "Septiembre";
				break;
			case 10:
				$mes = "Octubre";
				break;
			case 11:
				$mes = "Noviembre";
				break;
			case 12:
				$mes = "Diciembre";
				break;											
		}

		$json['mes'] = $mes;
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
		case "printTableConcept":
			$libs->printTableConcept();
			break;	
		case "printTableMonth":
			$libs->printTableMonth();
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
		case "getMonth":
			$libs->getMonth();
			break;		
	}
}

?>