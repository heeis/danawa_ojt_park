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
	
	function excelPartnerCount($partCode) {
		$stmt = mysqli_prepare($this->link, "SELECT COUNT(*) FROM tpartnerInfo WHERE partnerCode = ?");
		mysqli_stmt_bind_param($stmt, 's', $arr[0]);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		return $count;
	}
	
	function excelPartnerInsert($partCode, $partName) {
		$query = "INSERT INTO tpartnerInfo VALUES(?, ?)";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $partCode, $partName);
		mysqli_stmt_execute($stmt);
	}
	
	function excelPartnerUpdate ($partCode, $partName) {
		$query = "UPDATE tpartnerInfo SET partnerName = ? WHERE partnerCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $partName, $partCode);
		mysqli_stmt_execute($stmt);
	}
}