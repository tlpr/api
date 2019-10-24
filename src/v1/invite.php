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

  # Insert new record
  case "POST":
    
    $author_id = @$_GET[ "id" ];
    if (!$author_id)
      die( json_encode(array("status" => false, "status-text" => "User ID is needed for this action.", "code" => "invite-no-id")) );
    
    $response = generate_new_invite($author_id);
    echo json_encode($response);

    break;
  # ----

  # Validate invite code
  case "PUT":
    
    $put_vars = json_decode( file_get_contents("php://input"), "r" );

    $response = validate_invitation($put_vars);

    echo json_encode($response);

    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted in this context.", "code" => "method-not-accepted") );
    http_response_code(405);
    break;

}


function generate_new_invite ($issuer_id)
{

  global $mysqli, $perms;
  
  if ($perms["permissions"] < 3)
	return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  if ( !is_numeric($issuer_id) )
    return array("status" => false, "status-text" => "Please specify the User ID.", "code" => "invite-no-id");

  $issued_date = time();

  do {

    # 16-character code, sorry couldn't find better-looking way
    $code = rand( 1000000000000000, 9999999999999999 );

    $sql_query = "INSERT INTO `invitations` (id, issued_date, issuer, code) VALUES (0, $issued_date, $issuer_id, $code)";
    $response = $mysqli->query($sql_query);

    $code_unique = ($mysqli->errno != 1062);

  } while (!$code_unique);

  if (!$response)
    return array("status" => false, "status-text" => "MySQL error: $mysqli->error", "code" => "db-error");

  return array("status" => true, "status-text" => "Added!", "code" => "invite-success");

}


function validate_invitation ($put_vars)
{

  global $mysqli, $perms;
  
  if ($perms["permissions"] < 3)
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  $code = @$put_vars[ "code" ];
  $user_id = @$put_vars[ "user_id" ];

  if ( !@$code || !@$user_id )
    return array("status" => false, "status-text" => "Missing arguments.", "code" => "invite-args-missing");

  if (!is_numeric($code))
    return array("status" => false, "status-text" => "Code is incorrect.", "code" => "invite-code-invalid");

  $sql_escaped_username = $mysqli->real_escape_string($user_id);
  if ($user_id != $sql_escaped_username)
    return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");

  $select_sql_query = "SELECT `is_used` FROM `invitations` WHERE `code` = $code";
  $select_response = $mysqli->query($select_sql_query);
  if (!$select_response)
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  $is_used = $select_response->fetch_array(MYSQLI_ASSOC)[ "is_used" ];
  if ($is_used)
    return array("status" => false, "status-text" => "This code is in use.", "code" => "invite-in-use");
 
  $update_sql_query = "UPDATE `invitations` SET `is_used` = 1, `new_user` = $user_id WHERE `code` = $code";
  $response = $mysqli->query($update_sql_query);

  if (!$response)
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  return array("status" => true, "status-text" => "Code has been used correctly.", "code" => "invite-success");

}
