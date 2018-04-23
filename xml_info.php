<?php

$url = "../../comision/vivaanuncios.xml";
$xml = simplexml_load_file($url, 'SimpleXMLElement', LIBXML_NOCDATA);

foreach ($xml->property as $prop) {

	$params = array(
					'dbms' => "mysql",
					'host' => 'localhost',
					'name' => 'comision',
					'user' => 'root',
					'password' => '',
					'encoding' => 'utf8',
					'port' => '',
					'persistant' => false,
				);

	$dsn = "mysql:host=".$params["host"].";dbname=".$params["name"];
	$dsn .= (strlen(trim($params["encoding"])) == 0) ? "" : ";charset=".$params["encoding"];
	$dsn .= (strlen(trim($params["port"])) == 0) ? "" : ";port=".$params["port"];
	$db2 = new PDO($dsn,$params["user"],$params["password"],array(PDO::ATTR_PERSISTENT => $params["persistant"]));
	$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$sql_oc = "INSERT INTO vivaanuncios_type (nombre) 
			   VALUES( ? )";
	$values_oc = array($prop->type);

	$consulta_oc = $db2->prepare($sql_oc);

	try {
		$consulta_oc->execute($values_oc);
	} catch (PDOException $e) {
		die($e->getMessage());
	}

	echo $prop->type.'<br>';
}


?>