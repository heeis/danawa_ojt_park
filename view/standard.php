<?php 
	require_once '../manager/categoryManager.php';
	require_once '../manager/partnerManager.php';

	$cateManager = new categoryManager();
	$partnerManager = new partnerManager();

	$cateRes = $cateManager->categoryList();
	$partRes = $partnerManager->partnerList();
	
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<title>Insert title here</title>
<script type="text/javascript">
function onlyNum() {
	if(keycode == 8 || (keycode >= 35 && keycode <= 40) || (keycode >= 46 && keycode <= 57) || (keycode >= 96 && keycode <= 105) || keycode == 110 || keycode == 190) {
		$('#makedate_font').empty();
		$('#makedate_font').append('예) 2014년 9월 -> 201409');
		window.event.returnValue = true;
		return;
	} else {
		$('#makedate_font').empty();
		$('#makedate_font').append('숫자만입력가능합니다.');
		window.event.returnValue = false;
		return;
	}
}

function submitCheck() {
	if ($("input[name=stanname]").val().trim() == ''){
		alert('상품명을 입력하세요.');
		$("input[name=stanname]").focus();
		return;
	}
	var stringRegx = /[~!@\#$%<>^&*\()\-=+_\']/gi; 
	if( stringRegx.test($("input[name=stanname]").val().trim()) ){
		alert('상품명 특수문자는 입력불가능 합니다.');
		$("input[name=stanname]").focus();
		return;
	}
	if ($("input[name=stanname]").val().trim().length > 100){
		alert('상품명은 최대 100자까지 입력가능합니다.');
		$("input[name=stanname]").focus();
		return;
	}
	if ($("select[name=category]").val() == '[대분류]'){
		alert('카테고리를 선택하세요.');
		return;
	}

	// 정규식  198001~201912 까지 입력가능
	var num_check = /^(198\d{1}|200\d|201[0-9])(0[1-9]|1[0-2])*$/;
	if($("input[name=makedate]").val().trim() != ''){
		if(!num_check.test($("#makedate").val())){
			alert('예)2014년 9월 -> 201409 의 형식에 맞게 입력해주세요.');
			$("#makedate").focus();
			return;
		}
	} 

	var url_check = /((http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\wㄱ-ㅎㅏ-ㅣ가-힣\;\-\.,@?^=%&:/~\+#]*[\w\-\@?^=%&/~\+#])?)/g;
	if($("input[name=sourceurl]").val().trim() != 0) {
		if( !url_check.test($("input[name=sourceurl]").val()) ) {
			alert('출처URL형식에서 벗어납니다.');
			return;
		}
		if ($("input[name=sourceurl]").val().trim().length > 600){
			alert('출처URL은 최대 600자까지 입력가능합니다.');
			return;
		}
	}

	if ($("input[name=imagesource]:checked").val() == '협력사선택') {
		var select = $("select[name=partnerSelect]").val();
		$("input[name=imagesource]:checked").attr('value', select);
		alert($("input[name=imagesource]:checked").val());
	}

	if ($("input[name=explain]").val().trim().length > 1000){
		alert('설명은 최대 1000자까지 입력가능합니다.');
		return;
	}
	
	var image = $("input[name=image]").val();
	if (image != ''){
		var file = image.split('.');
		file[1] = file[1].toLowerCase();
		if(file[1] != 'jpg' && file[1] != null) {
			alert("이미지파일은 jpg 만 가능합니다.");
			return;
		}
	}
	//alert('전송');
	$("form[name=stan_frm]").submit();
}

function cancel() {
	location.href='productlink.php';
}
</script>
</head>
<body>
<div style="height:80px; width: 100%; background-color: rgb(217,217,217);">
	<?php include_once '../common/header.php';?>
</div>
<div style="height:auto; width: 100%; margin: 0 auto;">

<center>
<p align="left" style="margin-left: 10%;">
	# 기준상품등록
</p>
<form name=stan_frm enctype="multipart/form-data" action="standardProcess.php" method="post">
<input type="hidden" name="mode" value="insert">
<table border="1">
	<tr>
		<td>상품코드</td>
		<td><b>신규 상품 입력중입니다.</b></td>
	</tr>
	<tr>
		<td>상품명</td>
		<td><input type="text" name="stanname" size="80"></td>
	</tr>
	<tr>
		<td>카테고리</td>
		<td>
			<select size=7 name="category" style="width: 400px;">
				<option selected="selected">[대분류]</option>
			<?php 
  			while ($res = mysqli_fetch_row($cateRes)) {
  			?>
  				<option value="<?=$res[0]?>"><?=$res[1]?></option>
  			<?php 
			}
			?>
			</select> 
		</td>
	</tr>
	<tr>
		<td rowspan=2>상품이미지</td>
		<td rowspan="2">
			<input type="radio" name="imagesource" value="" checked="checked">없음
			<input type="radio" name="imagesource" value="다나와제작">다나와제작 
			<input type="radio" name="imagesource" value="제조사제작">제조사제작
			<input type="radio" name="imagesource" value="협력사선택">협력사선택
			<select name="partnerSelect">
			<?php 
  			while ($res = mysqli_fetch_row($partRes)) {
  			?>
  				<option value="<?=$res[1]?>"><?=$res[1]?></option>
  			<?php 
			}
			?>	
			</select>
			<br>
			<input type="file" name="image" value=""> 출처 URL <input type="text" name="sourceurl" size="50">
		</td>
	</tr>
	<tr>
	
	</tr>
	<tr>
		<td>제조일자</td>
		<td id="makedate_td">
			<input type="text" name="makedate" id="makedate" onkeydown="onlyNum()"><font id="makedate_font">예) 2014년 9월 -> 201409</font>
		</td>
	</tr>
	<tr>
		<td>설명추가</td>
		<td><input type="text" name="explain" size="90px;"></td>
	</tr>
</table>
</form>
</center>
<p align="center">
	<input type="button" value="추가" onclick="submitCheck()">
	<input type="button" value="취소" onclick="cancel()">
</p>
</div>
<div style="height:80px; width: 100%;">
<br>
	<?php include_once '../common/footer.php';?>
</div>
</body>
 
</html>