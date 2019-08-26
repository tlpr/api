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

    } # end if requesting_user_by_id
    else
      $response = array("status" => false, "status-text" => "Please specify the User ID");

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
      die( json_encode(array("status" => false, "status-text" => "Not enough parameters.")) );

    echo json_encode(create_new_account($username, $password, $email));

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

  if ($user_data === null)
    return array("status" => false, "status-text" => "User does not exist.");

  return array("status" => true, "status-text" => "Most likely success", "user-data" => $user_data);

}


function create_new_account ($username, $email, $password)
{

  global $mysqli;

  $email_specified = !empty($email);

  if ( strlen($username) > 20 )
    return array("status" => false, "status-text" => "Username too long.");

  if ( strlen($username) < 4 )
    return array("status" => false, "status-text" => "Username too short.");

  if ( !$email_specified )
  {

    if ( !filter_var($email, FILTER_VALIDATE_EMAIL) )
      return array("status" => false, "status-text" => "E-mail address not correct.");

    if ( strlen($email) > 40 )
      return array("status" => false, "status-text" => "E-mail address too long.");

    if ( strlen($email) < 4 )
      return array("status" => false, "status-text" => "E-mail address too short.");

  }

  if ( strlen($password) > 64 )
    return array("status" => false, "status-text" => "Password too long.");

  if ( strlen($password) < 6 )
    return array("status" => false, "status-text" => "Password too short.");

  
  $sql_escaped_username = $mysqli->real_escape_string($username);
  if ( $username != $sql_escaped_username )
    return array("status" => false, "status-text" => "Access denied.");

  if ( !$email_specified )
  {

    $sql_escaped_email = $mysqli->real_escape_string($email);
    if ($email != $sql_escaped_email)
      return array("status" => false, "status-text" => "Access denied.");
  
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
    return array("status" => false, "status-text" => "User with this username already exists.");


  if ( !$response )
    return array("status" => false, "status-text" => "Database error: $mysqli->error");

  else
    return array("status" => true, "status-text" => "Most likely success.");

}

function validate_password ($username, $password)
{

  global $mysqli;

  if (( strlen($username) > 20 ) || ( strlen($username) < 4 ))
    return array("status" => false, "status-text" => "Wrong username or password.");

  if (( strlen($password) > 64 ) || ( strlen($password) > 6 ))
    return array("status" => false, "status-text" => "Wrong username or password.");

  $sql_escaped_username = $mysqli->real_string_escape($username);

  if ($username != $sql_escaped_username)
    return array("status" => false, "status-text" => "Access denied.");

  $sql_query = "SELECT `password` FROM `users` WHERE `username` = '$username'";
  $response = $mysqli->query($sql_query);

  if (!$response)
    return array("status" => false, "status-text" => "Database error: $mysqli->error");

  $user_array = $response->fetch_array(MYSQLI_ASSOC);

  $result = password_verify($password, $user_array[ "password" ]);

  return array("status" => $result, "status-text" => "Checked without problems.");

}

