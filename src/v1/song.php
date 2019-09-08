<?php
/*
 *
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */


require_once("../database.php");
require_once("../configuration.php");
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

    if ( !isset($_POST[ "song_name" ], $_POST[ "album_title" ]) )
      die( json_encode(array("status" => false, "status-text" => "Required arguments not given.")) );

    $response = push_song_entry($_POST["song_name"], $_POST["album_title"]);
    echo json_encode( $response );

    break;
  # ----

  # Remove record
  case "DELETE":

	# by_id or by_title
    $format = @$_GET[ "format" ];
    
    $id = $title = @$_GET[ "id" ];

    if ($format == "by_id" && !is_numeric($id))
      die( json_encode(array("status" => false, "status-text" => "Missing arguments.")) );

    $response = remove_song_entry($format, $title); # $title may be an ID if $format == "by_id"

    echo json_encode($response);

    break;
  # ----

  default:
    echo json_encode( array("status" => false, "status-text" => "Method not accepted.") );
    http_response_code(405);
    break;

}


function get_song_information ($song_id)
{

  global $mysqli;
  global $icestats_url;


  if ($song_id == "icecast")
  {

    $response = file_get_contents($icestats_url, true);
    if (!$response)
      return array("status" => false, "status-text" => "Could not connect to Icecast.");

    $response_json = json_decode($response, true);
    $icestats = $response_json[ "icestats" ][ "source" ];

    $result = array();

    if (isset( $icestats[ "artist" ], $icestats[ "title" ] ))
    {

      $song_title = $icestats[ "artist" ] . " - " . $icestats[ "title" ];
      $result[0] = $song_title;

    }
    elseif ( !isset($icestats["artist"]) && isset($icestats["title"]) )
    {

      $song_title = $icestats["title"];
      $result[0] = $song_title;

    }
    elseif ( isset($icestats[0]) )
    {

      foreach ($icestats as $source)
      {

        if (isset($source["artist"]))
          $song_title = $source["artist"] . " - " . $source["title"];

        else
          $song_title = $source["title"];

        array_push($result, $song_title);
      }

    }

    return array("status" => true, "status-text" => "Success.", "songs" => $result);

  } # end of icecast

  elseif ( is_numeric($song_id) )
  {

	$sql = "SELECT * FROM `songs` WHERE `id` = $song_id";
	$response = $mysqli->query($sql);
	
	if (!$response)
		return array("status" => false, "status-text" => "Database error: $mysqli->error");
		
	$data = $response->fetch_array(MYSQLI_ASSOC);
	return array("status" => true, "status-text" => "Success.. probably.", "song-data" => $data);

  } # end of db

  else
    return array("status" => false, "status-text" => "song_id has to be either 'icecast' or a number.");

}


function push_song_entry ($song_name, $album_title)
{

  global $mysqli;
  global $album_covers_uri;

  if ( ( strlen($song_name) > 180 ) || ( strlen($song_name) < 5 ) )
    return array("status" => false, "status-text" => "Song name length is not correct. Please use \"Author - Song title\" format.");

  if ( ( strlen($album_title) > 60 ) || ( strlen($album_title) < 2 ) )
    return array("status" => false, "status-text" => "Album title length is not correct.");

  $song_name_sql_escaped = $mysqli->real_escape_string($song_name);
  $album_title_sql_escaped = $mysqli->real_escape_string($album_title);

  if ( $song_name_sql_escaped !== $song_name  ||  $album_title_sql_escaped !== $album_title )
    return array("status" => false, "status-text" => "SQL injection attempt.");

  $album_cover = ($album_covers_uri . $album_title . ".jpg");

  $sql_query = "INSERT INTO `songs` VALUES (0, '$song_name', '$album_title', '$album_cover')";
  $response = $mysqli->query($sql_query);

  if (!$response)
    return array("status" => false, "status-text" => "MySQLi error: $mysqli->error");

  return array("status" => true, "status-text" => "OK.");

}



function remove_song_entry ($format, $song)
{
	
	global $mysqli;
	
	$title = ($format == "by_title") ? $song : NULL;
	$id    = ($format == "by_id")    ? $song : NULL;
	
	if ($title === $id) return array("status" => false, "status-text" => "Unknown value.");
	
	if ($format == "by_title")
	{
		
		$sql_escaped_song_title = $mysqli->real_escape_string($song);
		if ($sql_escaped_song_title !== $song)
			return array("status" => false, "status-text" => "SQL injection attempt.");
			
		$sql = "DELETE FROM `songs` WHERE `title` = '$song'";
		$response = $mysqli->query($sql);
		
		if (!$response)
			return array("status" => false, "status-text" => "Database error: $mysqli->error");
			
		return array("status" => true, "status-text" => "If songs by that title existed, it is now gone.");
		
	}
	
	elseif ($format == "by_id")
	{
		
		if ( !is_numeric($id) )
			return array("status" => false, "status-text" => "ID has to be a number.");
		
		$sql = "DELETE FROM `songs` WHERE `id` = $id";
		$response = $mysqli->query($sql);
		
		if (!$response)
			return array("status" => false, "status-text" => "Database error: $mysqli->error");
			
		return array("status" => true, "status-text" => "If song by that ID existed, it is now gone.");
		
	}
	
}
