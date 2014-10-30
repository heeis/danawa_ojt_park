<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
    
	$req = $_SERVER["REQUEST_METHOD"];
	
	require_once '../manager/partnerProductManager.php';
	require_once '../lib/DNWInput.php';
	
	$oDnwInput = new DNWInput();
	$oDnwInput->setInjectionPattern(true);
	$oDnwInput->removeInjectionPattern(array(".",":","/","?","=")); // URL에서 . : / ?
	if ($req == "POST") {
	   $aPostResult = $oDnwInput->allPost(false);
	   $mode = $aPostResult['mode'];
	} else {
	   $aGetResult = $oDnwInput->allGet(false);
	   $mode = $aGetResult['mode'];
	}
	var_export($aPostResult);
	$ppManager = new partnerProductManager(null);
	
	if ($mode == 'insert') {
		var_export($aPostResult);
		$res = $ppManager->partnerProductInsert($aPostResult);
		echo "res : ".$res;
		if($res == 1){
			echo "<script>alert('추가성공'); location.href='partnerproduct.php';</script>";
		} else {
			echo "<script>alert('추가실패'); location.href='partnerproduct.php';</script>";
		} 
	 
	} else if ($mode == 'update') {
		$res = $ppManager->partnerProductUpdate($aPostResult);
		echo "res : ".$res;
		if($res == 1){
			echo "<script>alert('수정성공'); location.href='partnerproduct.php';</script>";
		} else {
			echo "<script>alert('수정실패'); location.href='partnerproduct.php';</script>";
		}
	} else if ($mode == 'delete') {
		$ppManager->partnerProductDeleteConfirm($aPostResult);
	} else if ($mode == 'deleteOK') {
		$ppManager->partnerProductDeleteOK($aGetResult);
	} 