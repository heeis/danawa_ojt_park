<?php
class linkDAO {
	private $link;

	function __construct($link){
		$this->link = $link;
	}

	function utf8 () {
		mysqli_query($this->link,"set session character_set_connection=utf8;");
		mysqli_query($this->link,"set session character_set_results=utf8;");
		mysqli_query($this->link,"set session character_set_client=utf8;");
	}
	// 기준상품 링크 해제
	function standardLinkDelete($stanCode) {
		$query = "DELETE
		  		  FROM 
			 		 tlink
		 		  WHERE
					 standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_error($this->link);
		mysqli_stmt_close($stmt);
		echo 'Linkres = '.$res.'standcode:'.$stanCode.":";
		return $res;
	}
	
	// 협력사상품 링크 해제
	function partnerProductLinkDelete($pCode, $ppCode) {
		$query = "DELETE
				  FROM
						tlink
				  WHERE
						partnerCode = ? 
				  AND
						partnerProductCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $pCode, $ppCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_affected_rows($link);
		return $res;
	}
	
	function linkDelete($pCode, $ppCode, $standardCode) {
		$query = "DELETE
				  FROM
						tlink
				  WHERE
						partnerCode = ?
				  AND
						partnerProductCode = ?
				  AND
						standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ssd', $pCode, $ppCode, $standardCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_affected_rows($link);
		return $res;
	}

	// 링크확인
	function partnerProductLinkCount($request) {
		$query = "SELECT
				 		COUNT(*)
				 FROM 
						tlink
				 WHERE 
						partnerProductCode = ? AND partnerCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $request['ppCode'], $request['pCode']);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		return $count;
	}
	
	// 링크추가
	function linkLinkage($standardCode, $ppCode, $pCode) {
		$query = "INSERT INTO
							tlink
				  VAlUES (
							?,?,?
				  )";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dss', $standardCode, $ppCode, $pCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_affected_rows($stmt);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	// 협력사, 협력사상품 코드로 링크된 기준상품 찾기 (최저가프로그램)
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