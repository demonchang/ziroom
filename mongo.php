<?php 
require_once(dirname(__FILE__).'/sql.php');
class Mongod{
	private static $keys;
	function __construct(){
		$m = new MongoClient();
		$db = $m->ziroom;
		$keys = $db->keys_test;
		self::$keys = $keys;
	}

	function insert($arr){
		self::$keys->insert($arr);
	}

} 

// $sqli = new Mysqlis();
// $mong = new Mongod();
// $select = "select * from zi_key";
// $select_res = $sqli->query($select);
// foreach ($select_res as $key => $value) {

// 	$mong->insert($value);
// }

// echo 'ok';

 ?>