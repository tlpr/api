<?php
/*
 *
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */

require_once('database.php');
$database = new database();
$mysqli = $database->get_connection_object();


function get_permissions()
{
	
	global $mysqli;
	$authcode = @$_GET[ "auth" ];
	
	if (!$authcode)
		return array("id" => 0, "permissions" => 0);
	
	$authcode_sql_escaped = $mysqli->real_escape_string($authcode);
	if ($authcode !== $authcode_sql_escaped)
		return array("id" => 0, "permissions" => 0);
		
	$response = $mysqli->query("SELECT `id`, `permissions` FROM `users` WHERE `authcode` = '$authcode'");
	$result = $response->fetch_array(MYSQLI_ASSOC);
	
	if (empty($result))
		return array("id" => 0, "permissions" => 0);
		
	else
		return $result;

}
