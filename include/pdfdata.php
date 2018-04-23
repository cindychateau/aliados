<?php
require_once("Core.php");

class pdfData extends Core
{
   public function getCredito($id_credito) {
      $result = array();
      $db = $this->_conexion;
      $sql = "SELECT CLIENTE.CLI_ID,
                    CLI_NOMBRE,
                    CLI_APELLIDO_PATERNO,
                    CLI_APELLIDO_MATERNO,
                    CONCAT(CLI_CALLE, ' ', CLI_NUMERO, ', ', CLI_COLONIA) AS DIRECCION,
                    CLI_CIUDAD,
                    CLI_ESTADO,
                    CLI_TELEFONO,
                    CLI_MOVIL,
                    CRE_ID,
                    CRE_DATE,
                    CRE_EMPRESA,
                    CRE_TIPO,
                    CRE_FRECUENCIA,
                    CRE_IMPORTE_SOLICITADO,
                    CRE_PLAZO,
                    CRE_TASA,
                    CRE_FECHA_PAGO,
                    CRE_IMPORTE_PAGO,
                    CRE_INTERES_MORATORIO
            FROM CREDITOS
            LEFT JOIN CLIENTE ON CREDITOS.CLI_ID = CLIENTE.CLI_ID
            WHERE CRE_ID = :valor";
      $consulta = $db->prepare($sql);
      $consulta->bindParam(':valor', $id_credito);
      $consulta->execute();

      if ($consulta->rowCount() > 0){
         $row = $consulta->fetch(PDO::FETCH_ASSOC);
         $result['plazo'] = $row['CRE_PLAZO'];
         $result['fecha'] = date("d/m/Y", strtotime($row['CRE_FECHA_PAGO']));
         $result['pago'] = $row["CRE_IMPORTE_PAGO"];
         $result['total'] = $row["CRE_IMPORTE_SOLICITADO"];
         $result['interes'] = $row['CRE_INTERES_MORATORIO']/100;
         $frecuencia = "m";
         switch ($row['CRE_FRECUENCIA']) {
            case 'MENSUAL':
               $frecuencia = "m";
               break;
            case 'QUINCENAL':
               $frecuencia = "q";
               break;
            case 'SEMANAL':
               $frecuencia = "s";
               break;               
         }

         $result['frecuencia'] = $frecuencia; //m = mensual; q = quincenal; s = semanal
         $result['nombre'] = $row["CLI_NOMBRE"]." ".$row["CLI_APELLIDO_PATERNO"]." ".$row["CLI_APELLIDO_MATERNO"];
         $result['domicilio'] = $row['DIRECCION'];
         $result['ciudad'] = $row['CLI_CIUDAD'];
         $result['telefono'] = $row['CLI_TELEFONO'];
         $result['nombre_aval'] = "Katya Maricela Castillo Elizondo";
         $result['domicilio_aval'] = "Calle EspaÑol #249, Privada ViÑedo EspaÑol, Col. Los ViÑedos";
         $result['ciudad_aval'] = "Guadalupe";
         $result['telefono_aval'] = "83172545";
      }

      //3, "2015-02-15", 1720.30, 0.05, "Cynthia Castillo Elizondo", "Puesta de los Encinos #1247, Puesta del Sol", "Guadalupe", "8317-2545", "Katya Castillo Elizondo", "Puesta de los Encinos #1247, Puesta del Sol", "Guadalupe", "8317-2545"
      /*$result['plazo'] = 7;
      $result['fecha'] = "13/02/2015";
      $result['pago'] = 1720.30;
      $result['total'] = 17200.30;
      $result['interes'] = 0.005;
      $result['frecuencia'] = "s"; //m = mensual; q = quincenal; s = semanal
      $result['nombre'] = "Cynthia Castillo Elizondo";
      $result['domicilio'] = "Puesta de los Encinos #1247, Puesta del Sol";
      $result['ciudad'] = "Guadalupe";
      $result['telefono'] = "8317-2545";
      $result['nombre_aval'] = "Katya Maricela Castillo Elizondo";
      $result['domicilio_aval'] = "Calle EspaÑol #249, Privada ViÑedo EspaÑol, Col. Los ViÑedos";
      $result['ciudad_aval'] = "Guadalupe";
      $result['telefono_aval'] = "8317-2545";*/

      return $result;
   }

   public function getResultados($id_credito) {
      $result = array();
      $db = $this->_conexion;
      $sql = "SELECT CLIENTE.CLI_ID,
                    CLI_NOMBRE,
                    CLI_APELLIDO_PATERNO,
                    CLI_APELLIDO_MATERNO,
                    CRE_EMPRESA,
                    CRE_TIPO,
                    CRE_FRECUENCIA,
                    CRE_IMPORTE_SOLICITADO,
                    CRE_COMISION_APERTURA,
                    CRE_CAPITAL,
                    CRE_PLAZO,
                    CRE_TASA,
                    CRE_TASA_GLOBAL,
                    CRE_TOTAL_INTERES,
                    CRE_GASTOS_COBRANZA,
                    CRE_TOTAL_DOCUMENTADO,
                    CRE_IMPORTE_PAGO,
                    CRE_BONIFICACION,
                    CRE_IMPORTE_OPORTUNO
            FROM CREDITOS
            LEFT JOIN CLIENTE ON CREDITOS.CLI_ID = CLIENTE.CLI_ID
            WHERE CRE_ID = :valor";
      $consulta = $db->prepare($sql);
      $consulta->bindParam(':valor', $id_credito);
      $consulta->execute();

      if ($consulta->rowCount() > 0){
         $row = $consulta->fetch(PDO::FETCH_ASSOC);
         $result['nombre'] = $row["CLI_NOMBRE"]." ".$row["CLI_APELLIDO_PATERNO"]." ".$row["CLI_APELLIDO_MATERNO"];
         $result['empresa'] = $row['CRE_EMPRESA'];
         $result['tipo'] = $row['CRE_TIPO'];
         $result['frecuencia'] = $row['CRE_FRECUENCIA'];
         $result['importe_autorizado'] = $row["CRE_IMPORTE_SOLICITADO"];
         $result['comision_apertura'] = $row["CRE_COMISION_APERTURA"];
         $result['capital'] = $row["CRE_CAPITAL"];
         $result['plazo'] = $row['CRE_PLAZO'];
         $result['tasa'] = $row['CRE_TASA'];
         $result['tasa_global'] = $row['CRE_TASA_GLOBAL'];
         $result['total_intereses'] = $row['CRE_TOTAL_INTERES'];
         $result['gastos_cobranza'] = $row['CRE_GASTOS_COBRANZA'];
         $result['total_documentado'] = $row['CRE_TOTAL_DOCUMENTADO'];
         $result['importe_pago'] = $row['CRE_IMPORTE_PAGO'];
         $result['bonificacion'] = $row['CRE_BONIFICACION'];
         $result['pago_oportuno'] = $row['CRE_IMPORTE_OPORTUNO'];         
      }

      return $result;
   }
}

$data = new pdfData();

?>