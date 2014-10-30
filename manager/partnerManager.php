<?php
	require_once '../dao/partnerDAO.php';
	require_once '../mysql/mysqlConn.php';
	class partnerManager {
		private $partnerDAO;
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
			$this->partnerDAO = new partnerDAO($this->link);
		}
		
		// 협력사 모든 정보
		function partnerList() {
			$result = $this->partnerDAO->partnerList();		
			return $result;
		}
	
	}