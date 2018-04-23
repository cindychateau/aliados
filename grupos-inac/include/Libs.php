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

$module = 36;

class Libs extends Common {

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2014-11-28
	 * 
	 * @param '$id'		int. 	ID de Cliente
	 * 
	 * Metodo que regresa los datos de Cliente en base a su id
	 */
	function showRecord() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["tabla"] = "";

		if(isset($_POST['id'])){
			try {
				$db = $this->_conexion;
				$sql = "SELECT * FROM GRUPOS WHERE GRU_ID = :valor";
				$consulta = $db->prepare($sql);
				$consulta->bindParam(':valor', $_POST['id']);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$puntero = $consulta->fetch(PDO::FETCH_ASSOC);
					$json = array_merge($json, $puntero);
					$json['GRU_FECHA'] = date("d/m/Y",strtotime($puntero['GRU_FECHA']));
					$json['GRU_FECHA_INICIAL'] = date("d/m/Y",strtotime($puntero['GRU_FECHA_INICIAL']));
					$json['GRU_FECHA_ENTREGA'] = date("d/m/Y",strtotime($puntero['GRU_FECHA_ENTREGA']));
					$json['GRU_TASA'] = $puntero['GRU_TASA'] * 100;
					$json['GRU_AHORRO_P'] = $puntero['GRU_AHORRO_P'] * 100;
					$json['GRU_COMISION_P'] = $puntero['GRU_COMISION_P'] * 100;

					$sql_cli = "SELECT PERSONAS.PER_ID,
								   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
								   PER_DIRECCION,
								   PER_TELEFONO,
								   MONTO_SOLICITADO,
								   MONTO_INDIVIDUAL,
								   AHORRO_D,
								   MONTO_OTORGAR,
								   COMAP_D,
								   PAGO_SEMANAL_IND
							FROM PERSONAS
							JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
							WHERE GRU_ID = :grupo";
					$consulta_cli = $db->prepare($sql_cli);
					$consulta_cli->bindParam(':grupo', $_POST['id']);
					try {
						$consulta_cli->execute();	
						$puntero = $consulta_cli->fetchAll(PDO::FETCH_ASSOC);
						$num = 0;
						foreach ($puntero as $row) {
							$num++;
							$json['ids_clientes'][] = $row['PER_ID'];
							$json["tabla"] .= '<tr id="row_'.$num.'">
												<td align="center">
													'.$row['PER_NOMBRE'].'
													<input type="text" id="cli_id_'.$num.'" name="cli_id['.$num.']" data-id="'.$num.'" value="'.$row['PER_ID'].'" style="display:none;">
												</td>
												<td align="center">
													'.$row['PER_DIRECCION'].'
												</td>
												<td align="center">
													'.$row['PER_TELEFONO'].'
												</td>
												<td align="center">
													'.$row['MONTO_SOLICITADO'].'
												</td>
												<td align="center">
													<input id="monto_individual_'.$num.'" name="monto_individual['.$num.']" type="text" class="form-control monto_individual" data-id="'.$num.'" value="'.$row['MONTO_INDIVIDUAL'].'">
												</td>
												<td align="center">
													<input id="ahorro_d_'.$num.'" name="ahorro_d['.$num.']" type="text" class="form-control ahorro_d" data-id="'.$num.'" readonly="readonly" value="'.$row['AHORRO_D'].'">
												</td>
												<td align="center">
													<input id="comision_d_'.$num.'" name="comision_d['.$num.']" type="text" class="form-control comision_d" data-id="'.$num.'" readonly="readonly" value="'.$row['COMAP_D'].'">
												</td>
												<td align="center">
													<input id="monto_otorgar_'.$num.'" name="monto_otorgar['.$num.']" type="text" class="form-control monto_otorgar" data-id="'.$num.'" readonly="readonly" value="'.$row['MONTO_OTORGAR'].'">
												</td>
												<td align="center">
													<input id="pago_semanal_'.$num.'" name="pago_semanal['.$num.']" type="text" class="form-control pago_semanal" data-id="'.$num.'" readonly="readonly" value="'.$row['PAGO_SEMANAL_IND'].'">
												</td>
												<td align="center" class="cont-button">
													<a class="eliminar-cl" href="#" data-id="'.$num.'" ><button class="btn btn-danger"><i class="fa fa-minus"></i></button></a>
												</td>
											</tr>';
						} 
					} catch (PDOException $e) {
						die($e->getMessage().$dbgMsg);
					}	

				} else {
					$json['error'] = true;
				} 
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		} else {
			$json['error'] = true;
		}

		echo json_encode($json);

	}

	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-02
	 * 
	 * Impresión de los Grupos en página de inicio
	 */
	function printGroups() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";		

		global $module;

		try {
			$db = $this->_conexion;
			$sql = "SELECT GRU_ID,
						   GRU_FECHA,
						   GRU_MONTO_TOTAL,
						   GRU_PLAZO,
						   GRU_TASA,
						   SIU_NOMBRE,
						   GRUPOS.SIU_ID,
						   GRU_RECREDITO,
						   GRU_REESTRUCTURA
					FROM GRUPOS 
					JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
					WHERE GRU_VIGENTE = 0
					ORDER BY GRU_FECHA DESC, GRU_ID DESC";
			$consulta = $db->prepare($sql);
			$consulta->execute();
			if ($consulta->rowCount() > 0){
				$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $row) {

					$editar = '<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-pencil"></i>
													</a>';

					$json['content'] .= '<div class="col-md-6">
										<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
											<div class="box-title">
												<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
												<div class="tools">
													<a href="javascript:;" class="expand">
														<i class="fa fa-chevron-down"></i>
													</a>
													<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
														<i class="fa fa-file-text-o"></i>
													</a>
													'.$this->printLink($module, "cambios", $editar).'
												</div>
											</div>
											<div class="box-body" style="display:none;">
												
												<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
													<tbody>
														<tr>
															<td align="center"><b>Fecha</b></td>
															<td align="center">'.date("d/m/Y",strtotime($row["GRU_FECHA"])).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Monto</b></td>
															<td align="center">$'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
														</tr>
													  	<tr>
															<td align="center"><b>Plazo</b></td>
															<td align="center">'.$row['GRU_PLAZO'].'</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Tasa</b></td>
															<td align="center">'.($row['GRU_TASA']*100).'%</td>
													  	</tr>
													  	<tr>
															<td align="center"><b>Promotor</b></td>
															<td align="center"><!--a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a-->'.$row['SIU_NOMBRE'].'</td>
													  	</tr>
													  	'.($row['GRU_RECREDITO'] != 0 ?

													  		'<tr>
																<td align="center"><b>Recrédito</b></td>
																<td align="center">'.$row['GRU_RECREDITO'].'</td>
														  	</tr>'


													  		: '').'
													</tbody>
												  </table>
												  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
													<thead>
														<tr>
															<th>#</th>
															<th>Acreditado</th>
															<th>Celular</th>
															<th>Préstamo Otorgado</th>
															<th>Pago Sem.</th>
														</tr>
													</thead>
													<tbody>';

					$num = 1;
					$sql_per = "SELECT PERSONAS.PER_ID,
									   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
									   STATUS,
									   MONTO_INDIVIDUAL,
									   PAGO_SEMANAL_IND,
									   PER_CELULAR
								FROM PERSONAS
								JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
								WHERE PERSONAS_GRUPOS.GRU_ID = :valor";
					$consulta_per = $db->prepare($sql_per);
					$consulta_per->bindParam(':valor', $row['GRU_ID']);
					$consulta_per->execute();
					$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result_per as $per) {
						$json['content'] .= '<tr>
												<td align="center">'.$num.'</td>
												<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'">'.$per['PER_NOMBRE'].'</a></td>
												<td align="center">'.$per['PER_CELULAR'].'</td>
												<td align="center">$'.$per['MONTO_INDIVIDUAL'].'</td>
												<td align="center">$'.$per['PAGO_SEMANAL_IND'].'</td>
											</tr>';
						$num++;
					}

					$json['content'] .='			</tbody>
												  </table>
											</div>
										</div>
										</div>';
				}

			} else {
				$json['content'] = "<h1 style='text-align:center;'>No se encuentran grupos vigentes</h1>";
			}
			
		} catch (PDOException $e) {
			die($e->getMessage().$dbgMsg);
			
		}

		echo json_encode($json);
	}
	
	/*
	 * @author: Cynthia Castillo 
	 * @version: 0.1 2016-02-03
	 * 
	 * Select de Promotores
	 */
	function getPromotores() {
		$json = array();
		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json["select"] = '<select id="promotor" name="promotor" class="form-control">';

		$sql = "SELECT SIU_ID, SIU_NOMBRE, SIU_DIRECCION 
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option data-dir="'.$row['SIU_DIRECCION'].'" value="'.$row['SIU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SIU_ID'] ? 'selected' : '' : '').' >'.$row['SIU_NOMBRE'].'</option>';
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
								<option value="0">Seleccione el Promotor</option>';

		$sql = "SELECT SIU_ID, SIU_NOMBRE, SIU_DIRECCION 
				FROM SISTEMA_USUARIO
				WHERE SUP_ID = 3";
		$consulta = $this->_conexion->prepare($sql);

		try {
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			foreach ($puntero as $row) {
				$json["select"] .= '<option data-dir="'.$row['SIU_DIRECCION'].'" value="'.$row['SIU_ID'].'" '.(isset($_POST['id']) ? $_POST['id'] == $row['SIU_ID'] ? 'selected' : '' : '').' >'.$row['SIU_NOMBRE'].'</option>';
			}
			
		} catch(PDOException $e) {
			die($e->getMessage());
			$json["error"] = true;
		}


		$json["select"] .= '</select>';



		echo json_encode($json);
	}

	function filterGroups() {
		$json = array();

		global $module;

		$json['error'] = false;
		$json['msg'] = "Experimentamos fallas técnicas. Comuníquese con su proveedor.";
		$json['content'] = "";		


		$promotor = "";
		if($_POST['promotor'] != 0) {
			$promotor = " AND SISTEMA_USUARIO.SIU_ID = ".$_POST['promotor']." ";
		}

		$fechas = "";
		if($_POST['fecha_1'] != '' && $_POST['fecha_2'] != '') {
			//Cambio de Formato
			$fecha_1 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_1'])));
			$fecha_2 = date("Y-m-d",strtotime(str_replace("/", "-", $_POST['fecha_2'])));

			$fechas = " AND GRU_FECHA_ENTREGA >= '".$fecha_1."' AND GRU_FECHA_ENTREGA <= '".$fecha_2."' ";
			$fechas_2 = " AND CRE_FECHA_ENTREGA >= '".$fecha_1."' AND CRE_FECHA_ENTREGA <= '".$fecha_2."' ";
		}



		if($_POST['tipo'] == 1 || $_POST['tipo'] == 2 || $_POST['tipo'] == 0 || $_POST['tipo'] == 3) {
			try {

				$tipo = "";
				if($_POST['tipo'] == 1) {
					$tipo = " AND GRU_RECREDITO = 0 AND GRU_REESTRUCTURA = 0 ";
				} else if($_POST['tipo'] == 2) {
					$tipo = " AND GRU_RECREDITO != 0 ";
				} else if($_POST['tipo'] == 3) {
					$tipo = " AND GRU_REESTRUCTURA = 1 ";
				}


				$db = $this->_conexion;
				$sql = "SELECT GRU_ID,
							   GRU_FECHA_ENTREGA,
							   GRU_MONTO_TOTAL,
							   GRU_PLAZO,
							   GRU_TASA,
							   SIU_NOMBRE,
							   GRUPOS.SIU_ID,
							   GRU_RECREDITO,
							   GRU_REESTRUCTURA
						FROM GRUPOS 
						JOIN SISTEMA_USUARIO ON GRUPOS.SIU_ID = SISTEMA_USUARIO.SIU_ID
						WHERE GRU_VIGENTE = 0".$promotor." ".$tipo." ".$fechas."
						ORDER BY GRU_FECHA DESC, GRU_ID DESC";
				//die($sql);		
				$consulta = $db->prepare($sql);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {

						$editar = '<a href="cambios.php?id='.$row['GRU_ID'].'" class="edit" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-pencil"></i>
														</a>';

						$json['content'] .= '<div class="col-md-6">
											<div class="box border '.($row['GRU_REESTRUCTURA'] == 1 ? 'orange' : ($row['GRU_RECREDITO'] != 0 ? 'purple' : 'primary')).'">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Grupo '.$row['GRU_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato.php?id='.$row['GRU_ID'].'" class="contrato" data-id="'.$row['GRU_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														'.$this->printLink($module, "cambios", $editar).'
													</div>
												</div>
												<div class="box-body" style="display:none;">
													
													<table class="table table-striped general-info" data-id="'.$row['GRU_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha Entrega</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["GRU_FECHA_ENTREGA"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Monto</b></td>
																<td align="center">$'.number_format($row['GRU_MONTO_TOTAL'], 2).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Plazo</b></td>
																<td align="center">'.$row['GRU_PLAZO'].'</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Tasa</b></td>
																<td align="center">'.($row['GRU_TASA']*100).'%</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Promotor</b></td>
																<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
														  	</tr>
														  	'.($row['GRU_RECREDITO'] != 0 ?

														  		'<tr>
																	<td align="center"><b>Recrédito</b></td>
																	<td align="center">'.$row['GRU_RECREDITO'].'</td>
															  	</tr>'


														  		: '').'
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['GRU_ID'].'">
														<thead>
															<tr>
																<th>#</th>
																<th>Acreditado</th>
																<th>Celular</th>
																<th>Préstamo Otorgado</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>';

						$num = 1;
						$sql_per = "SELECT PERSONAS.PER_ID,
										   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
										   STATUS,
										   MONTO_INDIVIDUAL,
										   PAGO_SEMANAL_IND,
										   PER_CELULAR
									FROM PERSONAS
									JOIN PERSONAS_GRUPOS ON PERSONAS.PER_ID = PERSONAS_GRUPOS.PER_ID
									WHERE PERSONAS_GRUPOS.GRU_ID = :valor";
						$consulta_per = $db->prepare($sql_per);
						$consulta_per->bindParam(':valor', $row['GRU_ID']);
						$consulta_per->execute();
						$result_per = $consulta_per->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result_per as $per) {
							$json['content'] .= '<tr>
													<td align="center">'.$num.'</td>
													<td align="center"><a data-id="'.$per['PER_ID'].'" href="../prospectos/cambios.php?id='.$per['PER_ID'].'&status='.$per['STATUS'].'">'.$per['PER_NOMBRE'].'</a></td>
													<td align="center">'.$per['PER_CELULAR'].'</td>
													<td align="center">$'.$per['MONTO_INDIVIDUAL'].'</td>
													<td align="center">$'.$per['PAGO_SEMANAL_IND'].'</td>
												</tr>';
							$num++;
						}

						$json['content'] .='			</tbody>
													  </table>
												</div>
											</div>
											</div>';
					}

				} else {
					$json['content'] = "<h1 style='text-align:center;'>No se encuentran grupos vigentes</h1>";
				}
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		}

		if($_POST['tipo'] == 3 || $_POST['tipo'] == 0) {
			//INDIVIDUALES
			try {
				$db = $this->_conexion;
				$sql = "SELECT CRE_ID,
							   CRE_FECHA,
							   CRE_MONTO_TOTAL,
							   CRE_PLAZO,
							   CRE_TASA,
							   SIU_NOMBRE,
							   CREDITO_INDIVIDUAL.PER_ID,
							   CONCAT(PER_NOMBRE, ' ', PER_APELLIDO_PAT, ' ', PER_APELLIDO_MAT) as PER_NOMBRE,
							   STATUS,
							   CRE_PAGO_SEMANAL,
							   CREDITO_INDIVIDUAL.SIU_ID,
							   PER_CELULAR
						FROM CREDITO_INDIVIDUAL 
						JOIN SISTEMA_USUARIO ON CREDITO_INDIVIDUAL.SIU_ID = SISTEMA_USUARIO.SIU_ID
						JOIN PERSONAS ON CREDITO_INDIVIDUAL.PER_ID = PERSONAS.PER_ID
						WHERE CRE_VIGENTE = 0 ".$promotor." ".$fechas_2."
						ORDER BY CRE_FECHA DESC, CRE_ID DESC";
				//die($sql);			
				$consulta = $db->prepare($sql);
				$consulta->execute();
				if ($consulta->rowCount() > 0){
					$result = $consulta->fetchAll(PDO::FETCH_ASSOC);
					foreach ($result as $row) {

						$editar = '<a href="cambios.php?id='.$row['CRE_ID'].'" class="edit" data-id="'.$row['CRE_ID'].'">
															<i class="fa fa-pencil"></i>
														</a>';

						$json['content'] .= '<div class="col-md-6">
											<div class="box border blue">
												<div class="box-title">
													<h4><i class="fa fa-group"></i>Individual '.$row['CRE_ID'].'</h4>
													<div class="tools">
														<a href="javascript:;" class="expand">
															<i class="fa fa-chevron-down"></i>
														</a>
														<a target="_blank" href="include/contrato-ind.php?id='.$row['CRE_ID'].'" class="contrato" data-id="'.$row['CRE_ID'].'">
															<i class="fa fa-file-text-o"></i>
														</a>
														'.$this->printLink($module, "cambios", $editar).'
													</div>
												</div>
												<div class="box-body" style="display:none;">
													
													<table class="table table-striped general-info" data-id="'.$row['CRE_ID'].'">
														<tbody>
															<tr>
																<td align="center"><b>Fecha</b></td>
																<td align="center">'.date("d/m/Y",strtotime($row["CRE_FECHA"])).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Monto</b></td>
																<td align="center">$'.number_format($row['CRE_MONTO_TOTAL'], 2).'</td>
															</tr>
														  	<tr>
																<td align="center"><b>Plazo</b></td>
																<td align="center">'.$row['CRE_PLAZO'].'</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Tasa</b></td>
																<td align="center">'.($row['CRE_TASA']*100).'%</td>
														  	</tr>
														  	<tr>
																<td align="center"><b>Promotor</b></td>
																<td align="center"><a href="grupos.php?id='.$row['SIU_ID'].'">'.$row['SIU_NOMBRE'].'</a></td>
														  	</tr>
														</tbody>
													  </table>
													  <table class="table table-bordered table-striped table-hover acreditados" data-id="'.$row['CRE_ID'].'">
														<thead>
															<tr>
																<th>#</th>
																<th>Acreditado</th>
																<th>Celular</th>
																<th>Pago Sem.</th>
															</tr>
														</thead>
														<tbody>
														<tr>
															<td align="center">1</td>
															<td align="center"><a data-id="'.$row['PER_ID'].'" href="../prospectos/cambios.php?id='.$row['PER_ID'].'&status='.$row['STATUS'].'">'.$row['PER_NOMBRE'].'</a></td>
															<td align="center">'.$row['PER_CELULAR'].'</td>
															<td align="center">$'.$row['CRE_PAGO_SEMANAL'].'</td>
														</tr>';

						$json['content'] .='			</tbody>
													  </table>
												</div>
											</div>
											</div>';
					}

				}
				
			} catch (PDOException $e) {
				die($e->getMessage().$dbgMsg);
				
			}
		}

		echo json_encode($json);
	}
}

if(isset($_REQUEST['accion'])){
	//Se inicializa la clase
	$libs = new Libs;
	switch($_REQUEST['accion']){
		case "printGroups":
			$libs->printGroups();
			break;	
		case "showRecord":
			$libs->showRecord();
			break;	
		case "getPromotores":
			$libs->getPromotores();
			break;	
		case "getPromotores2":
			$libs->getPromotores2();
			break;	
		case "filterGroups":
			$libs->filterGroups();
			break;											
	}
}
?>