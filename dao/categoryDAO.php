<?php	
	class categoryDAO {
		private $link;
	
		function __construct($link){
			$this->link = $link;
		}
		
		function categodyList(){
			$query = "SELECT * FROM tcategoryInfo";
			$result = mysqli_query($this->link, $query);
			return $result;
		}
		
		function excelCount($cateCode) {
			$query = "SELECT COUNT(*) FROM tcategoryInfo WHERE categorycode = ?";
			$stmt = mysqli_prepare($this->link, $query);
			mysqli_stmt_bind_param($stmt, "d", $cateCode);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $count);
			mysqli_stmt_fetch($stmt);
			mysqli_stmt_close($stmt);
			return $count;
		}
		
		function excelInsert ($cateCode, $cateName) {
			$query = "INSERT INTO tcategoryInfo VALUES(?,?)";
			$stmt = mysqli_prepare($this->link, $query);
			mysqli_stmt_bind_param($stmt, 'ds', $cateCode, $cateName);
			mysqli_stmt_execute($stmt);
		}
		
		function excelUpload ($cateCode, $cateName) {
			$query = "UPDATE tcategoryInfo SET categoryName = ? WHERE categoryCode = ?";
			$stmt = mysqli_prepare($this->link, $query);
			mysqli_stmt_bind_param($stmt, 'sd', $cateName, $cateCode);
			mysqli_stmt_execute($stmt);
		}
	}

	
	