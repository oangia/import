<?php
/**
 * Plugin Name: EZ Import
 * Plugin URI: http://ezimport.net
 * Description: ezimport plugin, show gallery from outter source, import data
 * Version: 1.0
 * Author: og
 * Author URI: http://og.com
 * License: GPLv2 or later
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// api route
$route = new Route();

$route->namespace('ezimport/v1');
$route->post('data', 									'add_data');
$route->get( 'get_last_id/(?P<table>[a-zA-Z0-9-]+)', 	'get_last_id');
$route->post('terms', 									'add_term');

// controller
function add_data($params)
{
	$content = trim(file_get_contents("php://input"));
	$data = json_decode($content, true);

	$table = $data['table'];
	$fields = $data['fields'];
	$values = $data['values'];

	$db = new Database();
	$db->connect();
	$success = $db->insert($table, $fields, $values);
	$db->disconnect();

	if (! $success) return 'Insert fail';

	return 'New record created successfully';
}

function get_last_id($params)
{
	$db = new Database();
	$db->connect();
	$lastId = $db->getLastId($params["table"]);
	$db->disconnect();
	return $lastId;
}

function add_term( $params ) {
	$content = trim(file_get_contents("php://input"));
	$data = json_decode($content, true);

	$type = $data['type'];
	$values = $data['values'];

	$db = new Database();
	$db->connect();
	$search = [];
	foreach ($values as $value) {
		$search[] = '"' . $value . '"';
	}
	$search = implode(',', $search);

	$sql = "SELECT " . $db->table_prefix . "terms.term_id, " . $db->table_prefix . "terms.name, " . $db->table_prefix . "term_taxonomy.taxonomy FROM `" . $db->table_prefix . "terms` LEFT JOIN " . $db->table_prefix . "term_taxonomy ON " . $db->table_prefix . "terms.term_id = " . $db->table_prefix . "term_taxonomy.term_id WHERE taxonomy = '$type' AND name IN (" . $search . ")";

	$result = $db->exec($sql);

	$terms = [];
	foreach ($result as $item) {
		$terms[$item["name"]] = intval($item["term_id"]);
	}

	//
	foreach ($values as $value) {
		if (! isset($terms[$value])) {
			// insert here
			$id = $db->insert('terms', 'name,slug,term_group', ['"'. $value . '", "' . sanitize_title($value) . '", 0']);
			$db->insert('term_taxonomy', 'term_id,taxonomy', [$id . ', "' . $type . '"']);
			$terms[$value] = intval($id);
		}
	}
	$db->disconnect();

  	return $terms;
}

// show post gallery from outter source
add_filter( 'wp_get_attachment_url', function (string $url, int $attachment_id) {
	$url = get_the_guid( $attachment_id );
	return $url;
}, 10, 2);

class Route
{
	private $namespace;

	public function namespace($namespace)
	{
		$this->namespace = $namespace;
	}

	public function post($name, $controller)
	{
		add_action( 'rest_api_init', function() use ($name, $controller) {
		  register_rest_route($this->namespace, '/' . $name, [
		    'methods' => 'POST',
		    'callback' => $controller,
		    'permission_callback' => '__return_true',
		  ] );
		} );
	}

	public function get($name, $controller)
	{
		add_action( 'rest_api_init', function() use ($name, $controller) {
		  register_rest_route($this->namespace, '/' . $name, [
		    'methods' => 'GET',
		    'callback' => $controller,
		    'permission_callback' => '__return_true',
		  ] );
		} );
	}
}

class Database 
{
	private $db_host;
	private $db_name;
	private $db_user;
	private $db_password;
	public $table_prefix;
	private $mysqli;

	function __construct()
	{
		$config = $this->getMySqlConfig();
		$this->db_host = $config['db_host'];
		$this->db_name = $config['db_name'];
		$this->db_user = $config['db_user'];
		$this->db_password = $config['db_password'];
		$this->table_prefix = $config['table_prefix'];

	}

	public function connect()
	{
		$this->mysqli = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);
	}

	public function disconnect()
	{
		$this->mysqli->close();
	}

	public function exec($sql)
	{
		$result = $this->mysqli->query($sql);
		if (! $result) return [];
		$arr = [];
		while ($row = $result->fetch_assoc()) {
			$arr[] = $row;
		}
		$result->free_result();
		return $arr;
	}

	public function select($fields, )
	{
		$result = $mysqli->query("SELECT ID FROM `" . $db_config['table_prefix'] . "posts` ORDER BY id DESC");
		if (! $result) return [];
		$arr = [];
		while ($row = $result -> fetch_assoc()) {
			$arr[] = $row;
		}
		$result -> free_result();
		$mysqli->close();
		return $arr;
	}

	public function insert($table, $fields, $values)
	{
		$valuesStr = $this->valuesToString($values);

		$sql = "INSERT INTO `" . $this->table_prefix . $table . "` (" . $fields . ")
			VALUES " . $valuesStr;

		if ($this->mysqli->query($sql) === TRUE) {
		  	$last_id = $this->mysqli->insert_id;
			return $last_id;
		}

		echo $sql. "<br />";
	  	echo "Error: " . $this->mysqli->error;
	  	return false;
	}

	public function update()
	{

	}

	public function delete()
	{

	}

	public function getLastId($table, $id_name = "ID")
	{
		$result = $this->mysqli->query('SELECT ' . $id_name . ' FROM `' . $this->table_prefix . $table . '` ORDER BY id DESC LIMIT 1');
		if (! $result) return 0;
		$row = $result->fetch_assoc();
		$result->free_result();
		return intval($row[$id_name]);
	}

	public function valuesToString($values)
	{
		$valueStr = [];
		foreach ($values as $value) {
			$valueStr[] = '(' . $value . ')';
		}
		return implode(',', $valueStr) . ';';
	}

	private function getMySqlConfig()
	{
		$config = file_get_contents(ABSPATH . '/wp-config.php');
		$db_host = $this->get_string_between($config, "'DB_HOST', '", "'");
		$db_user = $this->get_string_between($config, "'DB_USER', '", "'");
		$db_password = $this->get_string_between($config, "'DB_PASSWORD', '", "'");
		$db_name = $this->get_string_between($config, "'DB_NAME', '", "'");
		$table_prefix = $this->get_string_between($config, "table_prefix = '", "'");
		return compact('db_host', 'db_user', 'db_password', 'db_name', 'table_prefix');
	}

	private function get_string_between($str, $str1, $str2, $deep = 1) {
	    $str = explode($str1, $str);
	    $str = explode($str2, $str[$deep]);
	    return $str[0];
	}
}
?>