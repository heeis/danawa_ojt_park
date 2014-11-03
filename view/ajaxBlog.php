<?php
	require_once '../manager/partnerProductManager.php';
	require_once '../mysql/mysqlConn.php';
	require_once '../lib/DNWInput.php';
	
	$oDnwInput = new DNWInput();
	$oDnwInput->setInjectionPattern(true);
	$aPostResult = $oDnwInput->allPost(false);
	
	$db_conn = new mysqlConn();
	$link = $db_conn->connect();

	$ppMgr = new partnerProductManager($link);

	$page = $aPostResult['page'];
	$stanCode = $aPostResult['stanCode'];
	$market = $aPostResult['market'];
	
	$total = $ppMgr->blogMarketTotal($stanCode, $market);

	$res = $ppMgr->blogProductList($stanCode, $page, $market);
	
	$arr = array();
	array_push($arr, $res);
	array_push($arr, $total);
	
	echo json_encode($arr);