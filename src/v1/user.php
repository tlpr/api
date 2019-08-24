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

$oauth2 = @$_SERVER[ "HTTP_AUTHORIZATION" ];


switch ($request_method)
{

  # Get record from database.
  case "GET":

    $requesting_user_by_id = isset($_GET[ "id" ]);
    $user_id = ($requesting_user_by_id ? $_GET[ "id" ] : null);

    $requesting_specified_row = isset($_GET[ "row" ]);
    $requested_row = ($requesting_specified_row ? $_GET[ "row" ] : null);

    if ($requesting_user_by_id)
    {
      if ($requesting_specified_row)
        $response = get_user_information($user_id, $requested_row);
      else
        $response = get_user_information($user_id);
    }
    else
      $response = get_all_users();

    if ( gettype($response) == "array" )
      echo json_encode($response);
    else
      echo $response;

    break;
  # ----

  # Insert new record
  case "POST":
    # ...
    break;
  # ----

  # Update existing record
  case "PUT":
    # ...
    break;
  # ----

  # Remove record
  case "DELETE":
    # ...
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.") );
    http_response_code(405);
    break;

}


# -- Functions --

function get_user_information ($user_id, $requested_information="")
{

  global $mysqli;

  # since $user_id is a number, I'm skipping mysqli::real_escape_string
  if ( !is_numeric($user_id) )
    return array("status" => false, "status-text" => "User ID has to be a number.");


  if ( !$requested_information )
    $sql_query = "SELECT id, nickname, email, permissions, register_ip, last_login_ip, last_login_date, avatar_url FROM `users` WHERE `id` = $user_id";

  else
  {
    $escaped_requested_information_string = $mysqli->real_escape_string ($requested_information);
    if ($requested_information !== $escaped_requested_information_string)
      return array("status" => false, "status-text" => "Access denied.");

    $sql_query = "SELECT ($requested_information) FROM `users` WHERE `id` = $user_id";
  }


  $response = $mysqli->query($sql_query);
  if ( $mysqli->errno )
    return array("status" => false, "status-text" => "Database error: $mysqli->error");

  $user_data = $response->fetch_array(MYSQLI_ASSOC);
  return array("status" => true, "status-text" => "Most likely success", "user-data" => $user_data);

}


function get_all_users ()
{


}

