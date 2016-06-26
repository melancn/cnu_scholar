<?php
if($_GET['key'] != '631f1048196052042fdffdaa9150c22b')exit;
$res = false;
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
$data['subject'] = '公告更新通知';
$date = date('Y-m-d');

$content = get('http://www.cnu.edu.cn/xyxx/xygg/index.htm');
$data['html'] = '<table style="width:100%"><thead><tr><td>标题</td><td style="width:80px">发布时间</td><td style="width:180px">来源</td></tr></thead><tbody>';

// 解析链接
preg_match_all('/<li><span>(.*?)<\/span>.*?title="(.*?)" onclick="checkXnwk\(\'(.*?)\'/', $content, $matches);
$from = '学校首页';var_dump($matches);
foreach($matches[1] as $key => $val){
	if($val == $date){
        $res = true;
        if(preg_match('/\d+\.htm/',$matches[3][$key]))$url = "http://www.cnu.edu.cn/xyxx/xygg/{$matches[3][$key]}";
        else $url = $matches[3][$key];
		$data['html'] .= '<tr><td><a href="'.$url.'">'.$matches[2][$key].'</a></td><td>'.$date.'</td><td>'.$from.'</td></tr>';

	}
}

$content = get('http://jwc.cnu.edu.cn/Jwc/List.aspx?ColumnID=6016fc056f4b479183a539e45bf8bd0b');;
preg_match_all('/<li>&bull; <a href="([\s\S]*?)"[\s\S]*?title=\'([\s\S]*?)\'[\s\S]*?class="date">(\d+-\d+-\d+)<\/span>/i', $content, $matches);
$from = '教务处';
foreach($matches[3] as $key => $val){
	if($val == $date){
        $res = true;
        $url = "http://jwc.cnu.edu.cn{$matches[1][$key]}";
		$data['html'] .= '<tr><td><a href="'.$url.'">'.$matches[2][$key].'</a></td><td>'.$date.'</td><td>'.$from.'</td></tr>';

	}
}
$content = get('http://dc.cnu.edu.cn/jwcxkw/tzgg/index.htm');
preg_match_all('/<li><span>\[(\d+-\d+-\d+)\]<\/span><a href="(\d+)\.htm".*?title="([\s\S]*?)">/i', $content, $matches);var_dump($matches);
$from = '教务';
foreach($matches[1] as $key => $val){
	if($val == $date){
        $res = true;
        $url = "http://dc.cnu.edu.cn/jwcxkw/tzgg/{$matches[2][$key]}.htm";
		$data['html'] .= '<tr><td><a href="'.$url.'">'.$matches[3][$key].'</a></td><td>'.$date.'</td><td>'.$from.'</td></tr>';
	}
}
$data['html'] .= '<tr><td><hr></td><td><hr></td><td><hr></td></tr></tbody></table>';
if($res)curl_post('mailapi.php',$data);

function curl_post($url,$data){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST,10);
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
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_USERAGENT,"chouchang");
	$content = curl_exec($ch);
	$httpinfo = curl_getinfo($ch);
	$httperr = curl_error($ch);
	curl_close($ch);
	var_dump($content,$httperr);
    return $content;
}
