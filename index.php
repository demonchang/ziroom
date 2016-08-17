<?php 
require_once(dirname(__FILE__).'/curl.php');
require_once(dirname(__FILE__).'/simple_html_dom.php');
//require_once(dirname(__FILE__).'/redis.php');
require_once(dirname(__FILE__).'/sql.php');
require_once(dirname(__FILE__).'/mongo.php');

$config = include('./config.php');

$curl = new Curl();
$simple_html = new simple_html_dom();
$redis = new Rediss();
$sqli = new Mysqlis();
$mongo = new Mongod();
//hezufang
$zr_url = $config['ziroom']['url'].'/z/nl/z2.html';

#$content = $curl->request($zr_url);
#$content = file_get_contents('ziroom.html');

#$html = $simple_html->load($content);
#$link = $html->find('.zIndex6');
function getAreaName($content, $redis){
	preg_match_all('#<span class="tag"><a href="/z/nl/(d[0-9]*)?-z2.html">(.*?)</a><b></b></span>#', $content, $out);
	if ($out) {
		$out_count = count($out[0]);
		for ($i=0; $i < $out_count; $i++) { 
			$redis->set($out[1][$i], $out[2][$i]);
		}
	}

}

function areaUrls($content, $redis, $sqli, $config){
	preg_match_all('#<a href="/z/nl/(d[0-9]*)?-(.*?)" >(.*?)</a>#', $content, $urls);

	if ($urls) {
		$urls_count = count($urls[0]);
		for ($i=0; $i < $urls_count; $i++) { 
			$area = $redis->get($urls[1][$i]);
			$url = $config['ziroom']['url'].'/z/nl/'.$urls[1][$i].'-'.$urls[2][$i];
			$md5url = md5($url);
			$village = $urls[3][$i];
			$sql = "insert into zi_area(url, md5url, area, village) values('$url', '$md5url', '$area', '$village')";
			$res = $sqli->insert($md5url, 'zi_area', $sql);

			if (!$res) {
				file_put_contents('log.txt', $url.'-'.$urls[3][$i].PHP_EOL, FILE_APPEND);
			}

		}
	}else{
		echo 'no match data';
	}
}


function getVillage($sqli, $curl){
	$select = "select url from zi_area where tag=0";
	$select_res = $sqli->query($select);
	foreach ($select_res as $key => $value) {
		//var_dump($value['url']);
		getPage($sqli, $curl, $value['url']);
	}
	echo '0k'.PHP_EOL;

}


function getPage($sqli, $curl, $url){
	$content = $curl->request($url);
	preg_match('#<div class="pages" id="page">.*?<span>共(.*?)页</span><span class="marL20">#', $content, $out);
	if ($out) {
		$update = "update zi_area set page=".intval($out[1])." where md5url='".md5($url)."'";

		$res = $sqli->update($update);
	}
}

function getVilllageUrl($sqli, $curl, $config){
	$select = "select url,page from zi_area where tag=0";
	$select_res = $sqli->query($select);
	foreach ($select_res as $key => $value) {
		if ($value['page'] != null) {
			for($i=1; $i < ($value['page']+1);$i++){
				$url = $value['url'].'?p='.$i;
				$content = $curl->request($url);
				getDetailUrl($content, $config, $sqli);
			}
		}else{
			$content = $curl->request($value['url']);
			getDetailUrl($content, $config, $sqli);
		}

		$update = "update zi_area set tag=1 where md5url='".md5($value['url'])."'";
		$sqli->update($update);
		#echo 1;	
	}
	echo '0k'.PHP_EOL;
}

function getDetailUrl($content, $config, $sqli){
	preg_match_all('#<h3><a target="_blank" href="(.*?)" class="t1">(.*?)</a></h3>#', $content, $urls);
	if ($urls) {
		$urls_count = count($urls[0]);
		for ($i=0; $i < $urls_count; $i++) { 
			$url = $config['ziroom']['url'].$urls[1][$i];
			$md5url = md5($url);
			$mark = $urls[2][$i];
			$sql = "insert into zi_village(url, md5url, mark) values('$url', '$md5url', '$mark')";
			$res = $sqli->insert($md5url, 'zi_village', $sql);

			if (!$res) {
				file_put_contents('log.txt', $url.'-'.$urls[2][$i].PHP_EOL, FILE_APPEND);
			}

		}
	}else{
		echo 'no match data';
	}	
}
#update zi_area set tag=0;

function getContent($sqli, $curl, $mongo){
	$select = "select url from zi_village where tag=0";
	$select_res = $sqli->query($select);
	foreach ($select_res as $key => $value) {
		$content = $curl->request($value['url']);
		$res = getContentDetail($content, $sqli, $value['url'], $mongo);		
		if ($res) {
			$update = "update zi_village set tag=1 where md5url='".md5($value['url'])."'";
			$sqli->update($update);
		}
		echo $key/count($select_res);	
	}
	echo '0k'.PHP_EOL;

}

function getContentDetail($content, $sqli, $url, $mongo){
	preg_match('#<div class="room_detail_right">[\s]*?<div class="room_name">
[\s]*?<h2>[\s]*([\S]*?)[0-9][\s\S]*</h2>[\s\S]*?<p class="pr">[\s\S]*?<span class="room_price">￥(.*?)</span>[\s\S]*?<ul class="detail_room">[\s]*<li><b></b>面积： (.*?)㎡</li>[\s]*<!--[\s\S]*?-->[\s]*<li><b></b>朝向：(.*?)</li>[\s]*<li><b></b>户型： (.*?)室(.*?)厅[\s\S]*?<li><b></b>楼层： (.*?)</li>[\s]*<li class="last">#', $content, $out);

	preg_match('#<div class="node_infor area">[\s]*<a href="/">首页</a>[\s]*&gt;[\s]*?<a href="/z/nl/sub/">(.*?)合租</a>[\s]*&gt;[\s]*?<a href="[\S]*?">(.*?)公寓出租</a>[\s]*&gt;[\s]*?<a href="javascript:;">(.*?)租房信息</a>[\s]*</div>#', $content, $address);
	//var_dump($out, $address);exit();
	
	if ($out && $address) {
		// $sql = "insert into zi_key(area, village, town, price, acreage, direction, room, living_room, floor) values('$address[1]', '$address[2]', '$address[3]', '$out[2]', '$out[3]', '$out[4]', '$out[5]', '$out[6]', '$out[7]')";
		// $res = $sqli->insertContent($sql);
		// if (!$res) {
		// 	return false;
		// }
		
		$keys = array(
			'area' => $address[1],
			'village'=> $address[2],
			'town' => $address[3],
			'price' => $out[2],
			'acreage' => $out[3],
			'direction' => $out[4],
			'room' => $out[5],
			'living_room' => $out[6],
			'floor' => $out[7],
			);

		$mongo->insert($keys);


		// echo 'ok';
		return true;
	}else{
		file_put_contents('logContent2.txt', $url.PHP_EOL, FILE_APPEND);
		return false;	
	}
}

$content = file_get_contents('logContent.txt');
$arr = explode(PHP_EOL, $content);
$arr_count = count($arr);
//var_dump($arr_count);
foreach ($arr as $key => $value) {
	if ($value) {
		$content = $curl->request($value);
		getContentDetail($content, $sqli, $url, $mongo);
		echo $key;
	}
	if ($key > 10) {
		break;
	}
	
}
 // $content = $curl->request('http://sh.ziroom.com/z/vr/20004264.html');
 // getContentDetail($content, $sqli, $url);

?>