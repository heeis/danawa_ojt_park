<?php

class mysqlConn {
	public $db_hostname = 'localhost';
	public $db_database = 'ojt';
	public $db_username = 'root';
	public $db_password = '1234';
	
	public function __construct(){}
	
	public function connect(){
		$link = mysqli_connect($this->db_hostname, $this->db_username, $this->db_password, $this->db_database);
		mysqli_query($link,"set session character_set_connection=utf8;");
		mysqli_query($link,"set session character_set_results=utf8;");
		mysqli_query($link,"set session character_set_client=utf8;");
		return $link;
	}
	
	public function tranStart() {
		mysqli_query($link, "SET AUTOCOMMIT=0");
		mysqli_query($link, "BEGIN");
	}
	
	public function commit() {
		mysqli_query($link, "COMMIT");
	}
	
	public function rollback() {
		mysqli_query($link, "ROLLBACK");
	}
	
	
}
	
