<?php
require_once '../manager/linkManager.php';
require_once '../mysql/mysqlConn.php';
require_once '../program/lowestPriceProgram.php';
require_once '../lib/DNWInput.php';

$oDnwInput = new DNWInput();
$oDnwInput->setInjectionPattern(true);
$oDnwInput->removeInjectionPattern(array("_","/"));

$aPostResult = $oDnwInput->allPost(false);



$dbConn = new mysqlConn();
$link = $dbConn->connect();

$linkMgr = new linkManager($link);
$lowestPrice = new lowestPriceProgram($link);
$code = $aPostResult['code'];

$ppArr = split('/', $code);
$a = '..';
for($i = 0; $i < count($ppArr); $i++) {
	$p = split('_', $ppArr[$i]);
	$r = $linkMgr->linkDelete($p[0], $p[1], $p[2]);
	$lowestPrice->lowestPrice($p[2]);
	$a = $a . $r;
}



