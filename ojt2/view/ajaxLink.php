<?php
require_once '../manager/linkManager.php';
require_once '../mysql/mysqlConn.php';
require_once '../program/lowestPriceProgram.php';
$dbConn = new mysqlConn();
$link = $dbConn->connect();

$lowestPrice = new lowestPriceProgram($link);
$linkMgr = new linkManager($link);

$stanCode = $_POST['stanCode'];
$pp = $_POST['ppCode'];
/// pcode_ppcode/pcode_ppcode
$ppArr = split('/', $pp);

for($i = 0; $i < count($ppArr); $i++) {
	$p = split('_', $ppArr[$i]);
	$r = $linkMgr->linkLinkage($stanCode, $p[1], $p[0]);
	$a = $a . $r;
}
$lowestPrice->lowestPrice($stanCode);


