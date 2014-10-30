<?php
	// require_once '/var/www/html/ojt2/
	require_once '/var/www/html/ojt2/lib/simple_html_dom.php';
	require_once '/var/www/html/ojt2/manager/partnerProductManager.php';
	require_once '/var/www/html/ojt2/manager/standardManager.php';
	require_once '/var/www/html/ojt2/manager/linkManager.php';
	require_once '/var/www/html/ojt2/program/lowestPriceProgram.php';
	require_once '../lib/DNWInput.php';
	
	$oDNWInput = new DNWInput();
	$oDNWInput->setInjectionPattern(true);
	$oDNWInput->removeInjectionPattern(array('/',':','?','.','='));
	
	$ppManager = new partnerProductManager($link);
	$stanManager = new standardManager($link);
	$linkManager = new linkManager($link);
	$lowest = new lowestPriceProgram($link);

	$pCode = $oDNWInput->post('pCode', false);
	$ppUrl = $oDNWInput->post('ppUrl', false);
	$category =$oDNWInput->post('category');
	
	$html = file_get_html($ppUrl);
	if($html == null) {
		echo "<script>alert('해당 URL의 페이지가 없습니다.'); </script>";
	}

	$ppStr = explode('ITEMNO=', strtoupper($ppUrl));
	$ppStr = explode('&', $ppStr[1]);
	$ppCode = $ppStr[0]; // 협력사 상품 코드

	$checkResult = $ppManager->partnerProductCheck($pCode, $ppCode);
	if($checkResult != 0) { // 협력사코드, 협력사상품코드가 존재할경우
		echo "<script>alert('이미 존재합니다.'); history.back();</script>";
		exit();
	}
	
	if($pCode == 'EE715') {
		$ppImageUrl = $html->find('img[id=ucImageNavigator_himgBig1]')[0]->src; // 옥션 이미지URL (협력상품정보)
		
		$ppPrice = $html->find('input[name=hddnDiscountSellingPrice]')[0]->value; // 상품 가격
		if ($ppPrice == 0) {
			$ppPrice = $html->find('input[name=hddnSellingPrice]')[0]->value;
		}
		
		$ppName = $html->find('h2[id=ucCatalogAndItemName_hdivItemTitle]')[0]->plaintext;
		$ppName = trim(iconv("EUC-KR", "UTF-8", $ppName)); // 협력사상품명 (협력사상품정보)
		
	} else {
		
	}

	$ppArr = array(
			'ppCode'=>$ppCode,
			'pCode'=>$pCode,
			'category'=>$category,
			'ppName'=>$ppName,
			'ppUrl'=>$ppUrl,
			'ppPrice'=>$ppPrice,
			'ppImageUrl'=>$ppImageUrl
	);

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
	$res = $stanManager->standardInsert($sArr, $files);
	
	// 협력사상품, 기준상품 링크
	$res = $linkManager->linkLinkage($maxCode, $ppCode, $pCode);
	
	
	// 최저가 프로그램
	$lowest->lowestPrice($maxCode);
	echo "<script>alert('파싱완료'); location.href='productLink.php';</script>";
	
	
	
	