#!/usr/bin/php
<?php
echo "auto";
require_once "/var/www/html/ojt2/program/lowestPriceProgram.php";
require_once "/var/www/html/ojt2/manager/standardManager.php";
require_once "/var/www/html/ojt2/mysql/mysqlConn.php";

$stanManager = new standardManager();
$lowProgram = new lowestPriceProgram();

$dbconn = new mysqlConn();
$link = $dbconn->connect();

$query = "SELECT standardCode FROM tstandardInfo";
$result = mysqli_query($link, $query);

while ($res = mysqli_fetch_row($result)) {
	$lowProgram->lowestPrice($res[0]);
}

