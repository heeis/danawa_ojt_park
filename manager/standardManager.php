<?php
	require_once '../dao/standardDAO.php';
	require_once '../manager/linkManager.php';
	require_once '../mysql/mysqlConn.php';
	class standardManager {
		private $standardDAO;
		private $linkManager;
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
			$this->standardDAO = new standardDAO($this->link);
			$this->linkManager = new linkManager($this->link);
		}
	
		function getMaxCode() {
			return $this->standardDAO->getMaxCode();
		}
		
		// 기준상품등록
		function standardInsert($req, $files) {
			$maxCode = $this->getMaxCode();
			
			if($files['name'] != null) {
				$filename = $maxCode . "_00.jpg";
			} else {
				$filename = 'noimage.jpg';
			}
			$arr = array(
					'categoryCode'=>$req['category'],
					'standardName'=>$req['stanname'],
					'standardImageSource'=>$req['imagesource'],
					'standardImage'=>$filename,
					'standardSourceUrl'=>$req['sourceurl'],
					'standardMakeDate'=>$req['makedate'],
					'standardExplain'=>$req['explain'],
					'maxCode'=>$maxCode
			);	
			$res = $this->standardDAO->standardInsert($arr);
			if($res != 1)
				return $res;
			
			if($files['name'] != null) {
				// 파일업로드
				$dir =  "/var/www/upload/image/";
				$filename = $maxCode . "_00.jpg"; // 원본파일 코드_00 
				$uploadfile = $dir . $filename;
					
				if(($files['error'] > 0) || ($files['size'] <= 0)){
					echo "<script>alert('파일업로드실패1'); location.href='standard.php';</script>";
				} else {
					if (!is_uploaded_file($files['tmp_name'])) {
						echo "<script>alert('파일업로드실패2'); location.href='standard.php';</script>";
					} else {
						if (move_uploaded_file($files['tmp_name'], $uploadfile)) {
							echo "성공";
						} else {
							echo "<script>alert('파일업로드실패3'); location.href='standard.php';</script>";
						}
					}
				}
					
				// 썸네일 생성
				$oldsize = getimagesize($uploadfile);
				$new_img = imagecreatetruecolor(80, 80);
				$file = strtolower($file);
				
				$origin_img = imagecreatefromjpeg($uploadfile);
				imagecopyresampled($new_img, $origin_img, 0, 0, 0, 0, 80, 80, $oldsize[0], $oldsize[1]);
				imagejpeg($new_img, $dir . $maxCode . "_80.jpg"); // 썸네일 이미지는 원본업로드 파일명 앞에 thum을 추가 후 저장
			
			}
			
			return $res;
		}
		
		// 기준상품정보
		function standardInfo($code) {
			$res = $this->standardDAO->standardInfo($code);
			return $res;
		}
		
		// 기준상품수정
		function standardModify($req, $files) {
			$filename = $req['stancode'] . "_00";
			if($files['name'] != null) {
				// 파일업로드
				$dir =  "/var/www/upload/image/";
				//$filename = $req['stancode'] . "_00"; // 원본파일 코드_00 
				$file = substr(strrchr($files['name'],"."),1);
				$uploadfile = $dir . $filename;
			
				if(($files['error'] > 0) || ($files['size'] <= 0)){
					echo "<script>alert('파일업로드실패1'); location.href='standard.php';</script>";
				} else {
					if (!is_uploaded_file($files['tmp_name'])) {
						echo "<script>alert('파일업로드실패2'); location.href='standard.php';</script>";
					} else {
						if (move_uploaded_file($files['tmp_name'], $uploadfile)) {
							echo "성공";
						} else {
							echo "<script>alert('파일업로드실패3'); location.href='standard.php';</script>";
						}
					}
				}
				// 썸네일 생성
				$oldsize = getimagesize($uploadfile);
				$new_img = imagecreatetruecolor(80, 80);
				$file = strtolower($file);
				
				$origin_img = imagecreatefromjpeg($uploadfile);
				imagecopyresampled($new_img, $origin_img, 0, 0, 0, 0, 80, 80, $oldsize[0], $oldsize[1]);
				imagejpeg($new_img, $dir . $req['stancode'] . "_80"); // 썸네일 이미지는 원본업로드 파일명 앞에 thum을 추가 후 저장
				
			}
			echo '이미지값 : '.$filename;
			$arr = array(
					'standardCode'=>$req['stancode'],
					'categoryCode'=>$req['category'],
					'standardName'=>$req['stanname'],
					'standardImageSource'=>$req['imagesource'],
					'standardImage'=>$filename,
					'standardSourceUrl'=>$req['sourceurl'],
					'standardMakeDate'=>$req['makedate'],
					'standardExplain'=>$req['explain']
			);
			$res = $this->standardDAO->standardUpdate($arr);
			mysqli_close($this->link);
			return $res;
		}
		
		function standardProductDelete ($stanCode) {			
			$success = true;		
			mysqli_query($this->link, "SET AUTOCOMMIT=0");
			mysqli_query($this->link, "BEGIN");
			$result = $this->linkManager->standardLinkDelete($stanCode);
			if($result) {
				echo '링크삭제실패';
				$success = false;
			}
			$result = $this->standardDAO->standardDelete($stanCode);
			if($result) {	
				echo '기준삭제실패';
				$success = false;
			}
			if($success) {
				echo 'commit';
				mysqli_query($this->link, "COMMIT");
				return $success;
			} else {
				echo 'rollback';
				mysqli_query($this->link, "ROLLBACK");
				return $success;
			}
		}
		
		function standardTotal ($categorycode) {
			return $this->standardDAO->standardTotal($categorycode);
		}
		
		function ajaxLeftData($sort, $cate, $limit_idx, $page_set) {
			return $this->standardDAO->ajaxLeftData($sort, $cate, $limit_idx, $page_set);
		}
		
		function blogStandardInfo($stanCode) {
			return $this->standardDAO->blogStandardInfo($stanCode);
		}
		
		function excelStandardCount($stanCode) {
			return $this->standardDAO->excelStandardCount($stanCode);
		}
		
		function excelStandardInsert($stanCode, $cateCode, $stanName) {
			$this->standardDAO->excelStandardInsert($stanCode, $cateCode, $stanName);
		}
		
		function excelStandardUpdate($stanCode, $cateCode, $stanName) {
			$this->standardDAO->excelStandardUpdate($stanCode, $cateCode, $stanName);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	