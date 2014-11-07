<?php
	require_once '../dao/categoryDAO.php';
	require_once '../mysql/mysqlConn.php';
	class categoryManager {
		private $cateDAO;
		private $db_conn;
		private $link;
		function __construct($link) {
			$this->db_conn = new mysqlConn();
			if($link == null) {
				$this->db_conn = new mysqlConn();
				$this->link = $this->db_conn->connect();
			} else {
				$this->link = $link;
			}
			$this->cateDAO = new categoryDAO($this->link);
		}
		
		// 카테고리 모든 정보
		function categoryList() {	
			$result = $this->cateDAO->categodyList();
			return $result;
		}
		
		function excelCount($cateCode) {
			return $this->cateDAO->excelCount($cateCode);
		}
		
		function excelInsert ($cateCode, $cateName) {
			$this->cateDAO->excelInsert($cateCode, $cateName);
		}
		
		function excelUpload ($cateCode, $cateName) {
			$this->cateDAO->excelUpload($cateCode, $cateName);
		}
	}