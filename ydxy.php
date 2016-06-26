<?php
if($_GET['key'] != 'k')exit;
//燕都学院
$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://ydxy.cnu.edu.cn/xwzx/xzhd/index.htm');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
// 解析链接
preg_match_all('/<li><span>([\s\S]*?)<\/span><b><\/b>\s+<a href="(\d+)\.htm" target="_self">([\s\S]*?)<\/a>/i', $content, $matches);

foreach($matches[2] as $key => $val){
	$id = trim($val);
	$pre = $pdo->prepare("select * from scholar where type='ydxy' and scholarid=:scholarid");
	$pre->bindParam(':scholarid',$id);
	$pre->execute();
	$res = $pre->fetch();
	
	if($res === false){
		$pre = $pdo->prepare("insert into scholar (`scholarid`,`type`,`tittle`,`date`,`indate`) values(:scholarid,'ydxy',:tittle,:date,'".date('Y-m-d')."')");
		$pre->bindParam(':scholarid',$id);
		$title=trim($matches[3][$key]);
		$pre->bindParam(':tittle',$title);
		$date = trim($matches[1][$key]);
		$pre->bindParam(':date',$date);
		$pre->execute();
	}
}