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

$module = 20;

//Se incluye la clase Common
include_once($ruta."include/Common.php");

class Libs extends Common {

	function getMonthWord($month) {
		$mes = "Ene";
		switch ($month) {
			case 1:
				$mes = "Ene";
				break;
			case 2:
				$mes = "Feb";
				break;
			case 3:
				$mes = "Mar";
				break;
			case 4:
				$mes = "Abr";
				break;
			case 5:
				$mes = "May";
				break;
			case 6:
				$mes = "Jun";
				break;
			case 7:
				$mes = "Jul";
				break;
			case 8:
				$mes = "Ago";
				break;
			case 9:
				$mes = "Sep";
				break;
			case 10:
				$mes = "Oct";
				break;
			case 11:
				$mes = "Nov";
				break;
			case 12:
				$mes = "Dic";
				break;											
		}

		return $mes;
	}


	function searchClient() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "";
		$json['resultados'] = '';
		global $module;

		if(!$this->is_empty(trim($_POST['cliente']))) {
			$sql = "SELECT PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_TELEFONO,
						   MAXIMO_PAGAR
					FROM PERSONAS
					WHERE PER_ID = ?";
			$values = array($_POST['cliente']);
			$consulta = $this->_conexion->prepare($sql);	
			$consulta->execute($values);
			if($consulta->rowCount()){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$eliminar = '<a href="#" data-id="'.$row['PER_ID'].'" data-name="'.ucwords($row['PER_NOMBRE']).'" class="eliminar"><i class="fa fa-trash"></i></a>';
					$json['resultados'] .= '<div class="search-results">
											   <h4><a href="info.php?id='.$row['PER_ID'].'">'.ucwords($row['PER_NOMBRE']).' - '.$row['PER_ID'].'</a> '.$this->printLink($module, "baja", $eliminar).'</h4> 
											   <div class="url">Tel: '.($row['PER_TELEFONO'] == '' ? '-' : $row['PER_TELEFONO']).' <i class="fa fa-caret-down"></i></div>
											   <p>
											   		Dirección: '.$row['PER_DIRECCION'].'
											   </p>
											   <!--p>
											   		Máximo a Otorgar: $'.(number_format($row['MAXIMO_PAGAR'], 2)).' <a href="#" class="maximo_otorgar" data-id="'.$row['PER_ID'].'" data-value="'.(number_format($row['MAXIMO_PAGAR'], 2)).'"><i class="fa fa-pencil"></i></a>
											   </p-->
											</div>';
				}
			} else {
				$json['error'] = true;
				$json['msg'] = "No se encontraron resultados.";
			}
		} else {
			$json['error'] = true;
			$json['msg'] = "Favor de llenar el campo de Nombre.";
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
					$result['PER_FECHA_NAC'] = date("d/m/Y",strtotime($result['PER_FECHA_NAC']));
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

		$sql = "SELECT ACT_ID, ACT_NOMBRE FROM ACTIVIDADES_ECONOMICAS ORDER BY ACT_NOMBRE ASC";
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

	function getGrupos() {
		$json = array();
		$json["grupos"] ="";
		$sql = "SELECT PERSONAS_GRUPOS.GRU_ID,
					   GRU_FECHA,
					   MONTO_INDIVIDUAL,
					   PAGO_SEMANAL_IND
				FROM PERSONAS_GRUPOS
				JOIN GRUPOS ON GRUPOS.GRU_ID = PERSONAS_GRUPOS.GRU_ID
				WHERE PER_ID = ?";
		$values = array($_POST['id']);
		$consulta = $this->_conexion->prepare($sql);		
		try {
			$consulta->execute($values);
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if($consulta->rowCount()) {
				foreach ($puntero as $row) {

					$sql_ahorro = "SELECT SUM(PI_AHORRO) as PI_AHORRO
								   FROM PAGOS_INDIVIDUALES
								   WHERE PER_ID = ?
								   AND GRU_ID = ?";
					$values_ahorro = array($_POST['id'],
										   $row['GRU_ID']);

					$consulta_ahorro = $this->_conexion->prepare($sql_ahorro);
					$consulta_ahorro->execute($values_ahorro);

					$row_ahorro = $consulta_ahorro->fetch(PDO::FETCH_ASSOC);					



					$json["grupos"] .= '<div class="col-sm-3">
											<div class="well">
												<h4>GRUPO '.$row['GRU_ID'].'</h4> 
												Fecha: '.date("d/m/Y",strtotime($row['GRU_FECHA'])).'<br>
												Monto Otorgado: $'.$row['MONTO_INDIVIDUAL'].'<br>
												Pago Semanal: $'.$row['PAGO_SEMANAL_IND'].'<br>
												Ahorro: $'.(is_null($row_ahorro['PI_AHORRO']) ? 0 : $row_ahorro['PI_AHORRO']).'
											</div>
										</div>';
				}
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}

		echo json_encode($json);
	}

	function saveMaximo() {
		$json = array();
		$json["msg"] = "";
		$json["error"] = false;

		if (!isset($_POST['maximo']) || empty($_POST['maximo'])) {
			$json['msg'] = "Máximo a Otorgar es un campo obligatorio.";
			$json['error'] = true;
		} 

		if (!$json['error']) {
			try {

				$db = $this->_conexion;
				$sql = "UPDATE PERSONAS SET MAXIMO_PAGAR = ? WHERE PER_ID = ?";
				$values = array($_POST['maximo'],
								$_POST['id']);
				$consulta = $db->prepare($sql);
				$consulta->execute($values);

				$json['msg'] = "Se realizó el cambio con éxito.";	

			} catch (PDOException $e) {
				$dbgMsg = isset($debug)?"--SQL: ".$sql.(isset($values)?"\n--Values: ".print_r($values):""):"";
				die($e->getMessage().$dbgMsg);
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}	
		}

		echo json_encode($json);
	}


	function displayClients() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "";
		$json['resultados'] = '';
		global $module;

		//if(!$this->is_empty(trim($_POST['nombre']))) {
			$sql = "SELECT PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_TELEFONO,
						   MAXIMO_PAGAR
					FROM PERSONAS
					ORDER BY PER_NOMBRE ASC";
			$consulta = $this->_conexion->prepare($sql);	
			$consulta->execute();
			if($consulta->rowCount()){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$eliminar = '<a href="#" data-id="'.$row['PER_ID'].'" data-name="'.ucwords($row['PER_NOMBRE']).'" class="eliminar"><i class="fa fa-trash"></i></a>';
					$json['resultados'] .= '<div class="search-results">
											   <h4><a href="info.php?id='.$row['PER_ID'].'">'.ucwords($row['PER_NOMBRE']).' - '.$row['PER_ID'].'</a>  '.$this->printLink($module, "baja", $eliminar).'</h4> 
											   <div class="url">Tel: '.($row['PER_TELEFONO'] == '' ? '-' : $row['PER_TELEFONO']).' <i class="fa fa-caret-down"></i></div>
											   <p>
											   		Dirección: '.$row['PER_DIRECCION'].'
											   </p>
											   <!--p>
											   		Máximo a Otorgar: $'.(number_format($row['MAXIMO_PAGAR'], 2)).' <a href="#" class="maximo_otorgar" data-id="'.$row['PER_ID'].'" data-value="'.(number_format($row['MAXIMO_PAGAR'], 2)).'"><i class="fa fa-pencil"></i></a>
											   </p-->
											</div>';
				}
			} else {
				$json['error'] = true;
				$json['msg'] = "No se encontraron resultados.";
			}
		/*} else {
			$json['error'] = true;
			$json['msg'] = "Favor de llenar el campo de Nombre.";
		}*/

		echo json_encode($json);	
	}

	function saveRecord() {
		global $ruta;
		$json = array();
		$json["msg"] = "Los campos con asterisco son obligatorios.";
		$json["error"] = false;
		$json["focus"] = "";

		$obligatorios = array("fecha",
							  "nombre",
							  "apellido_pat",
							  "apellido_mat",
							  "fecha_nac",
							  "ife_num",
							  "direccion",
							  "numero",
							  "colonia",
							  "municipio",
							  "estado",
							  "cp",
							  "celular",
							  "antiguedad",
							  "act_establecimiento",
							  "ingreso_promedio",
							  "monto_solicitado");

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

		//Verifica el # de caracteres de la INE
		if(!$json['error']) {
			if(strlen($_POST['ife_num']) != 13) {
				$json['error'] = true;
				$json["focus"] = "ife_num";
				$json["valor"] = $_POST['ife_num'];
				$json['msg'] = "Número INE debe contener 13 dígitos.";
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
			$cant_padres = 0;
			$cant_hijos = 0;
			$cant_hermanos = 0;
			$cant_otros = 0;

			if(isset($_POST['depende_padres'])) {
				$depende_padres = 1;
				if($this->is_empty($_POST['depende_comment_padres']) || !is_numeric($_POST['depende_comment_padres'])) {
					$json['error'] = true;
					$json["focus"] = "depende_comment_padres";
					$json["valor"] = $_POST['depende_comment_padres'];
					$json['msg'] = "Favor de especificar la cantidad de Padres que dependen económicamente.";
				} else {
					$cant_padres = $_POST['depende_comment_padres'];
				}
			}

			if(isset($_POST['depende_conyugue'])) {
				$depende_conyugue = 1;
			}

			if(isset($_POST['depende_hijos'])) {
				$depende_hijos = 1;
				if($this->is_empty($_POST['depende_comment_hijos']) || !is_numeric($_POST['depende_comment_hijos'])) {
					$json['error'] = true;
					$json["focus"] = "depende_comment_hijos";
					$json["valor"] = $_POST['depende_comment_hijos'];
					$json['msg'] = "Favor de especificar la cantidad de Hijos que dependen económicamente.";
				} else {
					$cant_hijos = $_POST['depende_comment_hijos'];
				}
			}

			if(isset($_POST['depende_hermanos'])) {
				$depende_hermanos = 1;
				if($this->is_empty($_POST['depende_comment_hermanos']) || !is_numeric($_POST['depende_comment_hermanos'])) {
					$json['error'] = true;
					$json["focus"] = "depende_comment_hermanos";
					$json["valor"] = $_POST['depende_comment_hermanos'];
					$json['msg'] = "Favor de especificar la cantidad de Hermanos que dependen económicamente.";
				} else {
					$cant_hermanos = $_POST['depende_comment_hermanos'];
				}
			}

			if(isset($_POST['depende_otros'])) {
				$depende_otros = 1;
				if($this->is_empty($_POST['depende_comment_otros']) || !is_numeric($_POST['depende_comment_otros'])) {
					$json['error'] = true;
					$json["focus"] = "depende_comment_otros";
					$json["valor"] = $_POST['depende_comment_otros'];
					$json['msg'] = "Favor de especificar la cantidad de Otros que dependen económicamente.";
				} else {
					$cant_otros = $_POST['depende_comment_otros'];
				}
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

			if(!$this->is_empty($_POST['vivienda_gasto']) && !is_numeric($_POST['vivienda_gasto'])) {
				$json['error'] = true;
				$json["focus"] = "vivienda_gasto";
				$json["valor"] = $_POST['vivienda_gasto'];
				$json['msg'] = "Favor de ingresar una cifra válida.";
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
			} /*else if(!isset($_POST['id'])) {
				$json['error'] = true;
				$json["focus"] = "ife";
				$json['msg'] = "Favor de ingresar el documento correspondiente a la IFE.";
			}*/

			/*CORRESPONDIENTE AL ARCHIVO COMPROBANTE DE DOMICILIO*/
			if(isset($_FILES['comprobante_domicilio']['name']) && $_FILES['comprobante_domicilio']['name'] != "") {
				$filename_cd = $_FILES['comprobante_domicilio']['name'];
				$ext_cd = pathinfo($filename_cd, PATHINFO_EXTENSION);
			}/* else if(!isset($_POST['id'])) {
				$json['error'] = true;
				$json["focus"] = "comprobante_domicilio";
				$json['msg'] = "Favor de ingresar el documento correspondiente al Comprobante de Domicilio.";
			}*/

			/*Verifica si está rechazado desde un principio*/
			$rechazar = (isset($_POST['status']) ? $_POST['status'] : 0);
			if(isset($_POST['rechazar'])) {
				$rechazar = 2;
			}

			/*Verifica si la referencia es Cliente Aliado*/
			$referencia_cliente_1 = 0;
			$referencia_cliente_2 = 0;
			$referencia_cliente_3 = 0;
			$referencia_cliente_4 = 0;

			if(isset($_POST['referencia_cliente_1'])) {
				$referencia_cliente_1 = 1;
			}

			if(isset($_POST['referencia_cliente_2'])) {
				$referencia_cliente_2 = 1;
			}

			if(isset($_POST['referencia_cliente_3'])) {
				$referencia_cliente_3 = 1;
			}

			if(isset($_POST['referencia_cliente_4'])) {
				$referencia_cliente_4 = 1;
			}


			if(!$json['error']) {

				//Formato correcto para fecha
				$fecha = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha'])));
				$fecha_nac = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_nac'])));

				/*Calcula lo máximo que se le puede prestar*/
				$servicios = 63; //S

				$casa = ($this->is_empty($_POST['vivienda_gasto']) ? 0 : $_POST['vivienda_gasto']); //M
				$casa = $casa / 4; //S

				//Ingresos
				$ingreso_prom = $_POST['ingreso_promedio']; //S
				$ingreso_x_1 = ($this->is_empty($_POST['ingreso_monto_1']) ? 0 : $_POST['ingreso_monto_1']); //S
				$ingreso_x_2 = ($this->is_empty($_POST['ingreso_monto_2']) ? 0 : $_POST['ingreso_monto_2']); //S
				$ingreso_x_3 = ($this->is_empty($_POST['ingreso_monto_3']) ? 0 : $_POST['ingreso_monto_3']); //S
				$total_ingresos = $ingreso_prom + $ingreso_x_1 + $ingreso_x_2 + $ingreso_x_3; //S

				//Personas dependientes
				$dependientes = $cant_padres + $depende_conyugue + $cant_hijos + $cant_hermanos + $cant_otros;
				$total_dep = $dependientes * 400; //S

				//Otros Préstamos
				$prestamo_x_1 = ($this->is_empty($_POST['prestamos_pago_1']) ? 0 : $_POST['prestamos_pago_1']); //S
				$prestamo_x_2 = ($this->is_empty($_POST['prestamos_pago_2']) ? 0 : $_POST['prestamos_pago_2']); //S
				$total_otros_pr = $prestamo_x_1 + $prestamo_x_2;

				$restante = $total_ingresos - $servicios - $casa - $total_dep - $total_otros_pr; //Disponible semanalmente
				$max_semanal = $restante * .5; //El máximo que puede pagar semanalmente

				$max_prestamo = $max_semanal * 12;

				

				if(isset($_POST['id'])) {
				 	//Query de update sin nuevo contrato
					$sql = "UPDATE PERSONAS SET PER_FECHA = ?,
												  PER_NOMBRE = ?,
												  PER_APELLIDO_PAT = ?,
												  PER_APELLIDO_MAT = ?,
												  PER_FECHA_NAC = ?, 
												  PER_GENERO = ?, 
												  PER_ESCOLARIDAD = ?, 
												  PER_ESCOLARIDAD_OTRO = ?,
												  PER_EDO_CIVIL = ?,
												  PER_DIRECCION = ?,
												  PER_NUM = ?,
												  PER_COLONIA = ?,
												  PER_COLONIA_OTRA = ?,
												  PER_MUNICIPIO = ?,
												  PER_ESTADO = ?,
												  PER_CP = ?,
												  ANTIGUEDAD_DIRECCION = ?,
												  PER_EMAIL = ?,
												  PER_TELEFONO = ?,
												  PER_CELULAR = ?,
												  MONTO_SOLICITADO = ?,
												  MAXIMO_PAGAR = ?,
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
												  ACT_ESTABLECIMIENTO = ?,
												  ACT_DIRECCION = ?,
												  ACT_NUM_TRABAJADORES = ?,
												  ACT_VENTAS = ?,
												  INGRESO_SEMANAL = ?,
												  INGRESO_ADICIONAL_1 = ?,
												  INGRESO_MONTO_1 = ?,
												  INGRESO_ADICIONAL_2 = ?,
												  INGRESO_MONTO_2 = ?,
												  INGRESO_ADICIONAL_3 = ?,
												  INGRESO_MONTO_3 = ?,
												  VIVIENDA = ?,
												  VIVIENDA_GASTO = ?,
												  VIVIENDA_NOMBRE = ?,
												  VIVIENDA_NUM_HABITACIONES = ?,
												  VIVIENDA_NUM_AUTOS = ?,
												  PRESTAMO_OTRO_1 = ?,
												  PRESTAMO_PAGO_1 = ?,
												  PRESTAMO_OTRO_2 = ?,
												  PRESTAMO_PAGO_2 = ?,
												  REFERENCIA_NOMBRE_1 = ?,
												  REFERENCIA_RELACION_1 = ?,
												  REFERENCIA_TELEFONO_1 = ?,
												  REFERENCIA_CLIENTE_1 = ?,
												  REFERENCIA_NOMBRE_2 = ?,
												  REFERENCIA_RELACION_2 = ?,
												  REFERENCIA_TELEFONO_2 = ?,
												  REFERENCIA_CLIENTE_2 = ?,
												  REFERENCIA_NOMBRE_3 = ?,
												  REFERENCIA_RELACION_3 = ?,
												  REFERENCIA_TELEFONO_3 = ?,
												  REFERENCIA_CLIENTE_3 = ?,
												  REFERENCIA_NOMBRE_4 = ?,
												  REFERENCIA_RELACION_4 = ?,
												  REFERENCIA_TELEFONO_4 = ?,
												  REFERENCIA_CLIENTE_4 = ?,
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
												  IFE_NUM = ?,
												  PER_RFC = ?,
												  PER_CURP = ?,
												  STATUS = ?,
												  RAZON_RECHAZO = ?,
												  EGR_LUZ = ?,
												  EGR_AGUA = ?,
												  EGR_GAS = ?,
												  EGR_TRANSPORTE = ?,
												  EGR_ALIMENTOS = ?,
												  EGR_CELULAR = ?,
												  EGR_RECREACION = ?,
												  JSUB_RIESGO =?,
												  JSUB_HONESTIDAD =?,
												  JSUB_CALIDAD_REF =?,
												  JSUB_HABILIDADES_EMPR =?,
												  JSUB_CALIDAD_NEG =?,
												  JSUB_ENTENDIMIENTO_CRED =?,
												  JSUB_INVERS_REC =?,
												  JSUB_ENTENDIMIENTO_TASAS =?,
												  JSUB_APOYO_FAM =?,
												  JSUB_APARIENCIA_CASA =?
							WHERE PER_ID = ?";
					$values = array($fecha,
									$_POST['nombre'],
									$_POST['apellido_pat'],
									$_POST['apellido_mat'],
									$fecha_nac,
									$_POST['genero'],
									$_POST['escolaridad'],
									$_POST['otro_escolaridad'],
									$_POST['edo_civil'],
									$_POST['direccion'],
									$_POST['numero'],
									$_POST['colonia'],
									$_POST['otra_colonia'],
									$_POST['municipio'],
									$_POST['estado'],
									$_POST['cp'],
									$_POST['antiguedad_propiedad'],
									$_POST['email'],
									$_POST['telefono'],
									$_POST['celular'],
									$_POST['monto_solicitado'],
									$max_prestamo,
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
									$_POST['act_establecimiento'],
									$_POST['act_direccion'],
									$_POST['act_num_trabajadores'],
									$_POST['ventas_empresa'],
									$_POST['ingreso_promedio'],
									$_POST['ingreso_adicional_1'],
									$_POST['ingreso_monto_1'],
									$_POST['ingreso_adicional_2'],
									$_POST['ingreso_monto_2'],
									$_POST['ingreso_adicional_3'],
									$_POST['ingreso_monto_3'],
									$vivienda,
									$_POST['vivienda_gasto'],
									$_POST['vivienda_nombre'],
									$_POST['vivienda_num_habitaciones'],
									$_POST['vivienda_num_autos'],
									$_POST['prestamo_otro_1'],
									$_POST['prestamos_pago_1'],
									$_POST['prestamo_otro_2'],
									$_POST['prestamos_pago_2'],
									$_POST['referencia_nombre_1'],
									$_POST['referencia_relacion_1'],
									$_POST['referencia_telefono_1'],
									$referencia_cliente_1,
									$_POST['referencia_nombre_2'],
									$_POST['referencia_relacion_2'],
									$_POST['referencia_telefono_2'],
									$referencia_cliente_2,
									$_POST['referencia_nombre_3'],
									$_POST['referencia_relacion_3'],
									$_POST['referencia_telefono_3'],
									$referencia_cliente_3,
									$_POST['referencia_nombre_4'],
									$_POST['referencia_relacion_4'],
									$_POST['referencia_telefono_4'],
									$referencia_cliente_4,
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
									$_POST['ife_num'],
									$_POST['rfc'],
									$_POST['curp'],
									$rechazar,
									$_POST['razon_rechazo'],
									$_POST['egr_luz'],
									$_POST['egr_agua'],
									$_POST['egr_gas'],
									$_POST['egr_transporte'],
									$_POST['egr_alimentos'],
									$_POST['egr_celular'],
									$_POST['egr_recreacion'],
									$_POST['jsub_riesgo'],
									$_POST['jsub_honestiadad'],
									$_POST['jsub_calidad_ref'],
									$_POST['jsub_habilidad_empr'],
									$_POST['jsub_calidad_neg'],
									$_POST['jsub_entendimiento_cred'],
									$_POST['jsub_inver_rec'],
									$_POST['jsub_entendimiento_tasas'],
									$_POST['jsub_apoyo_fam'],
									$_POST['jsub_apariencia_casa'],
									$_POST['id']);

				} else {
					//INSERT
					$sql = "INSERT INTO PERSONAS (PER_FECHA,
												  PER_NOMBRE,
												  PER_APELLIDO_PAT,
												  PER_APELLIDO_MAT,
												  PER_FECHA_NAC, 
												  PER_GENERO, 
												  PER_ESCOLARIDAD, 
												  PER_ESCOLARIDAD_OTRO,
												  PER_EDO_CIVIL,
												  PER_DIRECCION,
												  PER_NUM,
												  PER_COLONIA,
												  PER_COLONIA_OTRA,
												  PER_MUNICIPIO,
												  PER_ESTADO,
												  PER_CP,
												  ANTIGUEDAD_DIRECCION,
												  PER_EMAIL,
												  PER_TELEFONO,
												  PER_CELULAR,
												  MONTO_SOLICITADO,
												  MAXIMO_PAGAR,
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
												  ACT_ESTABLECIMIENTO,
												  ACT_DIRECCION,
												  ACT_NUM_TRABAJADORES,
												  ACT_VENTAS,
												  INGRESO_SEMANAL,
												  INGRESO_ADICIONAL_1,
												  INGRESO_MONTO_1,
												  INGRESO_ADICIONAL_2,
												  INGRESO_MONTO_2,
												  INGRESO_ADICIONAL_3,
												  INGRESO_MONTO_3,
												  VIVIENDA,
												  VIVIENDA_GASTO,
												  VIVIENDA_NOMBRE,
												  VIVIENDA_NUM_HABITACIONES,
												  VIVIENDA_NUM_AUTOS,
												  PRESTAMO_OTRO_1,
												  PRESTAMO_PAGO_1,
												  PRESTAMO_OTRO_2,
												  PRESTAMO_PAGO_2,
												  REFERENCIA_NOMBRE_1,
												  REFERENCIA_RELACION_1,
												  REFERENCIA_TELEFONO_1,
												  REFERENCIA_CLIENTE_1,
												  REFERENCIA_NOMBRE_2,
												  REFERENCIA_RELACION_2,
												  REFERENCIA_TELEFONO_2,
												  REFERENCIA_CLIENTE_2,
												  REFERENCIA_NOMBRE_3,
												  REFERENCIA_RELACION_3,
												  REFERENCIA_TELEFONO_3,
												  REFERENCIA_CLIENTE_3,
												  REFERENCIA_NOMBRE_4,
												  REFERENCIA_RELACION_4,
												  REFERENCIA_TELEFONO_4,
												  REFERENCIA_CLIENTE_4,
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
												  IFE_NUM,
												  PER_RFC,
												  PER_CURP,
												  STATUS,
												  RAZON_RECHAZO,
												  EGR_LUZ ,
												  EGR_AGUA ,
												  EGR_GAS ,
												  EGR_TRANSPORTE ,
												  EGR_ALIMENTOS ,
												  EGR_CELULAR ,
												  EGR_RECREACION ,
												  JSUB_RIESGO,
												  JSUB_HONESTIDAD,
												  JSUB_CALIDAD_REF,
												  JSUB_HABILIDADES_EMPR,
												  JSUB_CALIDAD_NEG,
												  JSUB_ENTENDIMIENTO_CRED,
												  JSUB_INVERS_REC,
												  JSUB_ENTENDIMIENTO_TASAS,
												  JSUB_APOYO_FAM,
												  JSUB_APARIENCIA_CASA)
												VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
													   ?, ?, ?, ?, ?, ?, ?, ?)";
					$values = array($fecha,
									$_POST['nombre'],
									$_POST['apellido_pat'],
									$_POST['apellido_mat'],
									$fecha_nac,
									$_POST['genero'],
									$_POST['escolaridad'],
									$_POST['otro_escolaridad'],
									$_POST['edo_civil'],
									$_POST['direccion'],
									$_POST['numero'],
									$_POST['colonia'],
									$_POST['otra_colonia'],
									$_POST['municipio'],
									$_POST['estado'],
									$_POST['cp'],
									$_POST['antiguedad_propiedad'],
									$_POST['email'],
									$_POST['telefono'],
									$_POST['celular'],
									$_POST['monto_solicitado'],
									$max_prestamo,
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
									$_POST['act_establecimiento'],
									$_POST['act_direccion'],
									$_POST['act_num_trabajadores'],
									$_POST['ventas_empresa'],
									$_POST['ingreso_promedio'],
									$_POST['ingreso_adicional_1'],
									$_POST['ingreso_monto_1'],
									$_POST['ingreso_adicional_2'],
									$_POST['ingreso_monto_2'],
									$_POST['ingreso_adicional_3'],
									$_POST['ingreso_monto_3'],
									$vivienda,
									$_POST['vivienda_gasto'],
									$_POST['vivienda_nombre'],
									$_POST['vivienda_num_habitaciones'],
									$_POST['vivienda_num_autos'],
									$_POST['prestamo_otro_1'],
									$_POST['prestamos_pago_1'],
									$_POST['prestamo_otro_2'],
									$_POST['prestamos_pago_2'],
									$_POST['referencia_nombre_1'],
									$_POST['referencia_relacion_1'],
									$_POST['referencia_telefono_1'],
									$referencia_cliente_1,
									$_POST['referencia_nombre_2'],
									$_POST['referencia_relacion_2'],
									$_POST['referencia_telefono_2'],
									$referencia_cliente_2,
									$_POST['referencia_nombre_3'],
									$_POST['referencia_relacion_3'],
									$_POST['referencia_telefono_3'],
									$referencia_cliente_3,
									$_POST['referencia_nombre_4'],
									$_POST['referencia_relacion_4'],
									$_POST['referencia_telefono_4'],
									$referencia_cliente_4,
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
									$_POST['ife_num'],
									$_POST['rfc'],
									$_POST['curp'],
									$rechazar,
									$_POST['razon_rechazo'],
									$_POST['egr_luz'],
									$_POST['egr_agua'],
									$_POST['egr_gas'],
									$_POST['egr_transporte'],
									$_POST['egr_alimentos'],
									$_POST['egr_celular'],
									$_POST['egr_recreacion'],
									$_POST['jsub_riesgo'],
									$_POST['jsub_honestiadad'],
									$_POST['jsub_calidad_ref'],
									$_POST['jsub_habilidad_empr'],
									$_POST['jsub_calidad_neg'],
									$_POST['jsub_entendimiento_cred'],
									$_POST['jsub_inver_rec'],
									$_POST['jsub_entendimiento_tasas'],
									$_POST['jsub_apoyo_fam'],
									$_POST['jsub_apariencia_casa']);

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

	function showMunicipios() {
		$municipios = array();
		$term = trim($_GET['term']); //retrieve the search term that autocomplete sends
		try {
			$db = $this->_conexion;
			$sql = "SELECT DISTINCT(PER_MUNICIPIO)  
					FROM PERSONAS 
					WHERE PER_MUNICIPIO LIKE '%".$term."%'";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$municipio['name'] = $row['PER_MUNICIPIO'];
					$municipio['label'] = $row['PER_MUNICIPIO'];
					$municipios[] = $municipio;
				}

			} 
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($municipios);
	}

	function deleteRecord() {
		$json = array();
		$json['error'] = true;
		$json['msg'] = "Experimentamos fallas técnicas.";
		if(isset($_POST['id'])){
			try{
				$consulta = $this->_conexion->prepare("DELETE FROM PERSONAS WHERE PER_ID = :valor");
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if($consulta->rowCount()){
					$json['msg'] = "El Cliente fue eliminado con éxito.";
					$json['error'] = false;
				} else{
					$json['error'] = true;
					$json['msg'] = "El Cliente elegido no pudo ser eliminado.";
				}
			}catch(PDOException $e){
				die($e->getMessage());
			}	
		}

		echo json_encode($json);
	}

	function getMunicipios() {
		$json = array();
		$json["error"] = false;
		$json["select"] = '<select id="municipio" name="municipio" class="form-control">';

		$sql = "SELECT DISTINCT(MUN_MUNICIPIO) FROM MUNICIPIOS ORDER BY MUN_MUNICIPIO ASC";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			if($consulta->rowCount()) {
				foreach ($puntero as $row) {
					$json["select"] .= '<option value="'.$row['MUN_MUNICIPIO'].'" '.(isset($_POST['muni']) ? strcasecmp($_POST['muni'], $row['MUN_MUNICIPIO']) == 0 ? 'selected' : '' : '').' >'.$row['MUN_MUNICIPIO'].'</option>';
				}
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}

		$json["select"] .= '</select>';
		echo json_encode($json);
	}

	function getColonias() {
		$json = array();
		$json["error"] = false;
		$json["select"] = '<select id="colonia" name="colonia" class="form-control">';

		$sql = "SELECT DISTINCT(MUN_COLONIA),
					   MUN_CP 
				FROM MUNICIPIOS 
				WHERE MUN_MUNICIPIO = ?
				ORDER BY MUN_COLONIA ASC";
		$value = array($_POST['municipio'])	;	
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute($value);
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			$selected = false;
			$json['otra'] = false;

			if($consulta->rowCount()) {
				foreach ($puntero as $row) {
					$json["select"] .= '<option value="'.$row['MUN_COLONIA'].'" '.(isset($_POST['colonia']) ? $this->removeAccents($_POST['colonia']) == $this->removeAccents($row['MUN_COLONIA']) ? 'selected' : '' : '').' data-cp="'.$row['MUN_CP'].'">'.$row['MUN_COLONIA'].'</option>';

					if(isset($_POST['colonia']) && $this->removeAccents($_POST['colonia']) == $this->removeAccents($row['MUN_COLONIA'])) {
						$selected = true;
					}

				}
			}

			if(isset($_POST['colonia']) && !$selected) {
				$json['otra'] = true;
			}

			$json["select"] .= '<option value="0"'.(isset($_POST['colonia']) ? $_POST['colonia'] == "0" || !$selected ? 'selected' : '' : '').' >Otra</option>';
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}

		$json["select"] .= '</select>';
		echo json_encode($json);
	}

	function removeAccents($string) {
	    return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'))), ' '));
	}

	function getClientes() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="cliente" name="cliente" class="form-control">
								<option value="0">Seleccione el Cliente</option>';

		$sql = "SELECT PER_ID, CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE
				FROM PERSONAS
				WHERE STATUS != -1
				AND STATUS != 2";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option value="'.$row['PER_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['PER_ID'] ? 'selected' : '' : '').' >'.$row['PER_NOMBRE'].'</option>';
			}
			
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
		case "searchClient":
			$libs->searchClient();
			break;
		case "showRecord":
			$libs->showRecord();
			break;	
		case "getActivities":
			$libs->getActivities();
			break;	
		case "getGrupos":
			$libs->getGrupos();
			break;	
		case "saveMaximo":
			$libs->saveMaximo();
			break;
		case "displayClients":
			$libs->displayClients();
			break;
		case "saveRecord":
			$libs->saveRecord();
			break;	
		case "showMunicipios":
			$libs->showMunicipios();
			break;	
		case "deleteRecord":
			$libs->deleteRecord();
			break;		
		case "getMunicipios":
			$libs->getMunicipios();
			break;
		case "getColonias":
			$libs->getColonias();
			break;
		case "getClientes":
			$libs->getClientes();
			break;	

	}
}

?>