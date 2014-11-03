<?php
class standardDAO {
	private $link;
	
	function __construct($link){
		$this->link = $link;
	}
	
	function utf8 () {
		mysqli_query($this->link, "set session character_set_connection=utf8;");
		mysqli_query($this->link, "set session character_set_results=utf8;");
		mysqli_query($this->link, "set session character_set_client=utf8;");
	}
	
	// 기준상품코드 최대값
	function getMaxCode() {
		$query = "SELECT 
						ifnull(MAX(standardCode),0)+1 
				  FROM 
						tstandardInfo";
		$result = mysqli_query($this->link, $query);
		$max = mysqli_fetch_row($result);
		return $max[0]; 
	}
	
	// 기준상품등록
	function standardInsert($arr){
		$this->utf8();	
		$query = "INSERT 
				  INTO 
						  tstandardInfo 
				  VALUES(
				          ?, ?, ?, 0, 0, 0, SYSDATE(), SYSDATE(), ?, ?, ?, ?, ?
				  )";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ddssssss', $arr['maxCode'], $arr['categoryCode'], $arr['standardName'], $arr['standardImageSource'], $arr['standardImage'], 
							$arr['standardSourceUrl'], $arr['standardMakeDate'], $arr['standardExplain']);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_affected_rows($stmt);
		print_r($stmt);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	function standardParseInsert($standardCode, $standardName, $categoryCode) {
		$query = "INSERT
				  INTO
						  tstandardInfo
				  VALUES(
				          ?, ?, ?, 0, 0, 0, SYSDATE(), SYSDATE(), '', '', '', '', ''
						)";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dds', $standardCode, $categoryCode, $standardName);
		$res = mysqli_stmt_execute($stmt); // 성공 = 1
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	// 기준상품 수정
	function standardUpdate($arr) {
		$this->utf8();
		$query = "UPDATE 
						tstandardInfo 
				  SET 
						categoryCode = ?, 
						standardName = ?, 
						standardModifyDate = SYSDATE(),
			   			standardImageSource = ?, 
						standardImage = ?, 
						standardSourceUrl = ?, standardMakeDate = ?, 
						standardExplain = ?
			   	  WHERE 
						standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dssssssd', 
								$arr['categoryCode'], 
								$arr['standardName'], 
								$arr['standardImageSource'], 
								$arr['standardImage'], 
								$arr['standardSourceUrl'], 
								$arr['standardMakeDate'], 
								$arr['standardExplain'], 
								$arr['standardCode']);
		$res = mysqli_stmt_execute($stmt);
		print_r($stmt);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	// 기준상품 정보 불러오기
	function standardInfo($code) {
		$this->utf8();
		$query = "SELECT 
						standardCode, 
						standardName,
						categoryCode, 
						standardImageSource, 
						standardImage, 
						standardSourceUrl, 
						standardMakeDate, 
						standardExplain 
				  FROM 
						tstandardInfo 
				  WHERE 
						standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $code);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8);
		mysqli_stmt_fetch($stmt);	
		$arr = array();
		array_push($arr, $res1);
		array_push($arr, $res2);
		array_push($arr, $res3);
		array_push($arr, $res4);
		array_push($arr, $res5);
		array_push($arr, $res6);
		array_push($arr, $res7);
		array_push($arr, $res8);
		mysqli_stmt_close($stmt);
		return $arr;
	}
	
	// 기준상품 삭제
	function standardDelete($stanCode) {
		$query = "DELETE 
				  FROM 
					  tstandardInfo
				  WHERE
					  standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_error($this->link);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	//
	function standardTotal($categorycode) {
		$query = "SELECT COUNT(*) FROM tstandardInfo WHERE categoryCode= ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $categorycode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $total);
		mysqli_stmt_fetch($stmt);
		return $total;
	}
	
	function ajaxLeftData($sort, $cate, $limit_idx, $page_set) {
		if($sort != '0_0_0') {
			$sortQuery = " ORDER BY ";
			$sarr = split('_', $sort);
				
			if ($sarr[0]=='1') {
				$sortQuery = $sortQuery . "standardname ASC ";
			} else if ($sarr[0]=='2') {
				$sortQuery = $sortQuery . "standardname DESC ";
			}
				
			if ($sarr[0]!='0' && ($sarr[1]!='0' || $sarr[2]!='0'))
				$sortQuery = $sortQuery . ", ";
				
			if ($sarr[1]=='1') {
				$sortQuery = $sortQuery . "standardminprice ASC ";
			} else if ($sarr[1]=='2') {
				$sortQuery = $sortQuery . "standardminprice DESC ";
			}
		
			if ($sarr[1]!='0' && $sarr[2]!='0')
				$sortQuery = $sortQuery . ", ";
				
			if ($sarr[2]=='1') {
				$sortQuery = $sortQuery . "standardcount ASC ";
			} else if ($sarr[2]=='2') {
				$sortQuery = $sortQuery . "standardcount DESC ";
			}
				
			$query = $query +", standardcode DESC ";
				
			$query = "SELECT
							standardcode,
							t2.categoryname,
							standardname,
							standardminprice,
							standardavgprice,
							standardcount
					  FROM
							tstandardInfo t1 INNER JOIN tcategoryInfo t2 ON (t1.categoryCode = t2.categoryCode)
					  WHERE
							t1.categorycode = ?" . $sortQuery . "
					  LIMIT ?, ?";
		} else {
			$query = "SELECT
							standardcode,
							t2.categoryname,
							standardname,
							standardminprice,
							standardavgprice,
							standardcount
					  FROM
							tstandardInfo t1 INNER JOIN tcategoryInfo t2 ON (t1.categoryCode = t2.categoryCode)
					  WHERE
							t1.categorycode = ?
					  ORDER BY
							standardcode
					  DESC LIMIT
							?, ?";
		}
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ddd', $cate, $limit_idx, $page_set);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6);
		
		$arr = array();
		while ($res = mysqli_stmt_fetch($stmt)) {
			$rArr = array();
			array_push($rArr, $res1);
			array_push($rArr, $res2);
			array_push($rArr, $res3);
			array_push($rArr, $res4);
			array_push($rArr, $res5);
			array_push($rArr, $res6);
			array_push($arr, $rArr);
		}
		
		return $arr;
	}
	
	function blogStandardInfo($stanCode) {
		$query = "SELECT
						t2.categoryName,
						t1.standardName,
						t1.standardImage,
						t1.standardImageSource,
						t1.standardMakeDate,
						t1.standardExplain,
						t1.standardCode
				  FROM
						tstandardInfo t1 INNER JOIN tcategoryInfo t2
						ON (t1.categoryCode = t2.categoryCode)
				  WHERE standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7);
		mysqli_stmt_fetch($stmt);
		$res = array($res1, $res2, $res3, $res4, $res5, $res6, $res7);
		return $res;
	}
	
	function excelStandardCount($stanCode) {
		$stmt = mysqli_prepare($this->link, "SELECT COUNT(*) FROM tstandardInfo WHERE standardCode = ?");
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		return $count;
	}
	
	function excelStandardInsert($stanCode, $cateCode, $stanName) {
		$query = "INSERT INTO tstandardInfo VALUES(?, ?, ?, 0, 0, 0, sysdate(), sysdate())";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dds', $stanCode, $cateCode, $stanName);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	
	function excelStandardUpdate($stanCode, $cateCode, $stanName) {
		$query = "UPDATE tstandardInfo SET categorycode = ?, standardname = ?, standardmodifydate = SYSDATE() WHERE standardcode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dsd', $cateCode, $stanName, $stanCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
}




































