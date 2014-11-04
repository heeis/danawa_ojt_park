<?php
	require_once '../manager/standardManager.php';
	require_once '../lib/DNWInput.php';
	$standardManager = new standardManager();
	
	echo '이미지 1 : '.$_POST['oldimage'];
	
	$oDnwInput = new DNWInput();
	$oDnwInput->setInjectionPattern(true);
	$oDnwInput->removeInjectionPattern(array(".",":","/","?","_")); // URL에서 . : / ?
	
	$aPostResult = $oDnwInput->allPost(false);
	
	$mode = $aPostResult['mode'];
	if ($mode == 'insert') {
		// mime check
		if($_FILES['image']['name']!=null) {
			if (mime_content_type($_FILES['image']['tmp_name']) != 'image/jpeg') {
				echo "<script>alert('상품이미지는 jpg 파일만 가능합니다.'); location.href='standard.php';</script>";
				exit();
			}
		}
		$result = $standardManager->standardInsert($aPostResult, $_FILES['image']);
		if($result == 1){
			echo "<script>alert('추가성공'); location.href='standard.php';</script>";
		} else {
			echo "<script>alert('추가실패'); location.href='standard.php';</script>";
		}
	} else if ($mode == 'update') {
		// mime check
		if($_FILES['image']['name']!=null) {
			if (mime_content_type($_FILES['image']['tmp_name']) != 'image/jpeg') {
				echo "<script>alert('상품이미지는 jpg 파일만 가능합니다.'); location.href='standard.php';</script>";
				exit();
			}
		}
		$result = $standardManager->standardModify($aPostResult, $_FILES['image']);
		if($result == 1){
			echo "<script>alert('수정성공'); location.href='productlink.php';</script>";
		} else {
			echo "<script>alert('수정실패'); location.href='productlink.php';</script>";
		} 
	} else if ($mode == 'delete') {
		$stanCode = $aPostResult['stancode'];
		$result = $standardManager->standardProductDelete($stanCode);
		if($result == true){
			echo "<script>alert('삭제성공'); location.href='productlink.php';</script>";
		} else {
			echo "<script>alert('삭제실패'); location.href='productlink.php';</script>";
		}
	}