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
function parseSubmit() {
	$('form[name=pars_frm]').submit();
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

<!-- content 시작 -->
<div style="height:auto; width: 100%; margin: 0 auto;">
<center>
	<p align="left" style="margin-left: 15%;">
		# 상품정보 파싱
	</p>
	<form action="UrlParsingProcess.php" method="post" name="pars_frm">
	<table border="1">
		<tr>
			<td>협력사코드</td>
			<td>
				<select name="pCode">
					<option value="EE715">옥션</option>
  					<option value="TH201">11번가</option>
				</select>
			</td>
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
			<td>협력사상품 URL</td>
			<td>
				<input type="text" name="ppUrl" size="61">
			</td>
		</tr>
	</table>
	<input type="button" value="추가" onclick="parseSubmit()">
	<input type="button" value="취소" onclick="cancel()">
	</form>
</center>
</div>
<!-- content 끝 -->

<div style="height:80px; width: 100%;">
<br>
		<?php include_once '../common/footer.php';?>
</div>
    </body>
</html>