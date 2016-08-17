<?php 

require_once(dirname(__FILE__).'/sql.php');
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

$sqli = new Mysqlis();
$table = 'zi_key';

$sql = 'select * from '.$table;
$column = "select column_name from information_schema.columns where table_name='".$table."'";
//数据的字段
$cloumn_row = $sqli->insertContent($column);

$result = $sqli->insertContent($sql);
//字段数组
$column_key;

while($column_name=mysqli_fetch_assoc($cloumn_row)){ 
	$column_key[] = $column_name['column_name'];
}



function getCSV($result, $column_key){
	$str = ""; 
	$column_count = count($column_key);

	while($row=mysqli_fetch_row($result)){ 
		//每行数据拼接后的数据
		$row_val;
		for ($i=0; $i < $column_count; $i++) { 
			if (preg_match("/[\x7f-\xff]/", $row[$i])) {  //判断字符串中是否有中文
				$row_val .= iconv('utf-8','gb2312',$row[$i]).',';
			} else {
				//$row[$i] = $row[$i]?$row[$i]:'null';
				$row_val .= $row[$i].',';
			}

		}
		$row_val = substr($row_val, 0, -1);
			
	    $str .= $row_val.PHP_EOL; //用引文逗号分开
	    unset($row_val); 
	}
	$result->free();
	return $str;
}

function getSQL($result, $column_key, $table, $sqli){
	$sql = 'show create table '.$table;
	$res = $sqli->insertContent($sql);
  	$res_row = mysqli_fetch_row($res);

  	$info = "-- ----------------------------\r\n";
	$info .= "-- Table structure for `".$table."`\r\n";
	$info .= "-- ----------------------------\r\n";
	$info .= "DROP TABLE IF EXISTS `".$table."`;\r\n";
	$sql_str = $info.$res_row[1].";\r\n\r\n";

	//file_put_contents($table.'sql' ,$sql_str,FILE_APPEND);

	if(mysqli_num_rows($result)<1) continue;

	while($table_row = mysqli_fetch_row($result)){
		$table_field = "INSERT INTO `".$table."` VALUES (";
		foreach($table_row as $field){
			$table_field .= "'".$field."', ";
		}
		//去掉最后一个逗号和空格
   		$table_field = substr($table_field,0,-2);
   		$table_field .= ");\r\n";
		$sql_str .= $table_field;
		unset($table_field);
	}
	$result->free();
	return $sql_str;
}

function getTXT($result, $column_key){
	$str = ""; 
	$column_count = count($column_key);

	while($row=mysqli_fetch_row($result)){ 
		//每行数据拼接后的数据
		$row_val;
		for ($i=0; $i < $column_count; $i++) { 
			if (preg_match("/[\x7f-\xff]/", $row[$i])) {  //判断字符串中是否有中文
				$row_val .= iconv('utf-8','gb2312',trim($row[$i])).' ';
			} else {
				//$row[$i] = $row[$i]?$row[$i]:'null';
				$row_val .= trim($row[$i]).' ';
			}

		}
			
	    $str .= $row_val.PHP_EOL; //用引文逗号分开
	    unset($row_val); 
	}
	$result->free();
	return $str;
}

#$data = getCSV($result, $column_key);
#$data = getSQL($result, $column_key, $table, $sqli);
$ext = 'sql';
if ($data) {
	export_data($table, $data, $ext);
}else{
	echo 'no data';
}


function export_data($filename, $data, $ext) { 
	$filename = $filename.'.'.$ext; //设置文件名 
    header("Content-type:text/".$ext); 
    header("Content-Disposition:attachment;filename=".$filename); 
    echo $data; 
} 


 ?>