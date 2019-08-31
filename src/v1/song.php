<?php
/*
 *
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */


require_once("../database.php");
header("Content-Type: application/json");

$database = new database();
$mysqli = $database->get_connection_object();
$request_method = $_SERVER[ "REQUEST_METHOD" ];


switch ($request_method)
{

  # Get record from database.
  case "GET":
    
    if ( !isset($_GET[ "song_id" ]) )
      die( json_encode(array("status" => false, "status-text" => "Requested song not specified.")) );

    $response = get_song_information($_GET[ "song_id" ]);
    echo json_encode( $response );

    break;
  # ----

  # Insert new record
  case "POST":
    # ...
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.") );
    http_response_code(405);
    break;

}


function get_song_information ($song_id)
{

  if ($song_id == "icecast")
  {


  }
  elseif ( is_numeric($song_id) )
  {


  }
  else
  {
    return array("status" => false, "status-text" => "song_id has to be either 'icecast' or a number.");
  }

}
