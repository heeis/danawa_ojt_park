#!/usr/bin/php
<?php
echo "\n" . date("Y-m-d H:i:s",time());
$db_hostname = 'localhost';
$db_database = 'ojt';
$db_username = 'root';
$db_password = '1234';
echo "1";
$link = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);
mysqli_query($link,"set session character_set_connection=utf8;");
mysqli_query($link,"set session character_set_results=utf8;");
mysqli_query($link,"set session character_set_client=utf8;");
echo "1";
$query = "SELECT standardCode FROM tstandardInfo";
$result = mysqli_query($link, $query);
echo "1";
while ($res = mysqli_fetch_row($result)) {
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
						
		$stmt = mysqli_prepare($link, $query);
		mysqli_stmt_bind_param($stmt, 's', $res[0]);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_bind_result($stmt, $avg, $min, $count);
		mysqli_stmt_fetch($stmt);
		//print_r($stmt);
		mysqli_stmt_close($stmt);
		
		$query = "UPDATE 
						tstandardInfo 
				  SET
						standardMinPrice = ?,
						standardAvgPrice = ?,
						standardCount = ?
				  WHERE
						standardCode = ?";
		$stmt = mysqli_prepare($link, $query);
		mysqli_stmt_bind_param($stmt, 'dddd', $min, $avg, $count, $res[0]);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
}
echo "1";
$query = "SELECT 
						ifnull(MAX(standardCode),0)+1 
				  FROM 
						tstandardInfo";
$result = mysqli_query($link, $query);
$max = mysqli_fetch_row($result);
echo "max : ".$max[0];
$cate = 118;
$a = "crontab";
$query = "INSERT INTO tstandardInfo VALUES(?, ?, ?, 0, 0, 0, SYSDATE(), SYSDATE(), ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 'ddssssss', $max[0], $cate, $a, $a, $a, $a, $a, $a);
mysqli_stmt_execute($stmt);
echo "1";
