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

add_action( 'rest_api_init', function() {
  register_rest_route( 'ezimport/v1', '/data', [
    'methods' => 'POST',
    'callback' => 'add_data',
    'permission_callback' => '__return_true',
  ] );
} );

add_action( 'rest_api_init', function() {
  register_rest_route( 'ezimport/v1', '/terms', [
    'methods' => 'POST',
    'callback' => 'add_term',
    'permission_callback' => '__return_true',
  ] );
} );

add_action( 'rest_api_init', function() {
  register_rest_route( 'ezimport/v1', '/get_last_id/(?P<table>[a-zA-Z0-9-]+)', [
    'methods' => 'GET',
    'callback' => 'get_last_id',
    'permission_callback' => '__return_true',
  ] );
} );
// Get single project
function add_data( $params ) {
	$db_config = getMySqlConfig();
	$mysqli = new mysqli($db_config['db_host'],$db_config['db_user'],$db_config['db_password'],$db_config['db_name']);
	$content = trim(file_get_contents("php://input"));
	$data = json_decode($content, true);
	$table = $data['table'];
	$fields = $data['fields'];
	$values = [];
	foreach ($data['values'] as $value) {
		$values[] = '(' . $value . ')';
	}
	$values = implode(',', $values) . ';';
	
	$sql = "INSERT INTO `" . $db_config['table_prefix'] . $table . "` (" . $fields . ")
	VALUES " . $values;

	if ($mysqli->query($sql) === TRUE) {
	  	echo "New record created successfully";
	} else {
		echo $sql. "<br />";
	  	echo "Error: " . $mysqli->error;
	}
	$mysqli->close();
  	return "";
}

function add_term( $params ) {
	$db_config = getMySqlConfig();
	$mysqli = new mysqli($db_config['db_host'],$db_config['db_user'],$db_config['db_password'],$db_config['db_name']);
	$content = trim(file_get_contents("php://input"));
	$data = json_decode($content, true);
	$table = $data['table'];
	$fields = $data['fields'];
	$values = [];
	foreach ($data['values'] as $value) {
		$values[] = '(' . $value . ')';
	}
	$values = implode(',', $values) . ';';
	
	$sql = "INSERT INTO `" . $db_config['table_prefix'] . $table . "` (" . $fields . ")
	VALUES " . $values;

	if ($mysqli->query($sql) === TRUE) {
	  	echo "New record created successfully";
	} else {
		echo $sql. "<br />";
	  	echo "Error: " . $mysqli->error;
	}
	$mysqli->close();
  	return "hello";
}

function get_last_id( $params ) {
	return getLastId($params["table"]);
}

add_filter( 'wp_get_attachment_url', function (string $url, int $attachment_id) {
	$url = get_the_guid( $attachment_id );
	return $url;
}, 10, 2);

function getLastId($table) {
    $db_config = getMySqlConfig();
	$mysqli = new mysqli($db_config['db_host'],$db_config['db_user'],$db_config['db_password'],$db_config['db_name']);
	$result = $mysqli->query("SELECT ID FROM `" . $db_config['table_prefix'] . "$table` ORDER BY id DESC LIMIT 1");
	if (! $result) return 0;
	$row = $result -> fetch_assoc();
	$result -> free_result();
	$mysqli->close();
	return intval($row['ID']);
}

function getMySqlConfig() {
	$root = ABSPATH;
	$config = file_get_contents($root . '/wp-config.php');
	$db_host = get_string_between($config, "'DB_HOST', '", "'");
	$db_user = get_string_between($config, "'DB_USER', '", "'");
	$db_password = get_string_between($config, "'DB_PASSWORD', '", "'");
	$db_name = get_string_between($config, "'DB_NAME', '", "'");
	$table_prefix = get_string_between($config, "table_prefix = '", "'");
	return compact('db_host', 'db_user', 'db_password', 'db_name', 'table_prefix');
}

function get_string_between($str, $str1, $str2, $deep = 1) {
    $str = explode($str1, $str);
    $str = explode($str2, $str[$deep]);
    return $str[0];
}
?>