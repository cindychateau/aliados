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
include_once("datos.php");


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

			// Margen
			$this->SetDrawColor(207,207,207);//linea de contorno
			$this->Ln(22);
			$this->Cell(0, 5,"","T",0,"L",0);
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
		                        $xcadena = "CERO PESOS $xdecimales/100 M.N.";
		                    }
		                    if ($xcifra >= 1 && $xcifra < 2) {
		                        $xcadena = "UN PESO $xdecimales/100 M.N. ";
		                    }
		                    if ($xcifra >= 2) {
		                        $xcadena.= " PESOS $xdecimales/100 M.N. "; //
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

	// Escribe los datos del subtitulo del contrato
	$txt1 = utf8_decode("CONTRATO DE CRÉDITO MANCOMUNADO, QUE CELEBRAN POR UNA PARTE LOS SEÑORES FRANCISCO DE LA ROSA DIEZ GUTIERREZ Y ALEJANDRO CAGIGAS TIBURCIO, QUIENES COMPARECEN POR SU PROPIO DERECHO, Y A QUIEN EN LO SUCESIVO SE LES DENOMINARÁ COMO \"EL ACREDITANTE\", Y POR OTRA PARTE, LAS SEÑORAS ");

	$txt2 = "";
	foreach ($personas as $persona) {
		$txt2 .= $persona["PER_NOMBRE"] . ", ";
	}
	$txt2 = trim($txt2, ', ');
	$txt2 = utf8_decode($txt2);

	$txt3 = utf8_decode(" A QUIENES EN LO SUCESIVO SE LES DENOMINARÁ COMO \"LAS ACREDITADAS\", Y CUANDO SE NOMBREN EN CONJUNTO SE DENOMINARÁN COMO \"LAS PARTES\", AL TENOR DE LAS SIGUIENTES  DECLARACIONES Y CLÁUSULAS.");

	$pdf->Ln(8);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt1);
	$pdf->SetFont('Arial','U',9);
	$pdf->Write(4, $txt2);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt3);
	
	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Cell(0,4,"DECLARACIONES",0,0,'C');

	$txt4 = utf8_decode("I.- Declara ");
	$txt5 = utf8_decode("\"EL ACREDITANTE\"");
	$txt6 = utf8_decode(", por su propio derecho y bajo protesta de decir verdad:");
	$txt7 = utf8_decode("a) Que es una persona de nacionalidad Mexicana, con plena capacidad para celebrar el presente contrato por lo que cuenta con personalidad jurídica y capacidad legal para contratar y obligarse.");
	$txt8 = utf8_decode("b) Que es su voluntad apoyar a ");
	$txt9 = utf8_decode(" mediante el otorgamiento de un crédito, cuyos términos y condiciones ");
	$txt99 = utf8_decode("se estipulan más adelante.");
	$pdf->Ln(15);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt4);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt6);
	$pdf->Ln(7);
	$pdf->Cell(10);
	$pdf->MultiCell(0, 4, $txt7);
	$pdf->Ln(1);
	$pdf->Cell(10);
	$pdf->Cell(45, 4, $txt8);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(31, 4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Cell(0, 4, $txt9, 0, 1);
	$pdf->Cell(10);
	$pdf->Cell(0, 4, $txt99);

	$pdf->Ln(15);
	$txt10 = utf8_decode("II.- Declaran  ");
	$txt11 = utf8_decode(", por propio derecho y bajo protesta de decir verdad:");
	$txt12 = utf8_decode("a)	Ser mexicanas, mayores de edad, con plena capacidad para celebrar el presente contrato, por lo que cuentan con personalidad jurídica y capacidad legal para contratar y obligarse.");
	$txt13 = utf8_decode("b)	Que han solicitado a ");
	$txt14 = utf8_decode(" un crédito.");
	$txt15 = utf8_decode("c)	Que la información que han presentado a ");
	$txt16 = utf8_decode(",  para el otorgamiento de este crédito, refleja de manera ");
	$txt17 = utf8_decode("exacta y fiel su situación económica.");
	$txt18 = utf8_decode("d)	Que es su voluntad celebrar el presente instrumento y obligarse en los términos y condiciones que en el mismo se pactan, en términos del artículo 78 setenta y ocho del Código de Comercio.");
	$txt19 = utf8_decode("e)	Que para la celebración de este contrato de crédito y para la negociación de sus términos ha sostenido pláticas con ");
	$txt20 = utf8_decode(", por lo que el presente contrato de crédito se ajusta estrictamente a su voluntad. ");
	$txt21 = utf8_decode("f)	Que ha consultado y conoce a la letra el texto de las disposiciones legales que se citan en este contrato.");
	$pdf->Write(4, $txt10);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt11);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->MultiCell(0, 4,$txt12);
	$pdf->Ln(1);
	$pdf->Cell(10);
	$pdf->Write(4,$txt13);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4,$txt14);
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->Write(4,$txt15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt16);
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->Write(4, $txt17);
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->MultiCell(0, 4,$txt18);
	$pdf->Ln(1);
	$pdf->Cell(10);
	$pdf->Write(4, $txt19);
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(10);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt20);
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->Write(4, $txt21);

	$pdf->Ln(15);
	$txt22 = utf8_decode("III.- Declaran ");
	$txt23 = utf8_decode("\"LAS PARTES\":");
	$txt24 = utf8_decode("a)	Que se reconocen mutuamente la personalidad y capacidad jurídica con que comparecen a la celebración de este acto jurídico.");
	$txt25 = utf8_decode("b)	Que suscriben el presente contrato en pleno ejercicio de su voluntad, sin que exista error, dolo, violencia, mala fe, ni algún otro vicio que altere su libre voluntad.");
	$txt26 = utf8_decode("Expuesto lo anterior, los comparecientes otorgan las siguientes cláusulas:");
	$pdf->Write(4, $txt22);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt23);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Cell(10);
	$pdf->SetFont('Arial','',9);
	$pdf->MultiCell(0, 4,$txt24);
	$pdf->Ln(1);
	$pdf->Cell(10);
	$pdf->MultiCell(0, 4,$txt25);
	$pdf->Ln();
	$pdf->MultiCell(0, 4, $txt26);

	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Cell(0,4,"CLAUSULAS",0,0,'C');

	$txt27 = utf8_decode("PRIMERA.- ");
	$txt28 = utf8_decode("CRÉDITO.- ");
	$txt29 = utf8_decode("Por medio del presente contrato los Señores Francisco De La Rosa Diez Gutiérrez y Alejandro Cagigas Tiburcio, en su carácter de");
	$txt30 = utf8_decode(", otorgan a favor de las señoras ");
	$txt31 = utf8_decode("en su carácter de ");
	$txt32 = utf8_decode("\"LAS ACREDITADAS\"");
	$monto_bonito = number_format($grupo['GRU_MONTO_TOTAL'], 2);
	$monto_letras = $pdf->numtoletras($grupo['GRU_MONTO_TOTAL']);
	$txt33 = utf8_decode(", un crédito mancomunado hasta por la cantidad de $".$monto_bonito." (".$monto_letras."). ");
	$pdf->Ln(15);
	$pdf->Write(4, $txt27);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt28);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt29);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt30);
	$pdf->SetFont('Arial','U',9);
	$pdf->Write(4, $txt2);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt31);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt32);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt33);

	//Segunda hoja del contrato
	//$pdf->AddPage();
	$pdf->Ln(8);
	$txt34 = utf8_decode("SEGUNDA.- ");
	$txt35 = utf8_decode("DISPOSICIÓN.- \"LAS ACREDITADAS\"");
	$txt36 = utf8_decode(" podrán ejercer el importe del crédito concedido, al momento de la firma del presente contrato ya sea en una o varias disposiciones. Previo al retiro del importe del  crédito concedido, ");
	$txt37 = utf8_decode(" deberán realizar el pago de la comisión por apertura, la cual equivale al ".($grupo['GRU_COMISION_P']*100)."% del crédito concedido.");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt34);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt35);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt36);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt32);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt37);

	$pdf->Ln(15);
	$txt38 = utf8_decode("TERCERA.- ");
	$txt39 = utf8_decode("RESPONSABILIDAD MANCOMUNADA.- \"LAS ACREDITADAS\"");
	$txt40 = utf8_decode(" responderán mancomunadamente del pago del crédito solicitado mediante este contrato, en caso de incumplimiento el pago del crédito y sus accesorios podrán ser exigidos individualmente a ");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt38);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt39);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt40);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt32);

	$pdf->Ln(15);
	$txt41 = utf8_decode("CUARTA.- ");
	$txt42 = utf8_decode("PAGOS.- \"LAS ACREDITADAS\"");
	$txt43 = utf8_decode(" se obligan a pagar a  ");
	$txt44 = utf8_decode("en un plazo de ");
	$txt45 = utf8_decode($grupo['GRU_PLAZO']." semanas ");
	$txt46 = utf8_decode("contados a partir de la firma del presente contrato, la totalidad del monto del crédito cuya cantidad asciende a de $".$monto_bonito." (".$monto_letras."). Más los correspondientes intereses, lo anterior de acuerdo a la tabla de amortización en que se detallan las fechas y montos de pago, misma que se adjunta al presente contrato como ANEXO 1.");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt41);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt42);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt43);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt44);
	$pdf->SetFont('Arial','U',9);
	$pdf->Write(4, $txt45);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt46);
	$pdf->Ln(8);

	$fecha_inicial = date($grupo['GRU_FECHA_INICIAL']);
	$dia_junta = $pdf->diaLetras($grupo['GRU_FECHA_INICIAL']);
	$txt47 = utf8_decode("Los pagos se realizarán el día ");
	$txt48 = utf8_decode($dia_junta." de cada semana ");
	$txt49 = utf8_decode("en un horario acordado previamente entre las partes, en el domicilio ubicado en la ");
	$pdf->Write(4, $txt47);
	$pdf->SetFont('Arial','U',9);
	$pdf->Write(4, $txt48);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt49);
	$pdf->SetFont('Arial','U',9);
	if($grupo['GRU_DOMICILIO'] == "")
		$grupo['GRU_DOMICILIO'] = "-";
	$pdf->Write(4, $grupo['GRU_DOMICILIO']);


	$pdf->Ln(15);
	$txt50 = utf8_decode("QUINTA.- ");
	$txt51 = utf8_decode("INTERESES.- \"LAS ACREDITADAS\"");
	$tasa_bonito = number_format($grupo['GRU_TASA']*100, 2);
	$txt52 = utf8_decode(" se comprometen a pagar un interés del ".$tasa_bonito."% (uno punto setenta y cinco por ciento) semanal de la cantidad total del crédito, por concepto de intereses. ");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt50);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt51);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt52);

	$txt53 = utf8_decode("Dado que las amortizaciones del crédito se llevaran a cabo semanalmente, si ");
	$txt54 = utf8_decode("incurrieran en mora en el pago semanal del crédito pactado, ");
	$txt55 = utf8_decode(" aplicará un interés equivalente al 1.6% (uno punto seis por ciento) diario, hasta que se regularicen los pagos.");
	$pdf->Write(4, $txt55);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt32);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt54);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt55);


	$pdf->Ln(15);
	$txt56 = utf8_decode("SEXTA.- ");
	$txt57 = utf8_decode("GARANTÍAS.-");
	$txt58 = utf8_decode(" En virtud del crédito que en este acto se confiere, ");
	$txt59 = utf8_decode("\"LA ACREDITADA\"");
	$txt60 = utf8_decode(" pone disposición de ");
	$txt61 = utf8_decode(", en calidad de garantía los siguientes pagarés:");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt56);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt57);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt58);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt60);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt61);

	$pdf->Ln(8);
	$fecha_limite = strtotime($pagos[(count($pagos)-1)]['TP_FECHA']);
	$dia = date("d", $fecha_limite);
	$mes = date("m", $fecha_limite);
	$anio = date("Y", $fecha_limite);
	$txt62 = utf8_decode("·	Pagare por la cantidad de $".$monto_bonito." (".$monto_letras."), el cual deberá ser liquidado a más tardar el día ".$dia." de ".$pdf->mes($mes)." del año ".$anio."");

	$pdf->Write(4, $txt62);

	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, "Materiales tangibles suscritos en la solicitud");


	$pdf->Ln(15);
	$txt38 = utf8_decode("SÉPTIMA.- ");
	$txt39 = utf8_decode("CAUSAS DE VENCIMIENTO ANTICIPADO.- \"EL ACREDITANTE\"");
	$txt40 = utf8_decode(" podrá dar por vencido anticipadamente este contrato, y por lo tanto, exigir el pago inmediato de la suerte principal y demás accesorios legales que correspondan, si  ");
	$txt41 = utf8_decode("faltare al cumplimiento de cualquiera de las obligaciones aquí establecidas a su cargo y en especial si deja de cubrir puntualmente cualquier cantidad a su cargo, derivada del presente contrato.");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt38);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt39);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt40);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt41);



	$pdf->Ln(15);
	$txt38 = utf8_decode("OCTAVA.- ");
	$txt39 = utf8_decode("ACCIONES.- \"EL ACREDITANTE\"");
	$txt40 = utf8_decode(" se reserva la facultad de obtener el cobro de los saldos a cargo de   ");
	$txt41 = utf8_decode(" ejercitando la vía ejecutiva mercantil, la ordinaria o la que en su caso corresponda, en la inteligencia de que si se sigue la vía ejecutiva, ");
	$txt42 = utf8_decode(" podrá señalar los bienes suficientes para embargo, sin sujetarse al orden que establece el artículo 1395 mil trescientos noventa y cinco del Código de Comercio. Conviniéndose además expresamente, en que el ejercicio de alguna de estas acciones no implicará la pérdida de la otra, y que todas las que competen a ");
	$txt43 = utf8_decode(" permanecerán íntegramente subsistentes en tanto no se liquide la totalidad del crédito y sus accesorios a cargo de ");
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, $txt38);	
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt39);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt40);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt41);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt42);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, $txt43);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);

	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, " otorga a ");
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" su expreso consentimiento para que las cantidades que consigne en caso de juicio, las aplique en el siguiente orden: En primer lugar a gastos y costos del juicio, en segundo lugar a impuestos que se generen derivados del presente contrato, en tercer lugar a gastos de cobranza, en cuarto lugar a intereses moratorios, en quinto lugar a intereses ordinarios vencidos, en sexto lugar a intereses ordinarios vigentes, en séptimo lugar al capital vencido no pagado y el remanente al capital vigente del crédito. "));

	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("NOVENA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("INFORMACIÓN CREDITICIA.- \"LA ACREDITADA\""));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" autoriza expresamente a  "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode("para que en cualquier momento solicite y proporcione información a cualquier entidad de Información de Crédito, y a que utilice cualquier otro medio que considere pertinente para obtener información de su historial crediticio y verificar la información asentada en el presente contrato."));


	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("IMPUESTOS.- "));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" Cada una de las partes contratantes se hará cargo de los impuestos, derechos o cualquier otra prestación de carácter fiscal presente o futura, que le corresponda pagar conforme a la legislación fiscal vigente con motivo de la celebración y ejecución del presente contrato.  "));


	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA PRIMERA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("HEREDEROS, SUCESORES Y CESIONARIOS.- "));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" Este contrato operará para beneficio y será obligatorio para los herederos, sucesores o cesionarios de cada una de las partes y las obligaciones aquí contenidas subsistirán a cualquier evento, ya sea de  "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" o de "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt59);
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(", que convencional o legalmente sean o deban de ser trasmitidos, ya sea por herencia, sucesión, cesión, o cualquier otro medio de transmisión."));


	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA SEGUNDA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("CASO FORTUITO.- \"LAS ACREDITADAS\".- "));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" se obliga al cumplimiento del presente contrato, aún en caso fortuito o de fuerza mayor, en términos del artículo 2111 dos mil ciento once del Código Civil Federal."));


	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA TERCERA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, "DOMICILIOS");	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" Para todos los efectos judiciales y extrajudiciales relativos al presente contrato, las partes señalan como sus domicilios los siguientes: "));

	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt5);
	$pdf->Ln();
	$pdf->Write(4, utf8_decode("Francisco Jesús De La Rosa Diez Gutiérrez:"));
	$pdf->SetFont('Arial','',9);
	$pdf->Ln();
	$pdf->Write(4, utf8_decode(" C. Paseo Campestre Nº212, Fracc. Campestre Potosino de Golf, C.P. 78151, San Luis Potosí, S.L.P."));
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("Alejandro Cagigas Tiburcio:"));
	$pdf->SetFont('Arial','',9);
	$pdf->Ln();
	$pdf->Write(4, utf8_decode("Ave. Ejército Mexicano #892 Int.1  Col. Ylang Ylang Boca del Río, Ver, C.P. "));

	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, $txt32);
	$pdf->Ln();
	$pdf->SetFont('Arial','',9);
	foreach ($personas as $persona) {
		$pdf->Write(4, utf8_decode($persona['PER_NOMBRE']));
		$pdf->Ln();
		$pdf->Write(4, utf8_decode($persona['PER_DIRECCION']));
		$pdf->Ln();
		$pdf->Ln();
	}

	//$pdf->AddPage();
	$pdf->Ln();
	$pdf->Write(4, utf8_decode("Toda la correspondencia se dirigirá a y todas las notificaciones se deberán de hacer, en los domicilios arriba mencionados, a menos que cualesquiera de las partes informe a la otra parte su cambio de domicilio, caso en el cual la correspondencia se dirigirá a y las notificaciones se harán en el nuevo domicilio, se considerará que los avisos entre las partes han sido dados cuando se envíen por correo certificado con acuse de recibo, o personalmente con acuse de recibo."));

	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA CUARTA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("LOS SUBTÍTULOS.-"));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" Las partes hacen constar que los subtítulos de cada una de las cláusulas del presente contrato, son únicamente para pronta referencia e identificación, por lo que no se consideran para efectos de interpretación o cumplimiento de los mismos. "));

	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA QUINTA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, "RECONOCIMIENTO DE LAS PARTES.- ");	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" Las partes convienen en que este contrato contiene su voluntad expresa en cuanto a lo que en el mismo se especifica, por consiguiente, cualquier otro convenio, contrato o arreglo en forma verbal o escrita que se haya elaborado o que tácitamente pudiera interpretarse como manifestación de voluntad al respecto, queda desde ahora sin efectos. Las posteriores modificaciones que afecten a este documento, deberán hacerse por escrito y ser firmadas por ambas partes."));

	$pdf->Ln(15);
	$pdf->SetFont('Arial','BU',9);
	$pdf->Write(4, utf8_decode("DÉCIMA SEXTA.- "));
	$pdf->SetFont('Arial','B',9);
	$pdf->Write(4, utf8_decode("INTERPRETACIÓN, JURISDICCIÓN Y COMPETENCIA.- "));	
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode("Para todo lo relativo a la interpretación y cumplimiento del presente contrato, las partes se someten a la Jurisdicción y Competencia de los Tribunales de la ciudad de San Pedro Garza García, N.L., que serán los únicos competentes para conocer de cualquier reclamación derivada del presente contrato, a tales efectos renuncian expresamente a la competencia que en razón de su domicilio presente o futuro, les pudiere llegar a corresponder."));



	$pdf->Write(4, utf8_decode("L E I D O el presente contrato y enteradas las partes de su contenido y alcance legal lo firman al margen y al calce por duplicado, en la Ciudad de "));
	$pdf->SetFont('Arial','U',9);
	$fecha = strtotime($grupo['GRU_FECHA']);
	$dia = date("d", $fecha);
	$mes = date("m", $fecha);
	$anio = date("Y", $fecha);
	$pdf->Write(4, utf8_decode("San Pedro Garza García, Nuevo León, al día ".$dia." de ".$pdf->mes($mes)." del ".$anio));
	$pdf->SetFont('Arial','',9);
	$pdf->Write(4, utf8_decode(" en la inteligencia de que cada ejemplar será considerado un original y ambos en su conjunto constituirán uno y el mismo instrumento."));

	/*FIRMAS DE ACREDITANTES*/
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0, 4, $txt5, 0, 1, 'C');
	$pdf->SetDrawColor(0,0,0);//linea de contorno
	$pdf->Ln(22);
	$pdf->Cell(75, 5,"","T",0,"L",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,"","T",0,"R",0);
	$pdf->Ln();
	$pdf->Cell(75, 5,"FRANCISCO JESUS DE LA ROSA DIEZ GUTIERREZ",0,0,"C",0);
	$pdf->Cell(40, 5,"",0,0,"L",0);
	$pdf->Cell(75, 5,"ALEJANDRO CAGIGAS TIBURCIO",0,0,"C",0);

	/*FIRMAS DE ACREDITADAS*/
	$pdf->AddPage();
	$pdf->Ln(15);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(0, 4, $txt32, 0, 1, 'C');
	$pdf->SetDrawColor(0,0,0);//linea de contorno	
	$pdf->Ln(22);
	$c = true;
	foreach ($personas as $persona) {
		if($c) {
			$pdf->Cell(75, 8,utf8_decode($persona['PER_NOMBRE']),"T",0,"C",0);
			$pdf->Cell(40, 8,"",0,0,"L",0);
			$c = false;
		} else {
			$pdf->Cell(75, 8,utf8_decode($persona['PER_NOMBRE']),"T",0,"C",0);
			$pdf->Ln(30);
			$c = true;
		}
	}

	/*TABLA DE PAGOS*/
	$pdf->AddPage();
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
		$fecha = date("d/m/Y", strtotime($pago['TP_FECHA']));
		$pdf->Cell(63, 7, $fecha, $border, 0, "C");
		$monto = number_format($pago['TP_MONTO'], 2);
		$pdf->Cell(63, 7, $monto, $border, 0, "C");
		$index++;
		$pdf->Ln();
	}

	$archivo = 'pagare1.pdf';
	$pdf->Output();
	//$pdf->Output('../files/pagares/'.$archivo, 'F');
} else {
	die("Pagaré No Disponible");
}
?>