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

$lowestPrice = new lowestPriceProgram($link);
$linkMgr = new linkManager($link);

$stanCode = $aPostResult['stanCode'];
$pp = $aPostResult['ppCode'];
/// pcode_ppcode/pcode_ppcode
$ppArr = split('/', $pp);

for($i = 0; $i < count($ppArr); $i++) {
	$p = split('_', $ppArr[$i]);
	$r = $linkMgr->linkLinkage($stanCode, $p[1], $p[0]);
	$a = $a . $r;
}
$lowestPrice->lowestPrice($stanCode);


