<?php
require_once '../dao/partnerProductDAO.php';
require_once '../manager/linkManager.php';
require_once '../program/lowestPriceProgram.php';
require_once '../mysql/mysqlConn.php';
class partnerProductManager {
	private $ppDAO;
	private $linkManager;
	private $db_conn;
	private $link;
	private $lowestManager;
	
	function __construct($link) {
		if($link == null) {
			$this->db_conn = new mysqlConn();
			$this->link = $this->db_conn->connect();
		} else {
			$this->link = $link;
		}
		$this->ppDAO = new partnerProductDAO($this->link);
		$this->linkManager = new linkManager($this->link);
		$this->lowest = new lowestPriceProgram($this->link);
	}
	
    function partnerProductInsert($req) {
		$res = $this->ppDAO->partnerProductInsert($req);
		return $res;
	}

	function partnerProductInfo($ppCode, $pCode) {
		$res = $this->ppDAO->partnerProductInfo($ppCode, $pCode);
		return $res;
	}
	
	function partnerProductUpdate($req) {
		$pCode = $req['pCode'];
		$ppCode = $req['ppCode'];
		
		$count = $this->linkManager->partnerProductLinkCount($req);
		if($count == 0) { // 미링크상품
			echo '미링크';
			$res = $this->ppDAO->partnerProductUpdate($req);
		} else if ($count > 0) { // 링크상품
			echo '링크';
			// 가격갱신 될 경우 수정 후 최저가프로그램	
			$res = $this->ppDAO->partnerProductUpdate($req);
			$resArr = $this->linkManager->lowerStandardFind($pCode, $ppCode); // 해당상품과 링크중인 기준상품 정보를 가져옴
			for($i = 0; $i < count($resArr); $i++) {
				echo $resArr[$i];
				$this->lowest->lowestPrice($resArr[$i]); // 가져온 기준상품에 전부 최저가갱신
			}
		}
		return $res;
	}
	
	function partnerProductDeleteConfirm($req) { // 링크된 상품일경우 링크해제 + 최저가
		
		$pCode = $req['pCode'];
		$ppCode = $req['ppCode'];
		$count = $this->linkManager->partnerProductLinkCount($req);
		
		if($count > 0) {
			echo "<script>if(confirm('링크된 협력사 상품입니다. 삭제 하겠습니까?') == false) {
			history.back();
			} else {
				location.href='partnerProductProcess.php?mode=deleteOK&pCode=$pCode&ppCode=$ppCode&link=ok';
			}
			</script>";
			exit();
		} else {
			echo "<script>location.href='partnerProductProcess.php?mode=deleteOK&pCode=$pCode&ppCode=$ppCode&link=no';</script>";
		}
	}
	
	function partnerProductDeleteOK ($req) {
		$linkOk = $req['link'];
		$pCode = $req['pCode'];
		$ppCode = $req['ppCode'];
		if($linkOk == 'ok'){ // 링크상품일경우
			$resArr = $this->linkManager->lowerStandardFind($pCode, $ppCode); // 해당상품과 링크중인 기준상품 정보를 가져옴
			$res = $this->linkManager->partnerProductLinkDelete($pCode, $ppCode);
			$this->ppDAO->partnerProductDelete($pCode, $ppCode); // 삭제 후 최저가
			//$resArr = $this->lowestDAO->lowerStandardFind($pCode, $ppCode); // 해당상품과 링크중인 기준상품 정보를 가져옴
			for($i = 0; $i < count($resArr); $i++) {
				$this->lowest->lowestPrice($resArr[$i]); // 가져온 기준상품에 전부 최저가갱신
			}
			echo "<script>alert('삭제성공'); location.href='productLink.php'</script>";
		} else { // 미링크상품일경우
			$res = $this->ppDAO->partnerProductDelete($pCode, $ppCode);
			if($res != null) {
				echo "<script>alert('삭제성공'); location.href='productLink.php'</script>";
			} else {
				echo "<script>alert('삭제실패'); location.href='partnerProductModify.php?pCode=$pCode&ppCode=$ppCode'</script>";
			}
		}
		mysqli_close($this->link);
	}
	
	function partnerProductCheck($pCode, $ppCode) {
		return $this->ppDAO->partnerProductCheck($pCode, $ppCode);
	}
	
	function ajaxUnlinkCount($cateCode) {
		return $this->ppDAO->ajaxUnlinkCount($cateCode);
	}
	
	function ajaxLinkCount ($cateCode) {
		return $this->ppDAO->ajaxLinkCount($cateCode);
	}
	
	function ajaxSelectLinkCount ($stanCode) {
		return $this->ppDAO->ajaxSelectLinkCount($stanCode);
	}
	
	function ajaxUnlinkData ($sort, $cate, $limit_idx, $page_set) {
		return $this->ppDAO->ajaxUnlinkData($sort, $cate, $limit_idx, $page_set);	
	}
	
	function ajaxLinkData ($sort, $cate, $limit_idx, $page_set) {
		return $this->ppDAO->ajaxLinkData($sort, $cate, $limit_idx, $page_set);
	}
	
	function ajaxSelectLinkData ($sort, $stanCode, $limit_idx, $page_set) {
		return $this->ppDAO->ajaxSelectLinkData($sort, $stanCode, $limit_idx, $page_set);
	}
	
	// 블로그 협력사상품 리스트
	function blogProductList($stanCode, $page, $market) {
		$page_set = 20;
		$limit_idx = ($page-1)*20;
		return $this->ppDAO->blogProductList($stanCode, $limit_idx, $page_set, $market);
	}
	// 블로그 리스트의 평균, 상품수
	function blogListAvgTotal($stanCode) {
		return $this->ppDAO->blogListAvgTotal($stanCode);
	}
	
	function blogMarketTotal($stanCode, $market) {
		return $this->ppDAO->blogMarketTotal($stanCode, $market);
	}
	
	function excelPartnerProductCount($ppCode) {
		return $this->ppDAO->excelPartnerProductCount($ppCode);
	}
	
	function excelPartnerProductInsert($ppCode, $pCode, $cateCode, $ppName, $ppUrl, $ppPrice ) {
		$this->ppDAO->excelPartnerProductInsert($ppCode, $pCode, $cateCode, $ppName, $ppUrl, $ppPrice);
	}
	
	function excelPartnerProductUpdate($ppCode, $cateCode, $ppName, $ppUrl, $ppPrice ) {
		$this->ppDAO->excelPartnerProductUpdate($ppCode, $cateCode, $ppName, $ppUrl, $ppPrice);
	}
	
	
}