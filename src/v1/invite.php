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

  # Insert new record
  case "POST":
    
    $author_id = @$_GET[ "id" ];
    if (!$author_id)
      die( json_encode(array("status" => false, "status-text" => "User ID is needed for this action.")) );
    
    $response = generate_new_invite($author_id);
    echo json_encode($response);

    break;
  # ----

  # Validate invite code
  case "PUT":
    # ...
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted in this context.") );
    http_response_code(405);
    break;

}


function generate_new_invite ($issuer_id)
{

  global $mysqli;

  if ( !is_numeric($issuer_id) )
    return array("status" => false, "status-text" => "Please specify the User ID.");

  $issued_date = time();

  do {

    # 16-character code, sorry couldn't find better-looking way
    $code = rand( 1000000000000000, 9999999999999999 );

    $sql_query = "INSERT INTO `invitations` (id, issued_date, issuer, code) VALUES (0, $issued_date, $issuer_id, $code)";
    $response = $mysqli->query($sql_query);

    $code_unique = ($mysqli->errno != 1062);

  } while (!$code_unique);

  if (!$response)
    return array("status" => false, "status-text" => "MySQL error: $mysqli->error");

  return array("status" => true, "status-text" => "Added!");

}
