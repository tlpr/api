<?php
/*
 *
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */


require_once("../database.php");
require_once("../permissions.php");
header("Content-Type: application/json");

$database = new database();
$mysqli = $database->get_connection_object();
$request_method = $_SERVER[ "REQUEST_METHOD" ];

$perms = get_permissions();

switch ($request_method)
{

  # Get record from database.
  case "GET":
    
    if (!isset($_GET[ "song_id" ], $_GET["user_id"]))
		die( json_encode(array("status" => false, "status-text" => "User ID and Song ID are required.", "code" => "like-invalid-parameters")) );
		
	$response = get_status($_GET[ "user_id" ], $_GET[ "song_id" ]);
	echo json_encode($response);
    
    break;
  # ----

  # Insert new record
  case "POST":
    
    if ( !isset($_POST[ "user_id" ], $_POST[ "song_id" ], $_POST[ "status" ]) )
		die(json_encode(array("status" => false, "status-text" => "Not enough arguments.", "code" => "like-invalid-parameters")));
		
	$response = add_status($_POST[ "user_id" ], $_POST[ "song_id" ], $_POST[ "status" ]);
	echo json_encode($response);
    
    break;
  # ----

  # Remove record
  case "DELETE":
    
    if (!isset($_GET[ "song_id" ], $_GET["song_id"]))
		die( json_encode(array("status" => false, "status-text" => "User ID and Song ID are required.", "code" => "like-invalid-parameters")) );
		
	$response = delete_status($_GET[ "user_id" ], $_GET[ "song_id" ]);
	echo json_encode($response);
    
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.", "code" => "method-not-accepted") );
    http_response_code(405);
    break;

}



function get_status($user_id, $song_id)
{
	
	global $mysqli, $perms;
	
	if ( !is_numeric($user_id) || !is_numeric($song_id) )
		return array("status" => false, "status-text" => "IDs has to be a number.", "code" => "like-id-not-numeric");
		
	if ( ($perms["permissions"] < 2) && ($perms["id"] != $user_id) )
		return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");
		
	$sql = "SELECT * FROM `likes` WHERE `user_id` = $user_id AND `song_id` = $song_id";
	$response = $mysqli->query($sql);
	
	if (!$response)
		return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");
		
	$data = $response->fetch_array(MYSQLI_ASSOC);
	if ( empty($data) )
		return array("status" => false, "status-text" => "This user has not liked this song yet.", "code" => "like-song-not-liked");
		
	return array("status" => true, "status-text" => "Found status for this user and song.", "data" => $data, "code" => "like-success");
		
}


function add_status($user_id, $song_id, $status)
{
	
	global $mysqli;
	
	if ( !is_numeric($user_id) || !is_numeric($song_id) || !is_numeric($status) )
		return array("status" => false, "status-text" => "All values has to be numbers.", "code" => "like-id-not-numeric");
	
	$current_status = get_status($user_id, $song_id);
	
	if (@$current_status['code'] != "like-song-not-liked")
		$sql = "UPDATE `likes` SET `status` = $status WHERE `user_id` = $user_id AND `song_id` = $song_id";
	else
		$sql = "INSERT INTO `likes` VALUES (0, $song_id, $user_id, $status)";
		
	$response = $mysqli->query($sql);
	if (!$response)
		return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");
		
	return array("status" => true, "status-text" => "Updated!", "code" => "like-success");
	
}


function delete_status($user_id, $song_id)
{
	
	global $mysqli, $perms;
	
	if ( !is_numeric($user_id) || !is_numeric($song_id) )
		return array("status" => false, "status-text" => "All values has to be numbers.", "code" => "like-id-not-numeric");
		
	if ( ($perms["permissions"] < 2) && ($perms["id"] != $user_id) )
		return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");
		
	$sql = "DELETE FROM `likes` WHERE `user_id` = $user_id AND `song_id` = $song_id";
	$response = $mysqli->query($sql);
	
	if (!$response)
		return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");
		
	return array("status" => true, "status-text" => "If entry like this existed, it is now gone.", "code" => "like-success");
	
}

