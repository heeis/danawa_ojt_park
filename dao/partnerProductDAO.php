<?php
class partnerProductDAO {
	private $link;
	
	function __construct($link){
		$this->link = $link;
	}
	
	function utf8 () {
		mysqli_query($this->link,"set session character_set_connection=utf8;");
		mysqli_query($this->link,"set session character_set_results=utf8;");
		mysqli_query($this->link,"set session character_set_client=utf8;");
	}

	// 협력사 상품 등록
	function partnerProductInsert($req) {
		//$this->utf8();
		if($req['ppPrice'] == ''){
			$price = 0;
		} else {
			$price =  $req['ppPrice'];
		}
		
		$query = "INSERT INTO 
							  tpartnerProductInfo 
				  VALUES(
							  ?, ?, ?, ?, ?, ?, SYSDATE(), SYSDATE(), ?
				  )";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ssdssds', $req['ppCode'], $req['pCode'], $req['category'],
							   $req['ppName'], $req['ppUrl'], $price, $req['ppImageUrl']);
		mysqli_stmt_execute($stmt);
		echo $req['pCode'] . '<br>';
		$res = mysqli_stmt_affected_rows($stmt); // 성공 = 1
		print_r($stmt);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	function priceModify($pCode ,$ppCode ,$price){
		echo $pCode.':'.$ppCode.':'.$price;
		$query = "UPDATE 
						tpartnerProductInfo
				  SET
						partnerProductPrice = ?
				  WHERE 
						partnerCode = ? AND
						partnerProductCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'dss', $price, $pCode, $ppCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_affected_rows($stmt);
		return $res;
	}
	
	function partnerProductUpdate($req) {
		print_r($req);
		$this->utf8();
		if($arr['ppPrice'] == ''){
			$price = 0;
		} else {
			$price = $arr['ppPrice'];
		}
		$query = "UPDATE 
						tpartnerProductInfo 
				  SET 
						partnerProductName = ?, 
						partnerProductPrice = ?, 
						categoryCode = ?,
			    		partnerProductUrl = ?, 
						partnerProductImageUrl=?, 
						partnerProductModifyDate=SYSDATE() 
				  WHERE 
						partnerProductCode = ? AND
						partnerCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'sddssss', $req['ppName'], $req['ppPrice'], $req['category'] 
							, $req['ppUrl'], $req['ppImageUrl'], $req['ppCode'], $req['pCode']);
		$res = mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
		return $res;
	}
	
	function partnerProductInfo($ppCode, $pCode){
		$this->utf8();
		$query = "SELECT 
						partnerProductCode, 
						partnerCode, 
						partnerProductName, 
						partnerProductPrice,
				 		categoryCode, 
						partnerProductUrl, 
						partnerProductImageUrl 
				 FROM 
						tpartnerProductInfo 
				 WHERE 
						partnerProductCode = ? AND partnerCode=?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $ppCode, $pCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7);
		mysqli_stmt_fetch($stmt);
		$res = array();
		array_push($res, $res1);
		array_push($res, $res2);
		array_push($res, $res3);
		array_push($res, $res4);
		array_push($res, $res5);
		array_push($res, $res6);
		array_push($res, $res7);
		return $res;
	}
	
	function partnerProductDelete($pCode, $ppCode) {
		echo $pCode;
		echo $ppCode;
		$query = "DELETE FROM
						tpartnerProductInfo
				  WHERE
						partnerCode = ?
				  AND
						partnerProductCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $pCode, $ppCode);
		mysqli_stmt_execute($stmt);
		$res = mysqli_stmt_affected_rows($stmt);
		mysqli_stmt_close($stmt);
		echo "res : ".$res;
		return $res;
	}
	
	//
	function partnerProductCheck($pCode, $ppCode) {
		$query = "SELECT COUNT(*) 
				  FROM 
						tpartnerProductInfo 
				  WHERE 
						partnerProductCode = ? AND partnerCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ss', $ppCode, $pCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $count);
		mysqli_stmt_fetch($stmt);
		return $count;
	}
	
	// ajax : 협력사상품수
	function ajaxUnlinkCount ($catecode) {
		$query = "SELECT 
						COUNT(*) 
				  FROM
						tpartnerProductInfo t1
				  LEFT JOIN 
						tlink t2 ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
				  WHERE t2.partnerProductCode IS NULL AND t1.categoryCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $catecode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $total);
		mysqli_stmt_fetch($stmt);
		return $total;
	}
	
	function ajaxLinkCount ($catecode) {
		$query = "SELECT
						COUNT(*)
				  FROM
						tpartnerProductInfo t1
				  INNER JOIN
						tlink t2 ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
				  WHERE t1.categoryCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $catecode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $total);
		mysqli_stmt_fetch($stmt);
		return $total;
	}
	
	function ajaxSelectLinkCount ($stanCode) {
		$query = "SELECT
						COUNT(*)
				  FROM
						tlink
				  WHERE 
						standardCode = ?";
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $total);
		mysqli_stmt_fetch($stmt);
		return $total;
	}
	
	function ajaxUnlinkData ($sort, $cate, $limit_idx, $page_set) {
		if ($sort != '0_0') {
			$sortQuery = " ORDER BY ";
			$sarr = split('_', $sort);
		
			if ($sarr[0]=='1') {
				$sortQuery = $sortQuery . "partnerproductname ASC ";
			} else if ($sarr[0]=='2') {
				$sortQuery = $sortQuery . "partnerproductname DESC ";
			}
				
			if ($sarr[0]!='0' && $sarr[1]!='0')
				$sortQuery = $sortQuery . ", ";
				
			if ($sarr[1]=='1') {
				$sortQuery = $sortQuery . "partnerproductdate ASC ";
			} else if ($sarr[1]=='2' || $sarr[0]='0') {
				$sortQuery = $sortQuery . "partnerproductdate DESC ";
			}
			$query = "SELECT 
							partnername, 
							categoryname, 
							partnerProductName, 
							partnerProductImageUrl, 
							partnerProductUrl, 
							partnerProductPrice, 
							partnerProductDate, 
							t1.partnerCode, 
							t1.partnerProductCode 
					  FROM 
							tpartnerProductInfo t1 
					  LEFT JOIN 
							tlink t2 
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode) 
					  INNER JOIN 
							tcategoryInfo c 
							ON (t1.categoryCode = c.categoryCode) 
					  INNER JOIN 
							tpartnerInfo p 
							ON (t1.partnerCode = p.partnerCode)
					  WHERE 
							t2.partnerProductCode IS NULL 
					  AND t1.categoryCode = ?".$sortQuery."LIMIT ?, ?";
		} else {
			$query = "SELECT 
							partnername, 
							categoryname, 
							partnerProductName, 
							partnerProductImageUrl, 
							partnerProductUrl, 
							partnerProductPrice, 
							partnerProductDate, 
							t1.partnerCode, 
							t1.partnerProductCode 
					  FROM 
							tpartnerProductInfo t1 
					  LEFT JOIN 
							tlink t2 
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode) 
					  INNER JOIN 
							tcategoryInfo c 
							ON (t1.categoryCode = c.categoryCode) 
					  INNER JOIN 
							tpartnerInfo p 
							ON (t1.partnerCode = p.partnerCode)
					  WHERE t2.partnerProductCode IS NULL AND t1.categoryCode = ? ORDER BY partnerproductdate DESC LIMIT ?, ?;";
		}
		
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ddd', $cate, $limit_idx, $page_set);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8, $res9);
		
		$arr = array();
		while ($res = mysqli_stmt_fetch($stmt)) {
			$rArr = array();
			array_push($rArr, $res1);
			array_push($rArr, $res2);
			array_push($rArr, $res3);
			array_push($rArr, $res4);
			array_push($rArr, $res5);
			array_push($rArr, $res6);
			array_push($rArr, $res7);
			array_push($rArr, $res8);
			array_push($rArr, $res9);
			array_push($arr, $rArr);
		}
		return $arr;
	}
	
	function ajaxlinkData ($sort, $cate, $limit_idx, $page_set) {
		if ($sort != '0_0') {
			$sortQuery = " ORDER BY ";
			$sarr = split('_', $sort);
	
			if ($sarr[0]=='1') {
				$sortQuery = $sortQuery . "partnerproductname ASC ";
			} else if ($sarr[0]=='2') {
				$sortQuery = $sortQuery . "partnerproductname DESC ";
			}
	
			if ($sarr[0]!='0' && $sarr[1]!='0')
				$sortQuery = $sortQuery . ", ";
	
			if ($sarr[1]=='1') {
				$sortQuery = $sortQuery . "partnerproductdate ASC ";
			} else if ($sarr[1]=='2' || $sarr[0]='0') {
				$sortQuery = $sortQuery . "partnerproductdate DESC ";
			}
			$query = "SELECT
							partnername,
							categoryname,
							partnerProductName,
							partnerProductImageUrl,
							partnerProductUrl,
							partnerProductPrice,
							partnerProductDate,
							t1.partnerCode,
							t1.partnerProductCode,
							t2.standardCode
					  FROM
							tpartnerProductInfo t1
					  INNER JOIN
							tlink t2
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
					  INNER JOIN
							tcategoryInfo c
							ON (t1.categoryCode = c.categoryCode)
					  INNER JOIN
							tpartnerInfo p
							ON (t1.partnerCode = p.partnerCode)
					  WHERE
					  		AND t1.categoryCode = ?".$sortQuery."LIMIT ?, ?";
		} else {
			$query = "SELECT
							partnername,
							categoryname,
							partnerProductName,
							partnerProductImageUrl,
							partnerProductUrl,
							partnerProductPrice,
							partnerProductDate,
							t1.partnerCode,
							t1.partnerProductCode,
							t2.standardCode
					  FROM
							tpartnerProductInfo t1
					  INNER JOIN
							tlink t2
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
					  INNER JOIN
							tcategoryInfo c
							ON (t1.categoryCode = c.categoryCode)
					  INNER JOIN
							tpartnerInfo p
							ON (t1.partnerCode = p.partnerCode)
					  WHERE t1.categoryCode = ? ORDER BY partnerproductdate DESC LIMIT ?, ?;";
		}
	
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ddd', $cate, $limit_idx, $page_set);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8, $res9, $res10);
	
		$arr = array();
		while (mysqli_stmt_fetch($stmt)) {
			$rArr = array();
			array_push($rArr, $res1);
			array_push($rArr, $res2);
			array_push($rArr, $res3);
			array_push($rArr, $res4);
			array_push($rArr, $res5);
			array_push($rArr, $res6);
			array_push($rArr, $res7);
			array_push($rArr, $res8);
			array_push($rArr, $res9);
			array_push($rArr, $res10);
			array_push($arr, $rArr);
		}
		return $arr;
	}
	
	function ajaxSelectLinkData ($sort, $stanCode, $limit_idx, $page_set) {
		if ($sort != '0_0') {
			$sortQuery = " ORDER BY ";
			$sarr = split('_', $sort);
		
			if ($sarr[0]=='1') {
				$sortQuery = $sortQuery . "partnerproductname ASC ";
			} else if ($sarr[0]=='2') {
				$sortQuery = $sortQuery . "partnerproductname DESC ";
			}
		
			if ($sarr[0]!='0' && $sarr[1]!='0')
				$sortQuery = $sortQuery . ", ";
		
			if ($sarr[1]=='1') {
				$sortQuery = $sortQuery . "partnerproductdate ASC ";
			} else if ($sarr[1]=='2' || $sarr[0]='0') {
				$sortQuery = $sortQuery . "partnerproductdate DESC ";
			}
			$query = "SELECT
							partnername,
							categoryname,
							partnerProductName,
							partnerProductImageUrl,
							partnerProductUrl,
							partnerProductPrice,
							partnerProductDate,
							t1.partnerCode,
							t1.partnerProductCode,
							t2.standardCode
					  FROM
							tpartnerProductInfo t1
					  INNER JOIN
							tlink t2
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
					  INNER JOIN
							tcategoryInfo c
							ON (t1.categoryCode = c.categoryCode)
					  INNER JOIN
							tpartnerInfo p
							ON (t1.partnerCode = p.partnerCode)
					  WHERE
					  		AND t2.standardCode = ?".$sortQuery."LIMIT ?, ?";
		} else {
			$query = "SELECT
							partnername,
							categoryname,
							partnerProductName,
							partnerProductImageUrl,
							partnerProductUrl,
							partnerProductPrice,
							partnerProductDate,
							t1.partnerCode,
							t1.partnerProductCode,
							t2.standardCode
					  FROM
							tpartnerProductInfo t1
					  INNER JOIN
							tlink t2
							ON (t1.partnerProductCode = t2.partnerProductCode AND t1.partnerCode = t2.partnerCode)
					  INNER JOIN
							tcategoryInfo c
							ON (t1.categoryCode = c.categoryCode)
					  INNER JOIN
							tpartnerInfo p
							ON (t1.partnerCode = p.partnerCode)
					  WHERE t2.standardCode = ? ORDER BY partnerproductdate DESC LIMIT ?, ?;";
		}
		
		$stmt = mysqli_prepare($this->link, $query);
		mysqli_stmt_bind_param($stmt, 'ddd', $stanCode, $limit_idx, $page_set);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8, $res9, $res10);
		
		$arr = array();
		while (mysqli_stmt_fetch($stmt)) {
			$rArr = array();
			array_push($rArr, $res1);
			array_push($rArr, $res2);
			array_push($rArr, $res3);
			array_push($rArr, $res4);
			array_push($rArr, $res5);
			array_push($rArr, $res6);
			array_push($rArr, $res7);
			array_push($rArr, $res8);
			array_push($rArr, $res9);
			array_push($rArr, $res10);
			array_push($arr, $rArr);
		}
		return $arr;
	}
	
	function blogProductList($stanCode, $limit_idx, $page_set, $market) {
		$query = '';
		if($market == 'ALL') {
			$query = "SELECT
						t1.partnerProductName,
						t1.partnerProductPrice,
						t1.partnerCode,
						t1.partnerProductDate,
						t1.partnerProductUrl
				  FROM
						tpartnerProductInfo t1 INNER JOIN tlink t2
						ON (t1.partnerCode = t2.partnerCode AND t1.partnerProductCode = t2.partnerProductCode)
				  WHERE
						t2.standardCode = ?
				  ORDER BY t1.partnerProductPrice LIMIT ?, ?";
		} else {
			$query = "SELECT
						t1.partnerProductName,
						t1.partnerProductPrice,
						t1.partnerCode,
						t1.partnerProductDate,
						t1.partnerProductUrl
				  FROM
						tpartnerProductInfo t1 INNER JOIN tlink t2
						ON (t1.partnerCode = t2.partnerCode AND t1.partnerProductCode = t2.partnerProductCode)
				  WHERE
						t2.standardCode = ? AND t2.partnerCode = ?
				  ORDER BY t1.partnerProductPrice LIMIT ?, ?";
		}
		
		if($market == 'ALL') {
			$stmt = mysqli_prepare($this->link, $query);
			mysqli_stmt_bind_param($stmt, 'ddd', $stanCode, $limit_idx, $page_set);
		} else {
			$stmt = mysqli_prepare($this->link, $query);
			mysqli_stmt_bind_param($stmt, 'dsdd', $stanCode, $market, $limit_idx, $page_set);
		}
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2, $res3, $res4, $res5);
		$arr = array();
		while(mysqli_stmt_fetch($stmt)) {
			$rArr = array();
			array_push($rArr, $res1);
			array_push($rArr, $res2);
			array_push($rArr, $res3);
			array_push($rArr, $res4);
			array_push($rArr, $res5);
			array_push($arr, $rArr);
		}
		return $arr;
	}
	
	function blogListAvgTotal($stanCode) {
		$query = "SELECT
					COUNT(*),
					ROUND(AVG(t1.partnerProductPrice),0)
			  FROM
					tpartnerProductInfo t1 INNER JOIN tlink t2
					ON (t1.partnerCode = t2.partnerCode AND t1.partnerProductCode = t2.partnerProductCode)
			  WHERE
					t2.standardCode = ?
			  ORDER BY t1.partnerProductPrice";
		
		$stmt = mysqli_prepare($this->link, $query);
	
		mysqli_stmt_bind_param($stmt, 'd', $stanCode);
	
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $res1, $res2);
		mysqli_stmt_fetch($stmt);
		$res = array($res1, $res2);
		
		return $res;
	}
}



























