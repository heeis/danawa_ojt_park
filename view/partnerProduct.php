<?php 
require_once '../manager/partnerManager.php';
require_once '../manager/categoryManager.php';

$cateManager = new categoryManager();
$pManager = new partnerManager();

$cateListRes = $cateManager->categoryList();
$pListRes = $pManager->partnerList();
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script type="text/javascript">
function submitCheck() {
	var str_ppcode = /[A-Za-z0-9]/g;  
	if($("input[name=ppCode]").val().trim() == ''){
		alert("협력사상품코드를 입력하세요.");
		$("input[name=ppCode]").focus();
		return;
	} else if ($("input[name=ppCode]").val().trim().length <= 100){
		if(!str_ppcode.test($("input[name=ppCode]").val().trim())){
			alert("협력사상품코드는 영문/숫자 만 입력가능합니다.");
			$("input[name=ppCode]").focus();
			return;
		}
	} else {
		alert('협력사상품코드는 최대 100자까지 입력가능합니다.');
		return;	
	}
	
	if($("select[name=pCode]").val() == '협력사선택'){
		alert("협력사코드를 선택하세요.");
		return;
	}
	if(isNaN($("input[name=ppPrice]").val())==true){
		alert("숫자만 입력 가능합니다.");
		$("input[name=ppPrice]").focus();
		return;
	}
	if($("input[name=ppName]").val().trim() == ''){
		alert("협력사상품명를 입력하세요.");
		$("input[name=ppName]").focus();
		return;
	} else if ($("input[name=ppName]").val().trim().length > 255){
		alert('출처URL은 최대 255자까지 입력가능합니다.');
		return;
	}
	if($("select[name=category]").val() == '[대분류]'){
		alert("카테고리를 선택하세요.");
		return;
	}

	var url_check = /((http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\wㄱ-ㅎㅏ-ㅣ가-힣\;\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?)/g;
	if ($("input[name=ppUrl]").val().trim().length != 0) {
		if( !url_check.test($("input[name=ppUrl]").val()) ) {
			alert('출처URL형식에서 벗어납니다.');
			return;
		}
		if ($("input[name=ppUrl]").val().trim().length > 600){
			alert('출처URL은 최대 600자까지 입력가능합니다.');
			return;
		}
	}
	
	$("form[name=pp_frm]").submit();
}

function cancel() {
	window.history.back()
}
</script>
</head>

<body>
<div style="height:80px; width: 100%; background-color: rgb(217,217,217);">
	<?php include_once '../common/header.php';?>
</div>

<!-- content 시작 -->
<div style="height:auto; width: 100%; margin: 0 auto;">
<center>
<p align="left" style="margin-left: 15%;">
	# 협력사상품등록
</p>
<form name=pp_frm enctype="multipart/form-data" action="partnerProductProcess.php" method="post">
<input type="hidden" name="mode" value="insert">
<table border="1">
	<tr>
		<td>협력사상품코드</td>
		<td><input type="text" name="ppCode"></td>
	</tr>
	<tr>
		<td>협력사코드</td>
		<td>
			<select name=pCode>
				<option>협력사선택</option>
				<?php 
					while($res = mysqli_fetch_row($pListRes)){
				?>
						<option value=<?=$res[0] ?>><?=$res[1] ?></option>
				<?php 
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td>협력사상품명</td>
		<td><input type="text" name="ppName" size="90"></td>
	</tr>
	<tr>
		<td>가격</td>
		<td><input type="text" name="ppPrice" size="20"></td>
	</tr>
	<tr>
		<td>카테고리</td>
		<td>
			<select size=7 name="category" style="width: 400px;">
				<option selected="selected">[대분류]</option>
			<?php 
  			while ($res = mysqli_fetch_row($cateListRes)) {
  			?>
  				<option value="<?=$res[0]?>"><?=$res[1]?></option>
  			<?php 
			}
			?>
			</select> 
		</td>
	</tr>
	<tr>
		<td>협력사상품 URL</td>
		<td>
			<input type="text" name="ppUrl" size="90">
		</td>
	</tr>
	<tr>
		<td>상품이미지 URL</td>
		<td><input type="text" name="ppImageUrl" size="90"></td>
	</tr>
</table>
</form>
<p align="center">
	<input type="button" value="추가" onclick="submitCheck()">
	<input type="button" value="취소" onclick="cancel()">
</p>
</center>
</div>
<!-- content 끝 -->

<div style="height:80px; width: 100%;">
<br>
		<?php include_once '../common/footer.php';?>
</div>
</body>
 
</html>