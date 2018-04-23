<?php
session_start();
$url = explode("/admin", $_SERVER["REQUEST_URI"]);
$url = explode("/", $url[1]);

//$url = explode("/", $_SERVER["REQUEST_URI"]);

$ruta = "";
$file=$url[count($url)-1];
for ($i=1; $i < (count($url) - 1); $i++){
	$ruta .= "../";
}

//Se incluye la clase Common
//include_once($ruta."include/fpdf.php");
include_once($ruta.'include/rotation.php');
include_once("datos-ind.php");


if (isset($_GET['id'])) {

	class PDF extends PDF_Rotate{
		/*Funciones y vars para Tabla*/
		var $widths;
		var $aligns;

		function SetWidths($w)
		{
		    //Set the array of column widths
		    $this->widths=$w;
		}

		function SetAligns($a)
		{
		    //Set the array of column alignments
		    $this->aligns=$a;
		}

		function Row($data)
		{
		    //Calculate the height of the row
		    $nb=0;
		    for($i=0;$i<count($data);$i++)
		        $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		    $h=5*$nb;
		    //Issue a page break first if needed
		    $this->CheckPageBreak($h);
		    //Draw the cells of the row
		    for($i=0;$i<count($data);$i++)
		    {
		        $w=$this->widths[$i];
		        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		        //Save the current position
		        $x=$this->GetX();
		        $y=$this->GetY();
		        //Draw the border
		        //$this->Rect($x,$y,$w,$h);
		        //Print the text
		        $this->MultiCell($w,4,$data[$i],0,$a,0);
		        //Put the position to the right of the cell
		        $this->SetXY($x+$w,$y);
		    }
		    //Go to the next line
		    $this->Ln($h);
		}

		function CheckPageBreak($h)
		{
		    //If the height h would cause an overflow, add a new page immediately
		    if($this->GetY()+$h>$this->PageBreakTrigger)
		        $this->AddPage($this->CurOrientation);
		}

		function NbLines($w,$txt)
		{
		    //Computes the number of lines a MultiCell of width w will take
		    $cw=&$this->CurrentFont['cw'];
		    if($w==0)
		        $w=$this->w-$this->rMargin-$this->x;
		    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		    $s=str_replace("\r",'',$txt);
		    $nb=strlen($s);
		    if($nb>0 and $s[$nb-1]=="\n")
		        $nb--;
		    $sep=-1;
		    $i=0;
		    $j=0;
		    $l=0;
		    $nl=1;
		    while($i<$nb)
		    {
		        $c=$s[$i];
		        if($c=="\n")
		        {
		            $i++;
		            $sep=-1;
		            $j=$i;
		            $l=0;
		            $nl++;
		            continue;
		        }
		        if($c==' ')
		            $sep=$i;
		        $l+=$cw[$c];
		        if($l>$wmax)
		        {
		            if($sep==-1)
		            {
		                if($i==$j)
		                    $i++;
		            }
		            else
		                $i=$sep+1;
		            $sep=-1;
		            $j=$i;
		            $l=0;
		            $nl++;
		        }
		        else
		            $i++;
		    }
		    return $nl;
		}

		function mes($m) {
			$mes = "Enero";
			switch ($m) {
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

			return strtoupper($mes);
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-12
		 * 
		 * @param '$date'	string. Fecha
		 *
		 * @return 			boolean.True -> Si es Domingo; False -> Es otro día
		 * 
		 * Regresa si es Domingo la fecha que se le da
		 */
		function isWeekend($date) {
			$date_required = str_replace('/', '-', $date);
			$day = date('l', strtotime($date_required));
			if ($day == 'Sunday') {
				return true;
			} else {
				return false;
			}
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-13
		 * 
		 * @param '$date_str'	string. 	Fecha
		 * @param '$months'		int.		Meses que se van a sumar
		 *
		 * @return '$date'		DateTime.	Fecha con un mes más
		 * 
		 * Método que aumenta un mes a la fecha dada
		 */
		function addMonth($date_str, $new_date ,$months) {
		    $date = new DateTime($date_str);
		    $start_day = $date->format('j');
		    //Si el día que te dieron es el último día
		    if($start_day == $date->format('t')) {
		    	$date = new DateTime($new_date);
		    	$start_day = $date->format('j');
		    	if($start_day == $date->format('t')) {
		    		$date->modify('last day of next month');
		    	} else {
		    		$date->modify('last day of this month');
		    	}
		    	
		    } else {
		    	//Si no, solo agrega los meses
		    	$date->modify("+{$months} month");
			    $end_day = $date->format('j');

			    if ($start_day != $end_day)
			        $date->modify('last day of last month');
		    }

		    return $date;
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-13
		 * 
		 * @param '$date_str'	string. 	Fecha
		 *
		 * @return '$date'		DateTime.	Fecha de la siguiente quincena
		 * 
		 * Método que aumenta una quincena a la fecha dada
		 * 3 OPCIONES
		 * 1.- Si la fecha es igual al último día del mes -> date debe ser el día 15 del prox mes
		 * 2.- Si la fecha es menor a 13 -> date debe ser el día 15 de ese mes
		 * 3.- Si la fecha es mayor o igual a 15 -> date debe ser el último día de ese mes 
		 */
		function addFortnight($date_str) {
			$date = new DateTime($date_str);
			$start_day = $date->format('j');
			//Si el día que te dieron es el último día
			if($start_day == $date->format('t')) {
				$date->modify("first day of next month");
				$date->modify("+14 days");
			} else if($start_day < 13) {
				$date->modify("first day of this month");
				$date->modify("+14 days");
			} else if($start_day >= 15) {
				$date->modify('last day of this month');
			}

			return $date;
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-13
		 * 
		 * Agrega marca de agua
		 */
		function watermark() {
		    //Put the watermark
		    $this->SetFont('Arial','B',50);
		    $this->SetTextColor(255,192,203);
		    $this->RotatedText(80,80,'C O P I A',45);
		     $this->RotatedText(80,168,'C O P I A',45);
		      $this->RotatedText(80,256,'C O P I A',45);
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-13
		 * 
		 * Rota texto de marca de agua
		 */
		function RotatedText($x, $y, $txt, $angle) {
		    //Text rotated around its origin
		    $this->Rotate($angle,$x,$y);
		    $this->Text($x,$y,$txt);
		    $this->Rotate(0);
		}

		function Header() {
			/*LOGO*/
			$this->Cell(8, 8, $this->Image("logo.jpg", $this->GetX(), $this->GetY(), 18), 0, 0, 'L');
			$this->SetFont('Arial','B',9);
			$id_fill = str_pad($_GET['id'], 4, "0", STR_PAD_LEFT);
			$this->Cell(180, 5,"FOLIO I".$id_fill,0,0,"R",0);

			// Margen
			$this->SetDrawColor(207,207,207);//linea de contorno
			$this->Ln(22);
			$this->Cell(0, 5,"","T",0,"L",0);
			$this->Ln(8);
		}

		function Footer() {
			// Go to 1.5 cm from bottom
		    $this->SetY(-15);
		    // Select Arial italic 8
		    $this->SetFont('Arial','',8);
		    // Print centered page number
		    $this->Cell(0,10,$this->PageNo(),0,0,'R');
		}

		//------    CONVERTIR NUMEROS A LETRAS         ---------------
		//------    Máxima cifra soportada: 18 dígitos con 2 decimales
		//------    999,999,999,999,999,999.99
		// NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE BILLONES
		// NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE MILLONES
		// NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE PESOS 99/100 M.N.
		//------    Creada por:                        ---------------
		//------             ULTIMINIO RAMOS GALÁN     ---------------
		//------            uramos@gmail.com           ---------------
		//------    10 de junio de 2009. México, D.F.  ---------------
		//------    PHP Version 4.3.1 o mayores (aunque podría funcionar en versiones anteriores, tendrías que probar)
		function numtoletras($xcifra)
		{
		    $xarray = array(0 => "Cero",
		        1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
		        "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
		        "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
		        100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
		    );
		//
		    $xcifra = trim($xcifra);
		    $xlength = strlen($xcifra);
		    $xpos_punto = strpos($xcifra, ".");
		    $xaux_int = $xcifra;
		    $xdecimales = "00";
		    if (!($xpos_punto === false)) {
		        if ($xpos_punto == 0) {
		            $xcifra = "0" . $xcifra;
		            $xpos_punto = strpos($xcifra, ".");
		        }
		        $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
		        $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
		    }
		 
		    $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
		    $xcadena = "";
		    for ($xz = 0; $xz < 3; $xz++) {
		        $xaux = substr($XAUX, $xz * 6, 6);
		        $xi = 0;
		        $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
		        $xexit = true; // bandera para controlar el ciclo del While
		        while ($xexit) {
		            if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
		                break; // termina el ciclo
		            }
		 
		            $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
		            $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
		            for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
		                switch ($xy) {
		                    case 1: // checa las centenas
		                        if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas
		                             
		                        } else {
		                            $key = (int) substr($xaux, 0, 3);
		                            if (TRUE === array_key_exists($key, $xarray)){  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
		                                $xseek = $xarray[$key];
		                                $xsub = $this->subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
		                                if (substr($xaux, 0, 3) == 100)
		                                    $xcadena = " " . $xcadena . " CIEN " . $xsub;
		                                else
		                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
		                                $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
		                            }
		                            else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
		                                $key = (int) substr($xaux, 0, 1) * 100;
		                                $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
		                                $xcadena = " " . $xcadena . " " . $xseek;
		                            } // ENDIF ($xseek)
		                        } // ENDIF (substr($xaux, 0, 3) < 100)
		                        break;
		                    case 2: // checa las decenas (con la misma lógica que las centenas)
		                        if (substr($xaux, 1, 2) < 10) {
		                             
		                        } else {
		                            $key = (int) substr($xaux, 1, 2);
		                            if (TRUE === array_key_exists($key, $xarray)) {
		                                $xseek = $xarray[$key];
		                                $xsub = $this->subfijo($xaux);
		                                if (substr($xaux, 1, 2) == 20)
		                                    $xcadena = " " . $xcadena . " VEINTE " . $xsub;
		                                else
		                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
		                                $xy = 3;
		                            }
		                            else {
		                                $key = (int) substr($xaux, 1, 1) * 10;
		                                $xseek = $xarray[$key];
		                                if (20 == substr($xaux, 1, 1) * 10)
		                                    $xcadena = " " . $xcadena . " " . $xseek;
		                                else
		                                    $xcadena = " " . $xcadena . " " . $xseek . " Y ";
		                            } // ENDIF ($xseek)
		                        } // ENDIF (substr($xaux, 1, 2) < 10)
		                        break;
		                    case 3: // checa las unidades
		                        if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada
		                             
		                        } else {
		                            $key = (int) substr($xaux, 2, 1);
		                            $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
		                            $xsub = $this->subfijo($xaux);
		                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
		                        } // ENDIF (substr($xaux, 2, 1) < 1)
		                        break;
		                } // END SWITCH
		            } // END FOR
		            $xi = $xi + 3;
		        } // ENDDO
		 
		        if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
		            $xcadena.= " DE";
		 
		        if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
		            $xcadena.= " DE";
		 
		        // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
		        if (trim($xaux) != "") {
		            switch ($xz) {
		                case 0:
		                    if (trim(substr($XAUX, $xz * 6, 6)) == "1")
		                        $xcadena.= "UN BILLON ";
		                    else
		                        $xcadena.= " BILLONES ";
		                    break;
		                case 1:
		                    if (trim(substr($XAUX, $xz * 6, 6)) == "1")
		                        $xcadena.= "UN MILLON ";
		                    else
		                        $xcadena.= " MILLONES ";
		                    break;
		                case 2:
		                    if ($xcifra < 1) {
		                        $xcadena = "CERO PESOS $xdecimales/100 Moneda de curso legal de los Estados Unidos Mexicanos ";
		                    }
		                    if ($xcifra >= 1 && $xcifra < 2) {
		                        $xcadena = "UN PESO $xdecimales/100 Moneda de curso legal de los Estados Unidos Mexicanos ";
		                    }
		                    if ($xcifra >= 2) {
		                        $xcadena.= " PESOS $xdecimales/100 Moneda de curso legal de los Estados Unidos Mexicanos "; //
		                    }
		                    break;
		            } // endswitch ($xz)
		        } // ENDIF (trim($xaux) != "")
		        // ------------------      en este caso, para México se usa esta leyenda     ----------------
		        $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
		        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
		        $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
		        $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
		        $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
		        $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
		        $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
		    } // ENDFOR ($xz)
		    return trim($xcadena);
		}
		 
		// END FUNCTION
		 
		function subfijo($xx)
		{ // esta función regresa un subfijo para la cifra
		    $xx = trim($xx);
		    $xstrlen = strlen($xx);
		    if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
		        $xsub = "";
		    //
		    if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
		        $xsub = "MIL";
		    //
		    return $xsub;
		}
		 
		// END FUNCTION

		function diaLetras($fecha) {
			$dw = date("w", strtotime($fecha));
			$dia = "Viernes";
			switch ($dw) {
				case 0:
					$dia = "Domingo";
					break;
				case 1:
					$dia = "Lunes";
					break;
				case 2:
					$dia = "Martes";
					break;
				case 3:
					$dia = "Miércoles";
					break;
				case 4:
					$dia = "Jueves";
					break;
				case 5:
					$dia = "Viernes";
					break;
				case 6:
					$dia = "Sábado";
					break;							
			}

			return $dia;
		}

	}


	$id = $_GET['id'];
	$pdf = new PDF();

	$dd = new Datos;
	$grupo = $dd->getGroup($id);
	$personas = $dd->getPersons($id);
	$pagos = $dd->getPagos($id);

	// Agrega la pagina
	$pdf->AddPage();
	$pdf->SetTextColor(0,0,0);

	$dia_actual = date("d");
	$mes_act = date("m");
	$anio_actual = date("Y");

	$mes_actual = $pdf->mes($mes_act);

	$txt_personas = "";
	foreach ($personas as $persona) {
		$txt_personas .= $persona["PER_NOMBRE"] . ", ";
	}

	$txt_personas = utf8_decode($txt_personas);

	// Escribe los datos del subtitulo del contrato
	$txt1 = utf8_decode("CONTRATO DE CRÉDITO MANCOMUNADO (\"EL CONTRATO\"), CELEBRADO CON FECHA ".$dia_actual." DE ".$mes_actual." DE ".$anio_actual.", ENTRE GRUPO PROPULSOR DE MICROEMPRESAS DEL NORTE S.A.P.I. DE C.V., REPRESENTADA EN ESTE ACTO POR LOS SEÑORES ALEJANDRO CAGIGAS TIBURCIO Y FRANCISCO JESÚS DE LA ROSA DIEZ GUTIÉRREZ EN SU CARÁCTER DE APODERADOS LEGALES, A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ COMO EL \"ACREDITANTE\" Y POR LA OTRA PARTE, POR SU PROPIO DERECHO LOS SEÑORES ".$txt_personas." A QUIENES EN LO SUCESIVO SE LES DENOMINARÁ COMO LAS \"ACREDITADAS\" Y EN CONJUNTO CON EL ACREDITANTE SE LES DENOMINARÁ COMO LAS \"PARTES\" AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS.");

	//$pdf->Ln(8);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $txt1);
	
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,"D E C L A R A C I O N E S",0,0,'C');

	$txt4 = utf8_decode("I.-     Declara el Acreditante, por conducto de su apoderado legal que:");
	$txt5 = utf8_decode("\"EL ACREDITANTE\"");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt4);

	$pdf->Ln(15);
	$txt10 = utf8_decode("I.1     Es una Sociedad Anónima Promotora de Inversión de Capital Variable, debidamente constituida y válidamente existente conforme a las leyes de los Estados Unidos Mexicanos (en lo sucesivo \"México\"), según consta en la Escritura Pública número 10,977 de fecha 18 (dieciocho) de mayo de 2016, otorgada ante la fe del licenciado José Luis Farías Montemayor, titular de la Notaría Pública número 120 (ciento veinte) de la Ciudad de Monterrey, Estado de Nuevo León, cuyo primer testimonio se encuentra inscrito en el Registro Público del Comercio de la Ciudad de Monterrey, Estado de Nuevo León bajo el folio electrónico mercantil número 160941*1.");
	$txt11 = utf8_decode(", por propio derecho y bajo protesta de decir verdad:");
	$pdf->Write(5, $txt10);

	$pdf->Ln(15);
	$txt22 = utf8_decode("I.2     Sus apoderados legales cuentan con las facultades legales y suficientes para obligarla conforme a los términos y condiciones de este Contrato, tal como se desprende de la Escritura Pública número 10,977 de fecha 18 (dieciocho) de mayo de 2016, otorgada ante la fe del licenciado José Luis Farías Montemayor, titular de la Notaría Pública número 120 (ciento veinte) de la Ciudad de Monterrey, Estado de Nuevo León, cuyo primer testimonio se encuentra inscrito en el Registro Público del Comercio de la Ciudad de Monterrey, Estado de Nuevo León bajo el folio electrónico mercantil número 160941*1, facultades que a la fecha no le han sido revocadas, limitadas, ni modificadas en forma alguna.");
	$txt23 = utf8_decode("\"LAS PARTES\":");
	$pdf->Write(5, $txt22);

	if($grupo['CRE_DOMICILIO'] == "")
		$grupo['CRE_DOMICILIO'] = "la calle _______, número____, Colonia _____, Código Postal____, de la Ciudad de Monterrey, Estado de Nuevo León";

	$i3 = utf8_decode("I.3     Señala como domicilio para oír notificaciones y recibir toda clase de documentos, el inmueble ubicado en ".$grupo['CRE_DOMICILIO'].".");

	$pdf->Ln(15);
	$pdf->Write(5, $i3);	

	$i4 = utf8_decode("I.4     Es su deseo celebrar el presente Contrato de Crédito Mancomunado, para lo cual cuenta con la capacidad legal suficiente.");

	$pdf->Ln(15);
	$pdf->Write(5, $i4);

	$i42 = utf8_decode("II.     Declaran las Acreditadas por su propio derecho y bajo protesta de decir verdad que:");	
	$pdf->Ln(10);
	$pdf->Write(5, $i42);

	$i421 = utf8_decode("II.1     Son personas físicas, con capacidad jurídica suficiente para signar el presente instrumento.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i421);

	$i4211 = utf8_decode("          Mismas que se identifican con:");	
	$pdf->Ln(10);
	$pdf->Write(5, $i4211);
	$pdf->Ln(10);

	foreach ($personas as $persona) {
		$direccion = ($persona['PER_DIRECCION']); 
		if($persona['PER_NUM'] != '') {
			$direccion.= " No. ".($persona['PER_NUM']).", ".($persona['PER_COLONIA']).", ".($persona['PER_MUNICIPIO']).", ".($persona['PER_ESTADO']).", ".($persona['PER_CP']);
		}

		$ife = "";
		if($persona['IFE_NUM'] == '') {
			$ife = "__________________";
		} else {
			$ife = $persona['IFE_NUM'];
		}

		$cred = utf8_decode("Credencial para votar número ".$ife." emitida por el Instituto Federal Electoral, con domicilio en: ".$direccion);

		$pdf->Write(5, $cred);
		$pdf->Ln();
		$pdf->Ln();
	}

	$i422 = utf8_decode("II.2     Señalan como domicilio común para oír notificaciones y recibir toda clase de documentos, el inmueble ubicado en ".$grupo["CRE_DOMICILIO"].".");	
	$pdf->Ln(6);
	$pdf->Write(5, $i422);


	$i423 = utf8_decode("II.3     Han solicitado a el Acreditante un crédito mancomunado.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i423);


	$i424 = utf8_decode("II.4     La información que han presentado al Acreditante, para el otorgamiento de este crédito, refleja de manera exacta y fiel su situación económica.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i424);


	$i425 = utf8_decode("II.5     Es su deseo celebrar el presente Contrato de Crédito Mancomunado, para lo cual cuentan con la capacidad legal suficiente, para obligarse en los términos y condiciones que en el mismo se pactan, en los términos del artículo 78 (setenta y ocho) del Código de Comercio.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i425);

	$i43 = utf8_decode("III.     Ambas Partes declaran que:");	
	$pdf->Ln(10);
	$pdf->Write(5, $i43);

	$i431 = utf8_decode("III.1     Se reconocen mutuamente la personalidad que con se ostentan y que es su deseo celebrar el presente Contrato de Crédito Mancomunado.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i431);

	$i432 = utf8_decode("III.2     Suscriben el presente Contrato en pleno ejercicio de su voluntad, sin que exista error, dolo, violencia, mala fe, ni algún otro vicio que altere su libre voluntad.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i432);

	$i433 = utf8_decode("III.3     Tener la capacidad jurídica y legal suficiente para obligarse, en los términos del presente contrato, lo cual al efecto no se podrá alegar nulidad del mismo, por inexperiencia o ignorancia.");	
	$pdf->Ln(10);
	$pdf->Write(5, $i433);

	$i4331 = utf8_decode("          De conformidad con las anteriores declaraciones, mismas que forman parte integral del presente contrato, las Partes convienen en obligarse al tenor de las siguientes: ");	
	$pdf->Ln(10);
	$pdf->Write(5, $i4331);

	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(0,5,utf8_decode("C L Á U S U L A S"),0,0,'C');


	$txt27 = utf8_decode("PRIMERA. ");
	$txt28 = utf8_decode("OBJETO.");

	$monto_bonito = number_format($grupo['CRE_MONTO_TOTAL'], 2);
	$monto_letras = $pdf->numtoletras($grupo['CRE_MONTO_TOTAL']);

	$txt29 = utf8_decode("          El Acreditante otorga a favor de las Acreditadas, un crédito mancomunado por la cantidad de $".$monto_bonito);

	$txt30 = utf8_decode(" (".$monto_letras.").");

	$txt32 = utf8_decode("\"LAS ACREDITADAS\"");

	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $txt27);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $txt28);
	
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt29);

	$pdf->SetFont('Arial','I',10);
	$pdf->Write(5, $txt30);

	$seg = utf8_decode("SEGUNDA. ");
	$disp = utf8_decode("DISPOSICIÓN.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt31 = utf8_decode("          Las Acreditadas podrán ejercer el importe del crédito concedido, al momento de la firma del presente Contrato ya sea en una o varias disposiciones.");
	$txt33 = utf8_decode("          Previo al retiro del importe del crédito concedido, las Acreditadas deberán realizar el pago de la comisión por apertura, la cual equivale al 5% del valor del crédito concedido.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt31);
	$pdf->Ln(10);
	$pdf->Write(5, $txt33);

	$seg = utf8_decode("TERCERA. ");
	$disp = utf8_decode("RESPONSABILIDAD MANCOMUNADA.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt31 = utf8_decode("          Las Acreditadas responderán de manera mancomunadamente del pago del crédito solicitado a través del presente Contrato. En caso de incumplimiento el pago del crédito, intereses y accesorios podrán ser exigidos individualmente a cada una de las Acreditadas.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt31);


	$seg = utf8_decode("CUARTA. ");
	$disp = utf8_decode("PAGOS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt31 = utf8_decode("          Las Acreditadas se obligan a pagar al Acreditante en un plazo de ".$grupo['CRE_PLAZO']." semanas contados a partir de la firma del presente contrato, la totalidad del monto del crédito solicitado, cuya cantidad asciende a $".$monto_bonito);
	$txt33 = utf8_decode(" más intereses correspondientes, lo anterior de acuerdo a la tabla de amortización en que se detallan las fechas y montos de pago, la cual se adjunta al presente contrato como ");
	$txt34 = "ANEXO 1";	
	$txt35 = " (mismo que forma parte integral del presente Contrato).";

	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt31);
	$pdf->SetFont('Arial','I',10);
	$pdf->Write(5, $txt30);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt33);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $txt34);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt35);

	$txt33 = utf8_decode("          Los pagos se realizarán de manera integra el día ");
	$pdf->Ln(10);
	$pdf->Write(5, $txt33);

	$dia_junta = $pdf->diaLetras($grupo['CRE_FECHA_INICIAL']);
	$txt = utf8_decode($dia_junta." de cada semana");
	$pdf->SetFont('Arial','U',10);
	$pdf->Write(5, $txt);

	$txt = " (sin que se considere que ha incurrido en mora de las obligaciones a su cargo), en un horario acordado previamente entre las Partes, en el domicilio ubicado en ";
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);

	$pdf->SetFont('Arial','U',10);
	$pdf->Write(5, utf8_decode($grupo['CRE_DOMICILIO']).".");


	$seg = utf8_decode("QUINTA. ");
	$disp = utf8_decode("INTERESES.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$tasa_bonito = number_format($grupo['CRE_TASA']*100, 2);
	$txt31 = utf8_decode("          Las Acreditadas se comprometen a pagar un interés del ".$tasa_bonito."%");
	//$tasa_palabras = $pdf->numtoletras($tasa_bonito);
	$tasa_palabras = "uno punto setenta y cinco por ciento";
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt31);
	$pdf->SetFont('Arial','I',10);
	$pdf->Write(5, utf8_decode("(".$tasa_palabras.") "));

	$txt = utf8_decode("     Dado que las amortizaciones del crédito se llevaran a cabo semanalmente, si las Acreditadas incurrieran en mora en el pago semanal del crédito pactado, El Acreditante aplicará un interés equivalente al 1.6% (uno punto seis por ciento) diario, hasta que se regularicen todos y cada uno los pagos correspondientes.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("SEXTA. ");
	$disp = utf8_decode("GARANTÍAS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Para seguridad y garantía en el cumplimiento de todo lo estipulado en el presente contrato, El C. ______________, exhibe la Escritura Pública número ______________  de fecha __ (_____) de ____ de ___, otorgada ante la fe del licenciado ______________, titular de la Notaría Pública número __________________ (______) de la Ciudad de ______________, Estado de ______________, cuyo primer testimonio se encuentra inscrito en el Registro Público del Comercio de la Ciudad de ______________, Estado de ______________ bajo el folio electrónico mercantil número ______________.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);
	

	$seg = utf8_decode("SÉPTIMA. ");
	$disp = utf8_decode("CAUSAS DE VENCIMIENTO ANTICIPADO.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     El Acreditante podrá dar por vencido anticipadamente el presente Contrato, y por lo tanto, exigir el pago inmediato de la suerte principal y demás accesorios legales que correspondan, si las Acreditadas llegaren a incumplir con cualquiera de las obligaciones establecidas a su cargo y en especial si deja de cubrir puntualmente cualquier pago a su cargo, derivado del presente Contrato.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("OCTAVA. ");
	$disp = utf8_decode("ACCIONES.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     El Acreditante se reserva la facultad de obtener el cobro de los saldos a cargo de las Acreditadas, ejercitando por la vía ejecutiva y/o ordinaria mercantil, o la que en su derecho corresponda. En la inteligencia de que si se llegaré a ejecutar la vía ejecutiva, el Acreditante podrá señalar los bienes suficientes y necesarios para embargo, sin sujetarse al orden que establece el artículo 1395 (Un mil trescientos noventa y cinco) del Código de Comercio. Conviniéndose además expresamente, en que el ejercicio de alguna de estas acciones no implicará la pérdida de la otra, y que todas las que competen a el Acreditante, permanecerán íntegramente subsistentes en tanto no se liquide la totalidad del crédito y sus accesorios a cargo de las Acreditadas.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);

	$txt = utf8_decode("     Las Acreditadas otorgan al Acreditante su expreso consentimiento para que las cantidades que consigne en caso de juicio, las aplique en el siguiente orden: i) a gastos y costos del juicio, ii) a impuestos que se generen derivados del presente contrato, iii) a gastos de cobranza, iv) a intereses moratorios, v) a intereses ordinarios vencidos, vi) a intereses ordinarios vigentes, en vii) al capital vencido no pagado y el remanente al capital vigente del crédito.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);

	$seg = utf8_decode("NOVENA. ");
	$disp = utf8_decode("INFORMACIÓN CREDITICIA.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Las Acreditadas autorizan expresamente a el Acreditante para que en cualquier momento solicite y proporcione información a cualquier entidad de Información de Crédito, y a que utilice cualquier otro medio que considere pertinente para obtener información de su historial crediticio y verificar la información asentada en el presente Contrato.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA. ");
	$disp = utf8_decode("IMPUESTOS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Cada una de las Partes se hará cargo de los impuestos, derechos o cualquier otra prestación de carácter fiscal presente o futura, que le corresponda pagar conforme a la legislación fiscal vigente con motivo de la celebración y ejecución del presente Contrato.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA PRIMERA. ");
	$disp = utf8_decode("HEREDEROS, SUCESORES Y CESIONARIOS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     El presente Contrato operará para beneficio y será obligatorio para los herederos, sucesores o cesionarios de cada una de las Partes y las obligaciones aquí contenidas subsistirán a cualquier evento, ya sea de el Acreditante o de las Acreditadas, que convencional o legalmente sean o deban de ser trasmitidos, ya sea por herencia, sucesión, cesión, o cualquier otro medio de transmisión.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA SEGUNDA. ");
	$disp = utf8_decode("CASO FORTUITO.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Las Acreditadas se obliga al cumplimiento del presente Contrato, aún en caso fortuito o de fuerza mayor, en términos del artículo 2111 (Dos mil ciento once) del Código Civil Federal.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA TERCERA. ");
	$disp = utf8_decode("NOTIFICACIONES.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Se establece que todos los avisos, que se llegaren a realizar entre las Partes, se considerarán realizados cuando se envíen por correo certificado con acuse de recibo, o personalmente con acuse de recibo a la dirección señalada dentro de la Declaración I.3. para el Acreditante e II.2. para las Acreditadas.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA CUARTA. ");
	$disp = utf8_decode("ENCABEZADOS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Los encabezados utilizados al principio de cada una de las Cláusulas, constituyen solamente como pronta referencia e identificación, por lo que no se consideran para efectos de interpretación o cumplimiento de los mismos.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA QUINTA. ");
	$disp = utf8_decode("CONTRATO COMPLETO.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Este Contrato reemplaza completa y totalmente todos los arreglos, convenios o Contratos anteriores, tanto verbales como escritos, relacionados con la materia objeto del presente contrato. El presente Contrato constituye, en la fecha de su celebración, el Contrato total y completo entre las partes en relación con el objeto del mismo.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA SEXTA. ");
	$disp = utf8_decode("ADENDUMS.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Se pueden anexar adendums al presente contrato, siempre y cuando, sean firmados por las Partes, así como, convalidados y autorizados por el Proveedor, y formarán parte integrante del mismo.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA OCTAVA. ");
	$disp = utf8_decode("DIVISIBILIDAD.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     En caso de que cualquiera de las cláusulas de este Contrato por cualquier motivo sea declarada nula, ilegal o inejecutable en cualquier aspecto, las Partes convienen en negociar de buena fe las enmiendas, modificaciones o complementos de este Contrato, o cualquier otra acción adecuada que, en la medida máxima de lo posible y a la luz de tal determinación, permita cumplir las intenciones originales de las Partes tal como quedaron reflejadas en este Contrato; las demás cláusulas de este Contrato permanecerán tal como se enmienden, modifiquen, suplementen, o de cualquier otra forma sean afectadas, continuarán en plena fuerza y vigor.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("DÉCIMA NOVENA. ");
	$disp = utf8_decode("JURISDICCIÓN.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Las Partes convienen en que para lo no previsto en el presente Contrato, las disposiciones y preceptos legales aplicables serán los del Código de Comercio.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$seg = utf8_decode("VIGÉSIMA. ");
	$disp = utf8_decode("INTERPRETACIÓN Y COMPETENCIA.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $seg);
	$pdf->SetFont('Arial','BU',10);
	$pdf->Write(5, $disp);

	$txt = utf8_decode("     Las Partes acuerdan que en caso de controversia suscitada con motivo de la interpretación y cumplimiento del Contrato y sus ANEXOS, se someterán en primera instancia al procedimiento de mediación a través de un mediador autorizado por el Centro Estatal de Medios Alternos para la Solución de Conflictos del Estado de Nuevo León, de conformidad con la Ley de Metodos Alternos para la Solución de Conflictos del Estado de Nuevo León y demás leyes aplicables.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$txt = utf8_decode("     Una vez agotado el procedimiento de mediación, si las PARTES no llegaren a celebrar acuerdo alguno, convienen en someterse a la jurisdicción de los Tribunales de la ciudad de 
San Pedro Garza García, incluyendo de manera enunciativa más no limitativa cualquier contrademanda o procedimiento judicial o extrajudicial que se inicie con motivo de la celebración, interpretación y cumplimiento del Contrato, renunciando en este momento al fuero que pudiere corresponderles en razón de su domicilio presente o futuro o por cualquier otra causa.");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','',10);
	$pdf->Write(5, $txt);


	$txt = utf8_decode("     LEIDO QUE FUE POR AMBAS PARTES, EL PRESENTE CONTRATO Y DEBIDAMENTE ENTERADOS DE SU CONTENIDO Y DE LOS ANEXOS QUE FORMAN PARTE DEL MISMO, LO FIRMAN EN DOS TANTOS DE COMÚN ACUERDO EN LA CIUDAD SAN PEDRO GARCA GARCÍA, NUEVO LEÓN EL DÍA ".$dia_actual." DE ".$mes_actual." DE ".$anio_actual.".");
	$pdf->Ln(10);
	$pdf->SetFont('Arial','B',10);
	$pdf->Write(5, $txt);


	/*FIRMAS DE ACREDITANTES*/
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(75, 5,"\"EL ACREDITANTE\"",0,0,"C",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,"\"EL ACREDITANTE\"",0,0,"C",0);
	$pdf->SetDrawColor(0,0,0);//linea de contorno
	$pdf->Ln(22);
	$pdf->Cell(75, 5,"","T",0,"L",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,"","T",0,"R",0);
	$pdf->Ln();
	
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(75, 5,"ALEJANDRO CAGIGAS TIBURCIO",0,0,"C",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,"FRANCISCO JESUS DE LA ROSA DIEZ GUTIERREZ",0,0,"C",0);
	$pdf->Ln();

	$pdf->Cell(75, 5,utf8_decode("En nombre y representación de Grupo Propulsor de"),0,0,"C",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,utf8_decode("En nombre y representación de Grupo Propulsor de"),0,0,"C",0);
	$pdf->Ln();

	$pdf->Cell(75, 5,utf8_decode("Microempresas del Norte S.A.P.I. de C.V."),0,0,"C",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,utf8_decode("Microempresas del Norte S.A.P.I. de C.V."),0,0,"C",0);


	/*FIRMAS DE GESTORA*/
	$nombre_gestora = utf8_decode($grupo['SIU_NOMBRE']);
	$pdf->Ln(25);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(75, 5,"\"RESPONSABLE SOLIDARIO\"",0,0,"C",0);
	$pdf->Ln(22);
	$pdf->Cell(75, 5,"","T",0,"L",0);
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(75, 5,strtoupper($nombre_gestora),0,0,"C",0);

	/*FIRMAS DE ACREDITADAS*/
	$pdf->Ln(20);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0, 4, $txt32, 0, 1, 'C');
	$pdf->SetDrawColor(0,0,0);//linea de contorno	
	$pdf->Ln(22);
	$c = true;
	$pdf->SetFont('Arial','',9);
	foreach ($personas as $persona) {
		if($c) {
			$pdf->Cell(75, 8,utf8_decode(strtoupper($persona['PER_NOMBRE'])),"T",0,"C",0);
			$pdf->Cell(40, 8,"",0,0,"L",0);
			$c = false;
		} else {
			$pdf->Cell(75, 8,utf8_decode(strtoupper($persona['PER_NOMBRE'])),"T",0,"C",0);
			$pdf->Ln(30);
			$c = true;
		}
	}

	/*TABLA DE PAGOS*/
	$pdf->AddPage();
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0, 4, "ANEXO A", 0, 1, 'C');
	$pdf->Ln(2);
	$pdf->Cell(0, 4, utf8_decode("TABLA DE AMORTIZACIÓN"), 0, 1, 'C');
	$pdf->Ln(15);

	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(63, 7, utf8_decode("SEMANA"), "LTR", 0, "C");
	$pdf->Cell(63, 7, utf8_decode("FECHA"), "LTR", 0, "C");
	$pdf->Cell(63, 7, utf8_decode("PAGO SEMANAL"), "LTR", 0, "C");
	$pdf->SetFont('Arial','',9);
	$pdf->Ln();

	$index = 1;
	foreach ($pagos as $pago) {
		$border = "LTR";
		if($index == (count($pagos)))
			$border = 1;
		$pdf->Cell(63, 7, $index, $border, 0, "C");
		$fecha = date("d/m/Y", strtotime($pago['TPI_FECHA']));
		$pdf->Cell(63, 7, $fecha, $border, 0, "C");
		$monto = number_format($pago['TPI_MONTO'], 2);
		$pdf->Cell(63, 7, $monto, $border, 0, "C");
		$index++;
		$pdf->Ln();
	}



	foreach ($personas as $persona) {
		$pago_bonito = number_format($grupo['CRE_MONTO_TOTAL'], 2);
		$pago_letras = $pdf->numtoletras($pago_bonito);
		$dia_pago = date("d", strtotime($pagos[11]['TPI_FECHA']));
		$mes_pag = date("m", strtotime($pagos[11]['TPI_FECHA']));
		$mes_pago = $pdf->mes($mes_pag);
		$anio_pago = date("Y", strtotime($pagos[11]['TPI_FECHA']));
		$pdf->head = 2;
		$pdf->AddPage();
		$pdf->foot = 2;
		$pdf->SetFont('Arial','B',10);
		$pdf->Cell(0, 4, utf8_decode("P A G A R É"), 0, 1, 'C');
		$pdf->Ln(2);
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(0, 4, utf8_decode("BUENO POR: $".$pago_bonito." PESOS."), 0, 1, 'R');
		$pdf->Ln(5);
		$txt_pagare = utf8_decode("Fecha y lugar de suscripciÓn: ");
		$pdf->Write(7, strtoupper($txt_pagare));
		$txt_pagare = utf8_decode("San Pedro Garza GarcÍa, N.L., A ".$dia_actual." DE ".$mes_actual." DEL ".$anio_actual.".");
		$pdf->SetFont('Arial','I',8);
		$pdf->Write(7, strtoupper($txt_pagare));
		$pdf->Ln();
		$pdf->Ln();
		$txt_pagare = utf8_decode("Por este pagarÉ prometo y me obligo a pagar a la orden de: ");
		$pdf->SetFont('Arial','',8);
		$pdf->Write(7, strtoupper($txt_pagare));
		$pdf->SetFont('Arial','BIU',8);
		$pdf->Write(7, strtoupper("GRUPO PROPULSOR DE MICROEMPRESAS DEL NORTE S.A.P.I. DE C.V."));
		$txt_pagare = utf8_decode("., en esta plaza el dÍa ".$dia_pago." de ".$mes_pago." del ".$anio_pago." en el domicilio ubicacado en ".$grupo['SIU_DIRECCION'].", la cantidad de: $".$pago_bonito." (".$pago_letras."), el dÍa de su pago. Este documento causarÁ un interÉs del 7% mensual.");
		$pdf->SetFont('Arial','',8);
		$pdf->Write(7, strtoupper($txt_pagare));
		$pdf->Ln();
		$pdf->Ln();
		$txt_pagare = utf8_decode("El suscriptor renuncia al protesto de este pagarÉ y se somete expresamente a la jurisdicciÓn de los Tribunales de la Ciudad de San Pedro Garza GarcÍa, N.L., renunciando a los que pudieran ser competentes en relaciÓn con su domicilio presente o futuro.");
		$pdf->SetFont('Arial','',8);
		$pdf->Write(7, strtoupper($txt_pagare));

		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','BU',8);
		$pdf->Write(7, strtoupper("Suscriptor:"));

		$pdf->SetFont('Arial','',8);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		
		//for ($i=0; $i < 10; $i++) { 
		//	$persona = $personas[0];
			$txt_per = utf8_decode($persona['PER_NOMBRE']."\n");
			$direccion = utf8_decode($persona['PER_DIRECCION']); 
			if($persona['PER_NUM'] != '') {
				$direccion.= utf8_decode(" No. ".($persona['PER_NUM']).", ".($persona['PER_COLONIA']).", ".($persona['PER_MUNICIPIO']).", ".($persona['PER_ESTADO']).", ".($persona['PER_CP']));
			}
			//$txt_dir = utf8_decode("Domicilio: ".$persona['PER_DIRECCION']." No. ".$persona["PER_NUM"].", ".$persona['PER_COLONIA']);
			//if($c) {
				$current_y = $pdf->GetY();
				$pdf->MultiCell(75, 8,$txt_per.$direccion,"T","C",0);
				$pdf->SetY($current_y);
				$pdf->SetX(115);
				//$pdf->MultiCell(40, 8,"",1,"L",0);
				$c = false;
		//}


		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->SetFont('Arial','BU',8);
		$pdf->Write(7, strtoupper("Avales:"));
		$pdf->SetFont('Arial','',8);
		$pdf->Ln();
		$pdf->Ln();
		$pdf->Ln();
		$pdf->MultiCell(75, 8,utf8_decode($grupo['SIU_NOMBRE']."\n".$grupo['SIU_DIRECCION']),"T","C",0);	


	}


	$archivo = 'pagare1.pdf';
	$pdf->Output();
	//$pdf->Output('../files/pagares/'.$archivo, 'F');
} else {
	die("Pagaré No Disponible");
}
?>