
<?php
echo "1";
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
echo "1";

$query = "
		UPDATE 
			tstandardInfo 
		SET 
			categoryCode = ?, 
			standardName = ?, 
			standardModifyDate = SYSDATE(), 
		    standardImageSource = ?, 
			standardImage = ?, 
			standardSourceUrl = ?, 
			standardMakeDate = ?, 
			standardExplain = ?
		 	WHERE standardCode = ?";
$stmt = mysqli_prepare($link, $query);

$res1 = 72;
$res2 = '트렌1';
$res3 = '트렌1';
$res4 = '트렌11';
$res5 = '트렌1';
$res6 = '트렌1';
$res7 = '트렌1';
$res8 = 10;
 

mysqli_stmt_bind_param($stmt, 'dssssssd', $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8);
$res = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if($success==true && $res==null) {
	$success = false;
}

$res1 = 72;
$res2 = '트렌2';
$res8 = 11;
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, 'dssssssd', $res1, $res2, $res3, $res4, $res5, $res6, $res7, $res8);
$res = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
echo '<br>';
print_r($link);
if($success==true && $res==null) {
	$success = false;
}
if($success) {
	//mysqli_query($link, "ROLLBACK");
	echo 'commit';
	mysqli_query($link, "COMMIT");
} else {
	echo 'rollback';
	mysqli_query($link, "ROLLBACK");
}


mysqli_close($link);
