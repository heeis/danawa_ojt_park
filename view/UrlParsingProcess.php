<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	require_once '/var/www/html/ojt2/lib/simple_html_dom.php';
	require_once '/var/www/html/ojt2/manager/partnerProductManager.php';
	require_once '/var/www/html/ojt2/manager/standardManager.php';
	require_once '/var/www/html/ojt2/manager/linkManager.php';
	require_once '/var/www/html/ojt2/program/lowestPriceProgram.php';
	require_once '../mysql/mysqlConn.php';
	require_once '../lib/DNWInput.php';

	$oDNWInput = new DNWInput();
	$oDNWInput->setInjectionPattern(true);
	$oDNWInput->removeInjectionPattern(array("_","/",':','?','.','='));
	$oDNWInput->setHtmlSpecialchars(false);
	
	$pCode = $oDNWInput->post('pCode', false);
	$ppUrl = $oDNWInput->post('ppUrl', false); 
	$category = $oDNWInput->post('category', false);
	
	// URL 형식에 맞는 값인지 체크 맞으면 = true / 틀리면 = false
	if(!filter_var($ppUrl, FILTER_VALIDATE_URL)) {
		echo "<script>alert('잘못된 URL주소입니다.'); history.back();</script>";
		exit();
	}
	
 	$html = file_get_html($ppUrl) or die ("<script>alert('해당 URL의 페이지가 없습니다.');  history.back();</script>");
 
	$ppStr='';
	$ppCode='';
	
	if($pCode == 'EE715') {
		$ppStr = explode('ITEMNO=', strtoupper($ppUrl));
		$ppStr = explode('&', $ppStr[1]);
		$ppCode = $ppStr[0]; // 협력사 상품 코드
	} else if($pCode == 'TH201'){
		$ppStr = explode('prdNo=', $ppUrl);
		$ppStr = explode('&', $ppStr[1]);
		$ppCode = $ppStr[0]; // 협력사 상품 코드
	}

	$dbConn = new mysqlConn();
	$link = $dbConn->connect();
	$ppManager = new partnerProductManager($link);
	
	$checkResult = $ppManager->partnerProductCheck($pCode, $ppCode);
	if($checkResult != 0) { // 협력사코드, 협력사상품코드가 존재할경우
		echo "<script>alert('이미 존재합니다.'); history.back();</script>";
		exit();
	}
	
	if($pCode == 'EE715') {
		echo '옥션파싱';
		$ppImageUrl = $html->find('img[id=ucImageNavigator_himgBig1]')[0]->src; // 옥션 이미지URL (협력상품정보)
		
		$ppPrice = $html->find('input[name=hddnDiscountSellingPrice]')[0]->value; // 상품 가격
		if ($ppPrice == 0) {
			$ppPrice = $html->find('input[name=hddnSellingPrice]')[0]->value;
		}
		
		$ppName = $html->find('h2[id=ucCatalogAndItemName_hdivItemTitle]')[0]->plaintext;
		$ppName = trim(iconv("EUC-KR", "UTF-8", $ppName)); // 협력사상품명 (협력사상품정보)
		
	} else if($pCode == 'TH201') {
		echo '11번가파싱';
		$ppImageUrl = $html->find('img[id=bigImg]')[0]->src;
		
		for($i = 0; $i < count($html->find('meta')); $i++) {
			if($html->find('meta')[$i]->property == 'price') {
				$ppPrice = $html->find('meta')[$i]->content;
			}
		}
		$ppName = trim($html->find('div[class=prdc_heading_v2]')[0]->plaintext);
	}
	
	// 파싱결과 원하는 값을 추출못했을경우
	if($ppImageUrl == null || $ppPrice == null || $ppName == null) {
		echo "<script>alert('URL 파싱에 실패하였습니다.'); history.back();</script>";
		exit();
	}
	
	echo 'imageUrl : ' . $ppImageUrl . "<br>";
	echo 'price : ' . $ppPrice . "<br>";
	echo 'name : ' . $ppName . "<br>";
 	$ppArr = array(
			'ppCode'=>$ppCode,
			'pCode'=>$pCode,
			'category'=>$category,
			'ppName'=>$ppName,
			'ppUrl'=>$ppUrl,
			'ppPrice'=>$ppPrice,
			'ppImageUrl'=>$ppImageUrl
	); 

	$stanManager = new standardManager($link);
	$linkManager = new linkManager($link);
	$lowest = new lowestPriceProgram($link);


	$res = $ppManager->partnerProductInsert($ppArr);
	echo '협력사상품결과:'.$res;
	if($res != 1) {
		echo "<script>alert('협력사 상품 등록 실패'); history.back();</script>";
		exit();
	}
	
	// 기준상품 생성
	$maxCode = $stanManager->getMaxCode();
	$sArr = array(
			'maxCode'=>$maxCode,
			'category'=>$category,
			'stanname'=>$ppName
	);
	$res = $stanManager->parsStandardInsert($sArr);
	
	// 협력사상품, 기준상품 링크
	$res = $linkManager->linkLinkage($maxCode, $ppCode, $pCode);
	
	
	// 최저가 프로그램
	$lowest->lowestPrice($maxCode);
	echo "<script>alert('파싱완료'); location.href='productLink.php';</script>";


