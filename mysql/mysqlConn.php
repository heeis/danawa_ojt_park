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
}
	
