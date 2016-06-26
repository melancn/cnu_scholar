<?php

if($_GET['key'] != 'k')exit;
$mmc = new Memcache;
$mmc->connect('10.4.2.137', 5238);
try {
	$pdo = new PDO("mysql:host=10.4.12.173;port=3306;dbname=d05c4a8b49a9a45419d59a1d821352d21",'user','psw'); 
	$pdo->query('set names utf8');
} catch (PDOException $e) {var_dump($e->errorInfo);die;}


$res = $pdo->query("select * from `sdwb` where `status`>=1 limit 1")->fetch();
if($res === false)exit;
$post = http_build_query(array('content'=>$res['msg']));
$content = curl_post("weiboapi/mblogDeal/addAMblog",$post);;
$content1 = json_decode($content['content']);
if($content1->ok != 1){
$data = array();
$data['time'] = time();
$data['code'] = sha1('cnu.edu.cn'.$data['time']);
$data['to'] = '595312858@qq.com';
$data['subject'] = '发送微博失败';
$data['html'] = $content['content'].'<br>'.$content['error'].'<br>id:'.$res['id'].',msg:'.$res['msg'] ;
curl_post('mailapi.php',$data);
if($res['status'] >= 2)
    $pdo->query("update `sdwb` set `status`=-1 where id={$res['id']}");
elseif($res['status'] >= 1)$pdo->query("update `sdwb` set `status`=`status`+1 where id={$res['id']}");
    
}else$pdo->query("update `sdwb` set `status`=0 where id={$res['id']}");

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