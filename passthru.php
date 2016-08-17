<?php

	header("Content-Disposition: attachment; filename=ziroom.sql");
	header("Content-type: text/x-csv");
	passthru("/usr/local/mysql-5.7.11-osx10.9-x86_64/bin/mysqldump -uroot  -p123456 --databases ziroom");
?>

