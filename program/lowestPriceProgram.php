<?php
require_once '../mysql/mysqlConn.php';

class lowestPriceProgram {
	private $db_conn;
	private $link;
	
	function __construct($link){
		$this->db_conn = new mysqlConn();
		if($link == null) {
			$this->db_conn = new mysqlConn();
			$this->link = $this->db_conn->connect();
		} else {
			$this->link = $link;
		}
	}

	// 최저가, 평균, 업체수
	function lowestPrice($standardCode) {
		$query = "SELECT
						ROUND(AVG(partnerProductPrice),0),
						MIN(partnerProductPrice),
						COUNT(*)
				  FROM
						tlink t1
						INNER JOIN tpartnerProductInfo t2 
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
				  WHERE 
						t1.standardCode = ?";
						
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 's', $standardCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $avg, $min, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		
		$query = "UPDATE 
						tstandardInfo 
				  SET
						standardMinPrice = ?,
						standardAvgPrice = ?,
						standardCount = ?
				  WHERE
						standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dddd', $min, $avg, $count, $standardCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_affected_rows($stmt);
		return $res;
	}
}