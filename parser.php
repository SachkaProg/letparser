<?php 

$conn = mysqli_connect("localhost", "root", "root", 'let');
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
} 

$curl = curl_init();
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; rv:2.2) Gecko/20110201');

$url ='https://www.letu.ru/storeru/product/arnaud-paris-gel-dlya-snyatiya-makiyazha-s-litsa-i-glaz-rituel-visage-s-rozovoi-vodoi/73900028?pushSite=storeMobileRU&format=json';
$products = [];



// Парсинг url-ов из каталога
for($i = 1; $i < 15; $i++) {
    $page = ($i == 1) ? '' : 'page-'.$i;
    $url = 'https://www.letu.ru/storeru/browse/uhod-za-kozhei/uhod-za-litsom/ochishchenie/'.$page.'?pushSite=storeMobileRU&format=json&pushSite=storeMobileRU';

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);
    

    $str = curl_exec($curl);
    $arr = json_decode($str, true);
    

    foreach($arr['contents'][0]['mainContent'][4]['records'] as $value) {
        $products[] =  'https://www.letu.ru/storeru/product/'.$value['attributes']['product.sefName'][0].'/'.$value['attributes']['product.repositoryId'][0].'?pushSite=storeMobileRU&format=json';
    }
}



// Парсинг отдельгого товара
foreach($products as $value) {
    $url = $value;

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_REFERER, $url);

    $str = curl_exec($curl);
    $arr = json_decode($str, true);

    echo "<pre>";
    $product = $arr['contents'][0]['mainContent'][0]['contents'][0]['productContent'][0]['result']['product'];

    $name = $product['displayName'];
    $brand = $product['brand'];
    $rawPrice = $product['rawPrice'];
    $priceWithoutCoupons = $product['priceWithoutCoupons'];


    $sql = "INSERT INTO `products` (name, brand, rawPrice, priceWithoutCoupons, delivery, updated_at) VALUES ('$name', '$brand', '$rawPrice', '$priceWithoutCoupons',' ', date('Y-m-d H:i:s') )";

    if(!mysqli_query($conn, $sql)){
        echo "Ошибка: " . mysqli_error($conn);
    } 
}


curl_close($curl);