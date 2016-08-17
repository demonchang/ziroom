<?php 

class Mysqlis{
	private static $conn;

	public function __construct($host='127.0.0.1', $user='root', $password='123456', $dbname='ziroom'){
		$connection = new mysqli($host, $user, $password, $dbname);
		if(mysqli_connect_errno()){
		 	return mysqli_connect_error();
		}else{
			$connection->set_charset('utf8');
			self::$conn = $connection;
		}
	}


	public function insert($md5url, $tablename, $sql){
		$query = "select * from ".$tablename." where md5url='".$md5url."'";
		$query_res = $this->query($query);
		if (!$query_res) {
			
			return self::$conn->query($sql);
		}
		
	}

	public function query($sql){
		$res = [];
		$result = self::$conn->query($sql);
		while ($row = mysqli_fetch_assoc($result)){
		    $res[] = $row;
		}
		return $res;
	}
	public function update($sql){
		return  self::$conn->query($sql);
		
		 
	}

	public function insertContent($sql){
		return  self::$conn->query($sql);
	}



	public function  __destruct() {    
        self::$conn->close();  
    }
}



 ?>