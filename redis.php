<?php 
class Rediss{
	private static $redis;
	public function __construct(){

		$redis = new Redis();
		$redis->connect('127.0.0.1', 6379);
		self::$redis = $redis;
	}

	public function set($key, $value){
		self::$redis->set($key, $value);
	}

	public function get($key){
		return self::$redis->get($key);
	}

}




 ?>