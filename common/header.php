<?php 
	$page = substr(strrchr($_SERVER['PHP_SELF'],'/'),1);
?>
<table>
  <tr>
    <td width="160"><img alt="" src="http://image.ojt2.com/image/logo.JPG" style="margin-top: 10px; margin-left: 10px;"></td>
    <td>
    <?php 
		if ($page == 'standard.php') {
			echo "기준상품등록  | ";
		} else {
			echo "<a href='standard.php'>기준상품등록</a> | ";
		}
	?>
	</td>
    <td>
    <?php 
	    if ($page == 'partnerProduct.php') {
	    	echo "협력사상품등록 | ";
	    } else {
	    	echo "<a href='partnerProduct.php'>협력사상품등록</a> | ";
	    }
    ?>
    </td>
    <td>
    <?php 
	    if ($page == 'productLink.php') {
	    	echo "상품링크 | ";
	    } else {
	    	echo "<a href='productLink.php'>상품링크</a> | ";
	    }
    ?>
    </td>
    <td>
    <?php 
	    if ($page == 'UrlParsing.php') {
	    	echo "상품정보 파싱 | ";
	    } else {
	    	echo "<a href='UrlParsing.php'>상품정보 파싱</a> | ";
	    }
    ?>
    </td>
    <td>
    <?php 
	    if ($page == 'excelupload.php') {
	    	echo "엑셀 업로드";
	    } else {
	    	echo "<a href='excelupload.php'>엑셀 업로드</a> | ";
	    }
    ?>
    </td>
  </tr>
</table>
