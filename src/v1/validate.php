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

require_once("../vendor/autoload.php");
use OTPHP\TOTP;


switch ($request_method)
{

  # Get record from database.
  case "GET":
    
    $act = @$_GET[ "act" ];
    if (!$act)
		die( json_encode(array("status" => false, "status-text" => "Missing arguments.", "code" => "not-enough-parameters")) );
    
    if ($act == "totp")
	{
		$user_id = @$_GET[ "user_id" ];
		$totp_code = @$_GET[ "code" ];
		if (!$user_id || !$totp_code)
			die( json_encode(array("status" => false, "status-text" => "Missing arguments.", "code" => "not-enough-parameters")) );
		$response = validate_totp($user_id, $totp_code);
	}
	
	elseif ($act == "credentials")
	{
		$username = @$_GET[ "username" ];
		$password = @$_GET[ "password" ];
		if (!$username || !$password)
			die( json_encode(array("status" => false, "status-text" => "Missing arguments.", "code" => "not-enough-parameters")) );
		$response = validate_password($username, $password);
	}
	
	echo json_encode($response);
    
    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.", "code" => "method-not-accepted") );
    http_response_code(405);
    break;

}


function validate_totp ($id, $code)
{

  global $mysqli;

  if (!is_numeric($id))
    return array("status" => false, "status-text" => "ID has to be numeric.", "code" => "user-id-not-a-number");

  $sql_query = "SELECT `totp_key` FROM `users` WHERE `id`=$id";
  $response = $mysqli->query($sql_query);

  if (!$response)
    return array("status" => false, "status-text" => "User with this ID does not exist.", "code" => "user-not-exist");

  $response_data = $response->fetch_array(MYSQLI_ASSOC);
  $secret_key = $response_data[ "totp_key" ];

  $otp = TOTP::create($secret_key);
  $is_code_valid = $otp->verify($code);

  if ($is_code_valid)
    return array("status" => true, "status-text" => "The given code is valid.", "code" => "user-totp-valid");

  else
    return array("status" => false, "status-text" => "The given code is not valid.", "code" => "user-totp-wrong");

}


function validate_password ($username, $password)
{

  global $mysqli;

  if (( strlen($username) > 20 ) || ( strlen($username) < 4 ))
    return array("status" => false, "status-text" => "Wrong username or password.", "code" => "user-wrong-credentials");

  if (( strlen($password) > 64 ) || ( strlen($password) < 6 ))
    return array("status" => false, "status-text" => "Wrong username or password.", "code" => "user-wrong-credentials");

  $sql_escaped_username = $mysqli->real_escape_string($username);

  if ($username != $sql_escaped_username)
    return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");

  $sql_query = "SELECT `password` FROM `users` WHERE `nickname` = '$username'";
  $response = $mysqli->query($sql_query);

  if (!$response)
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  $user_array = $response->fetch_array(MYSQLI_ASSOC);

  $result = password_verify($password, $user_array[ "password" ]);

  return array(
    "status" => $result,
    "status-text" => "Checked without problems.", 
    "code" => $result ? "user-valid-credentials" : "user-wrong-credentials"
  );

}
