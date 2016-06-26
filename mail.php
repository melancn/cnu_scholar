<?php

if($_GET['key'] != 'k')exit;
$path = '/var/vcap/store/80a68911-df49-41fd-9d7a-8cef69efcf77';
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}

$data = array();
$data['time'] = time();
$data['code'] = sha1('cnu.edu.cn'.$data['time']);
$sql = "select * from `mail_account` where `type`='1'";
$resource = $pdo->query($sql);
$to = array();
while($res = $resource->fetch()){
    $to[] = $res['address'];
}
$data['to'] = implode(',',$to);
$data['subject'] = '讲座更新通知';
$sql = "select * from `scholar` where `indate`='".date('Y-m-d')."'";
file_put_contents($path.'/scholar_mail.log',$sql."\n",FILE_APPEND);
$resource = $pdo->query($sql);
$data['html'] = '<table style="width:100%"><thead><tr><td>标题</td><td style="width:80px">讲座时间（仅来源首页较准确）</td><td style="width:180px">来源</td><td style="width:80px">更新时间</td></tr></thead><tbody>';
while($res = $resource->fetch()){
	file_put_contents($path.'/scholar_mail.log',var_export($res,true)."\n",FILE_APPEND);
	$url = '';
	$from = '';
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
			$url = 'http://history.cnu.edu.cn/kxyj/xzhd/'.$res['scholarid'].'.htm';
			$from = '历史学院学术活动';
			break;
		case 'chinese':
			$url = 'http://chinese.cnu.edu.cn/xzhd/'.$res['scholarid'].'.htm';
			$from = '文学院学术活动';
			break;
		case 'ie':
			$url = 'http://www.ie.cnu.edu.cn/2011/index.php/Infodetail/activitycontent/id/'.$res['scholarid'];
			$from = '信工学术活动';
			break;
	}
	
	$data['html'] .= '<tr><td><a href="'.$url.'">'.$res['tittle'].'</a></td><td>'.$res['date'].'</td><td>'.$from.'</td><td>'.$res['indate'].'</td></tr>';
}
$data['html'] .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr></tbody></table>';
if(date('w') == 5)$t = date('Y-m-d',$data['time']+86400)."' or `indate`='".date('Y-m-d',$data['time']+86400*2);
else $t = date('Y-m-d',$data['time']+86400);
$sql = "select * from `scholar` where `date`='$t'";
file_put_contents($path.'/scholar_mail.log',$sql."\n",FILE_APPEND);
$resource = $pdo->query($sql);
$data['html'] .= '<table style="width:100%"><thead><tr><td>标题</td><td style="width:80px">讲座时间（仅来源首页较准确）</td><td style="width:180px">来源</td><td style="width:80px">更新时间</td></tr></thead><tbody>';
while($res = $resource->fetch()){
	file_put_contents($path.'/scholar_mail.log',var_export($res,true)."\n",FILE_APPEND);
	$url = '';
	$from = '';
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
			$url = 'http://history.cnu.edu.cn/kxyj/xzhd/'.$res['scholarid'].'.htm';
			$from = '历史学院学术活动';
			break;
		case 'chinese':
			$url = 'http://chinese.cnu.edu.cn/xzhd/'.$res['scholarid'].'.htm';
			$from = '文学院学术活动';
			break;
		case 'ie':
			$url = 'http://www.ie.cnu.edu.cn/2011/index.php/Infodetail/activitycontent/id/'.$res['scholarid'];
			$from = '信工学术活动';
			break;
	}
	
	$data['html'] .= '<tr><td><a href="'.$url.'">'.$res['tittle'].'</a></td><td>'.$res['date'].'</td><td>'.$from.'</td><td>'.$res['indate'].'</td></tr>';
}
$data['html'] .= '<tr><td><hr></td><td><hr></td><td><hr></td><td><hr></td></tr></tbody></table>';
curl_post('mailapi',$data);
get('weiboapi/tmscholar.php?key=k');
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