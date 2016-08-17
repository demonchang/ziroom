<?php 

require_once(dirname(__FILE__).'/sql.php');
require_once(dirname(__FILE__).'/tcpdf_min/tcpdf.php');
require_once(dirname(__FILE__).'/tcpdf_min/fonts/helvetica.php');
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=utf-8");

$sqli = new Mysqlis();

$pdf = new TCPDF(); 
$table = 'zi_area';

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
$pdf->SetDefaultMonospacedFont('courier'); 


$pdf->AddPage();
while($row=mysqli_fetch_row($result)){ 

$txt = $row[0].'  '.$row[1].'  '.$row[4].'  '.$row[5];
//$pdf->writeHTMLCell(500, 30, 10, 10, $html);
$pdf->MultiCell(180, 0 ,$txt, 1);
}
$pdf->lastPage();
$pdf->Output($table.'.pdf', 'D');




function export_data($filename, $data, $ext) { 
	$filename = $filename.'.'.$ext; //设置文件名 
    header("Content-type:text/".$ext); 
    header("Content-Disposition:attachment;filename=".$filename); 
    echo $data; 
} 


 ?>