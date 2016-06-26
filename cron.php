<?php

if($_GET['key'] != 'k')exit;

$h = date('H');
if(in_array($h,array(20,21,22,23,0,1,2,3,4,5,6,7)))exit;

get('/indexPage.php?key=k');
//燕都学院
get('/ydxy.php?key=k');
//数科
get('/math.php?key=k');
//政法
get('/zf.php?key=k');
//chinese
get('/chinese.php?key=k');
//history
get('/history.php?key=k');
//ie
get('/ie.php?key=k');




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