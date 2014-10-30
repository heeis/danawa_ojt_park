<?php 
	require_once '../manager/standardManager.php';
	require_once '../manager/partnerProductManager.php';
	require_once '../mysql/mysqlConn.php';
	$db_conn = new mysqlConn();
	$link = $db_conn->connect();
	
	$stanMgr = new standardManager($link);
	$ppMgr = new partnerProductManager($link);
	
	$stanCode = $_GET['stanCode'];
	
	$stanInfoResult = $stanMgr->blogStandardInfo($stanCode);
	$ppListResult = $ppMgr->blogProductList($stanCode, 1, 'ALL');
	$ppTotalAvg = $ppMgr->blogListAvgTotal($stanCode);
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<link rel="stylesheet" type="text/css" href="css/blog.css"/>
<title>Insert title here</title>
<script type="text/javascript">
var npage;
function listShow(page, stanCode) {
	var market = $("#market_select option:selected").val();
	alert(market+":"+stanCode);
	npage = page+1;
	$.ajax({      
        type:"POST",  
        url:"http://ojt2.com/ajaxBlog.php",      
        data:{'page':npage,'stanCode':stanCode,'market':market},      
        success:function(args){   
            alert(args)
        },
        error:function(e){  
            alert('error : '+e.responseText);  
        }  
	});
}
</script>
</head>
<!-- http://img.danawa.com/cmpny_info/images/PC410_logo.gif -->
<body>
  <div id="blog_top">
  	<img alt="" src="http://image.ojt2.com/image/logo.gif"> <?=$stanInfoResult[0] ?> > <b style=""><?=$stanInfoResult[1] ?></b>
  </div>
  <div id="blog_main">
  	<div style="float:left; width: 210px; height: 250px; margin-left: 10px;">
  		<div style="width: 210px; padding-left: 22px;">
  			<img width="170" height="170" src="test/p.jpg">
  		</div>
  		<div style="width: 210px;">
  		<p align="center" style="font-size: 8pt; color: rgb(139,139,139);">
  		이미지 출처 : <?=$stanInfoResult[3]!='' ? $stanInfoResult[5] : '정보없음' ?>
  		</p>
  		</div>
  		<div style="width: 210px; margin-top: 25px;">
  		<p align="center" style="font-size: 8pt; color: rgb(139,139,139);">
  		등록년월 <?=$stanInfoResult[4]!='' ? $stanInfoResult[5] : '정보없음' ?>
  		</p>
  		</div>
  	</div>
  	<div style="float:right; width: 450px; height: auto;">
  		<div style="width: 450px; height: 40px;">
  			<p style="font-weight:bold; ">
  			<?=$stanInfoResult[1] ?>
  			</p>
  		</div>
  		<div style="width: 450px; height: 50px;">
  			<img src="http://img.danawa.com/cmpny_info/images/<?=$ppListResult[0][2] ?>_logo.gif">
  			<font style="font-size: 26pt; font-weight: bold; margin-left: 10px; margin-right: 30px; color:rgb(139,139,139)">
  				<?=$ppListResult[0][1]?>원
  			</font> 
  			<a href="<?=$ppListResult[0][4]?>" target="_blank"><img src="test/btn.jpg"></a>
  		</div>
  		<div style="border-top: 1px solid rgb(139,139,139); ">
  			<table style="font-size: 10pt;">
  				<?php 
  				$roof = $ppTotalAvg[0]>5?5:$ppTotalAvg[0];
  				for($i = 1; $i < $roof; $i++) {
  				?>
  					<tr>
  					<td><img height="22" src="http://img.danawa.com/cmpny_info/images/<?=$ppListResult[$i][2] ?>_logo.gif"></td>
  					<td width="90" align="right"><?=$ppListResult[$i][1]?> 원</td>
  					<td width="90" align="right" style="color:rgb(139,139,139)">+<?=$ppListResult[$i][1]-$ppListResult[0][1]?>원</td>
  				</tr>
  				<?php 
  				}
  				?>

  			</table>
  		</div>
  		<br>
  		<div style="border-top: 1px solid; margin-bottom: 10px; color: rgb(139,139,139);">
  			(다나와 평균가:<?=$ppTotalAvg[1]?>원)
  			<div onclick="test()" style="float: right; border: 1px solid; border-top:0; cursor:pointer; font-size: 8pt;">
  			<span style="">&nbsp;&nbsp;쇼핑몰별 <?=$ppTotalAvg[0]?>개 더보기&nbsp;&nbsp;</span>
  			<span style="border-left: 1px solid rgb(139,139,139);" >&nbsp;<img src="test/plus.gif">&nbsp;</span>
  			</div>
  		</div>
  		<div style="color: rgb(139,139,139);">
  		<?=$stanInfoResult[5]!='' ? $stanInfoResult[5] : '' ?>
  		</div>
  	</div>
  </div>
  <div style="width: 700px; margin-bottom: 10px;">
  	<div style="width: 690px; height: 32px; margin-top: 5px; background-color: rgb(245,245,245); padding-top: 11px; padding-left: 10px; border-top: 2px solid rgb(233,233,233); border-bottom: 2px solid rgb(233,233,233);  color:rgb(27,27,27);">
  	<b>> 오픈마켓</b> 
  	<select id="market_select">
  		<option value="ALL">쇼핑몰선택</option>
  		<option value="TH201">11번가</option>
  		<option value="EE715">옥션</option>
  		<option value="EE128">G마켓</option>
  		<option value="ED910">인터파크</option>
  	</select>
   	</div>
  </div>
  <div id="ppList_div" style="width: 700px;">
  <?php 
  	foreach ($ppListResult as $res) {
  ?>
  	<div class="pp_div">
  	<table  class="pp_table">
  		<tr>
  			<td width="20%" align="center"><img src="http://img.danawa.com/cmpny_info/images/<?=$res[2] ?>_logo.gif"></td>
  			<td width="60%"><b><?=$res[1]?>원</b></td>
  			<td width="20%" align="center" rowspan="3"><img src="test/btn.jpg"></td>
  		</tr>
  		<tr>
  			<td></td>
  			<td><b><?=$res[0]?></b></td>
  		</tr>
  		<tr>
  			<td align="center">2014.08.23</td>
  		</tr>
  	</table>
  	</div>
  <?php 
  	}
  ?>	
  </div>

  <div style="width:700px; text-align: center; margin-top: 10px; margin-bottom: 10px;">
  	<a href="javascript:listShow('1','<?=$stanInfoResult[6] ?>')" class="moreBtn">상품더보기</a>
  </div>
  <div style="width:670px; border-top: 2px solid; font-size:8pt; color: rgb(139,139,139); padding-left: 30px;">
  <br>
  		다나와는 중개자로 상품의 가격 및 판매 배송은 해당 쇼핑몰의 책임하에 운영되고 있습니다.&nbsp;&nbsp; 
  		<img src="test/btn_respon2.gif" style="margin-top: 3px;"><br><br>
  
  	사업자등록번호 : 117-81-40065ㅣ통신판매 양천 918호ㅣTel : 1688-2451ㅣE-mail : webmaster@danawa.com<br>
  	주소 : (우)158-718 서울특별시 양천구 목동동로 233-1, 501 (목동, 드림타워)<br>
  	대표이사 : 손윤환ㅣ개인정보관리책임자 미래기획본부 고칠현
  	<p style="font-size: 8pt;">
  	Copyrightⓒ <b>danawa</b> Co., Ltd. All Rights Reserved.
  	</p>
  </div>
</body>
 
</html>