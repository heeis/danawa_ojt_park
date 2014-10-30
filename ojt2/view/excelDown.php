<?php
require_once '../manager/standardManager.php';
require_once '../manager/partnerProductManager.php';

$stanManager = new standardManager();
$ppManager = new partnerProductManager();

$page_set = 20;

$cate = $_GET['cateno'];
$page = $_GET['page'];
$table = $_GET['table'];
$sort = $_GET['sort'];
$stanCode = $_GET['stancode'];

if ($table == "left") {
	$limit_idx = ($page-1)*$page_set;
	$result = $stanManager->ajaxLeftData($sort, $cate, $limit_idx, $page_set);
} else if ($table == 'unlink') {
	$limit_idx = ($page-1)*$page_set;
	$result = $ppManager->ajaxUnlinkData($sort, $cate, $limit_idx, $page_set);
} else if ($table == 'link') {
	$limit_idx = ($page-1)*$page_set;
	$result = $ppManager->ajaxLinkData($sort, $cate, $limit_idx, $page_set);
} else if ($table == 'selectLink') {
	$limit_idx = ($page-1)*$page_set;
	$result = $ppManager->ajaxSelectLinkData($sort, $stanCode, $limit_idx, $page_set);
}
if($table == 'left') {
	$filename = "기준상품_" . $cate . "_" . $page . ".xls" ;
} else if ($table == 'selectLink') {
	$filename = $result[0][9] . "_Link_" . $cate . "_" . $page . ".xls" ;
} else if ($table == 'link') {
	$filename = "협력사상품_Link_" . $cate . "_" . $page . ".xls" ;
} else if ($table == 'unlink') {
	$filename = "협력사상품_unLink_" . $cate . "_" . $page . ".xls" ;
}
Header("Content-type: application/vnd.ms-excel");
Header("Content-type: charset=euc-kr");
header("Content-Disposition: attachment; filename=". $filename);
Header("Content-Description: PHP5 Generated Data");
Header("Pragma: no-cache");
Header("Expires: 0");


if($table == 'left'){
?>
<head>
	<meta http-equiv="Content-Type" content="application/vnd.ms-excel;charset=utf-8"> 
</head>
<table>
	<tr>
		<td>카테고리</td>
		<td>상품명</td>
		<td>최저가</td>
		<td>평균가</td>
		<td>업체수</td>
	</tr>
	<?php 
		foreach ($result as $res) {
	?>
		<tr>
			<td><?=$res[1]?></td>
			<td><?=$res[2]?></td>
			<td><?=$res[3]?></td>
			<td><?=$res[4]?></td>
			<td><?=$res[5]?></td>
		<tr>
	<?php 
		}
	?>
</table>
<?php 
} else {
?>	
<head>
	<meta http-equiv="Content-Type" content="application/vnd.ms-excel;charset=utf-8"> 
</head>
<table>
	<tr>
		<td>협력사</td>
		<td>카테고리</td>
		<td>협력사상품명</td>
		<td>가격</td>
		<td>입력일</td>
	</tr>
	<?php 
		foreach ($result as $res) {
	?>
		<tr>
			<td><?=$res[0]?></td>
			<td><?=$res[1]?></td>
			<td><?=$res[2]?></td>
			<td><?=$res[5]?></td>
			<td><?=$res[6]?></td>
		<tr>
	<?php 
		}
	?>
</table>
<?php 
}
?>
