<?php
/*
 *	Se identifica la ruta	
 */
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
	 * @version: 0.1 2016-01-20
	 * 
	 * @return '$output'	array. 	Datos necesarios para la tabla
	 * 
	 * Imprime tabla de Actividades Económicas
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
		$aColumns = array("ACT_NOMBRE");
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
		$aColumns = array("ACT_NOMBRE");
		$sWhere = "";
		
		//Búsquedas en todos los campos.
		if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
			$sWhere = " WHERE ";
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
			
			//die($sWhere);
		}

		//Búsquedas por campo.
		for ($i = 0; $i < count($aColumns); $i++) {
			
			if (isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '') {

				if($sWhere == '') {
					$sWhere = " WHERE ";
					$countWords = 1;
				}	
				
				$sWhere .= ($countWords > 1 ? " AND " : "")." ".$aColumns[$i]." LIKE '%".(str_replace('"', "", $_GET['sSearch_'.$i]))."%' ";
				
				$countWords++;
			}
		}

		/*
		 * Query principal
		 */

		$sqlQuery = "SELECT ACT_ID, ACT_NOMBRE FROM ACTIVIDADES_ECONOMICAS";

		$sqlQueryFiltered = $sqlQuery." ".$sWhere." ".$sOrder." ".$sLimit;
		//die($sqlQueryFiltered);
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
				$aRow[] = $row["ACT_NOMBRE"];
				//Botones
				$params_editar = array(	"link"		=>	"#",
										"title"		=>	"Editar",
										"classes" => "editar",
										"data_id" => $row['ACT_ID']);
				$btn_editar = $this->printButton(5, "cambios", $params_editar);
				$params_borrar = array(	"link" => "#",
										"title"		=>	"Borrar",
										"classes"	=>	"borrar",
										"data_id"	=>	$row['ACT_ID'],
										"extras"	=>	"");
				$btn_borrar = $this->printButton(5, "baja", $params_borrar);

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
	 * @version: 0.1 2016-01-20
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Guarda Actividad Económica
	 */
	function saveRecord() {
		$json = array();
		$json['msg'] = "";
		$json['error'] = false;
		
		if (!isset($_POST['actividad']) || empty($_POST['actividad'])) {
			$json['msg'] = "Actividad Económica es un campo obligatorio.";
			$json['error'] = true;
		} 

		if (!$json['error']) {
			try {
				$db = $this->_conexion;
				$db->beginTransaction();

				$values = array($_POST['actividad']);

				if(isset($_POST['id'])) {
					$sql = "UPDATE ACTIVIDADES_ECONOMICAS SET ACT_NOMBRE = ? WHERE ACT_ID = ?";

					$values[]= $_POST['id'];	
				} else {
					$sql = "INSERT INTO ACTIVIDADES_ECONOMICAS(ACT_NOMBRE) VALUES(?)";
				}

				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				$db->commit();
				$json['msg'] = "La Actividad Económica se ha guardado con éxito.";	

			} catch (PDOException $e) {
				$db->rollBack();
				$dbgMsg = isset($debug)?"--SQL: ".$sql.(isset($values)?"\n--Values: ".print_r($values):""):"";
				die($e->getMessage().$dbgMsg);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-20
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Regresa los datos de un IVA en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['actividad'] = "";

		if(isset($_POST['id'])){
			try {
				$db = $this->_conexion;
				$sql = "SELECT * FROM ACTIVIDADES_ECONOMICAS WHERE ACT_ID = :valor";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$row = $puntero[0];

					$json['actividad'] = $row['ACT_NOMBRE'];
				} else {
					$json['error'] = true;
				}
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		}

		echo json_encode($json);
	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-01-20
	 * 
	 * @return '$json'	array. 	Indica si la acción se hizo con éxito
	 * 
	 * Borra registro en base a su ID
	 */
	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";

		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM ACTIVIDADES_ECONOMICAS WHERE ACT_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "La Actividad Económica fue eliminada con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "La Actividad Económica elegida no pudo ser eliminada.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
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
		case "deleteRecord":
			$libs->deleteRecord();
			break;
		case "saveRecord":
			$libs->saveRecord();
			break;
		case "showRecord": 
			$libs->showRecord();
			break;			
	}
}

?>