<?php
/*
 -------------------------------------------------------------------------
 Lets-Software
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Lest-Software.

LETS is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 LETS is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with LETS. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */
 
 // Security measure
 if (!defined('$doc_root'){
	die("Sorry. You can't access this file directly");
}

class mysqli{

	//! Database Handler
	var $dbh;
	//! Database Error
	var $error = 0;
	// Is connected to the DB ?
	var $connected = false;


	function connect() {
	// In case we specify a specific port
	$hostport = explode(":", $database_host);
      if (count($hostport) < 2) {
         // Host
         $this->dbh = new mysqli($database_host, $database_user, $database_password,
                                 $database_name);

      } else if (intval($hostport[1])>0) {
         // Host:port
         $this->dbh = new mysqli($hostport[0], $database_user, $database_password,
                                 $database_name, $hostport[1]);
      } else {
         // :Socket
         $this->dbh = new mysqli($hostport[0], $database_user, $database_password,
                                 $database_name, ini_get('mysqli.default_port'), $hostport[1]);
      }

	  // Works as of PHP 5.2.9 and 5.3.0.
      if ($this->dbh->connect_error) {
         $this->connected = false;
         $this->error     = 1;
      } else {
         $this->dbh->query("SET NAMES '" . (isset($this->dbenc) ? $this->dbenc : "utf8") . "'");
         $this->connected = true;
      }
   }
}