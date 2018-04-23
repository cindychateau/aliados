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
		$aColumns = array("");
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
		$aColumns = array("");
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
		$sqlQuery = "SELECT 	YEAR(FECHA) as ANIO,
								SUM(ENTRADA) as SUM_ENTRADA,
								SUM(SALIDA) as SUM_SALIDA
					FROM
							((SELECT SAL_FECHA as FECHA,
									SAL_CANTIDAD as SALIDA,
									0 as ENTRADA
							FROM SALIDAS)
							UNION
							(SELECT GAS_FECHA as FECHA,
									GAS_MONTO as SALIDA,
									0 as ENTRADA
							FROM GASTOS)
							UNION
							(SELECT ENT_FECHA as FECHA,
									0 as SALIDA,
									ENT_CANTIDAD as ENTRADA
							FROM ENTRADAS)) tesoreria
					GROUP BY YEAR(FECHA)
					ORDER BY FECHA ASC";
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
				$aRow[] = '<a href="anio.php?y='.$row['ANIO'].'">'.$row['ANIO'].'</a>';
				$aRow[] = "$".number_format($row['SUM_ENTRADA'], 2);
				$aRow[] = "$".number_format($row['SUM_SALIDA'], 2);		


				//Botones
				/*$params_editar = array(	"link"		=>	"cambios.php?id=".$row['GAS_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(16, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['GAS_ID']);
				$btn_borrar = $this->printButton(16, "baja", $params_borrar);*/

				//$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}
			echo json_encode($output);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	function printTableYear() {
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
		$aColumns = array("");
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
		$aColumns = array("");
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

		/*"SELECT 	MONTH(FECHA) as MES,
					YEAR(FECHA) as ANIO,
					SUM(ENTRADA) as SUM_ENTRADA,
					SUM(SALIDA) as SUM_SALIDA
		FROM
				((SELECT SAL_FECHA as FECHA,
						SAL_CANTIDAD as SALIDA,
						0 as ENTRADA
				FROM SALIDAS)
				UNION
				(SELECT GAS_FECHA as FECHA,
						GAS_MONTO as SALIDA,
						0 as ENTRADA
				FROM GASTOS)
				UNION
				(SELECT ENT_FECHA as FECHA,
						0 as SALIDA,
						ENT_CANTIDAD as ENTRADA
				FROM ENTRADAS)) tesoreria
		WHERE YEAR(FECHA) = ".$_GET['y']."		
		GROUP BY MONTH(FECCHA), YEAR(FECHA)
		ORDER BY FECHA ASC"*/

		$sqlQuery = "SELECT 	MONTH(FECHA) as MES,
								YEAR(FECHA) as ANIO,
								SUM(ENTRADA) as SUM_ENTRADA,
								SUM(SALIDA) as SUM_SALIDA
					FROM
							((SELECT SAL_FECHA as FECHA,
									SAL_CANTIDAD as SALIDA,
									0 as ENTRADA
							FROM SALIDAS)
							UNION
							(SELECT GAS_FECHA as FECHA,
									GAS_MONTO as SALIDA,
									0 as ENTRADA
							FROM GASTOS)
							UNION
							(SELECT ENT_FECHA as FECHA,
									0 as SALIDA,
									ENT_CANTIDAD as ENTRADA
							FROM ENTRADAS)) tesoreria
					WHERE YEAR(FECHA) = ".$_GET['y']."		
					GROUP BY MONTH(FECHA), YEAR(FECHA)
					ORDER BY FECHA ASC";
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

			$total_entradas = 0;
			$total_salidas = 0;
			
			foreach ($puntero as $row) {
				$counter++;
				$aRow = array();
				$aRow[] = $counter;
				$aRow[] = '<a href="mes.php?m='.$row['MES'].'&y='.$row['ANIO'].'">'.$this->getMonthWord($row['MES']).'</a>';
				$aRow[] = "$".number_format($row['SUM_ENTRADA'], 2);
				$aRow[] = "$".number_format($row['SUM_SALIDA'], 2);	
				$total_entradas += $row['SUM_ENTRADA'];
				$total_salidas += $row['SUM_SALIDA'];


				//Botones
				/*$params_editar = array(	"link"		=>	"cambios.php?id=".$row['GAS_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(16, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['GAS_ID']);
				$btn_borrar = $this->printButton(16, "baja", $params_borrar);*/

				//$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}

			$aRow = array();
			$aRow[] = "-";
			$aRow[] = '<b>MONTO TOTAL</b>';
			$aRow[] = "<b>$".number_format($total_entradas, 2)."</b>";
			$aRow[] = "<b>$".number_format($total_salidas, 2)."</b>";

			$output['aaData'][] = $aRow;


			echo json_encode($output);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	}

	/*
	 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
	 * @version: 0.1 2013-12-27
	 *  
	 * Imprime la tabla de registros de perfil de usuarios EXCEPTUANDO 'daemon'
	 */
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
		$aColumns = array("GAS_FECHA",
						  "GAS_TIPO",
						  "",
						  "",
						  "GAS_CLASE");
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
		$aColumns = array("GAS_CONCEPTO",
						  "GAS_TIPO",
						  "GAS_CLASE",
						  "GAS_FACTURA");
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
					 FROM GASTOS
					 WHERE MONTH(GAS_FECHA) = ".$_GET['m']."
					 AND YEAR(GAS_FECHA) = ".$_GET['y'];
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
			$total = 0;
			
			foreach ($puntero as $row) {
				$counter++;
				$aRow = array();
				$aRow[] = $counter;
				$aRow[] = date("d/m/Y",strtotime($row["GAS_FECHA"]));
				$aRow[] = $row["GAS_TIPO"];	
				$aRow[] = $row["GAS_CONCEPTO"];
				$aRow[] = $row["GAS_FACTURA"];
				$aRow[] = $row["GAS_CLASE"];
				$aRow[] = "$".number_format($row['GAS_MONTO'], 2);

				$total += $row['GAS_MONTO'];
							
				//Botones
				$params_editar = array(	"link"		=>	"cambios.php?id=".$row['GAS_ID'],
										"title"		=>	"Editar");
				$btn_editar = $this->printButton(16, "cambios", $params_editar);
				$params_borrar = array(	"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['GAS_ID']);
				$btn_borrar = $this->printButton(16, "baja", $params_borrar);

				$aRow[] = $btn_editar.$btn_borrar;
				
				//Se guarda la fila en la matriz principal
				$output['aaData'][] = $aRow;
			}

			$aRow = array();
			$aRow[] = "-";
			$aRow[] = "-";
			$aRow[] = "-";
			$aRow[] = "-";
			$aRow[] = "-";
			$aRow[] = '<b>MONTO TOTAL</b>';
			$aRow[] = "<b>$".number_format($total, 2)."</b>";
			$aRow[] = "-";

			$output['aaData'][] = $aRow;



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
				$consulta = $this->_conexion->prepare("DELETE FROM GASTOS WHERE GAS_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Gasto fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Gasto elegido no pudo ser eliminado.";
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
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT *
					FROM GASTOS
					WHERE GAS_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {
					$row = $result[0];

					$json['fecha'] = date("d/m/Y",strtotime($row["GAS_FECHA"]));
					$json['concepto'] = $row['GAS_CONCEPTO'];
					$json['monto'] = $row['GAS_MONTO'];
					$json['factura'] = $row['GAS_FACTURA'];
					$json['clase'] = $row['GAS_CLASE'];
					$json['tipo'] = $row['GAS_TIPO'];
						
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


		//Verifica si la factura no fue ingresada anteriormente
		$sql_factura = "SELECT GAS_FACTURA,
							   GAS_ID
						FROM GASTOS
						WHERE GAS_FACTURA = ?";
		$values_factura = array($_POST['factura']);
		$consulta_factura = $this->_conexion->prepare($sql_factura);	
		try {
			$consulta_factura->execute($values_factura);	
			if($consulta_factura->rowCount()) {

				$row_factura = $consulta_factura->fetch(PDO::FETCH_ASSOC);

				if(isset($_POST['id']) && $row_factura['GAS_ID'] == $_POST['id']) {
					$nada = 0;
				} else {
					$json["error"] = true;
					$json["focus"] = "factura";
					$json["msg"] = "La Factura ingresada ya se encuentra registrada.";
				}
			}		
		} catch (PDOException $e) {
			$json["error"] = true;
			$json["msg"] = $e->getMessage();
		}		


		if(!$json["error"]) {

			//Modifica formato de fecha
			$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
			$values = array($fecha,
							$_POST["concepto"],
							$_POST["factura"],
							$_POST["clase"],
							$_POST["monto"],
							$_POST['tipo']);

			if(isset($_POST['id'])) { //UPDATE
				
				$sql = "UPDATE GASTOS SET GAS_FECHA = ?,
										  GAS_CONCEPTO = ?,
										  GAS_FACTURA = ?,
										  GAS_CLASE = ?,
										  GAS_MONTO = ?,
										  GAS_TIPO = ?
						WHERE GAS_ID = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO GASTOS (GAS_FECHA,
											GAS_CONCEPTO,
											GAS_FACTURA,
											GAS_CLASE,
											GAS_MONTO,
											GAS_TIPO) 
							 VALUES( ?, ?, ?, ?, ?, ? )";
			}

			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				$json['msg'] = "El Gasto fue guardado con éxito.";
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

	function printTesoreria() {
		$json = array();
		$json['error'] = false;


		$db = $this->_conexion;
		$sql = "SELECT 	YEAR(FECHA) as ANIO,
						SUM(ENTRADA) as SUM_ENTRADA,
						SUM(SALIDA) as SUM_SALIDA
				FROM
					((SELECT SAL_FECHA as FECHA,
							SAL_CANTIDAD as SALIDA,
							0 as ENTRADA
					FROM SALIDAS)
					UNION
					(SELECT GAS_FECHA as FECHA,
							GAS_MONTO as SALIDA,
							0 as ENTRADA
					FROM GASTOS)
					UNION
					(SELECT ENT_FECHA as FECHA,
							0 as SALIDA,
							ENT_CANTIDAD as ENTRADA
					FROM ENTRADAS)) tesoreria
				GROUP BY YEAR(FECHA)
				ORDER BY FECHA ASC";


		echo json_encode($json);
	}

	function getPromotores() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="promotor" name="promotor" class="form-control">';

		$sql = "SELECT SIU_ID, SIU_NOMBRE
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['SIU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SIU_ID'] ? 'selected' : '' : '').' >'.$row['SIU_NOMBRE'].'</option>';
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}


		$json["select"] .= '</select>';



		echo json_encode($json);
	}

	function getPromotores2() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="promotor" name="promotor" class="form-control">
							<option value="-1">Todos los Promotores</option>';

		$sql = "SELECT SIU_ID, SIU_NOMBRE
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['SIU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SIU_ID'] ? 'selected' : '' : '').' >'.$row['SIU_NOMBRE'].'</option>';
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}


		$json["select"] .= '</select>';



		echo json_encode($json);
	}

	function getPago() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Grupo seleccionado no válido. Favor de intentar otro.";

		if(isset($_POST['id']) || !$this->is_empty($_POST['id'])) {
			$sql = "SELECT SIU_ID, PAGO_SEMANAL
					FROM GRUPOS
					WHERE GRU_ID = ?";
			$values = array($_POST['id']);
			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				if ($consulta->rowCount() > 0) {
					$row = $consulta->fetch(PDO::FETCH_ASSOC);
					$json['pago'] = $row['PAGO_SEMANAL'];
					$json['promotor'] = $row['SIU_ID'];
				} else {
					$json["error"] = true;
				}
				
			} catch(PDOException $e) {
				die($e->getMessage());
				$json["error"] = true;
			}

		} else {
			$json['error'] = true;
		}	

		echo json_encode($json);
	}

	function saveEntrada() {
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";
		$error_msg = NULL;

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor)) && $clave != "comentarios") {
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
							$_POST["tipo"],
							$_POST["monto"],
							$_POST["grupo"],
							$_POST["comentarios"],
							$_POST['promotor']);

			if(isset($_POST['id'])) { //UPDATE
				
				$sql = "UPDATE ENTRADAS SET ENT_FECHA = ?,
										  	ENT_CONCEPTO = ?,
										  	ENT_CANTIDAD = ?,
										  	GRU_ID = ?,
										  	ENT_COMMENT = ?,
										  	SIU_ID_Entrada = ?
						WHERE ENT_ID = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO ENTRADAS (ENT_FECHA,
											  ENT_CONCEPTO,
											  ENT_CANTIDAD,
											  GRU_ID,
											  ENT_COMMENT,
											  SIU_ID_Entrada) 
						VALUES( ?, ?, ?, ?, ?, ? )";
			}

			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				$json['msg'] = "La Entrada fue guardada con éxito.";
			} catch(PDOException $e) {
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

		echo json_encode($json);		
	}

	function printTableMonth2() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		$json['table'] = "";

		if(isset($_POST['m']) && isset($_POST['y'])) {

			$where = "";

			if(isset($_POST['esg']) && ($_POST['esg'] != "-1" || $_POST['esg'] != -1)) {
				$where .= " AND ESG LIKE '".$_POST['esg']."'";
			}

			if(isset($_POST['tipo']) && ($_POST['tipo'] != "-1" || $_POST['tipo'] != -1)) {
				$where .= " AND TIPO LIKE '".$_POST['tipo']."'";
			}

			if(isset($_POST['concepto']) && $_POST['concepto'] != "" && $_POST['concepto'] != " ") {
				$where .= " AND CONCEPTO LIKE '%".$_POST['concepto']."%'";
			}

			if(isset($_POST['factura']) && $_POST['factura'] != "" && $_POST['factura'] != " ") {
				$where .= " AND FACTURA LIKE '".$_POST['factura']."'";
			}

			if(isset($_POST['grupo']) && $_POST['grupo'] != "" && $_POST['grupo'] != " ") {
				$where .= " AND GRUPO = ".$_POST['grupo'];
			}

			if(isset($_POST['promotor']) && ($_POST['promotor'] != "-1" || $_POST['promotor'] != -1)) {
				$where .= " AND SIU_ID = ".$_POST['promotor'];
			}

			if(isset($_POST['be']) && ($_POST['be'] != "-1" || $_POST['be'] != -1)) {
				$where .= " AND BE LIKE '".$_POST['be']."'";
			}


			
			$sql = "SELECT 	FECHA,
							ESG,
							TIPO,
							CONCEPTO,
							FACTURA,
							GRUPO,
							PROMOTOR,
							SIU_ID,
							BE,
							COMENTARIOS,
							MONTO,
							EDITAR,
							ID
					FROM
							((SELECT SAL_FECHA as FECHA,
									 'SALIDA' as ESG,
									 '-' as TIPO,
									 'Entrega de Crédito' as CONCEPTO,
									 '-' as FACTURA,
									 GRU_ID as GRUPO,
									 SIU_NOMBRE as PROMOTOR,
									 SIU_ID as SIU_ID,
									 'EFECTIVO' as BE,
									 SAL_COMMENT as COMENTARIOS,
									 SAL_CANTIDAD as MONTO,
									 CONCAT('_salida.php?id=', SAL_ID) as EDITAR,
									 SAL_ID as ID
							FROM SALIDAS
							JOIN SISTEMA_USUARIO on SIU_ID_Entrega = SIU_ID)
							UNION
							(SELECT GAS_FECHA as FECHA,
									'GASTO' as ESG,
									GAS_TIPO as TIPO,
									GAS_CONCEPTO as CONCEPTO,
									GAS_FACTURA as FACTURA,
									'-' as GRUPO,
									'-' as PROMOTOR,
									'-' as SIU_ID,
									GAS_CLASE as BE,
									'-' as COMENTARIOS,
									GAS_MONTO as SALIDA,
									CONCAT('.php?id=', GAS_ID) as EDITAR,
									GAS_ID as ID
							FROM GASTOS)
							UNION
							(SELECT ENT_FECHA as FECHA,
									'ENTRADA' as ESG,
									'-' as TIPO,
									ENT_CONCEPTO as CONCEPTO,
									'-' as FACTURA,
									GRU_ID as GRUPO,
									SIU_NOMBRE as PROMOTOR,
									SIU_ID as SIU_ID,
									'EFECTIVO' as BE,
									ENT_COMMENT as COMENTARIOS,
									ENT_CANTIDAD as MONTO,
									CONCAT('_entrada.php?id=', ENT_ID) as EDITAR,
									ENT_ID as ID
							FROM ENTRADAS
							JOIN SISTEMA_USUARIO on SIU_ID_Entrada = SIU_ID)) tesoreria
					WHERE YEAR(FECHA) = ".$_POST['y']."
					AND MONTH(FECHA) = ".$_POST['m']."
					".$where."
					ORDER BY FECHA ASC";

			$db = $this->_conexion;
			$consulta = $db->prepare($sql);

			$json['sql'] = $sql;

			try {

				$consulta->execute();
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$num = 1;

				if($consulta->rowCount()) {

					foreach ($result as $row) {

						/*$params_editar = array(	"link"		=>	"cambios".$row['EDITAR'],
												"title"		=>	"Editar");
						$btn_editar = $this->printButton(32, "cambios", $params_editar);
						$params_borrar = array(	"title"		=>	"Borrar",
												"classes"	=>	"borrar_".$row['ESG'],
												"data_id"	=>	$row['ID']);
						$btn_borrar = $this->printButton(32, "baja", $params_borrar);*/

						$btn_editar = '<a class="" href="cambios'.$row['EDITAR'].'" data-id=""><button type="button" class="btn btn-danger"><i class="fa fa-pencil"></i>Editar</button></a>';
						$btn_borrar = '<a class="borrar_'.$row['ESG'].'" href="" data-id="'.$row['ID'].'"><button class="btn btn-danger"><i class="fa fa-trash-o"></i>Borrar</button></a>';
						$btn_recibo = ($row['ESG'] != 'GASTO' ? '<a class="" href="recibos'.$row['EDITAR'].'" data-id=""><button type="button" class="btn btn-info"><i class="fa fa-file-o"></i>Recibo</button></a>' : '');

						$json['table'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center">'.date("d/m/Y",strtotime($row["FECHA"])).'</td>
												<td align="center">'.$row['ESG'].'</td>
												<td align="center">'.$row['TIPO'].'</td>
												<td>'.$row['CONCEPTO'].'</td>
												<td align="center">'.$row['FACTURA'].'</td>
												<td align="center">'.$row['GRUPO'].'</td>
												<td align="center">'.$row['PROMOTOR'].'</td>
												<td align="center">'.$row['BE'].'</td>
												<td>'.$row['COMENTARIOS'].'</td>
												<td align="center">$'.number_format($row['MONTO'], 2).'</td>
												<td align="center">'.$btn_editar.$btn_borrar.$btn_recibo.'</td>
										  </tr>';


						$num++;				  



					}


				} else {
					$json['table'] = "<tr><td colspan='11'><h4 style='text-align: center'>No se encontraron Registros en el Sistema.</h4></td></tr>";
				}


			} catch (PDOException $e) {
				
			}		



		} else {
			$json['error'] = true;
		}

		echo json_encode($json);		
	}

	function showRecordEntrada() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$sql = "SELECT ENT_FECHA,
							   ENT_CONCEPTO,
							   ENTRADAS.GRU_ID,
							   SIU_ID_Entrada,
							   ENT_CANTIDAD,
							   ENT_COMMENT,
							   PAGO_SEMANAL
						FROM ENTRADAS
						LEFT JOIN GRUPOS ON GRUPOS.GRU_ID = ENTRADAS.GRU_ID
						WHERE ENT_ID = :valor";

				$consulta = $this->_conexion->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				$row = $consulta->fetch(PDO::FETCH_ASSOC);

				if ($consulta->rowCount() > 0) {

					$json['fecha'] = date("d/m/Y",strtotime($row["ENT_FECHA"]));
					$json['tipo'] = $row['ENT_CONCEPTO'];
					$json['grupo'] = $row['GRU_ID'];
					$json['promotor'] = $row['SIU_ID_Entrada'];
					$json['monto'] = $row['ENT_CANTIDAD'];
					$json['comentarios'] = $row['ENT_COMMENT'];
					$json['pago'] = $row['PAGO_SEMANAL'];
						
				} else {
					$json['error'] = true;
				}

			}catch(PDOException $e){
				die($e->getMessage());
			}
		}
		echo json_encode($json);
	}

	function getCredito() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Grupo seleccionado no válido. Favor de intentar otro.";

		if(isset($_POST['id']) || !$this->is_empty($_POST['id'])) {
			$sql = "SELECT SIU_ID, GRU_MONTO_TOTAL
					FROM GRUPOS
					WHERE GRU_ID = ?";
			$values = array($_POST['id']);
			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				if ($consulta->rowCount() > 0) {
					$row = $consulta->fetch(PDO::FETCH_ASSOC);
					$json['credito'] = $row['GRU_MONTO_TOTAL'];
					$json['promotor'] = $row['SIU_ID'];
				} else {
					$json["error"] = true;
				}
				
			} catch(PDOException $e) {
				die($e->getMessage());
				$json["error"] = true;
			}

		} else {
			$json['error'] = true;
		}	

		echo json_encode($json);
	}

	function saveSalida() {
		$json = array();
		$json["msg"] = "Todos los campos son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";
		$error_msg = NULL;

		//VALIDACIÓN
		foreach($_POST as $clave=>$valor){
			if(!$json["error"]){
				if($this->is_empty(trim($valor)) && $clave != "comentarios") {
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
							$_POST["monto"],
							$_POST["grupo"],
							$_POST["comentarios"],
							$_POST['promotor']);

			if(isset($_POST['id'])) { //UPDATE
				
				$sql = "UPDATE SALIDAS SET SAL_FECHA = ?,
									  	   SAL_CANTIDAD = ?,
									  	   GRU_ID = ?,
									  	   SAL_COMMENT = ?,
									  	   SIU_ID_Entrega = ?
						WHERE SAL_ID = ?";

				$values[] = $_POST['id'];

			} else { //INSERCION
				$sql = "INSERT INTO SALIDAS (SAL_FECHA,
											 SAL_CANTIDAD,
											 GRU_ID,
											 SAL_COMMENT,
											 SIU_ID_Entrega) 
						VALUES( ?, ?, ?, ?, ? )";
			}

			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				$json['msg'] = "La Salida fue guardada con éxito.";
			} catch(PDOException $e) {
				$json["error"] = true;
				$json["msg"] = $e->getMessage();
			}	
		}

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
		case "printTableYear":
			$libs->printTableYear();
			break;
		case "printTableMonth":
			$libs->printTableMonth();
			break;
		case "printTableMonth2":
			$libs->printTableMonth2();
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
		case "getPromotores":
			$libs->getPromotores();
			break;
		case "getPromotores2":
			$libs->getPromotores2();
			break;	
		case "getPago":
			$libs->getPago();
			break;
		case "getCredito":
			$libs->getCredito();
			break;	
		case "saveEntrada":
			$libs->saveEntrada();
			break;	
		case "showRecordEntrada":
			$libs->showRecordEntrada();
			break;	
		case "saveSalida":
			$libs->saveSalida();
			break;									
	}
}

?>