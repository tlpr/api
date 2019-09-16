<?php
/*
 *
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */

require_once("../vendor/autoload.php");
use ParagonIE\ConstantTime\Base32;

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

    } # end if requesting_user_by_id
    else
      $response = array("status" => false, "status-text" => "Please specify the User ID", "code" => "user-id-required");

    if ( gettype($response) == "array" )
      echo json_encode($response);
    else
      echo $response;

    break;
  # ----

  # Insert new record
  case "POST":

    $username = @$_POST[ "username" ];
    $password = @$_POST[ "password" ];
    $email = @$_POST[ "email" ];

    if ( empty($username) || empty($password) )
      die( json_encode(array("status" => false, "status-text" => "Not enough parameters.", "code" => "not-enough-parameters")) );

    echo json_encode(create_new_account($username, $password, $email));

    break;
  # ----

  # Update existing record
  case "PUT":

    $id = $_GET["id"];
    $put_vars = json_decode( file_get_contents("php://input"), "r" );

    $response = edit_account($id, $put_vars);

    echo json_encode($response);

    break;
  # ----

  # Remove record
  case "DELETE":

    $is_id_specified = isset($_GET[ "id" ]);
    if (!$is_id_specified)
      die( json_encode(array("status" => false, "status-text" => "User ID missing.", "code" => "user-id-required")) );

    $user_id = $_GET[ "id" ];

    $response = delete_account($user_id);
    echo json_encode($response);

    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.", "code" => "method-not-accepted") );
    http_response_code(405);
    break;

}


# -- Functions --

function get_user_information ($user_id, $requested_information="")
{

  global $mysqli, $perms;

  if ($perms["permissions"] == 0)
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  # since $user_id is a number, I'm skipping mysqli::real_escape_string
  if ( !is_numeric($user_id) )
    return array("status" => false, "status-text" => "User ID has to be a number.", "code" => "user-id-not-a-number");


  if ( !$requested_information )
    $sql_query = "SELECT id, nickname, email, permissions, register_ip, last_login_ip, last_login_date, avatar_url FROM `users` WHERE `id` = $user_id";

  else
  {
    $escaped_requested_information_string = $mysqli->real_escape_string ($requested_information);
    if ($requested_information !== $escaped_requested_information_string)
      return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");

    $sql_query = "SELECT ($requested_information) FROM `users` WHERE `id` = $user_id";
  }


  $response = $mysqli->query($sql_query);
  if ( $mysqli->errno )
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  $user_data = $response->fetch_array(MYSQLI_ASSOC);

  if ( ($perms["permissions"] < 2) && ($perms["id"] != $user_data["id"]) )
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  if ($user_data === null)
    return array("status" => false, "status-text" => "User does not exist.", "code" => "user-not-exist");

  return array("status" => true, "status-text" => "Most likely success", "user-data" => $user_data, "code" => "user-data-success");

}


function create_new_account ($username, $email, $password)
{

  global $mysqli, $perms;

  if ($perms["permissions"] < 3)
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  $email_specified = !empty($email);

  if ( strlen($username) > 20 )
    return array("status" => false, "status-text" => "Username too long.", "code" => "user-username-long");

  if ( strlen($username) < 4 )
    return array("status" => false, "status-text" => "Username too short.", "code" => "user-username-short");

  if ( !$email_specified )
  {

    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) )
      return array("status" => false, "status-text" => "E-mail address not correct.", "code" => "user-email-incorrect");

    if ( strlen($email) > 40 )
      return array("status" => false, "status-text" => "E-mail address too long.", "code" => "user-email-long");

    if ( strlen($email) < 4 )
      return array("status" => false, "status-text" => "E-mail address too short.", "code" => "user-email-short");

  }

  if ( strlen($password) > 64 )
    return array("status" => false, "status-text" => "Password too long.", "code" => "user-password-long");

  if ( strlen($password) < 6 )
    return array("status" => false, "status-text" => "Password too short.", "code" => "user-password-short");

  
  $sql_escaped_username = $mysqli->real_escape_string($username);
  if ( $username != $sql_escaped_username )
    return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");

  if ( !$email_specified )
  {

    $sql_escaped_email = $mysqli->real_escape_string($email);
    if ($email != $sql_escaped_email)
      return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");
  
  }

  $secured_password = password_hash($password, PASSWORD_BCRYPT, array("cost" => 13));
  $register_ip = $_SERVER["REMOTE_ADDR"];

  if ( $email_specified )
  {

    $email_confirmation_code = (string)rand(1000000, 9999999);

    # THIS SECTION NEEDS TO BE EDITED
    #mail(
    #  $email,                        # TO (user) e-mail address
    #  "E-mail address confirmation", # Subject
    #  "Please confirm your e-mail at .....", # E-mail contents
    #  "From: webmaster@laspegasus.net" # Headers
    #);
    # --- --- --- --- --- --- --- ---
    
    $email = "code:$email_confirmation_code";

  }

  $current_timestamp = time();

  $sql_query = "INSERT INTO `users` (id, nickname, email, password, register_ip, last_login_ip, last_login_date, avatar_url) VALUES (0, '$username', '$email', '$secured_password', '$register_ip', '$register_ip', $current_timestamp, '')";

  $response = $mysqli->query($sql_query);

  if ( $mysqli->errno == 1062 )
    return array("status" => false, "status-text" => "User with this username already exists.", "code" => "user-already-exist");


  if ( !$response )
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  else
    return array("status" => true, "status-text" => "Most likely success.", "code" => "user-registered");

}


function delete_account ($id)
{

  global $mysqli, $perms;

  if ( !is_numeric($id) )
    return array("status" => false, "status-text" => "ID has to be a number.", "code" => "user-id-not-a-number");
    
  if ($perms["permissions"] < 3 && $id != $perms["id"])
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  $sql_query = "DELETE FROM `users` WHERE `id` = $id";
  $response = $mysqli->query($sql_query);

  if ($response)
    return array("status" => true, "status-text" => "Account with ID $id has been removed.", "code" => "user-removed");
  else
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

}


function edit_account ($id, $rows_to_change=[])
{

  global $mysqli;
  $new_totp_key = '';

  if ( empty($rows_to_change) )
    return array("status" => false, "status-text" => "Nothing to change.", "code" => "user-nothing-to-change");

  if ( !is_numeric($id) )
    return array("status" => false, "status-text" => "ID has to be a number.", "code" => "user-id-not-a-number");
    
  if ($perms["permissions"] < 2 && $id != $perms["id"])
    return array("status" => false, "status-text" => "Access denied.", "code" => "no-permissions");

  $sql_query = "UPDATE `users` SET ";

  foreach ($rows_to_change as $row_key => $row_content)
  {

    if ($row_key == "password")
      $row_content = password_hash($row_content, PASSWORD_BCRYPT, array("cost" => 13));

    elseif ($row_key == "totp_key")
      $new_totp_key = $row_content = trim(Base32::encodeUpper(random_bytes(128)), '=');
      
    echo strlen($new_totp_key);

    $sql_escaped_row_key = $mysqli->real_escape_string($row_key);
    $sql_escaped_row_content = $mysqli->real_escape_string($row_content);
    if ( ($row_key != $sql_escaped_row_key) && ($row_content != $sql_escaped_row_content) )
      return array("status" => false, "status-text" => "Access denied.", "code" => "sql-injection-attempt");

    $sql_query .= "`$row_key`='$row_content', ";

  }

  # remove the comma from the end
  $sql_query = substr($sql_query, 0, -2) . " WHERE `id`=$id";

  $response = $mysqli->query($sql_query);

  if ( !$response )
    return array("status" => false, "status-text" => "Database error: $mysqli->error", "code" => "db-error");

  if ( $new_totp_key )
    return array("status" => true, "status-text" => "Information updated!", "totp-key" => $new_totp_key, "code" => "user-updated");
  else
    return array("status" => true, "status-text" => "Information updated!", "code" => "user-updated");

}
