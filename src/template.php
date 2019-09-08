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
    # ...
    break;
  # ----

  # Insert new record
  case "POST":
    # ...
    break;
  # ----

  # Update existing record
  case "PUT":
    $put_vars = json_decode( file_get_contents("php://input"), "r" );
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

