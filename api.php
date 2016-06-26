<?php
header("content-type: application/json");

$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);

try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}
$start = is_numeric($_GET['start']) ? intval($_GET['start']) : 1;
$limit = is_numeric($_GET['limit']) && $_GET['limit'] < 50 ? intval($_GET['limit']) : 10;
$start = ($start-1)* $limit;

$data = array();
$sql = "select * from `scholar` WHERE DATE >= '".date('Y-m-d')."' order by date ASC limit {$start},{$limit}";
$md5sql = md5($sql);
if($out = $mmc->get($md5sql)){
	exit($out );
}
$resource = $pdo->query($sql);
$list = $out = array();
while($res = $resource->fetch()){
	$list[$res['date']][] = parseurl($res);
}
$resource = $pdo->query("select count(*) as count from `scholar` WHERE DATE >= '".date('Y-m-d')."'")->fetch();
$count = $resource['count'];
if(empty($list))$json = json_encode(array('status'=>0));
else {
    $out['status'] = 1;
    $out['count'] = $count;
    $out['data'] = $list;
    $json = json_encode($out);
}
$mmc->set($md5sql,$json,0,3660);
echo $json;


function parseurl($res){
	switch($res['type']){
		case 'indexpage':
			$url = 'http://www.cnu.edu.cn/xzhd/'.$res['scholarid'].'.htm';
			$from = '学校首页学术活动';
			break;
		case 'ydxy':
			$url = 'http://ydxy.cnu.edu.cn/xwzx/xzhd/'.$res['scholarid'].'.htm';
			$from = '燕都学院学术活动';
			break;
		case 'zf':
			$url = 'http://zf.cnu.edu.cn/xzdt/'.$res['scholarid'].'.htm';
			$from = '政法学院学术活动';
			break;
		case 'math':
			$url = 'http://math.cnu.edu.cn/xzhd1/'.$res['scholarid'].'.htm';
			$from = '数学科学学院学术活动';
			break;
		case 'history':
			$url = 'http://history.cnu.edu.cn/a/5_'.$res['scholarid'].'.asp';
			$from = '历史学院学术活动';
			break;
		case 'chinese':
            preg_match("/主题：(.*?)[\r\n]/",$res['tittle'],$match);
            $res['tittle'] = $match[1];
			$url = 'http://chinese.cnu.edu.cn/activity/detail.php?actid='.$res['scholarid'];
			$from = '文学院学术活动';
			break;
	}
	
	return '<a href="'.$url.'">'.$res['tittle'].'</a>';
}

function curl_post($url,$data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	echo $content = curl_exec($ch);
	curl_close($ch);
	return $content;
}

function get($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_USERAGENT,"chouchang");
	$content = curl_exec($ch);
	$httpinfo = curl_getinfo($ch);
	$httperr = curl_error($ch);
	curl_close($ch);
	var_dump($content,$httperr);
}