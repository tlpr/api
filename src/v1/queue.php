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
require_once("../configuration.php");
header("Content-Type: application/json");

$database = new database();
$mysqli = $database->get_connection_object();
$request_method = $_SERVER[ "REQUEST_METHOD" ];

$perms = get_permissions();

switch ($request_method)
{

  # Insert new record
  case "POST":
    
    $song_id = @$_POST['song_id'];
    if (!$song_id)
      die( json_encode(array("status" => false, "status-text" => "Requested song not specified.", "code" => "not-enough-parameters")) );
    
    echo json_encode(add_to_queue($song_id));
    
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.") );
    http_response_code(405);
    break;

}


function add_to_queue ( $song_id )
{

  global $mysqli, $perms;
  
  if ( $perms['permissions'] < 1 )
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");
  
  if ( !is_numeric($song_id) )
    return array("status" => false, "status-text" => "Song ID has to be a number.", "code" => "song-id-not-a-number");
  
  $sql = "SELECT * FROM `songs` WHERE `id` = $song_id";
  $response = $mysqli->query($sql);
  
	if (!$response)
		return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");
  
  $data = $response->fetch_array(MYSQLI_ASSOC);
  if ( !$data )
    return array("status" => false, "status-text" => "Song by this ID is not available in the database.", "code" => "song-not-found");
  
  $song_title = $data['title'];
  $requester  = $perms['id'];
  
  $sql = "INSERT INTO `queue` VALUES (0, '$song_title', $requester, $song_id)";
  $response = $mysqli->query($sql);
  
  if ($response)
    return array("status" => true, "status-text" => "Added to queue!", "code" => "song-get-success");
  
  else
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

}
