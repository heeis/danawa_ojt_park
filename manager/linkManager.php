<?php
require_once '../dao/linkDAO.php';
require_once '../mysql/mysqlConn.php';
class linkManager {
	private $linkDAO;
	private $link;
	private $db_conn;
	function __construct($link) {
		if($link == null) {
			$this->db_conn = new mysqlConn();
			$this->link = $this->db_conn->connect();
		} else {
			$this->link = $link;
		}
		$this->linkDAO = new linkDAO($this->link);
	}

	function partnerProductLinkCount($req) {
		$count = $this->linkDAO->partnerProductLinkCount($req);
		return $count;
	}	
	
	function standardLinkDelete($stanCode) {
		$result = $this->linkDAO->standardLinkDelete($stanCode);
		return $result;
	}
	
	function linkDelete($pCode, $ppCode, $standardCode) {
		return $this->linkDAO->linkDelete($pCode, $ppCode, $standardCode);
	}
	
	// 협력사, 협력사상품 코드로 링크된 기준상품 찾기 (최저가프로그램)
	function lowerStandardFind($pCode, $ppCode) {
		return $this->linkDAO->lowerStandardFind($pCode, $ppCode); 
	}
	
	function partnerProductLinkDelete($pCode, $ppCode) {
		return $this->linkDAO->partnerProductLinkDelete($pCode, $ppCode);
	}
	
	// 협력사상품, 기준상품 링크
	function linkLinkage($maxCode, $ppCode, $pCode) {
		return $this->linkDAO->linkLinkage($maxCode, $ppCode, $pCode);
	}
	
	function linkCount ($stanCode) {
		return $this->linkDAO->linkCount($stanCode);
	}
}
