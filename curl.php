<?php 

class Curl {

	public static function request($url, $method='get', $fields = array()){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}
		$result = curl_exec($ch);
		return $result;
		curl_close($ch);
	}
}
function inMonthTime($month=02, $year=2016){

	$_month['start'] = mktime(00,00,00,$month,01,$year);
	$t = date('t', $month['start']);
	$_month['end'] = mktime(23,59,59,$month,$t,$year); 
	
	return $_month;
	
}


 ?>