<?php
if($_GET['key'] != 'k')exit;

$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://math.cnu.edu.cn/xzhd1/index.htm');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
// 解析链接
preg_match_all('/<li><span>([\s\S]*?)<\/span><a href="(\d+)\.htm" title="([\s\S]*?)"/i', $content, $matches);

foreach($matches[2] as $key => $val){
	$id = trim($val);
	$pre = $pdo->prepare("select * from scholar where type='math' and scholarid=:scholarid");
	$pre->bindParam(':scholarid',$id);
	$pre->execute();
	$res = $pre->fetch();
	
	if($res === false){
		$pre = $pdo->prepare("insert into scholar (`scholarid`,`type`,`tittle`,`date`,`indate`) values(:scholarid,'math',:tittle,:date,'".date('Y-m-d')."')");
		$pre->bindParam(':scholarid',$id);
		$title=trim($matches[3][$key]);
		$pre->bindParam(':tittle',$title);
		$date = trim($matches[1][$key]);
		$pre->bindParam(':date',$date);
		$pre->execute();
        //加入微博发送列表
        $msg = '#CNU讲座#['.$date.']['.$title.']http://math.cnu.edu.cn/xzhd1/'.$id.'.htm';
		$pre = $pdo->prepare("insert into `sdwb` (`msg`) values(:msg)");
		$pre->bindParam(':msg',$msg);
		$pre->execute();
	}
}