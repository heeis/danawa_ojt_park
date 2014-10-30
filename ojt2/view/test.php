
<?php
require_once '../manager/partnerProductManager.php';
$ppManager = new partnerProductManager();
$cate = 118;
$total = $ppManager->partnerProdcutCount($cate);
echo $total;

$arr = $ppManager->ajaxUnlinkData('0_0', 118, 0, 20);

print_r($arr);
exit;
$hostname = 'localhost';
$username = 'root';
$password = '1234';
$database = 'ojt';
$link = mysqli_connect($hostname, $username, $password, $database);

$success = true;

mysqli_query($link, "set session character_set_connection=utf8;");
mysqli_query($link, "set session character_set_results=utf8;");
mysqli_query($link, "set session character_set_client=utf8;");
mysqli_query($link, "SET AUTOCOMMIT=0");
mysqli_query($link, "BEGIN");
$query = "DELETE
		  FROM 
			  tlink
		  WHERE
			  standardCode = ?";
$stmt = mysqli_prepare($link, $query);

$res1 = 10;
 
mysqli_stmt_bind_param($stmt, 'd', $res1);
$res = mysqli_stmt_execute($stmt);
$ress = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
echo $res . $ress;
if($success==true && $res==null) {
	$success = false;
}

if($success) {
	mysqli_query($link, "ROLLBACK");
	echo 'commit';
	//mysqli_query($link, "COMMIT");
} else {
	echo 'rollback';
	mysqli_query($link, "ROLLBACK");
}
mysqli_close($link);
