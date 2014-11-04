<?php 
	require_once '../manager/categoryManager.php';
	$cateManager = new categoryManager();
	$cateRes = $cateManager->categoryList();
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/list.css"/>
<link rel="stylesheet" type="text/css" href="css/common.css"/>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<title>Insert title here</title>
<script type="text/javascript">
var leftPage = 1;
var rightPage = 1;
var cateNo = 0;
var tableMode = ''; // link=링크된상품, unlink=링크안된상품, selectLink=링크상품보기
var stancode = '';
// 정렬변수 ( 0=정렬없음, 1=오름, 2=내림)
var sProduct = 0; // 기준상품명
var sPrice = 0;   // 기준상품 최저가
var sCount = 0;   // 기준상품 업체수
var sPartner = 0; // 협력사상품 상품명
var sDate = 0;    // 협력사상품 최초등록
var updown = {'0':'', '1':'↑', '2':'↓'};

// 엑셀 다운
function excelDown(table) {
	alert(table);
	if(table == 'left') {
		location.href="excelDown.php?cateno="+cateNo+"&page="+leftPage+"&sort="+sProduct+"_"+sPrice+"_"+sCount+"&table=left";
	} else if(table == 'selectLink'){stancode
		location.href="excelDown.php?cateno="+cateNo+"&page="+rightPage+"&sort="+sProduct+"_"+sPrice+"&table="+table+"&stancode="+stancode;
	} else {
		location.href="excelDown.php?cateno="+cateNo+"&page="+rightPage+"&sort="+sProduct+"_"+sPrice+"&table="+table;
	}
}

function cate_search() { // 검색버튼
	var cate = $("#cate_select option:selected").val();
	if (cate == "선택" ) {
		alert("카테고리를 선택해 주세요.");
		return;
	};
	
	if ($("#link_check").is(':checked')){
		tableMode = 'selectLink';
	} else {
 		tableMode = $("#link_radio:checked").val();
	} 
	//alert('mode : '+tableMode);
	sProduct = 0;
	sPrice = 0;
	sCount = 0;
	sPartner = 0;
	sDate = 0;
	cateNo = cate;
	stancode = '';
	if(tableMode == 'link') {
		$("#link_btn").attr("disabled","disabled");
		$("#unlink_btn").removeAttr("disabled");
	} else if (tableMode == 'unlink') {
		$("#unlink_btn").attr("disabled","disabled");
		$("#link_btn").removeAttr("disabled");
	} else if (tableMode == 'selectLink') {
		$("#link_btn").attr("disabled","disabled");
		$("#unlink_btn").removeAttr("disabled");
	}
	pageSet('1','left',cate);
	pageSet('1', tableMode, cate);
	//id="stan_excel_btn" onclick="stanDown()
	$("#stan_excel_btn").removeAttr('disabled');
	$("#part_excel_btn").removeAttr('disabled');
	$("#stan_excel_btn").attr("onclick", "excelDown('left')");
	$("#part_excel_btn").attr("onclick", "excelDown('"+tableMode+"')");
}

function listSort(table, sort, cateno) {
	if(sort == 'product') {
		if (sProduct == 0) {
			sProduct = 1;
		} else if (sProduct == 1){
			sProduct = 2;
		} else if (sProduct == 2) {
			sProduct = 0;
		}
	}
	if(sort == 'price') {
		if (sPrice == 0) {
			sPrice = 1;
		} else if (sPrice == 1){
			sPrice = 2;
		} else if (sPrice == 2) {
			sPrice = 0;
		}
	}
	if(sort == 'count') {
		if (sCount == 0) {
			sCount = 1;
		} else if (sCount == 1){
			sCount = 2;
		} else if (sCount == 2) {
			sCount = 0;
		}
	}
	if(sort == 'partner') {
		if (sPartner == 0) {
			sPartner = 1;
		} else if (sPartner == 1){
			sPartner = 2;
		} else if (sPartner == 2) {
			sPartner = 0;
		}
	}
	if(sort == 'date') {
		if (sDate == 0) {
			sDate = 1;
		} else if (sDate == 1){
			sDate = 2;
		} else if (sDate == 2) {
			sDate = 0;
		}
	} 
	pageSet('1',table,cateno);
	
}

function pageSet(page, table, cateno) {
	var params={};

	if (table == 'left') {
		params = {'cateno':cateno, 'page':page, 'table':table, 'sort':sProduct+"_"+sPrice+"_"+sCount};
	} else if(table == 'selectLink') { 
		params = {'cateno':cateno, 'page':page, 'table':table, 'sort':sPartner+"_"+sDate, 'stancode':+stancode};
	} else {
		params = {'cateno':cateno, 'page':page, 'table':table, 'sort':sPartner+"_"+sDate};
	}
	//alert(sPartner+"_"+sDate);
	$.ajax({      
        type:"POST",  
        url:"http://ojt2.com/ajaxProcess.php",      
        data:params,      
        success:function(args){   
            //alert(args);
        	var data = $.parseJSON(args);
        	if (table == "left") {	
				leftPage = page; // 기준상품 페이지저장
	        	$("#leftPageDiv").empty(); 
	        	var page_set = 20;
	        	var block_set = 10;
	        	var total = data[0][0];
	        	var block = Math.ceil(page / block_set); 
	        	var totalPage = Math.ceil(total/page_set);
	        	var first_page = ((block-1)*block_set)+1;
	        	var last_page = block*block_set;
	
	        	var next_block = last_page+1;
	        	var prev_block = first_page-1;

	        	if(prev_block > 0) {
					$("#leftPageDiv").append("<a href=javascript:pageSet('"+1+"','"+table+"','"+cateno+"')>&lt;&lt;&nbsp;</a>");
					$("#leftPageDiv").append("<a href=javascript:pageSet('"+prev_block+"','"+table+"','"+cateno+"')>&lt;&nbsp;</a>");
	        	}
	        	for(var i = first_page; i <= last_page; i++){
		        	if(i <= totalPage) {
			        	if ( i == page ) {
			        		$("#leftPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')><b>"+i+"</b></a>&nbsp;");	
			        	} else {
			        		$("#leftPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')>"+i+"</a>&nbsp;");
			        	}
		        	}
	        	}
	        	if(next_block < totalPage) {   
		        	$("#leftPageDiv").append("<a href=javascript:pageSet('"+next_block+"','"+table+"','"+cateno+"')>&gt;&nbsp;</a>");
		       		$("#leftPageDiv").append("<a href=javascript:pageSet('"+totalPage+"','"+table+"','"+cateno+"')>&gt;&gt;&nbsp;</a>");
			    }	
			    ///////////// 리스트 뿌려주기 //////////
	        	$("#left_table").empty(); 
	        	if(tableMode == 'link'){
	        		$("#left_table").append("<tr class='h_tr'>"+
							"<td width=13%>카테고리</td><td><a href=javascript:listSort('left','product','"+cateno+"')>상품명"+updown[sProduct]+"</a></td>"+
							"<td>ⓘ</td><td>ⓤ</td><td><a href=javascript:listSort('left','price','"+cateno+"')>최저가"+updown[sPrice]+"</a></td>"
							+"<td>평균가</td><td width='8%'><a href=javascript:listSort('left','count','"+cateno+"')>업체수"+updown[sCount]+"</a></td>"+
							"</tr>");
	        	} else {
	        		$("#left_table").append("<tr class='h_tr'>"+
							"<td>카테고리</td><td><a href=javascript:listSort('left','product','"+cateno+"')>상품명"+updown[sProduct]+"</a></td>"+
							"<td>ⓘ</td><td>ⓤ</td><td><a href=javascript:listSort('left','price','"+cateno+"')>최저가"+updown[sPrice]+"</a></td>"
							+"<td>평균가</td><td width='8%'><a href=javascript:listSort('left','count','"+cateno+"')>업체수"+updown[sCount]+"</a></td><td></td>"+
							"</tr>");
	        	}
	 
				if (data[1].length == 0) {
					$("#left_table").append("<tr><td colspan=8 align=center>검색결과 0개.</td></tr>");
					$("#stan_excel_btn").attr('disabled', 'disabled');
				} else {
		        	for(var i = 0; i <= data[1].length; i++) {
			        	if(tableMode == 'link') {
			        		$("#left_table").append("<tr>"+
									"<td width=12%>"+data[1][i][1]+"</td>"
								   +"<td width=48%><a href='standardModify.php?code="+data[1][i][0]+"'>"+data[1][i][2]+"</a></td>"
								   +"<td onmouseover=leftImageShow('"+data[1][i][0]+"',this) onmouseout=leftImageShow('"+data[1][i][0]+"',this)>ⓘ</td>"
								   +"<td><a href='blog.php?stanCode="+data[1][i][0]+"' target=_blank>ⓤ</td>"
								   +"<td>"+setComma(data[1][i][3])+"</td>"
								   +"<td>"+setComma(data[1][i][4])+"</td>"
								   +"<td>"+data[1][i][5]+"</td>"
								   +"</tr>");
			        	} else if(tableMode == 'unlink') {
			        		$("#left_table").append("<tr>"+
									"<td width=12%>"+data[1][i][1]+"</td>"
								   +"<td width=48%><a href='standardModify.php?code="+data[1][i][0]+"'>"+data[1][i][2]+"</a></td>"
								   +"<td onmouseover=leftImageShow('"+data[1][i][0]+"',this) onmouseout=leftImageShow('"+data[1][i][0]+"',this)>ⓘ</td>"
								   +"<td><a href='blog.php?stanCode="+data[1][i][0]+"' target=_blank>ⓤ</td>"
								   +"<td>"+setComma(data[1][i][3])+"</td>"
								   +"<td>"+setComma(data[1][i][4])+"</td>"
								   +"<td>"+data[1][i][5]+"</td>"
								   +"<td><input type='radio' name=table_radio value="+data[1][i][0]+"></td>"
								   +"</tr>");
			        	} else if(tableMode == 'selectLink') {
			        		$("#left_table").append("<tr>"+
									"<td width=12%>"+data[1][i][1]+"</td>"
								   +"<td width=48%><a href='standardModify.php?code="+data[1][i][0]+"'>"+data[1][i][2]+"</a></td>"
								   +"<td onmouseover=leftImageShow('"+data[1][i][0]+"',this) onmouseout=leftImageShow('"+data[1][i][0]+"',this)>ⓘ</td>"
								   +"<td><a href='blog.php?stanCode="+data[1][i][0]+"' target=_blank>ⓤ</td>"
								   +"<td>"+setComma(data[1][i][3])+"</td>"
								   +"<td>"+setComma(data[1][i][4])+"</td>"
								   +"<td>"+data[1][i][5]+"</td>"
								   +"<td><input type='radio' name=table_radio value="+data[1][i][0]+" onclick='stanSelect()'></td>"
								   +"</tr>");
			        	}
		        	}
				}
	        // $("#standardDown").attr("href","exceldown/standard_down.php?cateno="+cateno+"&page="+page+"&sort="+sProduct+"_"+sPrice+"_"+sCount);
        	} else if (table == 'unlink') {
        		rightPage = page;
        		$("#rightPageDiv").empty(); 
	        	var page_set = 20;
	        	var block_set = 10;
	        	var total = data[0][0];
	        	var block = Math.ceil(page / block_set); 
	        	var totalPage = Math.ceil(total/page_set);
	        	var first_page = ((block-1)*block_set)+1;
	        	var last_page = block*block_set;
				
	        	var next_block = last_page+1;
	        	var prev_block = first_page-1;
// 	        	alert(total);
// 	        	alert(data[1]);
	        	if(prev_block > 0) {
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+1+"','"+table+"','"+cateno+"')>&lt;&lt;&nbsp;</a>");
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+prev_block+"','"+table+"','"+cateno+"')>&lt;&nbsp;</a>");
	        	}
			    for(var i = first_page; i <= last_page; i++){
			    	if(i <= totalPage) {
				    	if ( i == page ) {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')><b>"+i+"</b></a>&nbsp;");	
			        	} else {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')>"+i+"</a>&nbsp;");
			        	}
			    	}
			    }
			    if(next_block < totalPage) {   
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+next_block+"','"+table+"','"+cateno+"')>&gt;&nbsp;</a>");
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+totalPage+"','"+table+"','"+cateno+"')>&gt;&gt;&nbsp;</a>");
			    }
		        	
	        	///////////// 리스트 뿌려주기 //////////
	        	$("#right_table").empty(); 
	        	$("#right_table").append("<tr class='h_tr'>"
	    	        	+"<td width='3%'><input type=checkbox ></td>"
						+"<td width='11%'>협력사</td>"
						+"<td width='10%'>카테고리</td>"
						+"<td width='40%'><a href=javascript:listSort('unlink','partner','"+cateno+"')>협력사상품명"+updown[sPartner]+"</a></td>"
						+"<td width='3%'>ⓘ</div></td>"
						+"<td width='3%'>ⓤ</td>"
						+"<td width='10%'>가격</td>"
						+"<td width='10%'><a href=javascript:listSort('unlink','date','"+cateno+"')>입력일"+updown[sDate]+"</a></td>"+
						"</tr>");
	        	if (data[1].length == 0) {
					$("#right_table").append("<tr><td colspan=8 align=center>검색결과 0개.</td></tr>");
					$("#part_excel_btn").attr('disabled', 'disabled');
				} else {
		        	for(var i = 0; i <= data[1].length; i++) {
			        	var date = data[1][i][6].slice(0,10).slice(2,10);
		        		$("#right_table").append("<tr>"
				        		+"<td><input type='checkbox' id='table_check' name='table_check' value="+data[1][i][7]+"_"+data[1][i][8]+"></td>"
								+"<td>"+data[1][i][0]+"</td>"
								+"<td>"+data[1][i][1]+"</td>"
								+"<td><a href='partnerProductModify.php?pCode="+data[1][i][7]+"&ppCode="+data[1][i][8]+"'>"+data[1][i][2]+"</a></td>"
								+"<td onmouseover=rightImageShow('"+data[1][i][3]+"',this) onmouseout=rightImageShow('null',this)>ⓘ</td>"
								+"<td><a href="+data[1][i][4]+" target=_blank>ⓤ</td>"
								+"<td>"+setComma(data[1][i][5])+"</td>"
								+"<td>"+date+"</td>"+
								"</tr>");
		        	}
				}
        	} else if(table == 'link') {
        		rightPage = page;
        		$("#rightPageDiv").empty(); 
	        	var page_set = 20;
	        	var block_set = 10;
	        	var total = data[0][0];
	        	//alert('total : '+total);
	        	var block = Math.ceil(page / block_set); 
	        	var totalPage = Math.ceil(total/page_set);
	        	var first_page = ((block-1)*block_set)+1;
	        	var last_page = block*block_set;
	
	        	var next_block = last_page+1;
	        	var prev_block = first_page-1;

	        	if(prev_block > 0) {
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+1+"','"+table+"','"+cateno+"')>&lt;&lt;&nbsp;</a>");
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+prev_block+"','"+table+"','"+cateno+"')>&lt;&nbsp;</a>");
	        	}
			    for(var i = first_page; i <= last_page; i++){
			    	if(i <= totalPage) {
				    	if ( i == page ) {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')><b>"+i+"</b></a>&nbsp;");	
			        	} else {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')>"+i+"</a>&nbsp;");
			        	}
			    	}
			    }
			    if(next_block < totalPage) {   
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+next_block+"','"+table+"','"+cateno+"')>&gt;&nbsp;</a>");
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+totalPage+"','"+table+"','"+cateno+"')>&gt;&gt;&nbsp;</a>");
			    }
		        	
	        	///////////// 리스트 뿌려주기 //////////
	        	$("#right_table").empty(); 
	        	$("#right_table").append("<tr class='h_tr'>"
	        			+"<td width='3%'><input type=checkbox></td>"
						+"<td width='11%'>협력사</td>"
						+"<td width='10%'>카테고리</td>"
						+"<td width='40%'><a href=javascript:listSort('link','partner','"+cateno+"')>협력사상품명"+updown[sPartner]+"</a></td>"
						+"<td width='5%'>ⓘ</td>"
						+"<td width='5%'>ⓤ</td>"
						+"<td width='10%'>가격</td>"
						+"<td width='10%'><a href=javascript:listSort('link','date','"+cateno+"')>입력일"+updown[sDate]+"</a></td>"+
						"</tr>");
	        	if (data[1].length == 0) {
					$("#right_table").append("<tr><td colspan=8 align=center>검색결과 0개.</td></tr>");
					$("#part_excel_btn").attr('disabled', 'disabled');
				} else {
		        	for(var i = 0; i <= data[1].length; i++) {
		        		var date = data[1][i][6].slice(0,10).slice(2,10);
		        		$("#right_table").append("<tr>"
				        		+"<td><input type='checkbox' id='table_check' name='table_check' value="+data[1][i][7]+"_"+data[1][i][8]+"_"+data[1][i][9]+"></td>"
								+"<td>"+data[1][i][0]+"</td>"
								+"<td>"+data[1][i][1]+"</td>"
								+"<td><a href='partnerProductModify.php?pCode="+data[1][i][7]+"&ppCode="+data[1][i][8]+"'>"+data[1][i][2]+"</a></td>"
								+"<td onmouseover=rightImageShow('"+data[1][i][3]+"',this) onmouseout=rightImageShow('null',this)>ⓘ</td>"
								+"<td><a href="+data[1][i][4]+" target=_blank>ⓤ</td>"
								+"<td>"+setComma(data[1][i][5])+"</td>"
								+"<td>"+date+"</td>"+
								"</tr>");
						/* t1.partnerCode, 7
						t1.partnerProductCode, 8
						t1.categoryCode 9
						*/
		        	}
				}
        	} else if(table == 'selectLink') {
        		rightPage = page;
        		$("#rightPageDiv").empty(); 
	        	var page_set = 20;
	        	var block_set = 10;
	        	var total = data[0][0];
	        	var block = Math.ceil(page / block_set); 
	        	var totalPage = Math.ceil(total/page_set);
	        	var first_page = ((block-1)*block_set)+1;
	        	var last_page = block*block_set;
	
	        	var next_block = last_page+1;
	        	var prev_block = first_page-1;
	        	/* alert(total);
	        	alert(data[1]); */
	        	if(prev_block > 0) {
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+1+"','"+table+"','"+cateno+"')>&lt;&lt;&nbsp;</a>");
					$("#rightPageDiv").append("<a href=javascript:pageSet('"+prev_block+"','"+table+"','"+cateno+"')>&lt;&nbsp;</a>");
	        	}
			    for(var i = first_page; i <= last_page; i++){
			    	if(i <= totalPage) {
				    	if ( i == page ) {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')><b>"+i+"</b></a>&nbsp;");	
			        	} else {
			        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+i+"','"+table+"','"+cateno+"')>"+i+"</a>&nbsp;");
			        	}
			    	}
			    }
			    if(next_block < totalPage) {   
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+next_block+"','"+table+"','"+cateno+"')>&gt;&nbsp;</a>");
	        		$("#rightPageDiv").append("<a href=javascript:pageSet('"+totalPage+"','"+table+"','"+cateno+"')>&gt;&gt;&nbsp;</a>");
			    }
		        	
	        	///////////// 리스트 뿌려주기 //////////
	        	$("#right_table").empty(); 
	        	$("#right_table").append("<tr class='h_tr'>"
	        			+"<td width='3%'><input type=checkbox></td>"
						+"<td width='11%'>협력사</td>"
						+"<td width='10%'>카테고리</td>"
						+"<td width='40%'><a href=javascript:listSort('selectLink','partner','"+cateno+"')>협력사상품명"+updown[sPartner]+"</a></td>"
						+"<td width='5%'>ⓘ</td>"
						+"<td width='5%'>ⓤ</td>"
						+"<td width='10%'>가격</td>"
						+"<td width='10%'><a href=javascript:listSort('selectLink','date','"+cateno+"')>입력일"+updown[sDate]+"</a></td>"+
						"</tr>");
	        	if (data[1].length == 0) {
	        		$("#part_excel_btn").attr('disabled', 'disabled');
					$("#right_table").append("<tr><td colspan=8 align=center>검색결과 0개.</td></tr>");
				} else {
					$("#part_excel_btn").removeAttr('disabled');
		        	for(var i = 0; i <= data[1].length; i++) {
			        	var date = data[1][i][6].slice(0,10).slice(2,10);
		        		$("#right_table").append("<tr>"
		        				+"<td><input type='checkbox' id='table_check' name='table_check' value="+data[1][i][7]+"_"+data[1][i][8]+"_"+data[1][i][9]+"></td>"
								+"<td>"+data[1][i][0]+"</td>"
								+"<td>"+data[1][i][1]+"</td>"
								+"<td><a href='partnerProductModify.php?pCode="+data[1][i][7]+"&ppCode="+data[1][i][8]+"'>"+data[1][i][2]+"</a></td>"
								+"<td onmouseover=rightImageShow('"+data[1][i][3]+"',this) onmouseout=rightImageShow('null',this)>ⓘ</td>"
								+"<td><a href="+data[1][i][4]+" target=_blank>ⓤ</td>"
								+"<td>"+setComma(data[1][i][5])+"</td>"
								+"<td>"+date+"</td>"+
								"</tr>");
		        	}
				}
        	}
        },
        error:function(e){  
            alert('error : '+e.responseText);  
        }  
	});
}

function link() {
	var ppCode = '';
	var stanCode = $("input[name=table_radio]:checked").val();	
	$("input[name=table_check]:checked").each(function() {
		if(ppCode == '') {
			ppCode = $(this).val();
		} else {
			ppCode = ppCode + "/" +$(this).val();
		}
	});
	
	 $.ajax({      
        type:"POST",  
        url:"http://ojt2.com/ajaxLink.php",      
        data:{'stanCode':stanCode,'ppCode':ppCode},      
        success:function(args){  
        	pageSet(leftPage, 'left', cateNo);
        	pageSet(rightPage, tableMode, cateNo);
        },
        error:function(e){  
            alert('error : '+e.responseText);  
        }  
	}); 
}

function unlink() {
	var code = '';
	$("input[name=table_check]:checked").each(function() {
		if(code == '') {
			code = $(this).val();
		} else {
			code = code + "/" +$(this).val();
		}
	});
	
	 $.ajax({      
        type:"POST",  
        url:"http://ojt2.com/ajaxUnLink.php",      
        data:{'code':code},      
        success:function(args){  
        	pageSet(leftPage, 'left', cateNo);
        	pageSet(rightPage, tableMode, cateNo);
        },
        error:function(e){  
            alert('error : '+e.responseText);  
        }  
	});  
}

function stanSelect(scode) {
	stancode = $("input[name=table_radio]:checked").val();
	pageSet('1', tableMode, cateNo);
}

function leftImageShow(stancode, e) {
	$("#leftImgDiv").css("top", 250+e.offsetTop);
	$("#leftImgDiv").css("left", 50+e.offsetLeft);
	if($("#leftImgDiv").css("display") == "none"){
		$("#leftImg").attr("src", "http://image.ojt2.com/productimage/"+stancode+".jpg");
		$("#leftImgDiv").css("display", "block");
	} else {
		$("#leftImgDiv").css("display", "none");
	}
}

function rightImageShow(Url, e) {
	var obj = document.getElementById("content_left");
	var width = obj.offsetWidth;
	$("#rightImgDiv").css("top", 250+e.offsetTop);
	$("#rightImgDiv").css("left", 50+width+e.offsetLeft);
	if($("#rightImgDiv").css("display") == "none"){
		$("#rightImg").attr("src", Url);
		$("#rightImgDiv").css("display", "block");
	} else {
		$("#rightImgDiv").css("display", "none");
	} 
}

function setComma(num){
	if(num==0) return 0;
	var reg = /(^[+-]?\d+)(\d{3})/;
	var n = (num + '');
	while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');
	return n;
}

</script>
</head>

<body>
<div style="height:80px; width: 100%; background-color: rgb(217,217,217);">
	<?php include_once '../common/header.php';?>
</div>

<!-- content 시작 -->
<div style="height:auto; width: 100%; margin: 0 auto;">
<div id="main">
<p># 상품링크</p>
  	<div id="content_top">
  		<table border="1" style="width: 500px;">
  			<tr>
  				<td colspan="2">
  					<input type="radio" value="link" id="link_radio" name="link_radio" checked="checked">링크된상품
  					<input type="radio" value="unlink" id="link_radio" name="link_radio">링크안된상품
  					<input type="checkbox" value="selectLink" id='link_check'>링크상품보기
  				</td>
  			</tr>
  			<tr>	
  				<td width="100">
  					카테고리
  				</td>
  				<td>
	  				<select id="cate_select">
	  					<option>선택</option>
	  					<?php 
  							while ($res = mysqli_fetch_row($cateRes)) {
  						?>
  							<option value="<?=$res[0]?>"><?=$res[1]?></option>
  						<?php 
							}
						?>
	  				</select>
	  				<button id="search_btn" onclick="cate_search()">검색</button>
  				</td>
  			</tr>
  		</table>
  	</div>
  	<div id="content_left">
  	<div id="leftImgDiv" style="position: absolute; width: 80px; height: 80px;  display:none ;">
  		<img id="leftImg"  width="80" height="80" alt="" src="http://image.ojt2.com/image/logo.JPG">
  	</div>
  	기준상품 <button id="stan_excel_btn" disabled>엑셀 다운로드</button>
  		<table border="1" id="left_table">
  			<tr class='h_tr'>
  				<td><a>상품코드</a></td>
  				<td>카테고리</td>
  				<td>상품명</td>
  				<td>최저가</td>
  				<td>평균가</td>
  				<td>업체수</td>
  			</tr>
  		</table>
  		<div id="leftPageDiv">
  		
  		</div>
  	</div>
  	<div id="content_right">
  	<div id="rightImgDiv" style="position: absolute; width: 80px; height: 80px; display:none ;">
  		<img id="rightImg"  width="80" height="80" alt="" src="http://image.ojt2.com/image/logo.JPG">
  	</div>
  	협력사 상품 <button id="part_excel_btn" disabled>엑셀 다운로드</button>
  		<table border="1" id="right_table">
  			<tr class='h_tr'>
  				<td width="20"><input type="checkbox"></td>
  				<td>협력사명</td>
  				<td >협력사코드</td>
  				<td>협력사상품명</td>
  				<td>협력사URL</td>
  				<td>가격</td>
  				<td>입력일</td>
  			</tr>
  		</table>
  		<div id="rightPageDiv">
  			
  		</div>
  	</div>
  </div>
  <div style="float: left; width: 100%">
  <p align="center"> 
  <input type="button" id="link_btn" value="링크생성" onclick="link()">
  <input type="button" id="unlink_btn" value="링크해제" onclick="unlink()">
  </p>
  </div>
</div>
<!-- content 끝 -->

<div style="float:left; height:80px; width: 100%;">
<br>
		<?php include_once '../common/footer.php';?>
</div>
</body>
 
</html>