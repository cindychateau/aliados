<?php
/*
 * El array $databases contiene todos los parametros de conexión para las BBDD,
 * se pueden definir cuantar BBDD se desean pero son 2 las que siempre debene de haber
 * "production" y "development".
 * 
 * Para agregar un grupo de parametros de conexión, se establecen como otro elemento del array $databases,
 * los parametros se agrupan en otro array, en pocas palabras el array $databases es un array multidimensional
 * 
 * A continuación se enlistan los parametros de la BBDD
 * 
 * @param 'default' string. Contiene los parametros que se van a tomar para realizar la conexión con la BBDD
 * 
 * Parametros de conexión:
 * 
 * @param 'dbms' string. Refiere que tipo de sistema de gestion de base de datos vamos a usar las opciones son:
 *		mysql => MySQL
 * 		sql => SQL
 * 		oracle => ORACLE
 * 
 * @param 'host' string. Almacena el host de la BBDD a la cuál nos vamos a conectar.
 * @param 'name' string. Indica el nombre de la BBDD a la cuál se va a conectar.
 * @param 'user' string. Nombre de usuario para establecer la conexión.
 * @param 'password' string. Almacena la contraseña que se empleará para la conexión.
 * @param 'encoding' string. Establece la codificación de caracteres, si se deja vacio la conexión se realizará con
 * la codificación por default de la BBDD
 * @param 'port' int. Si se desea conectar a la BBDD por un puerto especifico se indica en este paramtero como entero,
 * en caso contrario dejar el parametro vacio.
 * @param 'persistant' bool.  Para establecer la conexion como persistente cambiar el parametro a true. Por default deberá estar
 * como false.
 */
$databases = array(
	'default' => 'DESARROLLO',
	
	//Se establecen los parametros de conexión de la BBDD developmetn
	'PRODUCCION' => array(
		'dbms' => "mysql",
		'host' => 'localhost',
		'name' => 'aliadosc_aliados',
		'user' => 'aliadosc_usr',
		'password' => "Aliados2016",
		'encoding' => 'utf8',
		'port' => '',
		'persistant' => false,
	),
	'DESARROLLO' => array(
		'dbms' => "mysql",
		'host' => 'localhost',
		'name' => 'aliados',
		'user' => 'root',
		'password' => '',
		'encoding' => 'utf8',
		'port' => '',
		'persistant' => false,
	),
);

	class Database {
		//Se establece la variable que contendrá la conexión con la BBDD
		protected $_conexion = null;
		
		/*
		 * 
		 * @version: 0.2 2013-04-01
		 * 
		 * Metodo constructor de la clase que establece la conexión con la base de datos
		 * por medio de los parametros establecidos en el array $databases
		 */	
		public function __construct() {
			global $databases;
			
			//Se obtiene el array con los parametros de conexión
			$dbDefault = $databases[$databases["default"]];
			
			//Se determina que DBMS se va a utilizar para establecer la conexión
			switch ($dbDefault["dbms"]) {
				//Se conecta con MySQL
				case 'mysql':
					$this->conexionMysql($dbDefault);
					break;
				
				//Se conecta con SQL
				case 'sql':
					$this->conexionSql($dbDefault);
					break;
				
				//Se conecta con ORACLE
				case 'oracle':
					$this->conexionOracle($dbDefault);
					break;
			}
			
		}

		/*
		 *
		 * @version. 0.1 2013-04-09
		 * 
		 * @param $table string. Contiene la tabla la cual se va a borrar los registrps
		 * @param $fields. Array que contiene tanto el nombre del campo como el valor:
		 * 		'nombreDeCampo' como key del elemento en el array
		 * 		'valordeCampo' valor que se tomara para realizar el match.
		 * 
		 * @return bool. Devuelve verdadero o falso dependiendo si se ejecuto la consulta
		 * 
		 * @pd $sentencia se puede o no establecer
		 * 
		 * Metodo que elimina los registros de una tabla o todos los registros de la tabla, si se deja nulo el valor de fields
		 */
		public function delete($table, $fields = null) {
			
			
			 
			//Se verifica si el usuario desea borrar todos los registros o solo un registro
			if (is_null($fields)) {
				//Se prepara la consulta
				$consulta = $this->_conexion->prepare("DELETE FROM ".$table);
			} else {
				//Se prepara la consulta
				$key = key($fields);
				$consulta = $this->_conexion->prepare("DELETE FROM WHERE ".$key." = :valor");
				$consulta->bindParam(":valor", $fields );
			}
			
			//Se realiza la consulta
			try {
				$consulta->execute();
				
				//Se verifica si se ha ejecutado la consulta
				if ($consulta->rowCount() > 0) {
					//Se devuelve la respuesta
					return true;
				} else {
					//Se devuelve la respuesta
					return false;
				}
			} catch(PDOException $e) {
				die($e->getMessage());
			}
		}
		
		/*
		 * 
		 * @version: 0.1 2013-07-15
		 * 
		 * @param $table string. Contendrá la tabla sobre cual se ejecutará la acción
		 * @param $params array. Array asociativo la clave es el nombre del campo mientras que el valor es el valor
		 * 						 a insertar de dicho campo.
		 * 
		 * @return bool. Devuelve falso o verdadero dependiendo si se ejecuto la consulta.
		 * 
		 * Metodo que inserta datos en una tabla
		 */
		public function insert($table, $params) {
			//Se establecen las variables de control
			$i = 1;
			$campos = "";
			$valores = "";
			$datos = array();
						
			foreach ($params as $clave => $valor) {
				$campos .= (count($params) == $i) ? stripslashes($clave) : stripslashes($clave).", ";
				$valores .= (count($params) == $i) ? "?" : "?, ";
				$datos[] = $valor;
				$i++;
			}
			
			//Se forma el query
			$query = "INSERT INTO ".$table." (".$campos.") VALUES (".$valores.")";
			
			//Se ejecuta la consulta
			try {
				$consulta = $this->_conexion->prepare($query);
				
				return ($consulta->execute($datos)) ? true : false;
			} catch(PDOException $e) {
				die($e->getMessage());
			}
		}
		
		/*
		 *
		 * @version: 0.1 2013-07-17
		 */
		public function update($table, $params, $where) {
			//Se prepara la consulta
			$query = "UPDATE ".$table." SET ";
			$i = 1;
			$valores = array();
			$keyWhere = key($where);
			$keyWhere = explode(" ", $keyWhere);
			$operador = (count($keyWhere) == 1) ? "=" : $keyWhere[1];
			
			foreach ($params as $clave => $valor) {
				$query .= ($i == count($params)) ? $clave." = ?" : $clave." = ?, ";
				$valores[] = $valor;
			}
		}
		
		/*
		 * 
		 * @version. 0.1 2013-06-16
		 * 
		 * @param $params array. Contiene los campos y valores a evaluar.
		 * 		$params['table'] - La tabla donde se van a cotejar los campos
		 * 		$param['nombreDelCampoDeLaTabla'] - El identificador de la fila indica el nombre del campo en la BBDD y el valor
		 * 											indica el valor a cotejar.
		 * @param $singleString bool. Si el valor el tru se cotejarán todos los campos y valores como una cadena en conjunta.
		 * 
		 * @return mixed. Si el usuario solicita una validación de string (multiple o simple) regresará verdadero o falso (bool)
		 * 				  de lo contrario devolvera un array, siendo cada renglon la comparativa individual.
		 * 
		 * Metodo que determina si un valor se encuentra en la BBDD.
		 */
		public function isInDb($table, $field, $value) {
			//Se prepara la consulta
			$consulta = $this->_conexion->prepare("SELECT * FROM ".$table." WHERE ".$field." = :valor");
			$consulta->bindParam(':valor', $value);
			
			//Se ejecuta la consulta
			try {
				$consulta->execute();
				
				if ($consulta->rowCount() > 0) {
					return true;
				} else {
					return false;
				}
			} catch(PDOException $e) {
				die($e->getMessage());
			}
		}
		
		/*
		 * 
		 * @version: 0.1 2013-04-01
		 * 
		 * @param $params array. Contiene los paramteros de conexion con la BBDD
		 * 
		 * Metodo que establece la conexion con una BBDD MySQL
		 */
		private function conexionMysql($params) {
			//Se crea el DSN para la conexión
			$dsn = "mysql:host=".$params["host"].";dbname=".$params["name"];
			$dsn .= (strlen(trim($params["encoding"])) == 0) ? "" : ";charset=".$params["encoding"];
			$dsn .= (strlen(trim($params["port"])) == 0) ? "" : ";port=".$params["port"];
			
			try {
				//Se intenta establecer la conexion con la BBDD
				$this->_conexion = new PDO($dsn,$params["user"],$params["password"],array(PDO::ATTR_PERSISTENT => $params["persistant"]));
				//Se activa las excepciones de PDO
				$this->_conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch(PDOException $e) {
				//Si no se puedo establecer la conexion se mata el script y se informa al usuario
				die($e->getMessage());
			}
		}
		
		/*
		 * 
		 * @version: 0.1 2013-04-01
		 * 
		 * El método destructor de la clase cierra la conexión con la BBDD
		 */
		public function __destruct() {
			//Se cierra la conexión a la BBDD
			$this->_conexion = null;
		}

		/*
		 *
		 * @version: 0.1 2013-07-29
		 * 
		 * Regresa el ultimo id
		 */
		public function last_id() {
			return $this->_conexion->lastInsertId();
		}
	}
?>