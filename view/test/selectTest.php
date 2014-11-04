<?php
require_once '/var/www/html/ojt2/lib/simple_html_dom.php';
//phpinfo();
//  $str = iconv("EUC-KR", "UTF-8", $str);
$ppUrl = "http://deal.11st.co.kr/product/SellerProductDetail.tmall?method=getSellerProductDetail&prdNo=1158733035&trTypeCd=03&trCtgrNo=901214";
$ppStr = explode('prdNo=', $ppUrl);

echo $ppStr[1];

$html = file_get_html($ppUrl);
$price;
for($i = 0; $i < count($html->find('meta')); $i++) {
	if($html->find('meta')[$i]->property == 'price') {
		$price = $html->find('meta')[$i]->content;
	}
}
echo $price;

echo '<br>';
if($html == null) {
	echo 'ss';
}

$image = $html->find('img[id=bigImg]')[0]->src; // 11번가 이미지
echo $image . '<br>';

$title = $html->find('div[class=prdc_heading_v2]')[0]->plaintext;
echo $title;
$title = iconv("EUC-KR", "UTF-8", $title); // 상품명
echo $title;
