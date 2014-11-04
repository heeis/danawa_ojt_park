<?php 
	error_reporting(E_ALL);
	ini_set("display_errors", 1);


	require_once '../manager/standardManager.php';
	require_once '../manager/partnerProductManager.php';
	require_once '../manager/linkManager.php';
	require_once '../mysql/mysqlConn.php';
	$db_conn = new mysqlConn();
	$link = $db_conn->connect();
	
	$stanMgr = new standardManager($link);
	$ppMgr = new partnerProductManager($link);
	$linkMgr = new linkManager($link);
	
	$stanCode = $_GET['stanCode'];
	
	$stanInfoResult = $stanMgr->blogStandardInfo($stanCode);
	
	$linkCount = $linkMgr->linkCount($stanCode);
	if($linkCount != 0) {
		$ppListResult = $ppMgr->blogProductList($stanCode, 1, 'ALL');
		$ppTotalAvg = $ppMgr->blogListAvgTotal($stanCode);
	} 
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
var market='ALL';
var stanCode = <?=$stanInfoResult[6]?>;
var minPrice = <?=$ppListResult[0][1]?>;
function addList(page) {
		//alert(page);
	$.ajax({      
        type:"POST",  
        url:"http://ojt2.com/ajaxBlog.php",      
        data:{'page':page,'stanCode':stanCode,'market':market},      
        success:function(args){ 
        	var data = $.parseJSON(args);
        	var total = data[1]; 
        	var totalPage = Math.ceil(total/20);
        	
        	for(var i = 0; i < data[0].length; i++) {
        		var price = setComma(data[0][i][1]);
				var str;
            	if(minPrice==data[0][i][1]){
					str = "<td width='60%'><b style='color: rgb(226,16,3);'>"+price+"원</b></td>"
            	} else {
            		str = "<td width='60%'><b>"+price+"원</b></td>"
            	}
            	var date = data[0][i][3].substring(0, 10).replace(/-/gi,'.');
				$("#ppList_div").append("<div class='pp_div'>"
			  	+"<table  class='pp_table'>"
			  	+"<tr>"
			  	+"<td width='20%' align='center'><img src='http://img.danawa.com/cmpny_info/images/"+data[0][i][2]+"_logo.gif'></td>"
			  		+ str
			  		+"<td width='20%' align='center' rowspan='3'><a href='"+data[0][i][4]+"' target='_blank'><img src='test/btn.jpg'><a></td>"
			  		+"</tr>"
			  		+"<tr>"
			  		+"<td></td>"
			  		+"<td><b style='color: rgb(99,99,99);'>"+data[0][i][0]+"</b></td>"
			  		+"</tr>"
			  		+"<tr>"
			  		+"<td align='center'>"+date+"</td>"
			  		+"</tr>"
			  		+"</table>"
			  		+"</div>");
            }
        
            if(totalPage > page) {
                var p = ++page;
                $("#more_div").css("display","block");
            	$("#more_a").attr("href", "javascript:addList('"+p+"')");
            } else {
            	$("#more_div").css("display","none");
            }
        },
        error:function(e){  
            alert('error : '+e.responseText);  
        }  
	});
}

function changeMarket() {
	market = $("#market_select option:selected").val();
	$("#ppList_div").empty();
	addList(1);
}

function setComma(num){
	if(num==0) return 0;
	var reg = /(^[+-]?\d+)(\d{3})/;
	var n = (num + '');
	while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');
	return n;
}

function moveTop() {
	var top = $("#market_div").offset().top;
	$('html,body').animate({scrollTop:top}, 0);
}

function winOpen() {
	window.open("http://www.danawa.com/info/liability.html", "책임의 한계", "width=960, height=1000, resizable=no, scrollbars=no, status=no;");
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
  			<img width="170" height="170" src="http://image.ojt2.com/image/<?=$stanInfoResult[2] == '' ? 'noimage.gif' : $stanInfoResult[2]?>">
  		</div>
  		<div style="width: 210px;">
  		<p align="center" style="font-size: 8pt; color: rgb(139,139,139);">
  		이미지 출처 : <?=$stanInfoResult[3]!='' ? $stanInfoResult[3] : '정보없음' ?>
  		</p>
  		</div>
  		<div style="width: 210px; margin-top: 25px;">
  		<p align="center" style="font-size: 8pt; color: rgb(139,139,139);">
  		<?php 
  			$year = substr($stanInfoResult[4], 0, 4);
  			$month = substr($stanInfoResult[4], 4, 2);
  		?>
  		등록년월 <?=$stanInfoResult[4]!='' ? $year . '.' . $month : '정보없음' ?>
  		</p>
  		</div>
  	</div>
  	<div style="float:right; width: 450px; height: auto;">
  
  		<div style="width: 450px; height: 40px;">
  			<p style="font-weight:bold; ">
  			<?=$stanInfoResult[1] ?>
  			</p>
  		</div>
  		<?php 
  	if($linkCount != 0){
  	?>
  		<div style="width: 450px; height: 50px;">
  			<img src="http://img.danawa.com/cmpny_info/images/<?=$ppListResult[0][2] ?>_logo.gif">
  			<font style="font-size: 26pt; font-weight: bold; margin-left: 10px; margin-right: 30px; color:rgb(139,139,139)">
  				<?=number_format($ppListResult[0][1])?>원
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
  					<td width="90" align="right"><?=number_format($ppListResult[$i][1])?> 원</td>
  					<td width="90" align="right" style="color:rgb(139,139,139)">+<?=number_format($ppListResult[$i][1]-$ppListResult[0][1])?>원</td>
  				</tr>
  				<?php 
  				}
  				?>

  			</table>
  		</div>
  		<br>
  		<div style="border-top: 1px solid; margin-bottom: 10px; color: rgb(139,139,139);">
  			(다나와 평균가:<?=number_format($ppTotalAvg[1])?>원)
  			<div onclick="moveTop()" style="float: right; border: 1px solid; border-top:0; cursor:pointer; font-size: 8pt;">
  			<span style="">&nbsp;&nbsp;쇼핑몰별 <?=$ppTotalAvg[0]?>개 더보기&nbsp;&nbsp;</span>
  			<span style="border-left: 1px solid rgb(139,139,139);" >&nbsp;<img src="test/plus.gif">&nbsp;</span>
  			</div>
  		</div>
  	<?php 
  	} else {
  	?>
  		<div>
  			<img width="450" height="160" alt="" src="test/img6.jpg" style="margin-bottom: 10px;">
  		</div>	
  	<?php 
  	}
  	?>
  		<div style="color: rgb(139,139,139);">
  		<?=$stanInfoResult[5]!='' ? $stanInfoResult[5] : '' ?>
  		</div>
  	</div>
  </div>
  <div id="market_div" style="width: 700px; margin-bottom: 10px;">
  	<div style="width: 690px; height: 32px; margin-top: 5px; background-color: rgb(245,245,245); padding-top: 11px; padding-left: 10px; border-top: 2px solid rgb(233,233,233); border-bottom: 2px solid rgb(233,233,233);  color:rgb(27,27,27);">
  	<b>> 오픈마켓</b> 
  	<select id="market_select" onchange="changeMarket()" <?php if($linkCount == 0) echo "disabled='disabled'";?>>
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
  if($linkCount != 0){
  	foreach ($ppListResult as $res) {
  ?>
  	<div class="pp_div">
  	<table  class="pp_table">
  		<tr>
  			<td width="20%" align="center"><img src="http://img.danawa.com/cmpny_info/images/<?=$res[2] ?>_logo.gif"></td>
  			 
  			<?php if($res[1] == $ppListResult[0][1]) { ?>
  			<td width="60%"><b style="color: rgb(226,16,3);"><?=number_format($res[1])?>원</b></td>
  			<?php } else { ?>
  			<td width="60%"><b><?=number_format($res[1])?>원</b></td>
  			<?php }?>
  			<td width="20%" align="center" rowspan="3"><a href="<?=$res[4]?>" target="_blank"><img src="test/btn.jpg"></a></td>
  		</tr>
  		<tr>
  			<td></td>
  			<td><b style="color: rgb(99,99,99);"><?=$res[0]?></b></td>
  		</tr>
  		<tr>
  			<?php 
  				  $date = substr($res[3],0,10); 
  				  $date = str_replace("-", ".", $date);
  			?>
  			<td align="center"><?=$date?></td>
  		</tr>
  	</table>
  	</div>
  <?php 
  	}
  ?>	
  </div>
  <?php 
  if($ppTotalAvg[0] > 20) {
  ?>
  <div id="more_div" style="width:700px; text-align: center; margin-top: 10px; margin-bottom: 10px;" >
  	<a id="more_a" href="javascript:addList('2')" class="moreBtn">상품더보기</a>
  </div>
  <?php 
  }
  }
  ?>
  <div style="width:670px; border-top: 2px solid; font-size:8pt; color: rgb(139,139,139); padding-left: 30px;">
  <br>
  		다나와는 중개자로 상품의 가격 및 판매 배송은 해당 쇼핑몰의 책임하에 운영되고 있습니다.&nbsp;&nbsp; 
  		<a href="javascript:winOpen()"><img src="test/btn_respon2.gif" style="margin-top: 3px;"></a><br><br>
  
  	사업자등록번호 : 117-81-40065ㅣ통신판매 양천 918호ㅣTel : 1688-2451ㅣE-mail : webmaster@danawa.com<br>
  	주소 : (우)158-718 서울특별시 양천구 목동동로 233-1, 501 (목동, 드림타워)<br>
  	대표이사 : 손윤환ㅣ개인정보관리책임자 미래기획본부 고칠현
  	<p style="font-size: 8pt;">
  	Copyrightⓒ <b>danawa</b> Co., Ltd. All Rights Reserved.
  	</p>
  </div>
</body>
 
</html>