<?php
class partnerDAO {
	private $link;
	
	function __construct($link){
		$this->link = $link;
	}
	function utf8 () {
		mysqli_query($this->link, "set session character_set_connection=utf8;");
		mysqli_query($this->link, "set session character_set_results=utf8;");
		mysqli_query($this->link, "set session character_set_client=utf8;");
	}

	function partnerList(){
		$this->utf8();
		$query = "SELECT * FROM tpartnerInfo";
		$result = mysqli_query($this->link, $query);
		return $result;
	}
}