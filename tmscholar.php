<?php
if($_GET['key'] != 'k')exit;
$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}

$time = time();
if(date('w') == 5){
    $t = date('Y-m-d',$time+86400)."' or `indate`='".date('Y-m-d',$time+86400*2);
    $tod = date('Y-m-d',$time+86400)."/".date('d',$time+86400*2);
}else $tod = $t = date('Y-m-d',$time+86400);
$sql = "select * from `scholar` where `date`='$t'";
$resource = $pdo->query($sql);
$data = '';
$settitle = array();
while($res = $resource->fetch()){
	$url = $c = $p = '';
	$from = '';
	switch($res['type']){
		case 'indexpage':
			$url = 'http://www.cnu.edu.cn/xzhd/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="wenzhang1">[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
			break;
		case 'ydxy':
			$url = 'http://ydxy.cnu.edu.cn/xwzx/xzhd/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="d_ct">[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
			break;
		case 'zf':
			$url = 'http://zf.cnu.edu.cn/xzdt/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="t_artic2">[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
			break;
		case 'math':
			$url = 'http://math.cnu.edu.cn/xzhd1/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="art_list">[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
			break;
		case 'history':
			$url = 'http://history.cnu.edu.cn/kxyj/xzhd/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="article">[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
            list($date,$res['tittle']) = explode('：',$res['tittle']);
			break;
		case 'chinese':
			$url = 'http://chinese.cnu.edu.cn/xzhd/'.$res['scholarid'].'.htm';
			$c = file_get_contents($url);
            preg_match('/<div class="article"[\s\S]*?<\/div>/',$c,$m);
            $p = strip_tags($m[0]);
			break;
		case 'ie':
			$url = 'http://www.ie.cnu.edu.cn/2011/index.php/Infodetail/activitycontent/id/'.$res['scholarid'];
			$from = '信工学术活动';
            $c = file_get_contents($url);
			preg_match('/(?<=<hr\/>).+(?=<div style="clear:both;">)/s',$c,$m);
            $p = strip_tags($m[0]);
			break;
	}
	if(isset($settitle[$res['tittle']]) || empty($p))continue;
    else{
		$pt = 0;
		foreach($settitle as $k => $v){
			$per = 0;
			similar_text($res['tittle'],$k,$per);
			if($per > $pt) $pt = $per;
		}
		$settitle[$res['tittle']] = 1;
		if($pt >= 65) continue;
	}
    $p = str_replace('　','',$p);
	$data .= '<h1 style="font-weight: bold;">'.$res['tittle'].'</h1><div>'.trim($p).'</div>';
}
if(!$data)exit;
$post = http_build_query(array('content'=>$data,'title'=>'#CNU讲座#'.$tod.'讲座信息'));
$content = curl_post("weiboapi/mblogDeal/create_longtext",$post);
$content1 = json_decode($content['content']);
if($content1->ok != 1){
$post = array();
$post['time'] = time();
$post['code'] = sha1('cnu.edu.cn'.$post['time']);
$post['to'] = 'mailto';
$post['subject'] = '发送微博失败';
$post['html'] = $content['content'].'<br>'.$content['error'].'<br>msg:'.$data ;
curl_post('mailapi.php',$post);
if(!$_GET['times'])get('retry/tmscholar.php?key=k&times=1');
}

function curl_post($url,$data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	echo $content = curl_exec($ch);
    var_dump($error = curl_error($ch));
    $info = curl_getinfo($ch);
	curl_close($ch);
	return array('content'=>$content,'error'=>$error.json_encode($info));
}