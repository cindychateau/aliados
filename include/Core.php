<?php
//Se incluye la clase Database
include_once 'Database.php';

$baseUrl = $_SERVER["SERVER_NAME"]."/aliados/admin/";
//$baseUrl = $_SERVER["SERVER_NAME"]."/admin/";
	/*
	 * Para la clase Core se requiere la clase Database, ya que la primera extiende la sengunda
	 */
	class Core extends Database {
		
		/*
		 * Se establecen los diferentes parametros de los metodos de la clase
		 */
		protected $classParams = array(
			"salt" => 'blowfish', //Tipo de salt para la encriptación							
		);
		
		/*
		 * @author: Edgar Yerena <edgar.yerena@metodika.mx>
		 * @version: 0.1 2013-06-16
		 * 
		 * @param $string string. Contiene la cadena a validar
		 * 
		 * @return bool. Devuelve verdadero o falso si la cadena es un correo sintacticamente.
		 * 
		 * Metodo que devuelve si una cadena posee el formato de correo electrónico.
		 */
		public function isEmail($string) {
			return (!preg_match("/^[a-z]([\w\.]*)@[a-z]([\w\.-]*)\.[a-z]{2,3}$/", $string)) ? false : true;
		}
		
		/*
		 * @author: Edgar Yerena <edgar.yerena@metodika.mx>
		 * @version: 0.1 2013-10-05
		 * @author: Bruno Chávez <bruno.chavez@metodika.mx>
		 * @version: 0.2 2014-01-07
		 * 
		 * @param $string string. Contiene la cadena a validar
		 * 
		 * @return bool. Devuelve verdadero o falso si la cadena se encuentra vacia o no.
		 * 
		 * Metodo que devuelve si una cadena se encuentra vacia o no
		 */
		public function isPhone($string) {
			return (!preg_match("/0{0,2}([\+]?[\d]{1,3} ?)?([\(]([\d]{2,3})[)] ?)?[0-9][0-9 \-]{6,}( ?([xX]|([eE]xt[\.]?)) ?([\d]{1,5}))?/", $string)) ? false : true;
		}
		
		/*
		 * @author: Edgar Yerena <edgar.yerena@metodika.mx>
		 * @version: 0.1 2013-06-17
		 */
		public function isNull($string) {
			return (strlen(trim($string)) == 0) ? true : false;
		}
		
		/*
		 * @author: Edgar Yerena <edgar.yerena@metodika.mx>
		 * @version: 0.1 2013-11-23
		 */
		public function encrypt($string) {
			//Se voltea la cadena
			$string = strrev($string);
			//Se establece el tipo de salt a aplicar
			switch ($this->classParams["salt"]) {
				case 'blowfish':
					return crypt($string, '$2y$05$op4yhdui30oplkadfetgwui2p');
					break;
				case 'des':
					return crypt($string, 'oY');
					break;
				case 'md5':
					return crypt($string, '$1$heol38s028fg$');
					break;
				default:
					return crypt($string);
					break;
			}
			
			//Se devuelve la cadena
			return $string;
		}
		
		/*
		 * @author: Edgar Yerena <edgar.yerena@metodika.mx>
		 * @version. 0.1 2013-04-01
		 * 
		 * @param $params array. Contiene los datos para establecer las credenciales del usuario
		 * 		$params[table] - La tabla en donde se van a cotejar los datos
		 * 		$params[nombreCampoUsername] => "valorFormularioUsername" - Coteja el valor del formulario con la BBDD
		 * 		$params[nombreCampoPassword] => "valorFormularioPassword" - Coteja el valor del formulario con la BBDD
		 * 
		 * @param $encrypt string. Si se desea que se encripte primero la contraseña para despues cotejarla con la BBDD,
		 * los valores que puede poseer este parametro son:
		 * 		'md5' => md5()
		 * 		'sha1' => sha1()
		 * 		'crc' => crc32()
		 * 
		 * @param $credentials array. Contedrá los campos que se desea que se devuelva, de la misma tabla de donde se
		 * cotejarón los campos
		 * 
		 * @return array. Devuelve un array multidimensional.
		 * 		'userAuth' => bool. Devuelve verdadero o falso dependiendo si se ha autentificado el usuario.
		 * 		'credentials' => array. Devuelve las credenciales solicitados por el usuario
		 * 
		 * @pd 'credentials' es un valor de retorno que se puede devolver o no, dependiendo si el usuario pidio
		 * credenciales
		 * 
		 * Metodo que determina si un usuario esta autentificado correctamente y si lo esta se devuelve las
		 * credenciales del mismo.
		 */
		public function authUser($params, $credentials = null) {
			//Se obtienen los parametros separados
			
			$tabla = $params["table"];
			$fields = array(key(array_slice($params, 1, 1)), key(array_slice($params, 2, 1)));
			$user = $params[$fields[0]];
			$password = self::encrypt($params[$fields[1]]);
			//echo $password;
			//print_r($params);
			
			//Se establece la variable que contendrá la respuesta que se regresará
			$auth = array();
			//die(print_r($fields));
			//Se prepará la consulta
			$sql = "SELECT * FROM ".$tabla." WHERE ".$fields[1]." = :password AND ".$fields[0]." = :user";
			
			$consulta = $this->_conexion->prepare($sql);
			
			$consulta->bindParam(":password", $password);
			$consulta->bindParam(":user", $user);
			//die($sql."pass: ".$password." user: ".$user);
			//Se verifica si el usuario a mostrado datos correctos
			try {
				$consulta->execute();
				if ($consulta->rowCount() == 1) {
					//Se obtienen las credenciales solicitas
					if (!empty($credentials)) {
						$credentials = implode(", ", $credentials);
						$credentials = rtrim($credentials, ", ");
						$sql = "SELECT ".$credentials." FROM ".$tabla." WHERE ".$fields[1]." = :password AND ".$fields[0]." = :user";
						$getCredentials = $this->_conexion->prepare($sql);
						$getCredentials->bindParam(":password", $password);
						$getCredentials->bindParam(":user", $user);
						
						$getCredentials->execute();
							
							//Se obtiene los datos de las credenciales
						$auth["credentials"] = $getCredentials->fetch(PDO::FETCH_ASSOC);
					}					
					$auth["userAuth"] = true;
				} else {
					$auth["userAuth"] = false;
				}
			} catch(PDOException $e) {
				die($e->getMessage()."-- SQL: ".$sql);
			}
			
			//Se devuelve el resultado
			return $auth;
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2013-12-26
		 * 
		 * @return '$permisos' array. Matriz de módulos con sus arreglos respectivos de permisos ([0]->Vista, [1]->Alta, [2]->Baja, [3]->Cambio)
		 * 
		 * Metodo que devuelve los permisos de un usuario
		 */
		public function getPermissions() {
			if (!isset($_SESSION)) {
				@session_start();
			}
			$user = $_SESSION["mp"]["userid"];

			//Se prepara la consulta para ejecución
			$consulta = $this->_conexion->prepare('SELECT SUP_PERMISO
														FROM SISTEMA_USUARIO_PERFIL
														INNER JOIN SISTEMA_USUARIO 
														ON SISTEMA_USUARIO.SUP_ID = SISTEMA_USUARIO_PERFIL.SUP_ID
														WHERE SIU_ID = ?');
			
			//Se ejecuta la consulta
			try {
				$consulta->execute(array($user));
				$puntero = $consulta->fetch(PDO::FETCH_ASSOC);
				$puntero = $puntero["SUP_PERMISO"];
		
				//Se procesa los permisos
				$permisos = array();
				$puntero = explode("|", $puntero);
				
				foreach ($puntero as $clave => $valor) {
					$temporal = explode("-", $valor);
					$modulo = $temporal[0];
					$permisos[$modulo] = str_split($temporal[1]);
				}
				
				//Se guardan los permisos en la variable de sesion
				//$_SESSION["permisos"] = $permisos;
				return $permisos;
			} catch(PDOException $e) {
				die($e->getMessage());
			}
		}

		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2013-12-26
		 * 
		 * @param '$module'	int. 		ID de módulo
		 * @param '$type'	string. 	Tipo de botón
		 * @param '$params'	array. 		Link al que va, título de botón y clases de botón
		 * 
		 * @return '$boton'	string. 	Código html de botón en base a parámetros de entrada
		 * 
		 * Metodo que devuelve un botón en base a permisos
		 */
		public function printButton($module, $type, $params = null, $icon = null) {
			$permissions = $this->getPermissions();

			//Se establecen las variables de control
			if (count(@$params) == 0) {
				$link = "";
				$title = "";
				$classes = "";
				$data_id = "";
				$extras = "";
			} else {
				$link = (!isset($params["link"])) ? "" : $params["link"];
				$title = (!isset($params["title"])) ? "" : $params["title"];
				$classes = (!isset($params["classes"])) ? "" : $params["classes"];
				$data_id = (!isset($params["data_id"])) ? "" : $params["data_id"];
				$extras = (!isset($params["extras"])) ? "" : $params["extras"];
			}

			//Se verifica que el usuario tenga permisos para estar en el modulo
			if (array_key_exists($module, $permissions)) {
				//Se establece la variable de trabajo
				$boton = "";
				
				//Se verifica si tiene el permiso para realizar la acción
				switch ($type) {
					case 'alta':
						if ($permissions[$module][1] == 1) {
							if(is_null($icon)) {
								$icon = "fa-plus-circle";
							}
							$boton = '<a class="'.$classes.'" href="'.$link.'" data-id="'.$data_id.'" '.$extras.' ><button class="btn btn-success"><i class="fa '.$icon.'"></i>'.$title.'</button></a>';
						}
						break;
						
					case 'baja':
						if ($permissions[$module][2] == 1) {
							if(is_null($icon)) {
								$icon = "fa-trash-o";
							}
							$boton = '<a class="'.$classes.'" href="'.$link.'" data-id="'.$data_id.'" '.$extras.' ><button class="btn btn-danger"><i class="fa '.$icon.'"></i>'.$title.'</button></a>';
						}
						break;
						
					case 'cambios':
						if ($permissions[$module][3] == 1) {
							if(is_null($icon)) {
								$icon = "fa-pencil";
							}
							$boton = '<a class="'.$classes.'" href="'.$link.'" data-id="'.$data_id.'" '.$extras.' ><button class="btn btn-danger"><i class="fa '.$icon.'"></i>'.$title.'</button></a>';
						}
						break;
				}
				
				return $boton;
			} else {
				return "";
				return false;
			}

		}

		public function printLink($module, $type, $link) {
			$permissions = $this->getPermissions();

			//Se verifica que el usuario tenga permisos para estar en el modulo
			if (array_key_exists($module, $permissions)) {
				//Se establece la variable de trabajo
				$lnk = "";
				
				//Se verifica si tiene el permiso para realizar la acción
				switch ($type) {
					case 'alta':
						if ($permissions[$module][1] == 1) {
						
							$lnk = $link;
						}
						break;
						
					case 'baja':
						if ($permissions[$module][2] == 1) {
						
							$lnk =$link;
						}
						break;
						
					case 'cambios':
						if ($permissions[$module][3] == 1) {
						
							$lnk =$link;
						}
						break;
				}
				
				return $lnk;
			} else {
				return "";
				return false;
			}

		}


		/*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2013-12-27
		 * 
		 * @param '$module'		int. 		ID de módulo
		 * @param '$destiny'	string. 	En qué parte del módulo está ('alta.php', 'baja.php', 'cambios.php', '')
		 * 
		 * 
		 * Verifica permisos de usuario y redirecciona en base a ellos
		 */
		public function sentinel($module, $destiny=''){
			if (!isset($_SESSION)) {
				@session_start();
			}
			global $ruta;
			if(isset($_SESSION["mp"]["userid"])) {
				$permisos = $this->getPermissions();
				if(isset($permisos[$module])){
					switch ($destiny) {
						case 'alta.php':
							$stay = $permisos[$module][1]==1?true:false;
							break;
						case 'baja.php':
							$stay = $permisos[$module][2]==1?true:false;
							break;
						case 'cambios.php':
							$stay = $permisos[$module][3]==1?true:false;
							break;
						default:
							$stay = $permisos[$module][0]==1?true:false;
							break;
					}

					if(!$stay)
						header("Location:".$ruta);
				} else if($module != 0){
					header("Location:".$ruta);
				}
				
			} else {
				session_destroy();
				header("Location:".$ruta."index.php");
			}
		}
		
		 /*
		 * @author: Bruno Chávez <bruno.chavez@metodika.mx>
		 * @version: 0.1 2014-01-07
		 * 
		 * @param '$filename'	String. 	Nombre del archivo a validar.
		 * 
		 * @return bool. 			falso o verdadero si es de extension valido.
		 * 
		 * Metodo que que valida que la extension del archivo sea valido
		 */		
		private function fileExtension ($filename) {
			$ext = strtolower(substr(strrchr($filename, '.'), 1));
			
			switch ($ext) {
				case 'jpg': 
				case 'gif': 
				case 'jpeg':
				case 'png':
				case 'tif':
				case 'doc': 
				case 'docx':
				case 'txt':
				case 'rtf':
				case 'xls':
				case 'xlsx':
				case 'ppt': 
				case 'pptx':
				case 'pdf':
					return true;
				break;
				default:
					return false;
				break;
			}
		}
		
		
		 /*
		 * @author: Bruno Chávez <bruno.chavez@metodika.mx>
		 * @version: 0.1 2014-01-07
		 * 
		 * @param '$filename'	String. 	Nombre del archivo a validar.
		 * @param '$mime' 	String.		Mimetype a validar con respecto a la extension del archivo
		 * 
		 * @return bool. 			falso o verdadero si el archivo es valido.
		 * 
		 * Metodo que valida que la extension del archivo corresponda con el mimetype de carga del mismo.
		 */		
		 private function mimeCheck($filename, $mime) {
			$file_info = new finfo(FILEINFO_MIME);  // Obtiene la informacion del mime type en el archivo por medio binario
			$mime_type = $file_info->buffer(file_get_contents($filename));  // devuelve el mime type real del archivo y el tipo de archivo
			$type = explode(';', $mime_type); // Extrae unicamente el mimetype
			
			if (trim($type[0]) == $mime) 
				return true;
			else
				return false;
		 }
		 
		 /*
		 * @author: Bruno Chávez <bruno.chavez@metodika.mx>
		 * @version: 0.1 2014-01-07
		 * 
		 * @param '$file'	String. 	Nombre del archivo.
		 * @param 'type'	String. 	Tipo de archivo por grupo: imagen, word, powerpoint, excel, pdf, etc.
		 * 			'imagen'	jpg, jpeg, png, tif, gif
		 * 			'texto'		txt, rtf
		 * 			'word'		doc, docx
		 * 			'powerpoint'	ppt, pptx
		 * 			'excel'		xls, xlsx
		 * 			'pdf'		pdf
		 * 	 
		 * @return bool. 			falso o verdadero si el archivo pertenece a un grupo valido.
		 * 
		 * Metodo que indica si un archivo pertenece al grupo de archivos que se le solicita.
		 */
		 private function groupExtensionCheck($filename, $type) {
			$ext = strtolower(substr(strrchr($filename, '.'), 1));
			
			switch ($type) {
				case 'imagen':
					switch ($ext) {
						case 'jpg': 
						case 'gif': 
						case 'jpeg':
						case 'tif':
						case 'png':
							return true;
						break;
						default:
							return false;
						break;
					}					
				break;
				case 'texto':
					switch ($ext) {
						case 'txt':
						case 'rtf':
							return true;
						break;
						default:
							return false;
						break;
					}
				break;
				case 'word':
					switch ($ext) {
						case 'doc':
						case 'docx':
							return true;
						break;
						default:
							return false;
						break;
					}
				break;
				case 'powerpoint':
					switch ($ext) {
						case 'ppt':
						case 'pptx':
							return true;
						break;
						default:
							return false;
						break;
					}
				break;
				case 'excel':
					switch ($ext) {
						case 'xls':
						case 'xlsx':
							return true;
						break;
						default:
							return false;
						break;
					}
				break;
				case 'pdf':
					if ($ext == 'pdf') 
						return true;
					else
						return false;
				break;
				default:
					return false;
				break;
			}			
		 }
		 
		 /*
		 * @author: Bruno Chávez <bruno.chavez@metodika.mx>
		 * @version: 0.1 2014-01-07
		 * 
		 * @param '$file'	Array. 		Arreglo $_FILES envia.
		 * @param '$size'	int. 		Peso del archivo en kb.
		 * @param 'tipo'	String. 	Tipo de archivo por grupo: imagen, word, powerpoint, excel, pdf, etc.
		 * 			'imagen'	jpg, jpeg, png, tif, gif
		 * 			'texto'		txt, rtf
		 * 			'word'		doc, docx
		 * 			'powerpoint'	ppt, pptx
		 * 			'excel'		xls, xlsx
		 * 			'pdf'		pdf
		 * 
		 * @return '$array'. Entrega un arreglo con 'msg' Mensaje de error, 'valid' falso o verdadero.
		 * 
		 * Metodo que indica si un archivo es valido en extension, peso y tipo de archivo
		 */
		public function verifyFile ($file, $size, $tipo) {
			if ($file['file']['error'] == 0)  // Valido que el archivo se haya subido sin errores.
				if ($this->mimeCheck($file['file']['tmp_name'], $file['file']['type']))
					if ($this->fileExtension($file['file']['name']))
						if ($this->groupExtensionCheck($file['file']['name'], $tipo))
							if ($file['file']['size'] <= $size)
								return array('msg'=>'Archivo Valido', 'valid'=>true);
							else
								return array('msg'=>'Archivo muy grande', 'valid'=>false);
						else 
							return array('msg'=>'El archivo no corresponde al grupo indicado', 'valid'=>false);
					else
						return array('msg'=>'El tipo de archivo no es valido', 'valid'=>false);
				else
					return array('msg'=>'El tipo de archivo no corresponde con su extension', 'valid'=>false);					
			else
				return array('msg'=>'Error en la carga del archivo', 'valid'=>false);
		}

		 /*
		 * @author: Cynthia Castillo <cynthia.castillo@metodika.mx>
		 * @version: 0.1 2015-02-09
		 * 
		 * @param '$module'		int. 	Módulo para el que aplica el 	
		 * 
		 * @return '$profiles'	string.	Regresa un string con todos los id's de perfil de usuario separados por pipes(|)
		 * 
		 * Metodo que regresa todos los usuarios que pueden editar X módulo dependiendo de sus permisos
		 */
		public function getUsersIds ($module) {
			$sql = "SELECT * FROM SISTEMA_USUARIO_PERFIL";
			$consulta = $this->_conexion->prepare($sql);
			$consulta->execute();
			$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);

			$profiles = array();

			//Obtiene todos los perfiles que tienen el módulo que le pasaron
			foreach ($puntero as $row) {
				$permiso = $row["SUP_PERMISO"];
		
				//Se procesa los permisos
				$permisos = array();
				$permiso = explode("|", $permiso);
				
				foreach ($permiso as $clave => $valor) {
					$temporal = explode("-", $valor);
					$modulo = $temporal[0];
					$permisos[$modulo] = str_split($temporal[1]);
				}


				if(isset($permisos[$module][3])) 
					if($permisos[$module][3] == 1)
						$profiles[]= $row["SUP_ID"];
				
			}

			//Obtiene todos los usuarios que tienen ese perfil
			$users = "";
			if(count($profiles)) {
				$restriction = "";
				$values = array();
				foreach ($profiles as $prof) {
					$restriction.= " SUP_ID = ? OR ";
					$values[]= $prof;
				}

				$restriction = rtrim($restriction, " OR ");

				$sql = "SELECT SIU_ID FROM SISTEMA_USUARIO
						WHERE (".$restriction.")";
				$consulta = $this->_conexion->prepare($sql);
				$consulta->execute($values);
				$puntero = $consulta->fetchAll(PDO::FETCH_ASSOC);	

				foreach ($puntero as $row) {
					$users.= $row['SIU_ID']."|";
				}

				$users = rtrim($users, "|");

			}

			return $users;
		} 

	}
?>