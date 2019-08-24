<?php
/*
 * 
 * The Las Pegasus Radio (https://github.com/tlpr)
 * This code is licensed under the GNU AGPL-3.0-only license
 * https://www.gnu.org/licenses/agpl-3.0.html
 *
 */

class database
{

  # Please make sure to edit these before deploying to production.
  private const mysql_username = "root";
  private const mysql_password = "";
  private const mysql_db_name  = "tlpr-dev";

  private const mysql_address = array(
    "ip" => "127.0.0.1",
    "port" => 3306
  );

  var $mysqli;


  public static function get_connection_object ()
  {

    $connection = new mysqli(
      $this->mysql_address[ "ip" ], $this->mysql_username, $this->mysql_password,
      $this->mysql_db_name, $this->mysql_address[ "port" ]
    );

    if ( mysqli_connect_errno() )
      die( json_encode( array("status" => false, "status-text" => "Unable to connect to the database.") ) );

    $this->mysqli = $connection;
    return $this->mysqli;
  
  }

}

