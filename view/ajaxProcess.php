<?php
require_once '../manager/standardManager.php';
require_once '../manager/partnerProductManager.php';
require_once '../mysql/mysqlConn.php';
require_once '../lib/DNWInput.php';

$oDnwInput = new DNWInput();
$oDnwInput->setInjectionPattern(true);
$oDnwInput->removeInjectionPattern(array("/","_"));
$aPostResult = $oDnwInput->allPost(false);

$dbConn = new mysqlConn();
$link = $dbConn->connect();
$stanManager = new standardManager($link);
$ppManager = new partnerProductManager($link);

$page_set = 20;

$cate = $aPostResult['cateno'];
$page = $aPostResult['page'];
$table = $aPostResult['table'];
$sort = $aPostResult['sort'];
$stanCode = $aPostResult['stancode'];

$sortQuery ="";
if (!$page)
	$page = 1;

if ($table == "left") {
	$arr1 = array();
	
	$total = $stanManager->standardTotal($cate);
	array_push($arr1, $total);

	$limit_idx = ($page-1)*$page_set;

	$arr2 = $stanManager->ajaxLeftData($sort, $cate, $limit_idx, $page_set);
	$resArr = array();
	array_push($resArr, $arr1);
	array_push($resArr, $arr2);
	echo json_encode($resArr);
} else if ($table == 'unlink') {
	$arr1 = array();

	$total = $ppManager->ajaxUnlinkCount($cate);
	array_push($arr1, $total);

	$limit_idx = ($page-1)*$page_set;

	$arr2 = $ppManager->ajaxUnlinkData($sort, $cate, $limit_idx, $page_set);
	$resArr = array();
	array_push($resArr, $arr1);
	array_push($resArr, $arr2);
	echo json_encode($resArr);
} else if ($table == 'link') {
	$arr1 = array();

	$total = $ppManager->ajaxLinkCount($cate);
	array_push($arr1, $total);

	$limit_idx = ($page-1)*$page_set;

	$arr2 = $ppManager->ajaxLinkData($sort, $cate, $limit_idx, $page_set);
	$resArr = array();
	array_push($resArr, $arr1);
	array_push($resArr, $arr2);
	echo json_encode($resArr);
} else if ($table == 'selectLink') {
	$arr1 = array();
 	
	$total = $ppManager->ajaxSelectLinkCount($stanCode);
	array_push($arr1, $total);
	
	$limit_idx = ($page-1)*$page_set;
	
	$arr2 = $ppManager->ajaxSelectLinkData($sort, $stanCode, $limit_idx, $page_set);
	$resArr = array();
	array_push($resArr, $arr1);
	array_push($resArr, $arr2);
	echo json_encode($resArr); 
}


