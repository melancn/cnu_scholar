<?php
if($_GET['key'] != 'k')exit;

$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://www.ie.cnu.edu.cn/2011/index.php/Infodetail/showactivity');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$content = curl_exec($ch);
// 解析链接
preg_match_all("#/2011/index.php/Infodetail/activitycontent/id/(\d+)#i", $content, $matches);

foreach($matches[1] as $key => $val){
	$id = trim($val);
	$pre = $pdo->prepare("select * from scholar where type='ie' and scholarid=:scholarid");
	$pre->bindParam(':scholarid',$id);
	$pre->execute();
	$res = $pre->fetch();
	
	if($res === false){
		$ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, 'http://www.ie.cnu.edu.cn'.$matches[0][$key]);
        curl_setopt($ch1, CURLOPT_HEADER, 0);
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        $content1 = curl_exec($ch1);
        curl_close($ch1);
        if(empty($content1)) continue;
        preg_match('/<div id="title"([\s\S]*?)<\/div>/i', $content1, $match1);
        preg_match('/<font size="5"[\s\S]*?>([\s\S]*?)<\/font>/i', $match1[0], $match2);
		$title =  $match2[1];
		
        preg_match("#<div id=\"artical_real\"[^>]*>[\s\S]*</body>#i", $content1, $match1);
        $content1 = strip_tags($match1[0]);
        if(empty($content1)) continue;
        preg_match('/((\d+)年)?(\d+)月(\d+)日/i', $content1, $match);
        if(empty($match)) continue;
        if(strpos($match[0],'年') === false){
            $date = date('Y-');
            $date .= strlen($match[3]) === 1 ? '0'.$match[3].'-' : $match[3].'-';
            $date .= strlen($match[4]) === 1 ? '0'.$match[4] : $match[4];
        } else {
            $date = $match[2].'-';
            $date .= strlen($match[3]) === 1 ? '0'.$match[3].'-' : $match[3].'-';
            $date .= strlen($match[4]) === 1 ? '0'.$match[4] : $match[4];
        }
        var_dump($id ,$title,$date);
		$pre = $pdo->prepare("insert into scholar (`scholarid`,`type`,`tittle`,`date`,`indate`) values(:scholarid,'ie',:tittle,:date,'".date('Y-m-d')."')");
		$pre->bindParam(':scholarid',$id);
		$pre->bindParam(':tittle',$title);
		$pre->bindParam(':date',$date);
		$pre->execute();
	}
}