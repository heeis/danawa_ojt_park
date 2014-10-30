<?php
require_once '/var/www/html/ojt2/lib/simple_html_dom.php';
//phpinfo();
//  $str = iconv("EUC-KR", "UTF-8", $str);
$ppUrl = "http://www.11st.co.kr/product/SellerProductDetail.tmall?method=getSellerProductDetail&prdNo=1038634824&trTypeCd=03&trCtgrNo=1932115";
$ppStr = explode('prdNo=', $ppUrl);

$html = file_get_html($ppUrl);
print_r($html);
if($html == null) {
	echo 'ss';
}

$image = $html->find('img[id=bigImg]')[0]->src; // 11번가 이미지

echo $image . '<br>';
/* $image2 = strrchr($image,"."); // 확장자만 짜르기
echo '<br>'.$image2.'<br>'; */
//$price = $html->find('div[class=dis mprice]')
/* foreach ($html->find('div[class=dis mprice]') as $element) 
	echo $element->find('span')[0] . '<br>';  */
$sale = $html->find('img'); // 옥션 가격
echo $sale[1] . '..';

$title = $html->find('div[class=prdc_heading_v2]')[0]->plaintext;
$title = iconv("EUC-KR", "UTF-8", $title); // 상품명
echo $title;

// Open the file to get existing content
$data = file_get_contents($image);
// New file
$new = '/upload/url'.$image2;
// Write the contents back to a new file
file_put_contents($new, $data);