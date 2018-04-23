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

	function last_monday($date) {
		if (!is_numeric($date))
		    $date = strtotime($date);
		if (date('w', $date) == 1)
		    return $date;
		elseif (date('w', $date) == 0 || date('w', $date) == 7)
		    return strtotime(
		        'next monday',
		         $date
		    );
		else
		    return strtotime(
		        'last monday',
		         $date
		    );
	}


	function getPagosInd() {
		$json = array();
		$json['pagos'] = "";

		if(isset($_POST['id'])) {
			$db = $this->_conexion;
			$sql = "SELECT * FROM TABLA_PAGOS_IND
					WHERE CRE_ID = ?";
			$values = array($_POST['id']);
			$consulta =  $db->prepare($sql);
			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				if ($consulta->rowCount() > 0) {

					$json['pagos'] = '<form id="form-pago">
									<table class="table bordered">
										<thead>
											<tr>
												<th align="center">#</th>
												<th align="center">Fecha</th>
												<th align="center">Monto a Pagar</th>
												<th align="center">Pago Efectuado</th>
												<th align="center">Ahorro</th>
												<th align="center">Monto Total Pagado</th>
												<th align="center">Cantidad Faltante</th>
												<th align="center">Comentarios</th>
											<tr>
										</thead>
										<tbody>';
					$num = 0;
					$total_pagos = 0;
					$total_ahorro = 0;
					foreach ($result as $row) {
						$num++;
						$json['pagos'] .= '<tr>
												<td align="center">
													'.$num.'
												</td>
												<td align="center">
													'.date("d/m/Y", strtotime($row['TPI_FECHA'])).'
												</td>
												<td align="center">
													'.number_format($row['TPI_MONTO'], 2).'
													<input type="hidden" name="tpi_monto['.$row['TPI_ID'].']" id="tpi_monto_'.$row['TPI_ID'].'" value="'.$row['TPI_MONTO'].'" data-id="'.$row['TPI_ID'].'">
												</td>
												<td align="center">
													<input type="text" class="form-control pago" name="tpi_efectuado['.$row['TPI_ID'].']" id="tpi_efectuado_'.$row['TPI_ID'].'" value="'.$row['TPI_EFECTUADO'].'" data-id="'.$row['TPI_ID'].'">
												</td>
												<td align="center">
													<input type="text" class="form-control ahorro" name="tpi_ahorro['.$row['TPI_ID'].']" id="tpi_ahorro_'.$row['TPI_ID'].'" value="'.$row['TPI_AHORRO'].'" data-id="'.$row['TPI_ID'].'">
												</td>
												<td align="center">
													<span id="tpi_pagado_span_'.$row['TPI_ID'].'">'.number_format($row['TPI_PAGADO'], 2).'</span>
													<input type="hidden" name="tpi_pagado['.$row['TPI_ID'].']" id="tpi_pagado_'.$row['TPI_ID'].'" value="'.$row['TPI_PAGADO'].'" data-id="'.$row['TPI_ID'].'">
												</td>
												<td align="center">
													<span id="tpi_faltante_span_'.$row['TPI_ID'].'">'.number_format($row['TPI_FALTANTE'], 2).'</span>
													<input type="hidden" name="tpi_faltante['.$row['TPI_ID'].']" id="tpi_faltante_'.$row['TPI_ID'].'" value="'.$row['TPI_FALTANTE'].'" data-id="'.$row['TPI_ID'].'">
												</td>
												<td align="center">
													<textarea class="form-control" id="comentarios_'.$row['TPI_ID'].'" name="comentarios['.$row['TPI_ID'].']" data-id="'.$row['TPI_ID'].'">'.$row['TPI_COMMENT'].'</textarea>
												</td>
											</tr>';

						$total_pagos += $row['TPI_EFECTUADO'];
						$total_ahorro += $row['TPI_AHORRO'];						
					}

					$json['pagos'].='</tbody>
									<tfoot>
										<tr>
											<td colspan="3">Total</td>
											<td align="center">'.(number_format($total_pagos, 2)).'</td>
											<td align="center">'.(number_format($total_ahorro, 2)).'</td>
											<td colspan="3"></td>
										</tr>
									</tfoot>
									</table>

										<a class="guardar pull-left" href="#" data-id="">
											<button type="button" class="btn btn-info">
											<i class="fa fa-save"></i>Guardar</button>
										</a>

									</form>';
				}

			} catch (PDOException $e) {
				die($e->getMessage());
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}


		}

		echo json_encode($json);
	}

	function registrarInd2() {
		$fecha = date("Y-m-d");
		$inicio_str = $this->last_monday($fecha);
		$fecha_inicio = date("Y-m-d", $inicio_str);
		$sql = "UPDATE TABLA_PAGOS_IND SET TPI_FALTANTE = TPI_MONTO
			    WHERE TPI_FECHA >= ? ";
		$values = array($fecha_inicio);	
		$consulta = $this->_conexion->prepare($sql);
		try {
			$consulta->execute($values);
			/*$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $row) {
				$sql_up = "UPDATE TP SET TP.TP_FALTANTE = TP.TP_MONTO
						   FROM TABLA_PAGOS
						   WHERE TP_FECHA >= ? ";
			}*/

		} catch (PDOException $e) {
		 	die($e->getMessage());
		}
	}

	function showClients() {
		$personas = array();
		$term = trim($_GET['term']); //retrieve the search term that autocomplete sends

		$fecha_hoy = date("Y-m-d");
		$fecha_str = strtotime($fecha_hoy);
		$fin_str = strtotime('next sunday', $fecha_str);
		$fecha = date("Y-m-d", $fin_str);

		try {
			$db = $this->_conexion;
			$sql = "SELECT CREDITO_INDIVIDUAL.CRE_ID,
						   PERSONAS.PER_ID,
						   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
						   PER_DIRECCION,
						   PER_NUM,
						   PER_COLONIA,
						   PER_CELULAR,
						   MONTO_SOLICITADO
					FROM PERSONAS 
                    JOIN CREDITO_INDIVIDUAL ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
					WHERE (STATUS != -1
					AND STATUS != 2)
					AND PER_NOMBRE LIKE '%".$term."%'
                    AND CRE_FECHA_FINAL > '".$fecha."'
                    GROUP BY PER_ID";
			$consulta = $db->prepare($sql);
			$consulta->bindParam(':valor', $term);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {
					$persona['id'] = $row['PER_ID'];
					$persona['name'] = $row['PER_NOMBRE'];
					$persona['value'] = $row['PER_NOMBRE'];
					$persona['credito'] = $row['CRE_ID'];
					$personas[] = $persona;
				}

			} 
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($personas);
	}

	function savePagosInd() {
		$json = array();
		$json['msg'] = "Se guardó con éxito.";

		$db = $this->_conexion;
		$db->beginTransaction(); 
		$sql = "UPDATE PAGOS_INDIVIDUALES SET PI_PAGO = ?,
											   PI_AHORRO = ?,
											   PI_PENDIENTE = ?
			    WHERE PI_ID = ? ";

		foreach ($_POST['tpi_monto'] as $id => $value) {
			$values = array($_POST['tpi_efectuado'][$id],
							$_POST['tpi_ahorro'][$id],
							$_POST['tpi_faltante'][$id],
							$id);

			$consulta = $db->prepare($sql);

			try {
				$consulta->execute($values);

			} catch (PDOException $e) {
				$db->rollBack();
			 	die($e->getMessage());
			}

		}

		$db->commit();

		echo json_encode($json);
	}

	function getClientes() {
		$json = array();

		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="cliente" name="cliente" class="form-control">
							<option value="-1">SELECCIONE EL CLIENTE</option>';

		$sql = "SELECT PER_ID, CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE
				FROM PERSONAS
				WHERE STATUS != -1 AND STATUS != 2
				ORDER BY PER_NOMBRE";
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

	function getGrupos() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";

		if(isset($_POST['cliente'])) {
			$sql = "SELECT GRU_ID
					FROM PERSONAS_GRUPOS
					WHERE PER_ID = ?";
			$values = array($_POST['cliente']);	
			$consulta = $this->_conexion->prepare($sql);

			try {
				$consulta->execute($values);
				$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

				$json["select"] = '<select id="grupo" name="cliente" class="form-control">
										<option value="-1">SELECCIONE EL GRUPO</option>';

				foreach ($puntero as $row) {
					$json["select"] .= '<option value="'.$row['GRU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['GRU_ID'] ? 'selected' : '' : '').' >GRUPO '.$row['GRU_ID'].'</option>';
				}
				
			} catch(PDOException $e) {
				die($e->getMessage());
				$json["error"] = true;
			}


			$json["select"] .= '</select>';
		} else {
			$json['error'] = true;
		}

		echo json_encode($json);
	}

	function getPagosIndividuales() {
		$json = array();
		$json['pagos'] = "";

		if(isset($_POST['grupo'])) {
			$db = $this->_conexion;
			$sql = "SELECT * FROM PAGOS_INDIVIDUALES
					WHERE GRU_ID = ?
					AND PER_ID = ?";
			$values = array($_POST['grupo'],
							$_POST['cliente']);
			$consulta =  $db->prepare($sql);
			try {
				$consulta->execute($values);
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				if ($consulta->rowCount() > 0) {

					$json['pagos'] = '<form id="form-pago">
									<table class="table bordered">
										<thead>
											<tr>
												<th align="center">#</th>
												<th align="center">Fecha</th>
												<th align="center">Monto a Pagar</th>
												<th align="center">Pago Efectuado</th>
												<th align="center">Ahorro</th>
												<th align="center">Monto Total Pagado</th>
												<th align="center">Cantidad Faltante</th>
												<th align="center">Comentarios</th>
											<tr>
										</thead>
										<tbody>';
					$num = 0;
					$total_pagos = 0;
					$total_ahorro = 0;
					foreach ($result as $row) {
						$num++;
						$json['pagos'] .= '<tr>
												<td align="center">
													'.$num.'
												</td>
												<td align="center">
													'.date("d/m/Y", strtotime($row['PI_FECHA'])).'
												</td>
												<td align="center">
													'.number_format($row['PI_MONTO'], 2).'
													<input type="hidden" name="tpi_monto['.$row['PI_ID'].']" id="tpi_monto_'.$row['PI_ID'].'" value="'.$row['PI_MONTO'].'" data-id="'.$row['PI_ID'].'">
												</td>
												<td align="center">
													<input type="text" class="form-control pago" name="tpi_efectuado['.$row['PI_ID'].']" id="tpi_efectuado_'.$row['PI_ID'].'" value="'.$row['PI_PAGO'].'" data-id="'.$row['PI_ID'].'">
												</td>
												<td align="center">
													<input type="text" class="form-control ahorro" name="tpi_ahorro['.$row['PI_ID'].']" id="tpi_ahorro_'.$row['PI_ID'].'" value="'.$row['PI_AHORRO'].'" data-id="'.$row['PI_ID'].'">
												</td>
												<td align="center">
													<span id="tpi_pagado_span_'.$row['PI_ID'].'">'.number_format($row['PI_PAGO'], 2).'</span>
													<input type="hidden" name="tpi_pagado['.$row['PI_ID'].']" id="tpi_pagado_'.$row['PI_ID'].'" value="'.$row['PI_PAGO'].'" data-id="'.$row['PI_ID'].'">
												</td>
												<td align="center">
													<span id="tpi_faltante_span_'.$row['PI_ID'].'">'.number_format($row['PI_PENDIENTE'], 2).'</span>
													<input type="hidden" name="tpi_faltante['.$row['PI_ID'].']" id="tpi_faltante_'.$row['PI_ID'].'" value="'.$row['PI_PENDIENTE'].'" data-id="'.$row['PI_ID'].'">
												</td>
												<td align="center">
													<textarea class="form-control" id="comentarios_'.$row['PI_ID'].'" name="comentarios['.$row['PI_ID'].']" data-id="'.$row['PI_ID'].'">'.$row['PI_COMMENT'].'</textarea>
												</td>
											</tr>';

						$total_pagos += $row['PI_PAGO'];
						$total_ahorro += $row['PI_AHORRO'];	
					}

					$json['pagos'].='</tbody>
									<tfoot>
										<tr>
											<td colspan="3" align="center"><b>Total</b></td>
											<td align="center"><b>'.(number_format($total_pagos, 2)).'</b></td>
											<td align="center"><b>'.(number_format($total_ahorro, 2)).'</b></td>
											<td colspan="3"></td>
										</tr>
									</tfoot>
									</table>

										<a class="guardar pull-left" href="#" data-id="">
											<button type="button" class="btn btn-info">
											<i class="fa fa-save"></i>Guardar</button>
										</a>

									</form>';
				}

			} catch (PDOException $e) {
				die($e->getMessage());
				$json['msg'] = "Experimentamos Fallas Técnicas. Comuníquese con su proveedor.";
			}


		}

		echo json_encode($json);
	}

	
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "getPagosInd":
			$libs->getPagosInd();
			break;	
		case "registrarInd2":
			$libs->registrarInd2();
			break;
		case "showClients":
			$libs->showClients();
			break;	
		case "savePagosInd":
			$libs->savePagosInd();
			break;
		case "getClientes":
			$libs->getClientes();
			break;	
		case "getGrupos":
			$libs->getGrupos();
			break;	
		case "getPagosIndividuales":
			$libs->getPagosIndividuales();
			break;													
	}
}

?>