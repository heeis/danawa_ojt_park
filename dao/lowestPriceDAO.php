<?php
class lowestPriceDAO {
	
	private $link;
	
	function __construct($link){
		$this->link = $link;
	}
	
	// 최저가, 평균, 업체수
	function lowestPrice($standardCode) {
		$query = "SELECT
						ROUND(AVG(partnerProductPrice),0),
						MIN(partnerProductPrice),
						COUNT(*)
				  FROM
								  tpartnerProductInfo t1
						INNER JOIN 
								  tlink t2 
						ON 
							(t1.partnerProductCode = t2.partnerProductCode 
						AND 
							t1.partnerCode = t2.partnerCode)
				  WHERE 
						t2.standardCode = ?";
						
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 's', $standardCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $avg, $min, $count);
		mysqli_stmt_fetch($stmt);
		//print_r($stmt);
		mysqli_stmt_close($stmt);
		echo '<br>low:'.$avg .':'. $min .':'. $count;
		
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
	
	// 협력사, 협력사상품 코드로 링크된 기준상품 찾기
	function lowerStandardFind($pCode, $ppCode) {
		$query = "SELECT 
						standardCode 
				  FROM 
						tlink 
				  WHERE 
						partnerCode = ? 
				  AND 
						partnerProductCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $pCode, $ppCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $stanCode);
		$arr = array();
		while(mysqli_stmt_fetch($stmt)) {
			array_push($arr, $stanCode);
		}
		//echo '<br>'.$arr[0].':'.$arr[1];
		return $arr;
	}

}