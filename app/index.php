<?php
require 'db.php';
require_once "../Slim/Slim.php";
include_once('../geoPHP/geoPHP.inc');
require_once("../geoPHP/lib/geometry/Geometry.class.php");

Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim (); // slim run-time object
$app->get('/data','getData');
//$app->post('/linestring(/:id)','postLinestring');

function getData()
{
	//$sql = "SELECT * FROM paths";
	//$body = $app->request->getBody (); // get the body of the HTTP request (from client)
	//$geom = geoPHP::load("LINESTRING($body)");
	$geom = geoPHP::load('LINESTRING(1 1,5 1,5 5,1 5,1 1)');

	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$sql = "INSERT INTO PATHS (geom) values (ST_GeomFromWKB('$insert_string'))";
	//$sql = "INSERT INTO PATHS(geom) VALUES ($geom)";

	
	
	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$res = pg_fetch_all($stmt);
		$db = null;
		echo '{"res": ' . json_encode($res) . '}';
	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function postLinestring()
{
	$body = $app->request->getBody (); // get the body of the HTTP request (from client)
	
	$parts = explode('!',$body);
	$pathName = $parts[0];
	$pathGeom = $parts[1];
	$routeTime = $paths[2];
	$geom = geoPHP::load("LINESTRING('$pathGeom')");
	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$sql = "INSERT INTO routes (routename, routetime, geom) values ('$pathName', $routeTime, ST_GeomFromWKB('$insert_string'))";
	try {
		$db = getDB();
		$stmt =   pg_query($db, $sql);
		$db = null;
		echo 'Working';
	} 
	

	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


$app->map ( "/linestring/(:id)", function ($elementID = null) use ($app)
{
	$body = $app->request->getBody(); // get the body of the HTTP request (from client)
	$parts = explode('!',$body); //Parse linestring after '!' to create the three values 
	$pathName = $parts[0];
	$pathGeom = $parts[1];
	$routeTime = $parts[2];
	$visibility = $parts[3];
	$userID = $parts[4];
	$geom = geoPHP::load("LINESTRING('$pathGeom')");
	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$sql = "INSERT INTO routes (route_name, route_time, facebook_id, visibility, geom) values ('$pathName', $routeTime, $userID, '$visibility', ST_GeomFromWKB('$insert_string'))";
	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$db = null;
	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
} )->via( "POST");


$app->run ();
?>