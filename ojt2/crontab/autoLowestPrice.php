#!/usr/bin/php
<?php
require_once '/var/www/html/ojt2/program/lowestPriceProgram.php';
require_once '/var/www/html/ojt2/manager/standardManager.php';
require_once '/var/www/html/ojt2/mysql/mysqlConn.php';

$stanManager = new standardManager();
$lowProgram = new lowestPriceProgram();

$dbconn = new mysqlConn();
$link = $dbconn->connect();

$query = "SELECT standardCode FROM tstandardInfo";
$result = mysqli_query($link, $query);

while ($res = mysqli_fetch_row($result)) {
	$lowProgram->lowestPrice($res[0]);
}

$max = $stanManager->getMaxCode();
$cate = 118;
$a = 'crontab';
$query = "INSERT
				  INTO
						  tstandardInfo
				  VALUES(
				          ?, ?, ?, 0, 0, 0, SYSDATE(), SYSDATE(), ?, ?, ?, ?, ?
				  )";
$stmt = mysqli_prepare($this->link, $query);
mysqli_stmt_bind_param($stmt, 'ddssssss', $max, $cate, $a, $a, $a, $a, $a, $a);
mysqli_stmt_execute($stmt);
echo '1';